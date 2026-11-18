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
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

/**
 * Crea una entrega respetando PI-001; la excepción exige autoridad verificada en servidor.
 */
final class CreateSubmission
{
    public function __construct(private readonly SubmissionLink $vinculo) {}

    /**
     * @param  array<string, mixed>  $datos
     */
    public function execute(array $datos, ?User $autorizador = null, ?string $ip = null): Submission
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

            if (! $autorizador || mb_strlen($motivo) < 20 || mb_strlen($motivo) > 2000 || ! filter_var($ip, FILTER_VALIDATE_IP)) {
                throw new DomainRuleViolation(
                    'La excepción PI-001 exige autorizador, IP válida y justificación de 20 a 2000 caracteres.'
                );
            }
            Gate::forUser($autorizador)->authorize('authorizeUnlinkedException', $proyecto);
        }

        return DB::transaction(function () use ($datos, $proyecto, $autor, $asignacion, $autorizador, $ip): Submission {
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
                    'request_id' => (string) Str::uuid(),
                    'occurred_at' => now(),
                    'metadata' => [
                        'motivo' => trim((string) $datos['motivo_excepcion']),
                        'autor' => $autor->id,
                        'proyecto' => $proyecto->id,
                        'ip' => $ip,
                    ],
                ]);
            }

            return $entrega;
        });
    }
}
