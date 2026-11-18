<?php

namespace App\Actions\Identity;

use App\Domain\Identity\InstitutionalAuthority;
use App\Models\Ficha;
use App\Models\InstitutionMembership;
use App\Models\JurisdiccionEvaluador;
use App\Models\User;
use App\Services\Audit\RecordAuditEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class ConfigureEvaluatorScope
{
    public function execute(User $actor, InstitutionMembership $membership, Ficha $ficha, bool $active): void
    {
        DB::transaction(function () use ($actor, $membership, $ficha, $active): void {
            $membership = InstitutionMembership::whereKey($membership->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor)->authorize('manageRoles', $membership->institution);
            if ($ficha->especialidad->institution_id !== $membership->institution_id
                || ($active && ! app(InstitutionalAuthority::class)->memberships($membership->user, 'evaluador')->whereKey($membership->id)->exists())) {
                throw ValidationException::withMessages(['membership_id' => 'Selecciona un evaluador vigente y una ficha de la misma institución.']);
            }
            $scope = JurisdiccionEvaluador::updateOrCreate([
                'institution_membership_id' => $membership->id, 'ficha_id' => $ficha->id,
            ], ['assigned_by' => $actor->id, 'revoked_at' => $active ? null : now()]);
            app(RecordAuditEvent::class)->execute($actor, $membership->institution_id, 'identity.evaluator_scope_changed', 'jurisdiccion_evaluador', $scope->id, ['activo' => $active]);
        });
    }
}
