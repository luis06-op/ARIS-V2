<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class InstitutionMembership extends Model
{
    protected $fillable = ['institution_id', 'user_id', 'status', 'joined_at', 'left_at'];

    protected function casts(): array
    {
        return ['joined_at' => 'date', 'left_at' => 'date'];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'membership_roles')
            ->withPivot(['assigned_by', 'assigned_at']);
    }
}
