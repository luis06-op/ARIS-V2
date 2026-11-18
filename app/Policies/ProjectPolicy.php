<?php

namespace App\Policies;

use App\Domain\Identity\InstitutionalAuthority;
use App\Models\Ficha;
use App\Models\Project;
use App\Models\User;
use App\Queries\CreatableFichas;
use App\Queries\VisibleProjects;

final class ProjectPolicy
{
    public function __construct(private InstitutionalAuthority $authority) {}

    public function view(User $user, Project $project): bool
    {
        return app(VisibleProjects::class)->forUser($user)->whereKey($project->id)->exists();
    }

    public function create(User $user, Ficha $ficha): bool
    {
        return app(CreatableFichas::class)->forUser($user)->whereKey($ficha->id)->exists();
    }

    public function update(User $user, Project $project): bool
    {
        return $this->view($user, $project) && (
            $this->authority->isAdministrator($user, $project->institution_id)
            || ($this->authority->memberships($user, 'instructor')->where('institution_id', $project->institution_id)->exists()
                && $project->memberships()->where('user_id', $user->id)->where('function', 'instructor')
                    ->whereDate('assigned_at', '<=', today())
                    ->where(fn ($query) => $query->whereNull('ended_at')->orWhereDate('ended_at', '>', today()))->exists())
        );
    }

    public function authorizeUnlinkedException(User $user, Project $project): bool
    {
        return app(VisibleProjects::class)->forUser($user, staffOnly: true)->whereKey($project->id)->exists();
    }

    public function submit(User $user, Project $project): bool
    {
        return $this->authority->memberships($user, 'aprendiz')->where('institution_id', $project->institution_id)->exists()
            && $project->memberships()->where('user_id', $user->id)->where('function', 'aprendiz')
                ->whereDate('assigned_at', '<=', today())
                ->where(fn ($query) => $query->whereNull('ended_at')->orWhereDate('ended_at', '>', today()))->exists();
    }

    public function manageLearners(User $user, Project $project): bool
    {
        return $this->authority->memberships($user, 'instructor')->where('institution_id', $project->institution_id)->exists()
            && $project->memberships()->where('user_id', $user->id)->where('function', 'instructor')
                ->whereDate('assigned_at', '<=', today())
                ->where(fn ($query) => $query->whereNull('ended_at')->orWhereDate('ended_at', '>', today()))->exists();
    }

    public function manageTutors(User $user, Project $project): bool
    {
        return $this->authority->isAdministrator($user, $project->institution_id);
    }
}
