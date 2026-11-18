<?php

namespace App\Policies;

use App\Domain\Identity\InstitutionalAuthority;
use App\Models\Institution;
use App\Models\User;

final class InstitutionPolicy
{
    public function __construct(private InstitutionalAuthority $authority) {}

    public function manageRoles(User $user, Institution $institution): bool
    {
        return $this->authority->isAdministrator($user, $institution->id);
    }
}
