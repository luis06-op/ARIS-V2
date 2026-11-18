<?php

namespace App\Actions\Guides;

use App\Models\Activity;
use App\Models\ActivityVersion;
use App\Models\Project;
use App\Models\User;
use App\Services\Audit\RecordAuditEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

final class CreateGuide
{
    public function execute(User $actor, Project $project, array $data): ActivityVersion
    {
        return DB::transaction(function () use ($actor, $project, $data): ActivityVersion {
            $project = Project::whereKey($project->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor)->authorize('update', $project);
            if (Activity::where('project_id', $project->id)->where('code', $data['code'])->exists()) {
                throw ValidationException::withMessages(['code' => 'El código de guía ya existe en este proyecto.']);
            }
            $activity = Activity::create(['project_id' => $project->id, 'code' => $data['code'], 'title' => $data['title'], 'created_by' => $actor->id]);
            $version = ActivityVersion::create([
                'activity_id' => $activity->id, 'version_number' => 1, 'title' => $data['title'],
                'content' => $data['content'], 'checksum' => hash('sha256', $data['content']), 'author_id' => $actor->id,
            ]);
            app(RecordAuditEvent::class)->execute($actor, $project->institution_id, 'guide.created', 'activity_version', $version->id);

            return $version;
        });
    }
}
