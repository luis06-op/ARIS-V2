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

    public function createVersion(User $user, SubmissionVersion $version): bool
    {
        return $this->upload($user, $version) || (
            $version->submission->author_id === $user->id
            && $version->submission->status === 'ajustes_solicitados'
            && $version->status === 'finalizada' && $version->finalized_at !== null
            && $this->latest($version)
            && Gate::forUser($user)->allows('submit', $version->submission->project)
        );
    }

    public function send(User $user, SubmissionVersion $version): bool
    {
        return $this->upload($user, $version);
    }

    public function startReview(User $user, SubmissionVersion $version): bool
    {
        return $this->staff($user, $version) && $version->submission->status === 'entregada';
    }

    public function completeReview(User $user, SubmissionVersion $version): bool
    {
        return $this->staff($user, $version) && $version->submission->status === 'en_revision'
            && $version->reviewRounds()->where('status', 'en_curso')->where('evaluator_id', $user->id)->exists();
    }

    public function reopen(User $user, SubmissionVersion $version): bool
    {
        return $this->staff($user, $version) && $version->submission->status === 'revisada';
    }

    private function staff(User $user, SubmissionVersion $version): bool
    {
        return $version->status === 'finalizada' && $version->finalized_at !== null
            && $this->latest($version)
            && app(VisibleProjects::class)->forUser($user, staffOnly: true)
                ->whereKey($version->submission->project_id)->exists();
    }

    private function latest(SubmissionVersion $version): bool
    {
        return ! $version->submission->versions()->where('version_number', '>', $version->version_number)->exists();
    }
}
