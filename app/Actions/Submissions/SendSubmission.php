<?php

namespace App\Actions\Submissions;

use App\Models\Submission;
use App\Models\SubmissionVersion;
use App\Models\User;
use App\Services\Audit\RecordAuditEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class SendSubmission
{
    public function execute(User $actor, SubmissionVersion $version, string $ip): void
    {
        DB::transaction(function () use ($actor, $version, $ip): void {
            $submission = Submission::whereKey($version->submission_id)->lockForUpdate()->firstOrFail();
            $version = SubmissionVersion::whereKey($version->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor)->authorize('send', $version);
            $version->update(['status' => 'finalizada', 'finalized_at' => now()]);
            $submission->update(['status' => 'entregada', 'lock_version' => $submission->lock_version + 1]);
            app(RecordAuditEvent::class)->execute($actor, $submission->project->institution_id, 'submission.sent', 'submission_version', $version->id, ['entrega_id' => $submission->id, 'ip' => $ip]);
        });
    }
}
