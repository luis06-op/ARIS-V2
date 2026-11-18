<?php

namespace App\Actions\Identity;

use App\Models\AuditEvent;
use App\Models\Institution;
use App\Models\InstitutionMembership;
use App\Models\Role;
use App\Models\User;
use App\Services\Audit\RecordAuditEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

final class InitializeAdministration
{
    public function execute(array $data): void
    {
        $data['email'] = mb_strtolower(trim((string) ($data['email'] ?? '')));
        $data = Validator::make($data, [
            'institution_name' => ['required', 'string', 'max:150'],
            'institution_code' => ['required', 'string', 'max:32', 'unique:institutions,code'],
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ])->validate();

        DB::transaction(function () use ($data): void {
            $role = Role::firstOrCreate(['code' => 'administrador'], ['name' => 'Administrador', 'is_system' => true]);
            $role = Role::whereKey($role->id)->lockForUpdate()->firstOrFail();
            if ($role->institutionMemberships()->exists() || AuditEvent::where('action', 'identity.bootstrap')->exists()) {
                throw ValidationException::withMessages(['institution_code' => 'La administración inicial ya fue configurada. Usa el módulo de control de accesos.']);
            }
            $institution = Institution::create(['name' => $data['institution_name'], 'code' => $data['institution_code']]);
            $user = User::create(['name' => $data['name'], 'email' => $data['email'], 'password' => $data['password']]);
            $membership = InstitutionMembership::create(['institution_id' => $institution->id, 'user_id' => $user->id, 'joined_at' => today()]);
            $membership->roles()->attach($role, ['assigned_by' => $user->id, 'assigned_at' => now()]);
            app(RecordAuditEvent::class)->execute($user, $institution->id, 'identity.bootstrap', 'institution_membership', $membership->id);
        });
    }
}
