<?php

namespace App\Policies;

use App\Models\SubmissionVersion;
use App\Models\User;
use App\Queries\VisibleProjects;
use Illuminate\Support\Facades\Gate;

final class SubmissionVersionPolicy
{
    public function view(User $user, SubmissionVersion $version): bool
    {
        $submission = $version->submission;

        return ($submission->author_id === $user->id && Gate::forUser($user)->allows('submit', $submission->project))
            || app(VisibleProjects::class)->forUser($user, staffOnly: true)->whereKey($submission->project_id)->exists();
    }

    public function upload(User $user, SubmissionVersion $version): bool
    {
        $submission = $version->submission;

        return $submission->author_id === $user->id && $submission->status === 'borrador'
            && $version->status === 'borrador' && $version->finalized_at === null
            && ! $submission->versions()->where('version_number', '>', $version->version_number)->exists()
            && Gate::forUser($user)->allows('submit', $submission->project);
    }
}
