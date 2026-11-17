<?php

namespace App\Actions\Submissions;

use App\Domain\Submissions\SubmissionLink;
use App\Exceptions\DomainRuleViolation;
use App\Models\ActivityAssignment;
use App\Models\AuditEvent;
use App\Models\Project;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Crea una entrega respetando PI-001. La excepción sin asignación solo es posible si la capa HTTP
 * entrega un actor autorizado por una Policy; esa autoridad depende de PI-002 y hoy no se expone.
 */
final class CreateSubmission
{
    public function __construct(private readonly SubmissionLink $vinculo) {}

    /**
     * @param  array<string, mixed>  $datos
     */
    public function execute(array $datos, ?User $autorizador = null): Submission
    {
        $proyecto = Project::findOrFail($datos['project_id']);
        $autor = User::findOrFail($datos['author_id']);
        $asignacion = isset($datos['activity_assignment_id'])
            ? ActivityAssignment::findOrFail($datos['activity_assignment_id'])
            : null;

        if ($asignacion) {
            $this->vinculo->assertMismoProyecto($asignacion, $proyecto);
            $this->vinculo->versionDe($asignacion);
        } else {
            $motivo = trim((string) ($datos['motivo_excepcion'] ?? ''));

            if (! $autorizador || $motivo === '') {
                throw new DomainRuleViolation(
                    'Toda entrega debe declarar una asignación vigente, salvo excepción autorizada y auditada (PI-001).'
                );
            }
        }

        return DB::transaction(function () use ($datos, $proyecto, $autor, $asignacion, $autorizador): Submission {
            $entrega = Submission::create([
                'project_id' => $proyecto->id,
                'activity_assignment_id' => $asignacion?->id,
                'author_id' => $autor->id,
                'team_label' => $datos['team_label'] ?? null,
                'title' => $datos['title'],
            ]);

            if (! $asignacion) {
                AuditEvent::create([
                    'institution_id' => $proyecto->institution_id,
                    'actor_id' => $autorizador?->id,
                    'action' => 'submission.unlinked_exception',
                    'resource_type' => 'submission',
                    'resource_id' => (string) $entrega->id,
                    'result' => 'exito',
                    'metadata' => [
                        'motivo' => mb_substr(trim((string) $datos['motivo_excepcion']), 0, 255),
                        'autor' => $autor->id,
                    ],
                ]);
            }

            return $entrega;
        });
    }
}
