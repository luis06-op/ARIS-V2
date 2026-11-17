<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentVersion extends Model
{
    protected $fillable = [
        'document_id', 'version_number', 'blob_id', 'author_id', 'original_name', 'mime',
        'extension', 'size_bytes', 'sha256', 'status', 'finalized_at',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function blob(): BelongsTo
    {
        return $this->belongsTo(FileBlob::class, 'blob_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function documentLinks(): HasMany
    {
        return $this->hasMany(DocumentLink::class);
    }

    public function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    public function reviewComments(): HasMany
    {
        return $this->hasMany(ReviewComment::class);
    }
}
