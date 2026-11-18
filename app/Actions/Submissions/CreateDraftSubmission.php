<?php

namespace App\Actions\Submissions;

use App\Models\ActivityAssignment;
use App\Models\Project;
use App\Models\SubmissionVersion;
use App\Models\User;
use App\Services\Audit\RecordAuditEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class CreateDraftSubmission
{
    public function execute(User $actor, Project $project, array $data): SubmissionVersion
    {
        return DB::transaction(function () use ($actor, $project, $data): SubmissionVersion {
            $project = Project::whereKey($project->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor)->authorize('submit', $project);
            $assignment = ActivityAssignment::whereKey($data['activity_assignment_id'])->lockForUpdate()->first();
            if (! $assignment || $assignment->project_id !== $project->id || $assignment->status !== 'activa'
                || $assignment->assigned_at > now() || $assignment->target_type !== 'proyecto'
                || $assignment->target_ref !== (string) $project->id
                || $assignment->activityVersion->activity->project_id !== $project->id
                || $assignment->activityVersion->status !== 'publicada' || ! $assignment->activityVersion->published_at) {
                throw ValidationException::withMessages(['activity_assignment_id' => 'Selecciona una asignación vigente de una versión publicada para este proyecto.']);
            }
            $submission = app(CreateSubmission::class)->execute([
                'project_id' => $project->id, 'author_id' => $actor->id,
                'activity_assignment_id' => $assignment->id, 'title' => $data['title'],
            ]);
            $version = $submission->versions()->create(['version_number' => 1, 'author_id' => $actor->id, 'summary' => $data['summary']]);
            app(RecordAuditEvent::class)->execute($actor, $project->institution_id, 'submission.draft_created', 'submission_version', $version->id);

            return $version;
        });
    }
}
