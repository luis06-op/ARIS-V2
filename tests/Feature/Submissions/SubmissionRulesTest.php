<?php

namespace Tests\Feature\Submissions;

use App\Actions\Guides\UpdateGuideVersion;
use App\Actions\Submissions\CreateSubmission;
use App\Domain\Submissions\SubmissionLink;
use App\Exceptions\DomainRuleViolation;
use App\Models\Activity;
use App\Models\ActivityAssignment;
use App\Models\ActivityVersion;
use App\Models\AuditEvent;
use App\Models\Institution;
use App\Models\InstitutionMembership;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * PI-001 aprobada: entrega vinculada a versión concreta de guía, con excepción auditada.
 * La autoridad para aprobar la excepción aplica PI-002 aprobada y se verifica mediante Gate.
 */
class SubmissionRulesTest extends TestCase
{
    use RefreshDatabase;

    public function test_una_entrega_queda_vinculada_a_la_version_publicada_de_la_guia(): void
    {
        $autor = $this->usuario();
        $proyecto = $this->proyecto($autor);
        $version = $this->versionGuia($proyecto, $autor, 'publicada');
        $asignacion = $this->asignacion($version, $proyecto, $autor);

        $entrega = $this->accion()->execute([
            'project_id' => $proyecto->id,
            'author_id' => $autor->id,
            'title' => 'Entrega contra la guía',
            'activity_assignment_id' => $asignacion->id,
        ]);

        $this->assertSame($asignacion->id, $entrega->activity_assignment_id);
        $this->assertSame('borrador', $entrega->fresh()->status);
        $this->assertSame($version->id, $asignacion->activityVersion->id);
        $this->assertSame(0, AuditEvent::count());
    }

    public function test_se_rechaza_una_asignacion_de_otro_proyecto(): void
    {
        $autor = $this->usuario();
        $destino = $this->proyecto($autor);
        $otro = $this->proyecto($autor);
        $version = $this->versionGuia($otro, $autor, 'publicada');
        $asignacion = $this->asignacion($version, $otro, $autor);

        $this->expectException(DomainRuleViolation::class);
        $this->expectExceptionMessage('otro proyecto');

        $this->accion()->execute([
            'project_id' => $destino->id,
            'author_id' => $autor->id,
            'title' => 'Entrega cruzada',
            'activity_assignment_id' => $asignacion->id,
        ]);
    }

    public function test_se_rechaza_una_asignacion_revocada(): void
    {
        $autor = $this->usuario();
        $proyecto = $this->proyecto($autor);
        $version = $this->versionGuia($proyecto, $autor, 'publicada');
        $asignacion = $this->asignacion($version, $proyecto, $autor, 'revocada');

        $this->expectException(DomainRuleViolation::class);
        $this->expectExceptionMessage('revocada');

        $this->accion()->execute([
            'project_id' => $proyecto->id,
            'author_id' => $autor->id,
            'title' => 'Entrega con guía revocada',
            'activity_assignment_id' => $asignacion->id,
        ]);
    }

    public function test_se_rechaza_una_guia_no_publicada(): void
    {
        $autor = $this->usuario();
        $proyecto = $this->proyecto($autor);
        $version = $this->versionGuia($proyecto, $autor, 'borrador');
        $asignacion = $this->asignacion($version, $proyecto, $autor);

        $this->expectException(DomainRuleViolation::class);
        $this->expectExceptionMessage('no está publicada');

        $this->accion()->execute([
            'project_id' => $proyecto->id,
            'author_id' => $autor->id,
            'title' => 'Entrega con guía en borrador',
            'activity_assignment_id' => $asignacion->id,
        ]);
    }

    public function test_sin_asignacion_hace_falta_autorizacion_y_la_excepcion_se_audita(): void
    {
        $autor = $this->usuario();
        $autorizador = $this->usuario('autorizador@example.test');
        $proyecto = $this->proyecto($autor);

        $datos = [
            'project_id' => $proyecto->id,
            'author_id' => $autor->id,
            'title' => 'Entrega extraordinaria',
        ];

        try {
            $this->accion()->execute($datos);
            $this->fail('La entrega sin asignación debió rechazarse.');
        } catch (DomainRuleViolation) {
            $this->assertSame(0, AuditEvent::count());
        }

        $membership = InstitutionMembership::create(['institution_id' => $proyecto->institution_id, 'user_id' => $autorizador->id, 'joined_at' => today()]);
        $membership->roles()->attach(Role::create(['code' => 'administrador', 'name' => 'Administrador']));

        $entrega = $this->accion()->execute($datos + [
            'motivo_excepcion' => 'Convocatoria especial aprobada por coordinación',
        ], $autorizador, '127.0.0.1');

        $this->assertNull($entrega->activity_assignment_id);

        $evento = AuditEvent::firstOrFail();

        $this->assertSame('submission.unlinked_exception', $evento->action);
        $this->assertSame($autorizador->id, $evento->actor_id);
        $this->assertSame('Convocatoria especial aprobada por coordinación', $evento->metadata['motivo']);
        $this->assertSame('127.0.0.1', $evento->metadata['ip']);
        $this->assertSame($proyecto->id, $evento->metadata['proyecto']);
        $this->assertNotNull($evento->request_id);
        $this->assertNotNull($evento->occurred_at);
        $this->assertSame([], array_intersect_key($evento->metadata, array_flip(['password', 'token'])));
    }

    public function test_editar_una_version_publicada_crea_otra_y_no_altera_la_original(): void
    {
        $autor = $this->usuario();
        $proyecto = $this->proyecto($autor);
        $version = $this->versionGuia($proyecto, $autor, 'publicada');

        $nueva = (new UpdateGuideVersion)->execute($version, [
            'title' => 'Guía revisada',
            'content' => 'Contenido corregido',
        ], $autor);

        $this->assertNotSame($version->id, $nueva->id);
        $this->assertSame(2, $nueva->version_number);
        $this->assertSame('borrador', $nueva->status);
        $this->assertSame(hash('sha256', 'Contenido corregido'), $nueva->checksum);
        $this->assertSame('Contenido original', $version->fresh()->content);
        $this->assertSame('publicada', $version->fresh()->status);
    }

    public function test_editar_una_version_en_borrador_la_modifica_en_el_lugar(): void
    {
        $autor = $this->usuario();
        $proyecto = $this->proyecto($autor);
        $version = $this->versionGuia($proyecto, $autor, 'borrador');

        $editada = (new UpdateGuideVersion)->execute($version, [
            'content' => 'Contenido editado',
        ], $autor);

        $this->assertSame($version->id, $editada->id);
        $this->assertSame('Contenido editado', $editada->content);
        $this->assertSame(1, $editada->version_number);
    }

    private function accion(): CreateSubmission
    {
        return new CreateSubmission(new SubmissionLink);
    }

    private function usuario(string $correo = 'autor@example.test'): User
    {
        return User::factory()->create(['email' => $correo]);
    }

    private function proyecto(User $autor): Project
    {
        $numero = Project::count();

        return Project::create([
            'institution_id' => Institution::create(['name' => 'Institución de prueba'])->id,
            'code' => 'PROY-'.$numero,
            'slug' => 'proyecto-'.$numero,
            'title' => 'Proyecto de prueba',
            'owner_id' => $autor->id,
        ]);
    }

    private function versionGuia(Project $proyecto, User $autor, string $estado): ActivityVersion
    {
        $actividad = Activity::create([
            'project_id' => $proyecto->id,
            'code' => 'ACT-'.Activity::count(),
            'title' => 'Actividad de prueba',
            'created_by' => $autor->id,
        ]);

        return ActivityVersion::create([
            'activity_id' => $actividad->id,
            'version_number' => 1,
            'title' => 'Guía original',
            'content' => 'Contenido original',
            'author_id' => $autor->id,
            'checksum' => hash('sha256', 'Contenido original'),
            'status' => $estado,
            'published_at' => $estado === 'publicada' ? now() : null,
        ]);
    }

    private function asignacion(
        ActivityVersion $version,
        Project $proyecto,
        User $autor,
        string $estado = 'activa',
    ): ActivityAssignment {
        return ActivityAssignment::create([
            'activity_version_id' => $version->id,
            'project_id' => $proyecto->id,
            'target_type' => 'proyecto',
            'target_ref' => (string) $proyecto->id,
            'assigned_by' => $autor->id,
            'status' => $estado,
        ]);
    }
}
