<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityVersion extends Model
{
    protected $fillable = [
        'activity_id', 'version_number', 'lock_version', 'title', 'content', 'author_id', 'checksum',
        'status', 'published_at',
    ];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ActivityAssignment::class);
    }

    public function documentLinks(): HasMany
    {
        return $this->hasMany(DocumentLink::class);
    }
}
