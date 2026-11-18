<?php

namespace App\Policies;

use App\Models\ActivityVersion;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class ActivityVersionPolicy
{
    public function view(User $user, ActivityVersion $version): bool
    {
        $project = $version->activity->project;

        return Gate::forUser($user)->allows('view', $project)
            && (Gate::forUser($user)->allows('update', $project)
                || ($version->status === 'publicada' && $version->published_at !== null
                    && $version->assignments()->where('project_id', $project->id)->where('target_type', 'proyecto')
                        ->where('target_ref', (string) $project->id)->where('status', 'activa')->where('assigned_at', '<=', now())->exists()));
    }

    public function update(User $user, ActivityVersion $version): bool
    {
        return Gate::forUser($user)->allows('update', $version->activity->project);
    }
}
