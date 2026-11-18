<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'institution_id', 'actor_id', 'action', 'resource_type', 'resource_id', 'result',
        'request_id', 'ip_hash', 'metadata', 'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'occurred_at' => 'datetime',
        ];
    }

    /** Detalles no sensibles para el visor Administrativo; excluye IP, justificación y correo. */
    public function safeDetail(): string
    {
        if (! is_array($this->metadata)) {
            return '';
        }
        $parts = [];
        foreach (['proyecto', 'version', 'resultado', 'extension'] as $key) {
            if (isset($this->metadata[$key]) && is_scalar($this->metadata[$key])) {
                $parts[] = $key.': '.(string) $this->metadata[$key];
            }
        }

        return implode(' · ', $parts);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
