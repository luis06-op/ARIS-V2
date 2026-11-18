<?php

namespace Tests\Feature\Auth;

use App\Models\AuditEvent;
use App\Models\Especialidad;
use App\Models\Ficha;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\AcademicScenario;
use Tests\TestCase;

class AccessManagementTest extends TestCase
{
    use AcademicScenario, RefreshDatabase;

    public function test_admin_can_manage_roles_and_jurisdiction_with_audit_and_revoke_access(): void
    {
        $institution = Institution::create(['name' => 'Institución de prueba']);
        $admin = $this->member($institution, 'administrador');
        $evaluator = $this->member($institution, 'aprendiz');
        $membership = $evaluator->institutionMemberships()->firstOrFail();
        $ficha = $this->ficha($institution);
        $project = $this->project($ficha, $admin);
        $url = '/accesos/'.$institution->id;
        $this->actingAs($admin)->get('/accesos')->assertOk()->assertSee($institution->name);
        $this->get($url)->assertOk();
        $this->post($url, ['operation' => 'rol', 'membership_id' => $membership->id, 'role' => 'evaluador', 'active' => 1])->assertRedirect('/accesos');
        $this->post($url, ['operation' => 'evaluador', 'membership_id' => $membership->id, 'ficha_id' => $ficha->id, 'active' => 1])->assertRedirect('/accesos');
        $this->get($url)->assertOk()->assertSee($ficha->numero);
        $this->actingAs($evaluator)->get('/proyectos/'.$project->id)->assertOk();
        $this->actingAs($admin)->post($url, ['operation' => 'rol', 'membership_id' => $membership->id, 'role' => 'evaluador', 'active' => 0])->assertRedirect();
        $this->actingAs($evaluator)->get('/proyectos/'.$project->id)->assertForbidden();
        $this->assertSame(3, AuditEvent::count());
    }

    public function test_non_admin_cannot_manage_roles_or_tutors_even_with_forged_payload(): void
    {
        $institution = Institution::create(['name' => 'Institución de prueba']);
        $evaluator = $this->member($institution, 'evaluador');
        $url = '/accesos/'.$institution->id;
        $this->get($url)->assertRedirect('/ingresar');
        $this->actingAs($evaluator)->get($url)->assertForbidden();
        $this->post($url, ['operation' => 'rol', 'membership_id' => $evaluator->institutionMemberships()->firstOrFail()->id, 'role' => 'administrador', 'active' => 1])->assertForbidden();
        $this->assertDatabaseCount('audit_events', 0);
    }

    public function test_cross_institution_scope_and_target_membership_are_rejected(): void
    {
        $institution = Institution::create(['name' => 'Institución de prueba']);
        $other = Institution::create(['name' => 'Otra institución']);
        $admin = $this->member($institution, 'administrador');
        $evaluator = $this->member($institution, 'evaluador');
        $foreign = $this->member($other, 'evaluador');
        $ficha = $this->ficha($other);
        $url = '/accesos/'.$institution->id;
        $this->actingAs($admin)->post($url, [
            'operation' => 'evaluador', 'membership_id' => $evaluator->institutionMemberships()->firstOrFail()->id,
            'ficha_id' => $ficha->id, 'active' => 1,
        ])->assertSessionHasErrors('membership_id');
        $this->post($url, [
            'operation' => 'rol', 'membership_id' => $foreign->institutionMemberships()->firstOrFail()->id,
            'role' => 'administrador', 'active' => 1,
        ])->assertSessionHasErrors('membership_id');
        $this->get('/accesos/'.$other->id)->assertForbidden();
        $this->assertDatabaseCount('jurisdicciones_evaluador', 0);
        $this->assertDatabaseCount('audit_events', 0);
    }

    public function test_catalog_and_membership_are_created_by_admin_without_automatic_roles(): void
    {
        $institution = Institution::create(['name' => 'Institución de prueba']);
        $admin = $this->member($institution, 'administrador');
        $person = User::factory()->create();
        $url = '/accesos/'.$institution->id;
        $this->actingAs($person)->post($url.'/miembros', ['email' => $person->email])->assertForbidden();
        $this->post($url.'/catalogo', ['operation' => 'especialidad', 'codigo' => 'PRUEBA', 'nombre' => 'Especialidad de prueba'])->assertForbidden();
        $this->actingAs($admin)->post($url.'/miembros', ['email' => $person->email])->assertRedirect();
        $this->assertSame(0, $person->institutionMemberships()->firstOrFail()->roles()->count());
        $this->post($url.'/miembros', ['email' => $person->email])->assertRedirect();
        $this->assertSame(1, $person->institutionMemberships()->count());
        $this->post($url.'/catalogo', ['operation' => 'especialidad', 'codigo' => 'PRUEBA', 'nombre' => 'Especialidad de prueba'])->assertRedirect();
        $specialty = Especialidad::firstOrFail();
        $this->post($url.'/catalogo', ['operation' => 'ficha', 'especialidad_id' => $specialty->id, 'numero' => 'PRUEBA-1', 'cohorte' => 'Cohorte de prueba'])->assertRedirect();
        $this->assertSame($specialty->id, Ficha::firstOrFail()->especialidad_id);
        $this->post($url.'/catalogo', ['operation' => 'ficha', 'especialidad_id' => $specialty->id, 'numero' => 'PRUEBA-1', 'cohorte' => 'Duplicada'])->assertSessionHasErrors('numero');
        $this->assertDatabaseCount('fichas', 1);
        $this->assertSame(3, AuditEvent::count());
    }

    public function test_instructor_cannot_enable_own_creation_permission_and_admin_can_revoke_it(): void
    {
        $institution = Institution::create(['name' => 'Institución de prueba']);
        $admin = $this->member($institution, 'administrador');
        $instructor = $this->member($institution, 'instructor');
        $ficha = $this->ficha($institution);
        $url = '/accesos/'.$institution->id;
        $data = ['operation' => 'instructor', 'membership_id' => $instructor->institutionMemberships()->firstOrFail()->id, 'especialidad_id' => $ficha->especialidad_id, 'active' => 1];
        $this->actingAs($instructor)->post($url, $data)->assertForbidden();
        $this->actingAs($admin)->post($url, $data)->assertRedirect();
        $this->actingAs($instructor)->get('/proyectos/crear')->assertOk();
        $this->actingAs($admin)->post($url, array_replace($data, ['active' => 0]))->assertRedirect();
        $this->actingAs($instructor)->get('/proyectos/crear')->assertForbidden();
    }
}
