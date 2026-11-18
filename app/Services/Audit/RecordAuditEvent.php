<?php

namespace App\Services\Audit;

use App\Models\AuditEvent;
use App\Models\User;
use Illuminate\Support\Str;

final class RecordAuditEvent
{
    public function execute(User $actor, int $institutionId, string $action, string $type, int $id, array $metadata = []): void
    {
        AuditEvent::create([
            'institution_id' => $institutionId,
            'actor_id' => $actor->id,
            'action' => $action,
            'resource_type' => $type,
            'resource_id' => (string) $id,
            'request_id' => (string) Str::uuid(),
            'result' => 'exito',
            'metadata' => $metadata,
            'occurred_at' => now(),
        ]);
    }
}
