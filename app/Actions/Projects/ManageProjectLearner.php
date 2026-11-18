<?php

namespace App\Actions\Projects;

use App\Domain\Identity\InstitutionalAuthority;
use App\Models\Project;
use App\Models\ProjectMembership;
use App\Models\User;
use App\Services\Audit\RecordAuditEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class ManageProjectLearner
{
    public function execute(User $actor, Project $project, User $learner, bool $active): void
    {
        DB::transaction(function () use ($actor, $project, $learner, $active): void {
            $project = Project::whereKey($project->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor)->authorize('manageLearners', $project);
            if ($active && ! app(InstitutionalAuthority::class)->memberships($learner, 'aprendiz')->where('institution_id', $project->institution_id)->exists()) {
                throw ValidationException::withMessages(['user_id' => 'Selecciona un aprendiz vigente de esta institución.']);
            }
            if ($active) {
                ProjectMembership::updateOrCreate([
                    'project_id' => $project->id, 'user_id' => $learner->id, 'function' => 'aprendiz',
                ], ['assigned_at' => today(), 'ended_at' => null, 'assigned_by' => $actor->id]);
            } else {
                ProjectMembership::where('project_id', $project->id)->where('user_id', $learner->id)->where('function', 'aprendiz')->update(['ended_at' => today()]);
            }
            app(RecordAuditEvent::class)->execute($actor, $project->institution_id, 'project.learner_changed', 'project', $project->id, ['aprendiz_id' => $learner->id, 'activo' => $active]);
        });
    }
}
