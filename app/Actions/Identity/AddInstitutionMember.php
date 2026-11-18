<?php

namespace App\Actions\Identity;

use App\Models\Institution;
use App\Models\InstitutionMembership;
use App\Models\User;
use App\Services\Audit\RecordAuditEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class AddInstitutionMember
{
    public function execute(User $actor, Institution $institution, string $email): void
    {
        DB::transaction(function () use ($actor, $institution, $email): void {
            $institution = Institution::whereKey($institution->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor)->authorize('manageRoles', $institution);
            $user = User::where('email', mb_strtolower(trim($email)))->where('status', 'activo')->first();
            if (! $user) {
                throw ValidationException::withMessages(['email' => 'La persona debe tener una cuenta activa registrada antes de vincularla.']);
            }
            $membership = InstitutionMembership::firstOrCreate([
                'institution_id' => $institution->id, 'user_id' => $user->id,
            ], ['joined_at' => today()]);
            if ($membership->wasRecentlyCreated) {
                app(RecordAuditEvent::class)->execute($actor, $institution->id, 'identity.membership_created', 'institution_membership', $membership->id);
            }
        });
    }
}
