<?php

namespace Tests\Feature\Auth;

use App\Models\Institution;
use App\Models\InstitutionMembership;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class InstitutionalAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private Institution $institution;

    private Project $project;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo(now()->setDate(2026, 11, 18)->startOfDay());
        $this->institution = Institution::create(['name' => 'Institución de prueba']);
        $this->user = User::factory()->create();
        $this->project = Project::create([
            'institution_id' => $this->institution->id,
            'code' => 'PRUEBA',
            'slug' => 'prueba',
            'title' => 'Proyecto de prueba',
            'owner_id' => $this->user->id,
        ]);
    }

    public function test_only_administrator_can_manage_roles_and_tutors(): void
    {
        foreach (['evaluador', 'instructor', 'aprendiz', 'coordinador'] as $code) {
            $membership = $this->assignRole($code);
            $this->assertPermissions(false);
            $membership->roles()->detach();
        }

        $this->assignRole('administrador');
        $this->assertPermissions(true);
    }

    public function test_anonymous_and_account_without_membership_are_denied_even_as_project_owner(): void
    {
        $this->assertFalse(Gate::forUser(null)->allows('manageRoles', $this->institution));
        $this->assertFalse(Gate::forUser(null)->allows('manageTutors', $this->project));
        $this->assertPermissions(false);
    }

    public function test_administrator_cannot_manage_another_institution_by_changing_resource_id(): void
    {
        $this->assignRole('administrador');
        $other = Institution::create(['name' => 'Otra institución de prueba']);
        $project = Project::create([
            'institution_id' => $other->id,
            'code' => 'AJENO',
            'slug' => 'ajeno',
            'title' => 'Proyecto ajeno de prueba',
            'owner_id' => $this->user->id,
        ]);

        $this->assertPermissions(true);
        $this->assertFalse(Gate::forUser($this->user)->allows('manageRoles', $other));
        $this->assertFalse(Gate::forUser($this->user)->allows('manageTutors', $project));
    }

    public function test_revocation_is_effective_even_with_preloaded_relations(): void
    {
        $membership = $this->assignRole('administrador');
        $this->user->load('institutionMemberships.roles');
        $this->assertPermissions(true);

        $membership->roles()->detach();

        $this->assertPermissions(false);
    }

    public function test_membership_must_be_active_and_current(): void
    {
        $membership = $this->assignRole('administrador');
        foreach ([
            ['status' => 'inactiva', 'joined_at' => '2026-11-17', 'left_at' => null],
            ['status' => 'egresado', 'joined_at' => '2026-11-17', 'left_at' => null],
            ['status' => 'activa', 'joined_at' => '2026-11-19', 'left_at' => null],
            ['status' => 'activa', 'joined_at' => '2026-11-17', 'left_at' => '2026-11-18'],
            ['status' => 'activa', 'joined_at' => '2026-11-17', 'left_at' => '2026-11-17'],
        ] as $attributes) {
            $membership->update($attributes);
            $this->assertPermissions(false);
        }

        $membership->update(['joined_at' => '2026-11-18', 'left_at' => '2026-11-19']);
        $this->assertPermissions(true);
    }

    public function test_inactive_institution_and_disabled_accounts_are_denied(): void
    {
        $this->assignRole('administrador');
        foreach (['inactivo', 'bloqueado'] as $status) {
            User::whereKey($this->user->id)->update(['status' => $status]);
            $this->assertPermissions(false);
        }

        User::whereKey($this->user->id)->update(['status' => 'activo']);
        $this->institution->update(['status' => 'inactiva']);
        $this->assertPermissions(false);
    }

    public function test_administrator_has_no_implicit_permission_for_unimplemented_actions(): void
    {
        $this->assignRole('administrador');

        $this->assertFalse(Gate::forUser($this->user)->allows('delete', $this->project));
        $this->assertFalse(Gate::forUser($this->user)->allows('publish', $this->project));
    }

    private function assignRole(string $code): InstitutionMembership
    {
        $membership = InstitutionMembership::firstOrCreate([
            'institution_id' => $this->institution->id,
            'user_id' => $this->user->id,
        ], ['joined_at' => '2026-11-18']);
        $role = Role::create(['code' => $code, 'name' => ucfirst($code)]);
        $membership->roles()->attach($role);

        return $membership;
    }

    private function assertPermissions(bool $expected): void
    {
        $this->assertSame($expected, Gate::forUser($this->user)->allows('manageRoles', $this->institution));
        $this->assertSame($expected, Gate::forUser($this->user)->allows('manageTutors', $this->project));
    }
}
