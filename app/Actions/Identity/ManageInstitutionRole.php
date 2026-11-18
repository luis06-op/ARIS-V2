<?php

namespace App\Actions\Identity;

use App\Models\InstitutionMembership;
use App\Models\Role;
use App\Models\User;
use App\Services\Audit\RecordAuditEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class ManageInstitutionRole
{
    public function execute(User $actor, InstitutionMembership $membership, string $code, bool $active): void
    {
        DB::transaction(function () use ($actor, $membership, $code, $active): void {
            $membership = InstitutionMembership::whereKey($membership->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor)->authorize('manageRoles', $membership->institution);
            if (! in_array($code, ['administrador', 'evaluador', 'instructor', 'aprendiz'], true)) {
                throw ValidationException::withMessages(['role' => 'El rol no está aprobado por la institución.']);
            }
            $role = Role::firstOrCreate(['code' => $code], ['name' => ucfirst($code), 'is_system' => true]);
            if ($active) {
                $membership->roles()->syncWithoutDetaching([$role->id => ['assigned_by' => $actor->id, 'assigned_at' => now()]]);
            } else {
                $membership->roles()->detach($role->id);
            }
            app(RecordAuditEvent::class)->execute($actor, $membership->institution_id, 'identity.role_changed', 'institution_membership', $membership->id, ['rol' => $code, 'activo' => $active]);
        });
    }
}
