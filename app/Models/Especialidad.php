<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Especialidad extends Model
{
    protected $table = 'especialidades';

    protected $fillable = ['institution_id', 'nombre', 'codigo'];

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function fichas(): HasMany
    {
        return $this->hasMany(Ficha::class);
    }

    public function vinculos(): HasMany
    {
        return $this->hasMany(VinculoEspecialidad::class);
    }
}
