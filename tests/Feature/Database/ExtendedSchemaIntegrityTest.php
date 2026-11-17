<?php

namespace Tests\Feature\Database;

use App\Models\Activity;
use App\Models\ActivityAssignment;
use App\Models\ActivityVersion;
use App\Models\AuditEvent;
use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\FileBlob;
use App\Models\Institution;
use App\Models\Project;
use App\Models\Publication;
use App\Models\PublicationEvent;
use App\Models\ReviewRound;
use App\Models\Submission;
use App\Models\SubmissionVersion;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Integridad del esquema completo de F1 sobre SQLite de pruebas.
 * Las restricciones CHECK «exactamente uno de…» se validan en la capa de dominio (ADR-009):
 * Laravel no las expresa en migraciones y SQLite no permite añadirlas con ALTER TABLE.
 */
class ExtendedSchemaIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_esquema_contiene_las_catorce_tablas_restantes(): void
    {
        $tablas = [
            'activities', 'activity_versions', 'activity_assignments',
            'submissions', 'submission_versions', 'file_blobs',
            'documents', 'document_versions', 'document_links',
            'review_rounds', 'review_comments',
            'publications', 'publication_events', 'audit_events',
        ];

        foreach ($tablas as $tabla) {
            $this->assertTrue(Schema::hasTable($tabla), "Falta la tabla {$tabla}");
        }
    }

    public function test_no_pueden_existir_dos_versiones_de_guia_con_el_mismo_numero(): void
    {
        $usuario = $this->usuario();
        $version = $this->versionGuia($this->actividad($usuario, $usuario), $usuario, 1);

        $this->assertSame(1, $version->version_number);

        $this->expectException(QueryException::class);
        $this->versionGuia($version->activity, $usuario, 1);
    }

    public function test_no_pueden_existir_dos_versiones_de_entrega_con_el_mismo_numero(): void
    {
        $usuario = $this->usuario();
        $entrega = $this->entrega($usuario);
        $this->versionEntrega($entrega, $usuario, 1);

        $this->expectException(QueryException::class);
        $this->versionEntrega($entrega, $usuario, 1);
    }

    public function test_no_pueden_existir_dos_versiones_de_documento_con_el_mismo_numero(): void
    {
        $usuario = $this->usuario();
        $documento = $this->documento($usuario);
        $this->versionDocumento($documento, $usuario, 1);

        $this->expectException(QueryException::class);
        $this->versionDocumento($documento, $usuario, 1);
    }

    public function test_una_asignacion_no_se_repite_para_el_mismo_destinatario(): void
    {
        $usuario = $this->usuario();
        $proyecto = $this->proyecto($usuario);
        $version = $this->versionGuia($this->actividad($proyecto, $usuario), $usuario);

        $datos = [
            'activity_version_id' => $version->id,
            'project_id' => $proyecto->id,
            'target_type' => 'usuario',
            'target_ref' => (string) $usuario->id,
            'target_user_id' => $usuario->id,
            'assigned_by' => $usuario->id,
        ];

        ActivityAssignment::create($datos);

        $this->expectException(QueryException::class);
        ActivityAssignment::create($datos);
    }

    public function test_un_usuario_con_evidencia_no_puede_borrarse(): void
    {
        $usuario = $this->usuario();
        $this->versionGuia($this->actividad($usuario, $usuario), $usuario);

        $borrado = false;

        try {
            $usuario->delete();
        } catch (QueryException) {
            $borrado = true;
        }

        $this->assertTrue($borrado, 'El borrado del autor de una versión debe estar restringido.');
        $this->assertNotNull(User::find($usuario->id));
    }

    public function test_borrar_un_proyecto_con_entregas_esta_restringido(): void
    {
        $usuario = $this->usuario();
        $proyecto = $this->proyecto($usuario);
        $this->entrega($usuario, $proyecto);

        $borrado = false;

        try {
            $proyecto->delete();
        } catch (QueryException) {
            $borrado = true;
        }

        $this->assertTrue($borrado, 'El borrado de un proyecto con historial debe estar restringido.');
        $this->assertNotNull(Project::find($proyecto->id));
    }

    public function test_una_ronda_de_revision_es_unica_por_version_evaluador_y_numero(): void
    {
        $autor = $this->usuario();
        $evaluador = $this->usuario('evaluador@example.test');
        $version = $this->versionEntrega($this->entrega($autor), $autor);

        ReviewRound::create([
            'submission_version_id' => $version->id,
            'evaluator_id' => $evaluador->id,
            'round_number' => 1,
        ]);

        $this->expectException(QueryException::class);
        ReviewRound::create([
            'submission_version_id' => $version->id,
            'evaluator_id' => $evaluador->id,
            'round_number' => 1,
        ]);
    }

    public function test_la_ultima_version_se_calcula_por_numero_y_no_por_un_indicador_mutable(): void
    {
        $usuario = $this->usuario();
        $entrega = $this->entrega($usuario);

        foreach ([1, 3, 2] as $numero) {
            $this->versionEntrega($entrega, $usuario, $numero);
        }

        $ultima = $entrega->versions()->orderByDesc('version_number')->first();

        $this->assertSame(3, $ultima->version_number);
        $this->assertFalse(Schema::hasColumn('submission_versions', 'is_latest'));
    }

    public function test_los_estados_por_defecto_son_conservadores(): void
    {
        $usuario = $this->usuario();
        $institucion = Institution::create(['name' => 'Institución de prueba']);

        $blob = FileBlob::create([
            'uuid' => '11111111-1111-4111-8111-111111111111',
            'storage_key' => 'privado/11111111-1111-4111-8111-111111111111.pdf',
            'size_bytes' => 1024,
            'sha256' => str_repeat('a', 64),
            'mime' => 'application/pdf',
            'extension' => 'pdf',
        ]);
        $documento = Document::create([
            'institution_id' => $institucion->id,
            'project_id' => $this->proyecto($usuario, $institucion)->id,
            'created_by' => $usuario->id,
        ]);
        $version = $this->versionDocumento($documento, $usuario, 1, $blob);
        $publicacion = Publication::create([
            'document_version_id' => $version->id,
            'requested_by' => $usuario->id,
            'scope' => 'institucional',
        ]);

        $this->assertSame('cuarentena', $blob->fresh()->status);
        $this->assertSame('privado', $documento->fresh()->classification);
        $this->assertSame('borrador', $version->fresh()->status);
        $this->assertSame('pendiente', $publicacion->fresh()->status);
    }

    public function test_la_auditoria_guarda_actor_accion_recurso_y_resultado_sin_secretos(): void
    {
        $usuario = $this->usuario();
        $institucion = Institution::create(['name' => 'Institución de prueba']);

        $evento = AuditEvent::create([
            'institution_id' => $institucion->id,
            'actor_id' => $usuario->id,
            'action' => 'document.download',
            'resource_type' => 'document_version',
            'resource_id' => '7',
            'result' => 'exito',
            'metadata' => ['motivo' => 'consulta'],
        ]);

        $this->assertDatabaseHas('audit_events', [
            'id' => $evento->id,
            'action' => 'document.download',
            'result' => 'exito',
        ]);
        $this->assertFalse(Schema::hasColumn('audit_events', 'password'));
        $this->assertFalse(Schema::hasColumn('audit_events', 'token'));
        $this->assertNotNull($evento->fresh()->occurred_at);
    }

    public function test_revocar_una_publicacion_conserva_el_historial_y_la_evidencia(): void
    {
        $usuario = $this->usuario();
        $version = $this->versionDocumento($this->documento($usuario), $usuario);
        $publicacion = Publication::create([
            'document_version_id' => $version->id,
            'requested_by' => $usuario->id,
            'scope' => 'publico',
            'status' => 'revocada',
            'revoked_at' => now(),
            'revocation_reason' => 'Solicitud del autor',
        ]);
        PublicationEvent::create([
            'publication_id' => $publicacion->id,
            'event' => 'revocada',
            'actor_id' => $usuario->id,
            'reason' => 'Solicitud del autor',
        ]);

        $this->assertSame('revocada', $publicacion->fresh()->status);
        $this->assertSame('revocada', $publicacion->events()->first()->event);
        $this->assertNotNull(DocumentVersion::find($version->id));
    }

    private function usuario(string $correo = 'autor@example.test'): User
    {
        return User::factory()->create(['email' => $correo]);
    }

    private function proyecto(User $usuario, ?Institution $institucion = null): Project
    {
        return Project::create([
            'institution_id' => ($institucion ?? Institution::create(['name' => 'Institución de prueba']))->id,
            'code' => 'PROY-'.Project::count(),
            'slug' => 'proyecto-'.Project::count(),
            'title' => 'Proyecto de prueba',
            'owner_id' => $usuario->id,
        ]);
    }

    private function actividad(Project|User $proyectoOUusuario, User $usuario): Activity
    {
        $proyecto = $proyectoOUusuario instanceof Project
            ? $proyectoOUusuario
            : $this->proyecto($proyectoOUusuario);

        return Activity::create([
            'project_id' => $proyecto->id,
            'code' => 'ACT-'.Activity::count(),
            'title' => 'Actividad de prueba',
            'created_by' => $usuario->id,
        ]);
    }

    private function versionGuia(Activity $actividad, User $usuario, int $numero = 1): ActivityVersion
    {
        return ActivityVersion::create([
            'activity_id' => $actividad->id,
            'version_number' => $numero,
            'title' => 'Guía de prueba',
            'content' => 'Contenido de la guía',
            'author_id' => $usuario->id,
            'checksum' => str_repeat('b', 64),
        ]);
    }

    private function entrega(User $usuario, ?Project $proyecto = null): Submission
    {
        return Submission::create([
            'project_id' => ($proyecto ?? $this->proyecto($usuario))->id,
            'author_id' => $usuario->id,
            'title' => 'Entrega de prueba',
        ]);
    }

    private function versionEntrega(Submission $entrega, User $usuario, int $numero = 1): SubmissionVersion
    {
        return SubmissionVersion::create([
            'submission_id' => $entrega->id,
            'version_number' => $numero,
            'author_id' => $usuario->id,
            'summary' => 'Versión de prueba',
        ]);
    }

    private function documento(User $usuario): Document
    {
        return Document::create([
            'institution_id' => Institution::create(['name' => 'Institución de prueba'])->id,
            'project_id' => $this->proyecto($usuario)->id,
            'created_by' => $usuario->id,
        ]);
    }

    private function versionDocumento(
        Document $documento,
        User $usuario,
        int $numero = 1,
        ?FileBlob $blob = null,
    ): DocumentVersion {
        $blob ??= FileBlob::create([
            'uuid' => fake()->uuid(),
            'storage_key' => 'privado/'.fake()->uuid().'.pdf',
            'size_bytes' => 2048,
            'sha256' => str_repeat('c', 64),
            'mime' => 'application/pdf',
            'extension' => 'pdf',
        ]);

        return DocumentVersion::create([
            'document_id' => $documento->id,
            'version_number' => $numero,
            'blob_id' => $blob->id,
            'author_id' => $usuario->id,
            'original_name' => 'evidencia.pdf',
            'mime' => 'application/pdf',
            'extension' => 'pdf',
            'size_bytes' => 2048,
            'sha256' => str_repeat('c', 64),
        ]);
    }
}
