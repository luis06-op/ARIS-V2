<?php

namespace App\Actions\Projects;

use App\Models\Project;
use App\Models\User;
use App\Services\Audit\RecordAuditEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class UpdateProject
{
    public function execute(User $actor, Project $project, array $data): void
    {
        DB::transaction(function () use ($actor, $project, $data): void {
            $project = Project::whereKey($project->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor)->authorize('update', $project);
            if ($project->lock_version !== (int) $data['lock_version']) {
                throw ValidationException::withMessages(['lock_version' => 'El proyecto cambió. Recarga la página antes de guardar.']);
            }
            $project->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'lock_version' => $project->lock_version + 1,
            ]);
            app(RecordAuditEvent::class)->execute($actor, $project->institution_id, 'project.updated', 'project', $project->id);
        });
    }
}
