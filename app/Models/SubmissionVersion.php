<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubmissionVersion extends Model
{
    protected $fillable = [
        'submission_id', 'version_number', 'author_id', 'summary', 'status', 'finalized_at',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function reviewRounds(): HasMany
    {
        return $this->hasMany(ReviewRound::class);
    }

    public function documentLinks(): HasMany
    {
        return $this->hasMany(DocumentLink::class);
    }
}
