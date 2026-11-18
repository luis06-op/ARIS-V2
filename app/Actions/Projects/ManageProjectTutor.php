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

final class ManageProjectTutor
{
    public function execute(User $actor, Project $project, User $tutor, bool $active): void
    {
        DB::transaction(function () use ($actor, $project, $tutor, $active): void {
            $project = Project::whereKey($project->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor)->authorize('manageTutors', $project);
            if ($active && ! app(InstitutionalAuthority::class)->memberships($tutor, 'instructor')->where('institution_id', $project->institution_id)->exists()) {
                throw ValidationException::withMessages(['user_id' => 'Selecciona un instructor vigente de esta institución.']);
            }
            $assignment = ProjectMembership::where('project_id', $project->id)->where('user_id', $tutor->id)->where('function', 'instructor')->first();
            if ($active) {
                ProjectMembership::updateOrCreate([
                    'project_id' => $project->id, 'user_id' => $tutor->id, 'function' => 'instructor',
                ], ['assigned_at' => today(), 'ended_at' => null, 'assigned_by' => $actor->id]);
            } elseif ($assignment) {
                $assignment->update(['ended_at' => today()]);
            }
            app(RecordAuditEvent::class)->execute($actor, $project->institution_id, 'project.tutor_changed', 'project', $project->id, [
                'tutor_id' => $tutor->id, 'activo' => $active,
            ]);
        });
    }
}
