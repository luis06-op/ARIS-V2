<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityAssignment extends Model
{
    protected $fillable = [
        'activity_version_id', 'project_id', 'target_type', 'target_ref', 'target_user_id',
        'assigned_by', 'assigned_at', 'due_at', 'status',
    ];

    public function activityVersion(): BelongsTo
    {
        return $this->belongsTo(ActivityVersion::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }
}
