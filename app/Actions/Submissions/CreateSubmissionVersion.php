<?php

namespace App\Actions\Submissions;

use App\Models\Submission;
use App\Models\SubmissionVersion;
use App\Models\User;
use App\Services\Audit\RecordAuditEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class CreateSubmissionVersion
{
    public function execute(User $actor, SubmissionVersion $previous, string $summary): SubmissionVersion
    {
        return DB::transaction(function () use ($actor, $previous, $summary): SubmissionVersion {
            $submission = Submission::whereKey($previous->submission_id)->lockForUpdate()->firstOrFail();
            $previous = SubmissionVersion::findOrFail($previous->id);
            Gate::forUser($actor)->authorize('upload', $previous);
            $number = (int) $submission->versions()->max('version_number') + 1;
            $version = $submission->versions()->create(['version_number' => $number, 'author_id' => $actor->id, 'summary' => $summary]);
            app(RecordAuditEvent::class)->execute($actor, $submission->project->institution_id, 'submission.version_created', 'submission_version', $version->id);

            return $version;
        });
    }
}
