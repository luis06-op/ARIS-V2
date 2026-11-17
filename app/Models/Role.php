<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = ['code', 'name', 'description', 'is_system'];

    protected function casts(): array
    {
        return ['is_system' => 'boolean'];
    }

    public function institutionMemberships(): BelongsToMany
    {
        return $this->belongsToMany(InstitutionMembership::class, 'membership_roles')
            ->withPivot(['assigned_by', 'assigned_at']);
    }
}
