<?php

namespace Tests\Feature\Projects;

use App\Actions\Identity\ConfigureEvaluatorScope;
use App\Actions\Identity\ConfigureInstructorSpecialty;
use App\Actions\Projects\ManageProjectTutor;
use App\Models\AuditEvent;
use App\Models\Ficha;
use App\Models\Institution;
use App\Models\Project;
use App\Models\ProjectMembership;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AcademicScenario;
use Tests\TestCase;

class ProjectAccessTest extends TestCase
{
    use AcademicScenario, RefreshDatabase;

    private Institution $institution;

    private User $admin;

    private Ficha $ficha;

    private Project $project;

    protected function setUp(): void
    {
        parent::setUp();
        $this->institution = Institution::create(['name' => 'Institución de prueba']);
        $this->admin = $this->member($this->institution, 'administrador');
        $this->ficha = $this->ficha($this->institution);
        $this->project = $this->project($this->ficha, $this->admin, 'Proyecto visible de prueba');
    }

    public function test_anonymous_and_unassigned_users_cannot_access_projects(): void
    {
        $this->get('/proyectos')->assertRedirect('/ingresar');
        $this->get('/proyectos/'.$this->project->id)->assertRedirect('/ingresar');
        foreach (['instructor', 'aprendiz', 'evaluador'] as $role) {
            $this->actingAs($this->member($this->institution, $role))->get('/proyectos')->assertOk()->assertDontSee($this->project->title);
            $this->get('/proyectos/'.$this->project->id)->assertForbidden();
            $this->get('/proyectos/crear')->assertForbidden();
        }
    }

    public function test_evaluator_sees_only_exact_assigned_ficha_and_specialty_and_revocation_is_immediate(): void
    {
        $evaluator = $this->member($this->institution, 'evaluador');
        $sameSpecialty = Ficha::create(['especialidad_id' => $this->ficha->especialidad_id, 'numero' => 'OTRA', 'cohorte' => 'Otra cohorte']);
        $otherCohort = $this->project($sameSpecialty, $this->admin, 'Otra cohorte secreta');
        $otherSpecialty = $this->project($this->ficha($this->institution), $this->admin, 'Otra especialidad secreta');
        $membership = $evaluator->institutionMemberships()->firstOrFail();
        app(ConfigureEvaluatorScope::class)->execute($this->admin, $membership, $this->ficha, true);

        $this->actingAs($evaluator)->get('/proyectos')->assertOk()->assertSee($this->project->title)->assertDontSee($otherCohort->title)->assertDontSee($otherSpecialty->title);
        $this->get('/proyectos/'.$this->project->id)->assertOk();
        $this->get('/proyectos/'.$otherCohort->id)->assertForbidden();
        $this->get('/proyectos/'.$otherSpecialty->id)->assertForbidden();
        $this->get('/proyectos/'.$this->project->id.'/editar')->assertForbidden();
        $this->post('/proyectos/'.$this->project->id.'/tutor', ['user_id' => $evaluator->id, 'active' => 1])->assertForbidden();

        app(ConfigureEvaluatorScope::class)->execute($this->admin, $membership, $this->ficha, false);
        $this->get('/proyectos/'.$this->project->id)->assertForbidden();
    }

    public function test_creation_requires_explicit_permission_in_correct_specialty_and_ignores_injected_authority(): void
    {
        $instructor = $this->member($this->institution, 'instructor');
        $data = ['ficha_id' => $this->ficha->id, 'code' => 'NUEVO', 'title' => 'Nuevo proyecto'];
        $this->actingAs($instructor)->post('/proyectos', $data)->assertForbidden();
        app(ConfigureInstructorSpecialty::class)->execute($this->admin, $instructor->institutionMemberships()->firstOrFail(), $this->ficha->especialidad, true);
        $this->get('/proyectos/crear')->assertOk();
        $this->post('/proyectos', array_merge($data, ['institution_id' => 999, 'owner_id' => $this->admin->id, 'status' => 'activo']))->assertRedirect('/proyectos');
        $created = Project::where('code', 'NUEVO')->firstOrFail();
        $this->assertSame($instructor->id, $created->owner_id);
        $this->assertSame($this->institution->id, $created->institution_id);
        $this->assertSame('borrador', $created->status);
        $this->assertDatabaseMissing('project_memberships', ['project_id' => $created->id]);
        $this->get('/proyectos/'.$created->id)->assertForbidden();
        $this->post('/proyectos', array_merge($data, ['ficha_id' => $this->ficha($this->institution)->id]))->assertForbidden();

        app(ConfigureInstructorSpecialty::class)->execute($this->admin, $instructor->institutionMemberships()->firstOrFail(), $this->ficha->especialidad, false);
        $this->post('/proyectos', $data)->assertForbidden();
    }

    public function test_assigned_instructor_edits_metadata_with_conflict_detection_and_cannot_change_scope(): void
    {
        $instructor = $this->member($this->institution, 'instructor');
        app(ManageProjectTutor::class)->execute($this->admin, $this->project, $instructor, true);
        $this->actingAs($instructor)->get('/proyectos/'.$this->project->id.'/editar')->assertOk();
        $this->patch('/proyectos/'.$this->project->id, [
            'title' => '<script>alert(1)</script>', 'description' => '<b>Texto</b>', 'lock_version' => 0,
            'ficha_id' => 999, 'owner_id' => $instructor->id, 'status' => 'archivado',
        ])->assertRedirect('/proyectos/'.$this->project->id);
        $this->get('/proyectos/'.$this->project->id)->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->assertSame($this->ficha->id, $this->project->fresh()->ficha_id);
        $this->assertSame($this->admin->id, $this->project->fresh()->owner_id);
        $this->assertSame('borrador', $this->project->fresh()->status);
        $this->patch('/proyectos/'.$this->project->id, ['title' => 'Edición obsoleta', 'lock_version' => 0])->assertSessionHasErrors('lock_version');
        app(ManageProjectTutor::class)->execute($this->admin, $this->project, $instructor, false);
        $this->get('/proyectos/'.$this->project->id)->assertForbidden();
    }

    public function test_apprentice_requires_current_membership_and_cannot_edit(): void
    {
        $student = $this->member($this->institution, 'aprendiz');
        $assignment = ProjectMembership::create(['project_id' => $this->project->id, 'user_id' => $student->id, 'function' => 'aprendiz', 'assigned_at' => today()]);
        $this->actingAs($student)->get('/proyectos/'.$this->project->id)->assertOk();
        $this->get('/proyectos/'.$this->project->id.'/editar')->assertForbidden();
        $assignment->update(['ended_at' => today()]);
        $this->get('/proyectos/'.$this->project->id)->assertForbidden();
    }

    public function test_admin_is_isolated_and_duplicate_codes_and_validation_do_not_create_projects(): void
    {
        $other = Institution::create(['name' => 'Otra institución']);
        $foreign = $this->project($this->ficha($other), $this->admin, 'Proyecto de otra institución');
        $this->actingAs($this->admin)->get('/proyectos')->assertOk()->assertDontSee($foreign->title);
        $this->get('/proyectos/'.$foreign->id)->assertForbidden();
        $this->post('/proyectos', ['ficha_id' => $this->ficha->id, 'code' => $this->project->code, 'title' => 'Duplicado'])->assertSessionHasErrors('code');
        $this->post('/proyectos', ['ficha_id' => $this->ficha->id, 'code' => 'VALIDO', 'title' => str_repeat('a', 181)])->assertSessionHasErrors('title');
        $this->assertDatabaseCount('projects', 2);
        $this->assertSame(0, AuditEvent::where('action', 'project.created')->count());
    }

    public function test_only_admin_can_assign_and_remove_a_tutor_with_audit(): void
    {
        $instructor = $this->member($this->institution, 'instructor');
        $this->actingAs($this->admin)->post('/proyectos/'.$this->project->id.'/tutor', ['user_id' => $instructor->id, 'active' => 1])->assertRedirect();
        $this->get('/proyectos/'.$this->project->id)->assertOk()->assertSee($instructor->email);
        $this->assertDatabaseHas('project_memberships', ['project_id' => $this->project->id, 'user_id' => $instructor->id, 'ended_at' => null]);
        $this->post('/proyectos/'.$this->project->id.'/tutor', ['user_id' => $instructor->id, 'active' => 0])->assertRedirect();
        $this->assertSame(2, AuditEvent::where('action', 'project.tutor_changed')->count());
        $this->assertNotNull(ProjectMembership::firstOrFail()->ended_at);
    }

    public function test_blocked_account_loses_access_during_existing_session(): void
    {
        $this->actingAs($this->admin)->get('/proyectos/'.$this->project->id)->assertOk();
        User::whereKey($this->admin->id)->update(['status' => 'bloqueado']);
        $this->get('/proyectos')->assertOk()->assertDontSee($this->project->title);
        $this->get('/proyectos/'.$this->project->id)->assertForbidden();
    }

    public function test_only_assigned_tutor_links_learners_from_same_institution(): void
    {
        $instructor = $this->member($this->institution, 'instructor');
        $learner = $this->member($this->institution, 'aprendiz');
        $url = '/proyectos/'.$this->project->id.'/aprendiz';
        $data = ['user_id' => $learner->id, 'active' => 1];
        $this->actingAs($instructor)->post($url, $data)->assertForbidden();
        app(ManageProjectTutor::class)->execute($this->admin, $this->project, $instructor, true);
        $this->post($url, $data)->assertRedirect();
        $this->actingAs($learner)->get('/proyectos/'.$this->project->id)->assertOk();
        $this->post($url, $data)->assertForbidden();
        $other = Institution::create(['name' => 'Otra institución']);
        $foreign = $this->member($other, 'aprendiz');
        $this->actingAs($instructor)->post($url, ['user_id' => $foreign->id, 'active' => 1])->assertSessionHasErrors('user_id');
        $this->post($url, ['user_id' => $learner->id, 'active' => 0])->assertRedirect();
        $this->actingAs($learner)->get('/proyectos/'.$this->project->id)->assertForbidden();
    }
}
