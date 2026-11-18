<?php

namespace App\Actions\Guides;

use App\Models\Activity;
use App\Models\ActivityAssignment;
use App\Models\ActivityVersion;
use App\Models\User;
use App\Services\Audit\RecordAuditEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class PublishAndAssignGuide
{
    public function execute(User $actor, ActivityVersion $version): void
    {
        DB::transaction(function () use ($actor, $version): void {
            $activity = Activity::whereKey($version->activity_id)->lockForUpdate()->firstOrFail();
            $version = ActivityVersion::whereKey($version->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor)->authorize('update', $version);
            if ($version->status === 'borrador') {
                $version->update(['status' => 'publicada', 'published_at' => now(), 'checksum' => hash('sha256', (string) $version->content)]);
            }
            $activity->update(['status' => 'publicada']);
            ActivityAssignment::firstOrCreate([
                'activity_version_id' => $version->id, 'target_type' => 'proyecto', 'target_ref' => (string) $activity->project_id,
            ], ['project_id' => $activity->project_id, 'assigned_by' => $actor->id, 'assigned_at' => now()]);
            app(RecordAuditEvent::class)->execute($actor, $activity->project->institution_id, 'guide.assigned', 'activity_version', $version->id);
        });
    }
}
