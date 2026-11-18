<?php

namespace App\Queries;

use App\Domain\Identity\InstitutionalAuthority;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class VisibleProjects
{
    public function __construct(private InstitutionalAuthority $authority) {}

    public function forUser(User $user, bool $staffOnly = false): Builder
    {
        return Project::query()->where(function (Builder $query) use ($user, $staffOnly): void {
            $query->whereIn('institution_id', $this->authority->memberships($user, 'administrador')->select('institution_id'));

            foreach ($staffOnly ? ['instructor'] : ['instructor', 'aprendiz'] as $role) {
                $query->orWhere(function (Builder $member) use ($user, $role): void {
                    $member->whereIn('institution_id', $this->authority->memberships($user, $role)->select('institution_id'))
                        ->whereHas('memberships', fn (Builder $assignment) => $assignment
                            ->where('user_id', $user->id)->where('function', $role)
                            ->whereDate('assigned_at', '<=', today())
                            ->where(fn (Builder $dates) => $dates->whereNull('ended_at')->orWhereDate('ended_at', '>', today())));
                });
            }

            $query->orWhere(function (Builder $evaluator) use ($user): void {
                $memberships = $this->authority->memberships($user, 'evaluador');
                $evaluator->whereIn('institution_id', (clone $memberships)->select('institution_id'))
                    ->whereHas('ficha.especialidad', fn (Builder $specialty) => $specialty->whereColumn('institution_id', 'projects.institution_id'))
                    ->whereHas('ficha.jurisdicciones', fn (Builder $scope) => $scope
                        ->whereNull('revoked_at')
                        ->whereIn('institution_membership_id', $memberships->whereColumn('institution_id', 'projects.institution_id')->select('id')));
            });
        });
    }
}
