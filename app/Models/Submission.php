<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Submission extends Model
{
    protected $fillable = [
        'project_id', 'activity_assignment_id', 'author_id', 'team_label', 'title', 'status',
        'lock_version',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(ActivityAssignment::class, 'activity_assignment_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(SubmissionVersion::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}
