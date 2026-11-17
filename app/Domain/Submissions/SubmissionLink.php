<?php

namespace App\Domain\Submissions;

use App\Exceptions\DomainRuleViolation;
use App\Models\ActivityAssignment;
use App\Models\ActivityVersion;
use App\Models\Project;

/**
 * PI-001: una entrega se vincula a una asignación vigente y, por ella, a una versión concreta
 * de la guía. No existe la asignación implícita «a la última guía» (INV-010…INV-013).
 */
final class SubmissionLink
{
    public function versionDe(ActivityAssignment $asignacion): ActivityVersion
    {
        if ($asignacion->status !== 'activa') {
            throw new DomainRuleViolation('La asignación de la guía está revocada.');
        }

        $version = $asignacion->activityVersion;

        if ($version->status !== 'publicada' || $version->published_at === null) {
            throw new DomainRuleViolation('La versión de la guía asignada no está publicada.');
        }

        return $version;
    }

    public function assertMismoProyecto(ActivityAssignment $asignacion, Project $proyecto): void
    {
        if ($asignacion->project_id !== $proyecto->id) {
            throw new DomainRuleViolation('La asignación pertenece a otro proyecto.');
        }
    }
}
