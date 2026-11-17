<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Institution extends Model
{
    protected $fillable = ['name', 'code', 'status', 'timezone'];

    public function memberships(): HasMany
    {
        return $this->hasMany(InstitutionMembership::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
