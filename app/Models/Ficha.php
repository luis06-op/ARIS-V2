<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ficha extends Model
{
    protected $fillable = ['especialidad_id', 'numero', 'cohorte'];

    public function especialidad(): BelongsTo
    {
        return $this->belongsTo(Especialidad::class);
    }

    public function jurisdicciones(): HasMany
    {
        return $this->hasMany(JurisdiccionEvaluador::class);
    }
}
