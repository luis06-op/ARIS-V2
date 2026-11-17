<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentLink extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'document_id', 'document_version_id', 'activity_version_id', 'submission_version_id',
        'review_round_id', 'created_at',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function documentVersion(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class);
    }

    public function activityVersion(): BelongsTo
    {
        return $this->belongsTo(ActivityVersion::class);
    }

    public function submissionVersion(): BelongsTo
    {
        return $this->belongsTo(SubmissionVersion::class);
    }

    public function reviewRound(): BelongsTo
    {
        return $this->belongsTo(ReviewRound::class);
    }
}
