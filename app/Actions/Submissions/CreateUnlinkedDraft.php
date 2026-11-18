<?php

namespace App\Actions\Submissions;

use App\Models\Project;
use App\Models\SubmissionVersion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class CreateUnlinkedDraft
{
    public function execute(User $actor, Project $project, User $beneficiary, array $data, string $ip): SubmissionVersion
    {
        return DB::transaction(function () use ($actor, $project, $beneficiary, $data, $ip): SubmissionVersion {
            $project = Project::whereKey($project->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor)->authorize('authorizeUnlinkedException', $project);
            if (! Gate::forUser($beneficiary)->allows('submit', $project)) {
                throw ValidationException::withMessages(['user_id' => 'Selecciona un aprendiz vigente de este proyecto.']);
            }
            $submission = app(CreateSubmission::class)->execute([
                'project_id' => $project->id, 'author_id' => $beneficiary->id,
                'title' => $data['title'], 'motivo_excepcion' => $data['motivo_excepcion'],
            ], $actor, $ip);

            return $submission->versions()->create(['version_number' => 1, 'author_id' => $beneficiary->id, 'summary' => $data['summary']]);
        });
    }
}
