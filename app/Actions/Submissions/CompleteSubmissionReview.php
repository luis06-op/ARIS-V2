<?php

namespace App\Actions\Submissions;

use App\Models\Submission;
use App\Models\SubmissionVersion;
use App\Models\User;
use App\Services\Audit\RecordAuditEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

final class CompleteSubmissionReview
{
    public function execute(User $actor, SubmissionVersion $version, string $result, string $feedback, string $ip): void
    {
        Validator::make(['resultado' => $result, 'retroalimentacion' => trim($feedback)], [
            'resultado' => ['required', Rule::in(['cumple', 'requiere_ajustes'])],
            'retroalimentacion' => ['required', 'string', 'max:10000'],
        ])->validate();
        DB::transaction(function () use ($actor, $version, $result, $feedback, $ip): void {
            $submission = Submission::whereKey($version->submission_id)->lockForUpdate()->firstOrFail();
            $version = SubmissionVersion::whereKey($version->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor)->authorize('completeReview', $version);
            $round = $version->reviewRounds()->where('status', 'en_curso')->where('evaluator_id', $actor->id)->lockForUpdate()->firstOrFail();
            $round->comments()->create(['author_id' => $actor->id, 'body' => trim($feedback), 'visibility' => 'autor']);
            $round->update(['decision' => $result === 'cumple' ? 'aprobada' : 'ajustes', 'status' => 'finalizada', 'completed_at' => now()]);
            $submission->update(['status' => $result === 'cumple' ? 'revisada' : 'ajustes_solicitados', 'lock_version' => $submission->lock_version + 1]);
            app(RecordAuditEvent::class)->execute($actor, $submission->project->institution_id, 'submission.review_completed', 'review_round', $round->id, ['entrega_id' => $submission->id, 'version_id' => $version->id, 'resultado' => $result, 'ip' => $ip]);
        });
    }
}
