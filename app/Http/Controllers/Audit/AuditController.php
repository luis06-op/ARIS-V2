<?php

namespace App\Http\Controllers\Audit;

use App\Domain\Identity\InstitutionalAuthority;
use App\Http\Controllers\Controller;
use App\Models\AuditEvent;
use App\Models\Institution;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function __invoke(Request $request, InstitutionalAuthority $authority): View
    {
        Gate::authorize('viewAny', AuditEvent::class);
        $institutions = Institution::whereIn('id', $authority->memberships($request->user(), 'administrador')->select('institution_id'))
            ->orderBy('name')->get();
        $allowed = $institutions->modelKeys();
        $requested = (int) $request->input('institucion_id');
        $institutionId = $requested > 0 && in_array($requested, $allowed, true) ? $requested : ($allowed[0] ?? null);
        abort_if($institutionId === null, 403);
        Gate::authorize('viewInstitution', new AuditEvent(['institution_id' => $institutionId]));

        $filters = $request->validate([
            'accion' => ['sometimes', 'nullable', 'string', 'max:120'],
            'actor_id' => ['sometimes', 'nullable', 'integer'],
            'desde' => ['sometimes', 'nullable', 'date_format:Y-m-d H:i'],
            'hasta' => ['sometimes', 'nullable', 'date_format:Y-m-d H:i'],
        ]);
        $events = AuditEvent::where('institution_id', $institutionId)
            ->with('actor')
            ->when(($filters['accion'] ?? '') !== '', fn ($query) => $query->where('action', 'like', '%'.$filters['accion'].'%'))
            ->when(($filters['actor_id'] ?? '') !== '', fn ($query) => $query->where('actor_id', $filters['actor_id']))
            ->when(($filters['desde'] ?? '') !== '', fn ($query) => $query->where('occurred_at', '>=', $filters['desde']))
            ->when(($filters['hasta'] ?? '') !== '', fn ($query) => $query->where('occurred_at', '<=', $filters['hasta'].':59'))
            ->orderByDesc('occurred_at')->paginate(20)->withQueryString();
        $actions = AuditEvent::where('institution_id', $institutionId)->distinct()->orderBy('action')->pluck('action');

        return view('auditoria.indice', [
            'eventos' => $events,
            'filtros' => $filters,
            'acciones' => $actions,
            'instituciones' => $institutions,
            'institucionId' => $institutionId,
        ]);
    }
}
