<?php

namespace App\Actions\Submissions;

use App\Models\Submission;
use App\Models\SubmissionVersion;
use App\Models\User;
use App\Services\Audit\RecordAuditEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class StartSubmissionReview
{
    public function execute(User $actor, SubmissionVersion $version, string $ip): void
    {
        DB::transaction(function () use ($actor, $version, $ip): void {
            $submission = Submission::whereKey($version->submission_id)->lockForUpdate()->firstOrFail();
            $version = SubmissionVersion::whereKey($version->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor)->authorize('startReview', $version);
            $round = $version->reviewRounds()->create([
                'evaluator_id' => $actor->id, 'round_number' => (int) $version->reviewRounds()->max('round_number') + 1,
                'status' => 'en_curso', 'started_at' => now(),
            ]);
            $submission->update(['status' => 'en_revision', 'lock_version' => $submission->lock_version + 1]);
            app(RecordAuditEvent::class)->execute($actor, $submission->project->institution_id, 'submission.review_started', 'review_round', $round->id, ['entrega_id' => $submission->id, 'version_id' => $version->id, 'ip' => $ip]);
        });
    }
}
