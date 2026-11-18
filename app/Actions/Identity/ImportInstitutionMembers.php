<?php

namespace App\Actions\Identity;

use App\Models\Institution;
use App\Models\InstitutionMembership;
use App\Models\User;
use App\Services\Audit\RecordAuditEvent;
use App\Services\Identity\MembershipCsvReader;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class ImportInstitutionMembers
{
    public function __construct(private MembershipCsvReader $reader) {}

    /** @return array{creadas: int, existentes: int, sin_cuenta: array<int, string>, invalidas: array<int, string>, duplicados: array<int, string>} */
    public function preview(User $actor, Institution $institution, string $path): array
    {
        Gate::forUser($actor)->authorize('manageRoles', $institution);
        $parsed = $this->reader->read($path);

        return $this->classify($institution, $parsed);
    }

    /** @return array{creadas: int, existentes: int, sin_cuenta: array<int, string>, invalidas: array<int, string>, duplicados: array<int, string>} */
    public function execute(User $actor, Institution $institution, string $path): array
    {
        Gate::forUser($actor)->authorize('manageRoles', $institution);
        $parsed = $this->reader->read($path);

        return DB::transaction(function () use ($actor, $institution, $parsed): array {
            $locked = Institution::whereKey($institution->id)->lockForUpdate()->firstOrFail();
            $result = $this->classify($locked, $parsed);
            foreach ($parsed['validos'] as $email) {
                $user = User::where('email', $email)->where('status', 'activo')->first();
                if (! $user || InstitutionMembership::where('institution_id', $locked->id)->where('user_id', $user->id)->exists()) {
                    continue;
                }
                $membership = InstitutionMembership::create([
                    'institution_id' => $locked->id, 'user_id' => $user->id, 'joined_at' => today(),
                ]);
                app(RecordAuditEvent::class)->execute($actor, $locked->id, 'identity.membership_imported', 'institution_membership', $membership->id, ['correo' => $email]);
            }

            return $result;
        });
    }

    /** @param array{validos: array<int, string>, invalidos: array<int, string>, duplicados: array<int, string>} $parsed */
    private function classify(Institution $institution, array $parsed): array
    {
        $sinCuenta = [];
        $existentes = 0;
        foreach ($parsed['validos'] as $email) {
            $user = User::where('email', $email)->where('status', 'activo')->first();
            if (! $user) {
                $sinCuenta[] = $email;

                continue;
            }
            if (InstitutionMembership::where('institution_id', $institution->id)->where('user_id', $user->id)->exists()) {
                $existentes++;
            }
        }

        return [
            'creadas' => count($parsed['validos']) - count($sinCuenta) - $existentes,
            'existentes' => $existentes,
            'sin_cuenta' => $sinCuenta,
            'invalidas' => $parsed['invalidos'],
            'duplicados' => $parsed['duplicados'],
        ];
    }
}
