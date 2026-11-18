<?php

namespace App\Queries;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class SearchSubmissions
{
    public function __construct(private VisibleProjects $projects) {}

    /** @param array{q?: string, estado?: string, ficha_id?: int|string|null, desde?: string|null, hasta?: string|null} $filters */
    public function forUser(User $user, array $filters): Builder
    {
        $allowed = $this->projects->forUser($user)->select('projects.id');

        return Submission::query()
            ->whereIn('project_id', $allowed)
            ->with(['project.ficha.especialidad', 'versions' => fn ($query) => $query->orderByDesc('version_number')])
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
