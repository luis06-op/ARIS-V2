<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JurisdiccionEvaluador extends Model
{
    protected $table = 'jurisdicciones_evaluador';

    protected $fillable = ['institution_membership_id', 'ficha_id', 'assigned_by', 'revoked_at'];

    protected function casts(): array
    {
        return ['revoked_at' => 'datetime'];
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(InstitutionMembership::class, 'institution_membership_id');
    }
}
