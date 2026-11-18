<?php

namespace App\Actions\Guides;

use App\Models\Activity;
use App\Models\ActivityVersion;
use App\Models\User;
use App\Services\Audit\RecordAuditEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class SaveGuideVersion
{
    public function execute(User $actor, ActivityVersion $version, array $data): ActivityVersion
    {
        return DB::transaction(function () use ($actor, $version, $data): ActivityVersion {
            Activity::whereKey($version->activity_id)->lockForUpdate()->firstOrFail();
            $version = ActivityVersion::whereKey($version->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor)->authorize('update', $version);
            $updated = app(UpdateGuideVersion::class)->execute($version, $data, $actor);
            app(RecordAuditEvent::class)->execute($actor, $version->activity->project->institution_id, 'guide.updated', 'activity_version', $updated->id);

            return $updated;
        });
    }
}
