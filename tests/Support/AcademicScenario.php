<?php

namespace Tests\Support;

use App\Models\Especialidad;
use App\Models\Ficha;
use App\Models\Institution;
use App\Models\InstitutionMembership;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Str;

trait AcademicScenario
{
    protected function member(Institution $institution, string $role): User
    {
        $user = User::factory()->create();
        $membership = InstitutionMembership::create(['institution_id' => $institution->id, 'user_id' => $user->id, 'joined_at' => today()]);
        $membership->roles()->attach(Role::firstOrCreate(['code' => $role], ['name' => ucfirst($role)]));

        return $user;
    }

    protected function ficha(Institution $institution): Ficha
    {
        $specialty = Especialidad::create(['institution_id' => $institution->id, 'nombre' => 'Especialidad de prueba', 'codigo' => (string) Str::uuid()]);

        return Ficha::create(['especialidad_id' => $specialty->id, 'numero' => (string) Str::uuid(), 'cohorte' => 'Cohorte de prueba']);
    }

    protected function project(Ficha $ficha, User $owner, string $title = 'Proyecto de prueba'): Project
    {
        return Project::create([
            'institution_id' => $ficha->especialidad->institution_id, 'ficha_id' => $ficha->id,
            'owner_id' => $owner->id, 'code' => (string) Str::uuid(), 'slug' => (string) Str::uuid(), 'title' => $title,
        ]);
    }
}
