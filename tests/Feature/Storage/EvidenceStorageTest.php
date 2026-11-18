<?php

namespace Tests\Feature\Storage;

use App\Console\Commands\VerifyPrivateStorage;
use App\Exceptions\InvalidEvidence;
use App\Models\Document;
use App\Models\FileBlob;
use App\Models\Institution;
use App\Models\User;
use App\Services\Storage\EvidenceStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class EvidenceStorageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
    }

    public function test_una_evidencia_valida_se_guarda_fuera_de_public_y_queda_registrada(): void
    {
        $documento = $this->documento();
        $contenido = "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n%%EOF";

        $version = $this->servicio()->attach(
            $documento,
            $this->archivo('evidencia.pdf', $contenido),
            $documento->creator
        );

        $blob = $version->blob;

        $this->assertSame(1, $version->version_number);
        $this->assertSame('disponible', $blob->status);
        $this->assertStringStartsWith('documents/', $blob->storage_key);
        $this->assertTrue(Storage::disk('local')->exists($blob->storage_key));
        $this->assertSame(hash('sha256', $contenido), hash_file('sha256', Storage::disk('local')->path($blob->storage_key)));
        $this->assertSame('evidencia.pdf', $version->original_name);
        $this->assertSame('document.upload', $version->document->institution->auditEvents()->first()->action);
        $this->assertFalse(config('filesystems.disks.local.serve'));
        $this->assertFalse(file_exists(public_path('storage')));
    }

    public function test_un_contenido_que_no_es_pdf_se_rechaza_sin_dejar_metadato(): void
    {
        $documento = $this->documento();

        try {
            $this->servicio()->attach($documento, $this->archivo('falso.pdf', 'esto no es un pdf'), $documento->creator);
            $this->fail('El MIME falso debió rechazarse.');
        } catch (InvalidEvidence) {
            $this->assertSame(0, Document::first()->versions()->count());
            $this->assertSame(0, FileBlob::count());
            $this->assertSame([], Storage::disk('local')->files('quarantine'));
            $this->assertSame([], Storage::disk('local')->files('documents'));
        }
    }

    public function test_un_formato_no_permitido_se_rechaza(): void
    {
        $documento = $this->documento();

        $this->expectException(InvalidEvidence::class);
        $this->expectExceptionMessage('formato');

        $this->servicio()->attach($documento, $this->archivo('ejecutable.exe', 'MZ'), $documento->creator);
    }

    public function test_un_archivo_vacio_se_rechaza(): void
    {
        $documento = $this->documento();

        $this->expectException(InvalidEvidence::class);
        $this->expectExceptionMessage('vacío');

        $this->servicio()->attach($documento, $this->archivo('vacio.pdf', ''), $documento->creator);
    }

    public function test_el_tamano_maximo_se_lee_de_la_configuracion(): void
    {
        config()->set('aris.files.max_bytes', 16);
        $documento = $this->documento();

        $this->expectException(InvalidEvidence::class);
        $this->expectExceptionMessage('tamaño máximo');

        $this->servicio()->attach(
            $documento,
            $this->archivo('grande.pdf', "%PDF-1.4\n".str_repeat('x', 200)),
            $documento->creator
        );
    }

    public function test_un_nombre_con_ruta_se_sanea_y_nunca_se_usa_como_ruta(): void
    {
        $documento = $this->documento();

        $version = $this->servicio()->attach(
            $documento,
            $this->archivo('../../malicioso.pdf', "%PDF-1.4\n%%EOF"),
            $documento->creator
        );

        $this->assertStringNotContainsString('..', $version->original_name);
        $this->assertStringNotContainsString('/', $version->original_name);
        $this->assertStringNotContainsString('..', $version->blob->storage_key);
        $this->assertStringStartsWith('documents/', $version->blob->storage_key);
    }

    public function test_un_vinculo_debe_apuntar_a_un_solo_destino(): void
    {
        $documento = $this->documento();

        $this->expectException(InvalidEvidence::class);
        $this->expectExceptionMessage('un solo destino');

        $this->servicio()->attach(
            $documento,
            $this->archivo('evidencia.pdf', "%PDF-1.4\n%%EOF"),
            $documento->creator,
            ['activity_version_id' => 1, 'review_round_id' => 2]
        );
    }

    public function test_la_descarga_devuelve_los_bytes_y_cabeceras_seguras(): void
    {
        $documento = $this->documento();
        $contenido = "%PDF-1.4\ncontenido privado\n%%EOF";

        $version = $this->servicio()->attach(
            $documento,
            $this->archivo('evidencia.pdf', $contenido),
            $documento->creator
        );

        $respuesta = $this->servicio()->download($version);

        ob_start();
        $respuesta->sendContent();
        $cuerpo = ob_get_clean();

        $this->assertSame($contenido, $cuerpo);
        $this->assertSame('application/pdf', $respuesta->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename="evidencia.pdf"', (string) $respuesta->headers->get('Content-Disposition'));
        $this->assertSame('nosniff', $respuesta->headers->get('X-Content-Type-Options'));
        $this->assertStringContainsString('no-store', (string) $respuesta->headers->get('Cache-Control'));
        $this->assertStringContainsString('private', (string) $respuesta->headers->get('Cache-Control'));
    }

    public function test_la_descarga_sin_archivo_no_revela_la_existencia_del_blob(): void
    {
        $documento = $this->documento();
        $version = $this->servicio()->attach(
            $documento,
            $this->archivo('evidencia.pdf', "%PDF-1.4\n%%EOF"),
            $documento->creator
        );

        Storage::disk('local')->delete($version->blob->storage_key);

        $this->expectException(NotFoundHttpException::class);

        $this->servicio()->download($version);
    }

    public function test_el_reconciliador_marca_incidentes_y_mueve_huerfanos_sin_borrar(): void
    {
        $blob = FileBlob::create([
            'uuid' => '11111111-1111-4111-8111-111111111111',
            'disk' => 'local',
            'storage_key' => 'documents/inexistente.pdf',
            'size_bytes' => 100,
            'sha256' => str_repeat('a', 64),
            'mime' => 'application/pdf',
            'extension' => 'pdf',
            'status' => 'disponible',
        ]);
        Storage::disk('local')->put('documents/sin-blob.pdf', '%PDF-1.4');

        Artisan::call(VerifyPrivateStorage::class);

        $this->assertSame('huerfano', $blob->fresh()->status);
        $this->assertFalse(Storage::disk('local')->exists('documents/sin-blob.pdf'));
        $this->assertTrue(Storage::disk('local')->exists('quarantine/sin-blob.pdf'));
    }

    public function test_el_dry_run_no_modifica_estados_ni_archivos(): void
    {
        $blob = FileBlob::create([
            'uuid' => '22222222-2222-4222-8222-222222222222',
            'disk' => 'local',
            'storage_key' => 'documents/inexistente.pdf',
            'size_bytes' => 100,
            'sha256' => str_repeat('b', 64),
            'mime' => 'application/pdf',
            'extension' => 'pdf',
            'status' => 'disponible',
        ]);
        Storage::disk('local')->put('documents/sin-blob.pdf', '%PDF-1.4');

        Artisan::call(VerifyPrivateStorage::class, ['--dry-run' => true]);

        $this->assertSame('disponible', $blob->fresh()->status);
        $this->assertTrue(Storage::disk('local')->exists('documents/sin-blob.pdf'));
    }

    private function servicio(): EvidenceStorage
    {
        return app(EvidenceStorage::class);
    }

    private function documento(): Document
    {
        $usuario = User::factory()->create();

        return Document::create([
            'institution_id' => Institution::create(['name' => 'Institución de prueba'])->id,
            'project_id' => null,
            'created_by' => $usuario->id,
        ]);
    }

    private function archivo(string $nombre, string $contenido): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($nombre, $contenido);
    }
}
