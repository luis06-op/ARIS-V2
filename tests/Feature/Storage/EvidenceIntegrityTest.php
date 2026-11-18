<?php

namespace Tests\Feature\Storage;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Institution;
use App\Models\User;
use App\Services\Storage\EvidenceIntegrity;
use App\Services\Storage\EvidenceStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class EvidenceIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_integrity_command_verifies_bytes_and_never_changes_metadata_or_files(): void
    {
        $version = $this->version();
        $metadata = $version->blob->toArray();
        $bytes = Storage::disk('local')->get($version->blob->storage_key);
        $this->artisan('aris:verificar-integridad')->expectsOutput('Comprobación de solo lectura: 1 blobs disponibles, 0 incidentes.')->assertSuccessful();
        $this->assertSame($metadata, $version->blob->fresh()->toArray());
        $this->assertSame($bytes, Storage::disk('local')->get($version->blob->storage_key));
    }

    public function test_same_length_corruption_is_detected_without_deleting_or_reclassifying_the_file(): void
    {
        $version = $this->version();
        $bytes = str_repeat('x', (int) $version->size_bytes);
        Storage::disk('local')->put($version->blob->storage_key, $bytes);
        $this->artisan('aris:verificar-integridad')->assertFailed();
        $this->assertSame('disponible', $version->blob->fresh()->status);
        $this->assertSame($bytes, Storage::disk('local')->get($version->blob->storage_key));
        $this->expectException(NotFoundHttpException::class);
        app(EvidenceStorage::class)->download($version);
    }

    public function test_truncated_and_missing_files_are_rejected(): void
    {
        $version = $this->version();
        Storage::disk('local')->put($version->blob->storage_key, '%PDF-');
        $this->assertFalse(app(EvidenceIntegrity::class)->openVersion($version));
        Storage::disk('local')->delete($version->blob->storage_key);
        $this->assertFalse(app(EvidenceIntegrity::class)->openVersion($version));
        $this->artisan('aris:verificar-integridad')->assertFailed();
    }

    public function test_version_metadata_mismatch_is_rejected_even_when_blob_bytes_are_valid(): void
    {
        $version = $this->version();
        $version->update(['mime' => 'image/png']);
        $this->artisan('aris:verificar-integridad')->assertFailed();
        $this->expectException(NotFoundHttpException::class);
        app(EvidenceStorage::class)->download($version);
    }

    public function test_response_serves_verified_snapshot_when_original_changes_before_streaming(): void
    {
        $version = $this->version();
        $original = Storage::disk('local')->get($version->blob->storage_key);
        $response = app(EvidenceStorage::class)->download($version);
        Storage::disk('local')->put($version->blob->storage_key, 'Contenido sustituido tras verificar');
        ob_start();
        $response->sendContent();
        $bytes = ob_get_clean();
        $this->assertSame($original, $bytes);
        $this->assertSame((string) strlen($original), $response->headers->get('Content-Length'));
    }

    public function test_traversal_symlinks_and_unapproved_disks_are_rejected(): void
    {
        $version = $this->version();
        $original = $version->blob->storage_key;
        $path = Storage::disk('local')->path('documents/enlace.pdf');
        $this->assertTrue(symlink(Storage::disk('local')->path($original), $path));
        $version->blob->update(['storage_key' => 'documents/enlace.pdf']);
        $this->assertFalse(app(EvidenceIntegrity::class)->openVersion($version));
        unlink($path);
        $version->blob->update(['storage_key' => 'documents/../'.$original]);
        $this->assertFalse(app(EvidenceIntegrity::class)->openVersion($version));
        $version->blob->update(['storage_key' => $original, 'disk' => 'public']);
        $this->assertFalse(app(EvidenceIntegrity::class)->openVersion($version));
    }

    public function test_cached_blob_relation_does_not_override_current_quarantine_status(): void
    {
        $version = $this->version();
        $version->load('blob');
        $version->blob()->update(['status' => 'cuarentena']);
        $this->assertSame('disponible', $version->blob->status);
        $this->assertFalse(app(EvidenceIntegrity::class)->openVersion($version));
    }

    private function version(): DocumentVersion
    {
        $user = User::factory()->create();
        $document = Document::create(['institution_id' => Institution::create(['name' => 'Institución de prueba'])->id, 'created_by' => $user->id]);

        return app(EvidenceStorage::class)->attach($document, UploadedFile::fake()->createWithContent('prueba.pdf', "%PDF-1.4\ncontenido de prueba\n%%EOF"), $user);
    }
}
