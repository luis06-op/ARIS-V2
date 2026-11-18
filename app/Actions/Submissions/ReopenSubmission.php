<?php

namespace App\Actions\Submissions;

use App\Models\Submission;
use App\Models\SubmissionVersion;
use App\Models\User;
use App\Services\Audit\RecordAuditEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

final class ReopenSubmission
{
    public function execute(User $actor, SubmissionVersion $version, string $reason, string $ip): void
    {
        Validator::make(['motivo' => trim($reason)], ['motivo' => ['required', 'string', 'max:5000']])->validate();
        DB::transaction(function () use ($actor, $version, $reason, $ip): void {
            $submission = Submission::whereKey($version->submission_id)->lockForUpdate()->firstOrFail();
            $version = SubmissionVersion::whereKey($version->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor)->authorize('reopen', $version);
            $submission->update(['status' => 'ajustes_solicitados', 'lock_version' => $submission->lock_version + 1]);
            app(RecordAuditEvent::class)->execute($actor, $submission->project->institution_id, 'REAPERTURA_ENTREGA', 'submission', $submission->id, [
                'entrega_id' => $submission->id, 'version_id' => $version->id, 'motivo' => trim($reason), 'ip_address' => $ip,
            ]);
        });
    }
}
