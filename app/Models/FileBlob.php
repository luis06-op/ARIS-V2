<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FileBlob extends Model
{
    protected $fillable = [
        'uuid', 'disk', 'storage_key', 'size_bytes', 'sha256', 'mime', 'extension', 'status',
    ];

    public function documentVersions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class, 'blob_id');
    }
}
