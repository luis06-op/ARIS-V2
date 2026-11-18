<?php

namespace App\Queries;

use App\Domain\Identity\InstitutionalAuthority;
use App\Models\Ficha;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class CreatableFichas
{
    public function __construct(private InstitutionalAuthority $authority) {}

    public function forUser(User $user): Builder
    {
        return Ficha::query()->whereHas('especialidad', function (Builder $specialty) use ($user): void {
            $specialty->where(function (Builder $access) use ($user): void {
                $access->whereIn('institution_id', $this->authority->memberships($user, 'administrador')->select('institution_id'))
                    ->orWhereHas('vinculos', fn (Builder $link) => $link
                        ->whereNull('revoked_at')->where('can_create_projects', true)
                        ->whereIn('institution_membership_id', $this->authority->memberships($user, 'instructor')
                            ->whereColumn('institution_id', 'especialidades.institution_id')->select('id')));
            });
        });
    }
}
