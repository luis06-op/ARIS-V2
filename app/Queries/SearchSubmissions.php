<?php

namespace App\Queries;

use App\Domain\Identity\InstitutionalAuthority;
use App\Models\ProjectMembership;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Busca entregas aplicando el mismo alcance autorizado que SubmissionVersionPolicy::view:
 * el autor ve sus propias entregas solo si mantiene acceso vigente al proyecto, y el
 * personal institucional (administrador, instructor, evaluador con jurisdicción vigente)
 * ve las entregas de sus proyectos.
 */
final class SearchSubmissions
{
    public function __construct(private VisibleProjects $projects) {}

    /** @param array{q?: string, estado?: string, ficha_id?: int|string|null, desde?: string|null, hasta?: string|null} $filters */
    public function forUser(User $user, array $filters): Builder
    {
        // Proyectos sobre los que el usuario ejerce autoridad efectiva como personal
        // institucional (administrador, instructor o evaluador con jurisdicción vigente).
        $staffProjects = app(VisibleProjects::class)->forUser($user, staffOnly: true)
            ->whereNotNull('projects.id')->select('projects.id');

        // Proyectos donde el usuario es aprendiz con membresía vigente (alcance de 'submit').
        // Se reutiliza InstitutionalAuthority::memberships() como fuente de verdad para la
        // membresía institucional del rol aprendiz (usuario/institución activos y fechas vigentes),
        // igual que ProjectPolicy::submit(). La asignación al proyecto aporta su propia vigencia
        // (assigned_at/ended_at) y el proyecto/institución activos.
        $institucionesAprendiz = app(InstitutionalAuthority::class)
            ->memberships($user, 'aprendiz')
            ->select('institution_id');

        $learnerProjects = ProjectMembership::query()
            ->where('user_id', $user->getKey())
            ->where('function', 'aprendiz')
            ->whereDate('assigned_at', '<=', today())
            ->where(fn ($query) => $query->whereNull('ended_at')->orWhereDate('ended_at', '>', today()))
            ->whereHas('project', fn ($query) => $query
                ->where('status', 'activo')
                ->whereIn('institution_id', $institucionesAprendiz))
            ->whereNotNull('project_id')
            ->select('project_id');

        return Submission::query()
            ->where(function (Builder $query) use ($user, $staffProjects, $learnerProjects): void {
                // El autor ve sus propias entregas solo si mantiene acceso vigente al proyecto.
                $query->where(function (Builder $autor) use ($user, $learnerProjects): void {
                    $autor->where('author_id', $user->getKey())
                        ->whereIn('project_id', $learnerProjects);
                })
                // El personal institucional ve las entregas de sus proyectos.
                    ->orWhereIn('project_id', $staffProjects);
            })
            // Solo se necesita la última versión y el total para el listado: se evita cargar
            // el historial completo de cada entrega (OPT REV-PERF). La búsqueda por resumen
            // y nombres de archivo usa subconsultas SQL, no depende del eager loading.
            ->with(['project.ficha.especialidad', 'latestVersion'])
            ->withCount('versions')
            ->when(($filters['estado'] ?? '') !== '', fn ($query) => $query->where('status', $filters['estado']))
            ->when(($filters['ficha_id'] ?? '') !== '', fn ($query) => $query->whereHas('project', fn ($project) => $project->where('ficha_id', $filters['ficha_id'])))
            ->when(($filters['desde'] ?? '') !== '', fn ($query) => $query->whereDate('created_at', '>=', $filters['desde']))
            ->when(($filters['hasta'] ?? '') !== '', fn ($query) => $query->whereDate('created_at', '<=', $filters['hasta']))
            ->when($this->text($filters) !== '', function (Builder $query) use ($filters): void {
                $term = '%'.addcslashes($this->text($filters), '%_\\').'%';
                $query->where(function (Builder $group) use ($term): void {
                    $group->where('title', 'like', $term)
                        ->orWhere('team_label', 'like', $term)
                        ->orWhereHas('versions', fn ($version) => $version->where('summary', 'like', $term))
                        ->orWhereHas('assignment.activityVersion', fn ($guide) => $guide
                            ->where('title', 'like', $term)->orWhere('content', 'like', $term))
                        ->orWhereHas('documents.versions', fn ($file) => $file->where('original_name', 'like', $term));
                });
            })
            ->orderByDesc('id');
    }

    private function text(array $filters): string
    {
        return trim((string) ($filters['q'] ?? ''));
    }
}
