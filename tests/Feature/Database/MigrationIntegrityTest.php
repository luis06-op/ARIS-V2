<?php

namespace Tests\Feature\Database;

use App\Models\Institution;
use App\Models\InstitutionMembership;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MigrationIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_relations_persist_with_unique_memberships_and_roles(): void
    {
        $institution = Institution::create(['name' => 'Institución de prueba']);
        $user = User::factory()->create();
        $membership = InstitutionMembership::create([
            'institution_id' => $institution->id,
            'user_id' => $user->id,
            'joined_at' => '2026-11-16',
        ]);
        $role = Role::create(['code' => 'prueba', 'name' => 'Rol de prueba']);
        $membership->roles()->attach($role->id);

        $this->assertDatabaseHas('institution_memberships', [
            'institution_id' => $institution->id,
            'user_id' => $user->id,
        ]);
        $this->assertTrue($membership->roles()->whereKey($role->id)->exists());

        $this->expectException(QueryException::class);
        InstitutionMembership::create([
            'institution_id' => $institution->id,
            'user_id' => $user->id,
            'joined_at' => '2026-11-16',
        ]);
    }

    public function test_user_deletion_is_restricted_when_project_history_references_them(): void
    {
        $institution = Institution::create(['name' => 'Institución de prueba']);
        $user = User::factory()->create();
        Project::create([
            'institution_id' => $institution->id,
            'code' => 'PRUEBA-1',
            'slug' => 'proyecto-prueba',
            'title' => 'Proyecto de prueba',
            'owner_id' => $user->id,
        ]);

        $this->expectException(QueryException::class);
        $user->delete();
    }
}
