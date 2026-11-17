<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewComment extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'review_round_id', 'author_id', 'body', 'criterion_ref', 'document_version_id', 'visibility',
    ];

    public function reviewRound(): BelongsTo
    {
        return $this->belongsTo(ReviewRound::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function documentVersion(): BelongsTo
    {
        return $this->belongsTo(DocumentVersion::class);
    }
}
