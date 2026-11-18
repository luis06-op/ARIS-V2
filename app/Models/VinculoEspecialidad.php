<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VinculoEspecialidad extends Model
{
    protected $table = 'vinculos_especialidad';

    protected $fillable = ['institution_membership_id', 'especialidad_id', 'can_create_projects', 'assigned_by', 'revoked_at'];

    protected function casts(): array
    {
        return ['can_create_projects' => 'boolean', 'revoked_at' => 'datetime'];
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(InstitutionMembership::class, 'institution_membership_id');
    }
}
