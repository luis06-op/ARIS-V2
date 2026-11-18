<?php

namespace App\Actions\Identity;

use App\Domain\Identity\InstitutionalAuthority;
use App\Models\Especialidad;
use App\Models\InstitutionMembership;
use App\Models\User;
use App\Models\VinculoEspecialidad;
use App\Services\Audit\RecordAuditEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class ConfigureInstructorSpecialty
{
    public function execute(User $actor, InstitutionMembership $membership, Especialidad $specialty, bool $enabled): void
    {
        DB::transaction(function () use ($actor, $membership, $specialty, $enabled): void {
            $membership = InstitutionMembership::whereKey($membership->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor)->authorize('manageRoles', $membership->institution);
            if ($specialty->institution_id !== $membership->institution_id
                || ! app(InstitutionalAuthority::class)->memberships($membership->user, 'instructor')->whereKey($membership->id)->exists()) {
                throw ValidationException::withMessages(['membership_id' => 'La especialidad y el instructor deben pertenecer a la misma institución activa.']);
            }
            $link = VinculoEspecialidad::updateOrCreate([
                'institution_membership_id' => $membership->id, 'especialidad_id' => $specialty->id,
            ], ['can_create_projects' => $enabled, 'assigned_by' => $actor->id, 'revoked_at' => null]);
            app(RecordAuditEvent::class)->execute($actor, $membership->institution_id, 'identity.creation_permission_changed', 'vinculo_especialidad', $link->id, ['habilitado' => $enabled]);
        });
    }
}
