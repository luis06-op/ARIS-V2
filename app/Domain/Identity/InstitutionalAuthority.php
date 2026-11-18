<?php

namespace App\Domain\Identity;

use App\Models\InstitutionMembership;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class InstitutionalAuthority
{
    public function isAdministrator(User $user, int $institutionId): bool
    {
        return $this->memberships($user, 'administrador')
            ->where('institution_id', $institutionId)->exists();
    }

    public function memberships(User $user, string $role): Builder
    {
        return InstitutionMembership::query()
            ->where('user_id', $user->getKey())
            ->where('status', 'activa')
            ->whereDate('joined_at', '<=', today())
            ->where(fn ($query) => $query->whereNull('left_at')->orWhereDate('left_at', '>', today()))
            ->whereHas('user', fn ($query) => $query->where('status', 'activo'))
            ->whereHas('institution', fn ($query) => $query->where('status', 'activa'))
            ->whereHas('roles', fn ($query) => $query->where('code', $role));
    }
}
