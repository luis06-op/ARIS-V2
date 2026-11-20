<?php

namespace App\Actions\Identity;

use App\Domain\Identity\InstitutionalAuthority;
use App\Models\Institution;
use App\Models\InstitutionMembership;
use App\Models\Role;
use App\Models\User;
use App\Services\Audit\RecordAuditEvent;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ManageInstitutionRole
{
    /**
     * Roles que la institución puede asignar a una membresía.
     *
     * @var list<string>
     */
    private const ROLES_VALIDOS = ['administrador', 'evaluador', 'instructor', 'aprendiz'];

    public function execute(User $actor, InstitutionMembership $membership, string $code, bool $active): void
    {
        DB::transaction(function () use ($actor, $membership, $code, $active): void {
            // Bloqueo transaccional común por institución: serializa cualquier cambio de
            // roles de la misma institución ANTES de consultar o modificar autoridad efectiva.
            // Así dos retiros concurrentes de administradores de una misma institución no
            // pierden la actualización del otro (MySQL 8, fila de la institución).
            $institution = Institution::whereKey($membership->institution_id)->lockForUpdate()->firstOrFail();

            // Autoridad efectiva: usuario activo, institución activa y membresía vigente,
            // consultada en cada evaluación sin reutilizar relaciones precargadas.
            if (! app(InstitutionalAuthority::class)->isAdministrator($actor, $institution->id)) {
                throw new AuthorizationException('Solo el administrador vigente de la institución puede gestionar roles.');
            }

            $membership = InstitutionMembership::whereKey($membership->id)->lockForUpdate()->firstOrFail();

            if (! in_array($code, self::ROLES_VALIDOS, true)) {
                throw ValidationException::withMessages(['role' => 'El rol no está aprobado por la institución.']);
            }

            $role = Role::firstOrCreate(['code' => $code], ['name' => ucfirst($code), 'is_system' => true]);

            if ($active) {
                $membership->roles()->syncWithoutDetaching([$role->id => ['assigned_by' => $actor->id, 'assigned_at' => now()]]);
            } else {
                $this->protegerUltimoAdministrador($institution, $membership, $code);
                $membership->roles()->detach($role->id);
            }

            app(RecordAuditEvent::class)->execute($actor, $membership->institution_id, 'identity.role_changed', 'institution_membership', $membership->id, ['rol' => $code, 'activo' => $active]);
        });
    }

    /**
     * Rechaza la retirada cuando no quedaría ningún administrador vigente en la institución.
     *
     * Solo cuentan administradores efectivos: usuario activo, institución activa y membresía
     * vigente (joined_at pasado y left_at nulo o futura). Se excluye la membresía que se está
     * modificando para no contarse a sí misma.
     */
    private function protegerUltimoAdministrador(Institution $institution, InstitutionMembership $membership, string $code): void
    {
        if ($code !== 'administrador') {
            return;
        }

        $quedanAdministradores = InstitutionMembership::where('institution_id', $institution->id)
            ->where('id', '!=', $membership->id)
            ->where('status', 'activa')
            ->whereHas('user', fn ($query) => $query->where('status', 'activo'))
            ->whereHas('institution', fn ($query) => $query->where('status', 'activa'))
            ->whereDate('joined_at', '<=', today())
            ->where(fn ($query) => $query->whereNull('left_at')->orWhereDate('left_at', '>', today()))
            ->whereHas('roles', fn ($query) => $query->where('code', 'administrador'))
            ->count();

        if ($quedanAdministradores === 0) {
            throw ValidationException::withMessages(['role' => 'No se puede retirar el rol de administrador: es el último administrador vigente de la institución.']);
        }
    }
}
