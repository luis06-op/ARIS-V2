<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReviewRound extends Model
{
    protected $fillable = [
        'submission_version_id', 'evaluator_id', 'round_number', 'status', 'decision',
        'started_at', 'completed_at',
    ];

    public function submissionVersion(): BelongsTo
    {
        return $this->belongsTo(SubmissionVersion::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ReviewComment::class);
    }

    public function documentLinks(): HasMany
    {
        return $this->hasMany(DocumentLink::class);
    }
}
