<?php

namespace App\Policies;

use App\Domain\Identity\InstitutionalAuthority;
use App\Models\AuditEvent;
use App\Models\User;

final class AuditEventPolicy
{
    public function __construct(private InstitutionalAuthority $authority) {}

    public function viewAny(User $user): bool
    {
        return $this->authority->memberships($user, 'administrador')->exists();
    }

    public function viewInstitution(User $user, AuditEvent $event): bool
    {
        return $this->authority->isAdministrator($user, (int) $event->institution_id);
    }
}
