<?php

namespace Tests\Feature;

use App\Actions\Projects\ManageProjectTutor;
use App\Models\Activity;
use App\Models\ActivityAssignment;
use App\Models\ActivityVersion;
use App\Models\AuditEvent;
use App\Models\Institution;
use App\Models\ProjectMembership;
use App\Models\SubmissionVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Support\AcademicScenario;
use Tests\TestCase;

class ComplementaryFeaturesTest extends TestCase
{
    use AcademicScenario, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function setupWorkflow(): array
    {
        $institution = Institution::create(['name' => 'Institución de prueba']);
        $admin = $this->member($institution, 'administrador');
        $instructor = $this->member($institution, 'instructor');
        $student = $this->member($institution, 'aprendiz');
        $ficha = $this->ficha($institution);
        $project = $this->project($ficha, $admin);
        $project->update(['status' => 'activo']);
        app(ManageProjectTutor::class)->execute($admin, $project, $instructor, true);
        ProjectMembership::create(['project_id' => $project->id, 'user_id' => $student->id, 'function' => 'aprendiz', 'assigned_at' => today()]);
        $activity = ActivityVersion::create([
            'activity_id' => Activity::create(['project_id' => $project->id, 'code' => 'ACT', 'title' => 'Actividad', 'created_by' => $admin->id, 'status' => 'publicada'])->id,
            'version_number' => 1, 'title' => 'Guía de criterios', 'content' => 'Contenido académico', 'author_id' => $admin->id,
            'checksum' => hash('sha256', 'Contenido académico'), 'status' => 'publicada', 'published_at' => now(),
        ]);
        $assignment = ActivityAssignment::create([
            'activity_version_id' => $activity->id, 'project_id' => $project->id, 'status' => 'activa',
            'target_type' => 'proyecto', 'target_ref' => (string) $project->id, 'assigned_at' => now(),
            'assigned_by' => $admin->id,
        ]);
        $this->actingAs($student)->post('/proyectos/'.$project->id.'/entregas', [
            'title' => 'Entrega con guía de criterios', 'summary' => 'Resumen con palabrabuscada', 'activity_assignment_id' => $assignment->id,
        ])->assertRedirect();
        $version = SubmissionVersion::firstOrFail();
        $this->post('/entregas/versiones/'.$version->id.'/evidencias', ['archivo' => UploadedFile::fake()->image('archivounico.png')])->assertRedirect();

        return compact('institution', 'admin', 'instructor', 'student', 'project', 'ficha', 'version');
    }

    public function test_busqueda_respeta_jurisdiccion_y_busca_en_datos_reales(): void
    {
        $data = $this->setupWorkflow();
        $this->actingAs($data['student'])->get('/entregas/buscar?q=inexistente')->assertOk()->assertDontSee('Entrega con guía');
        $this->get('/entregas/buscar?q=palabrabuscada')->assertOk()->assertSee('Entrega con guía');
        $this->get('/entregas/buscar?q=criterios')->assertSee('Entrega con guía');
        $this->get('/entregas/buscar?q=archivounico')->assertSee('Entrega con guía');
        $this->get('/entregas/buscar?estado=revisada')->assertDontSee('Entrega con guía');
        $this->get('/entregas/buscar?estado=borrador')->assertSee('Entrega con guía');
        $this->get('/entregas/buscar?estado=invalido')->assertSessionHasErrors('estado');
        $this->get('/entregas/buscar?ficha_id='.$data['ficha']->id)->assertSee('Entrega con guía');
        $other = $this->ficha($data['institution']);
        $this->get('/entregas/buscar?ficha_id='.$other->id)->assertDontSee('Entrega con guía');
        $this->get('/entregas/buscar?q=%25')->assertOk()->assertDontSee('Entrega con guía');
    }

    public function test_visor_de_auditoria_solo_administradores_y_sin_datos_sensibles(): void
    {
        $data = $this->setupWorkflow();
        AuditEvent::create([
            'institution_id' => $data['institution']->id, 'actor_id' => $data['admin']->id,
            'action' => 'document.upload', 'resource_type' => 'document_version', 'resource_id' => '1',
            'result' => 'exito', 'request_id' => (string) Str::uuid(), 'occurred_at' => now(),
            'metadata' => ['motivo' => 'Secreto personal', 'ip' => '203.0.113.9', 'proyecto' => 42],
        ]);
        $this->actingAs($data['student'])->get('/auditoria')->assertForbidden();
        $this->actingAs($data['instructor'])->get('/auditoria')->assertForbidden();
        $this->actingAs($data['admin'])->get('/auditoria')->assertOk()->assertSee('document.upload')
            ->assertDontSee('Secreto personal')->assertDontSee('203.0.113.9')->assertSee('proyecto: 42');
        $this->get('/auditoria?accion=submission.sent')->assertOk()->assertSee('Eventos (0)');
        $this->get('/auditoria?institucion_id=999999')->assertOk()->assertSee('proyecto: 42');
        $outside = Institution::create(['name' => 'Otra institución']);
        $this->actingAs($this->member($outside, 'administrador'))->get('/auditoria')->assertOk()->assertDontSee('Entrega con guía');
    }

    public function test_importacion_por_lotes_valida_duplicados_y_solo_vincula_cuentas_existentes(): void
    {
        $data = $this->setupWorkflow();
        $url = '/accesos/'.$data['institution']->id.'/importar';
        $this->actingAs($data['student'])->get($url)->assertForbidden();
        $existing = $data['student']->email;
        $known = User::factory()->create(['email' => 'nuevo@example.invalid', 'status' => 'activo']);
        $inactive = User::factory()->create(['email' => 'inactivo@example.invalid', 'status' => 'inactivo']);
        $csv = "email\n{$known->email}\n{$existing}\n{$known->email}\ninvalido\n{$inactive->email}\n";
        $file = UploadedFile::fake()->createWithContent('miembros.csv', $csv);
        $this->actingAs($data['admin'])->post($url, ['archivo' => $file, 'operacion' => 'vista_previa'])
            ->assertOk()->assertSee('Nuevas vinculaciones: 1')->assertSee('Ya estaban vinculadas: 1')
            ->assertSee('Duplicados en el archivo: 1')->assertSee('Sin cuenta activa: 1')->assertSee('Correos no válidos: 1');
        $this->assertDatabaseMissing('institution_memberships', ['institution_id' => $data['institution']->id, 'user_id' => $known->id]);
        $file = UploadedFile::fake()->createWithContent('miembros.csv', $csv);
        $this->post($url, ['archivo' => $file, 'operacion' => 'confirmar'])->assertOk()->assertSee('Nuevas vinculaciones: 1');
        $this->assertDatabaseHas('institution_memberships', ['institution_id' => $data['institution']->id, 'user_id' => $known->id]);
        $this->assertDatabaseMissing('institution_memberships', ['institution_id' => $data['institution']->id, 'user_id' => $inactive->id]);
        $this->assertDatabaseHas('audit_events', ['action' => 'identity.membership_imported', 'actor_id' => $data['admin']->id]);
        $this->assertDatabaseCount('users', 5);
        $bad = UploadedFile::fake()->createWithContent('mal.csv', "correo\na@b.com\n");
        $this->post($url, ['archivo' => $bad, 'operacion' => 'vista_previa'])->assertSessionHasErrors('archivo');
        $wrongExt = UploadedFile::fake()->createWithContent('mal.txt', "email\na@b.com\n");
        $this->post($url, ['archivo' => $wrongExt, 'operacion' => 'vista_previa'])->assertSessionHasErrors('archivo');
    }
}
