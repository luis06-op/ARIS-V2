<?php

namespace App\Http\Controllers\Projects;

use App\Actions\Projects\CreateProject;
use App\Actions\Projects\UpdateProject;
use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\CreateProjectRequest;
use App\Http\Requests\Projects\UpdateProjectRequest;
use App\Models\ActivityAssignment;
use App\Models\ActivityVersion;
use App\Models\Ficha;
use App\Models\Project;
use App\Models\Submission;
use App\Models\User;
use App\Queries\CreatableFichas;
use App\Queries\VisibleProjects;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ProjectController extends Controller
{
    public function index(Request $request, VisibleProjects $projects, CreatableFichas $fichas): View
    {
        return view('proyectos.indice', [
            'proyectos' => $projects->forUser($request->user())->with('ficha.especialidad')->orderByDesc('id')->paginate(15),
            'puedeCrear' => $fichas->forUser($request->user())->exists(),
        ]);
    }

    public function create(Request $request, CreatableFichas $fichas): View
    {
        $available = $fichas->forUser($request->user())->with('especialidad.institution')->get();
        abort_if($available->isEmpty(), 403);

        return view('proyectos.crear', ['fichas' => $available]);
    }

    public function store(CreateProjectRequest $request, CreateProject $action): RedirectResponse
    {
        $action->execute($request->user(), Ficha::findOrFail($request->validated('ficha_id')), $request->validated());

        return to_route('proyectos.index')->with('status', 'Proyecto creado. El administrador puede designar al instructor tutor.');
    }

    public function show(Project $project): View
    {
        Gate::authorize('view', $project);

        return view('proyectos.ver', [
            'proyecto' => $project->load('ficha.especialidad'),
            'candidatosAprendices' => Gate::allows('manageLearners', $project)
                ? User::whereHas('institutionMemberships', fn ($query) => $query->where('institution_id', $project->institution_id)
                    ->whereHas('roles', fn ($roles) => $roles->where('code', 'aprendiz')))
                    ->orWhereHas('projectMemberships', fn ($query) => $query->where('project_id', $project->id)->where('function', 'aprendiz'))
                    ->orderBy('name')->get() : collect(),
            'aprendices' => Gate::allows('authorizeUnlinkedException', $project)
                ? User::whereHas('projectMemberships', fn ($query) => $query->where('project_id', $project->id)->where('function', 'aprendiz')
                    ->whereDate('assigned_at', '<=', today())->where(fn ($dates) => $dates->whereNull('ended_at')->orWhereDate('ended_at', '>', today())))->get() : collect(),
            'entregas' => Submission::where('project_id', $project->id)
                ->when(! Gate::allows('authorizeUnlinkedException', $project), fn ($query) => $query->where('author_id', auth()->id()))
                ->with(['versions' => fn ($query) => $query->orderByDesc('version_number')])->orderByDesc('id')->get(),
            'asignaciones' => ActivityAssignment::where('project_id', $project->id)->where('status', 'activa')
                ->where('target_type', 'proyecto')->where('target_ref', (string) $project->id)->where('assigned_at', '<=', now())
                ->whereHas('activityVersion', fn ($query) => $query->where('status', 'publicada')->whereNotNull('published_at'))
                ->with('activityVersion')->get(),
            'guias' => ActivityVersion::whereHas('activity', fn ($query) => $query->where('project_id', $project->id))
                ->when(! Gate::allows('update', $project), fn ($query) => $query->where('status', 'publicada')->whereNotNull('published_at')
                    ->whereHas('assignments', fn ($assignment) => $assignment->where('project_id', $project->id)
                        ->where('target_type', 'proyecto')->where('target_ref', (string) $project->id)->where('status', 'activa')->where('assigned_at', '<=', now())))
                ->orderByDesc('id')->get(),
            'instructores' => Gate::allows('manageTutors', $project)
                ? User::whereHas('institutionMemberships', fn ($query) => $query->where('institution_id', $project->institution_id)
                    ->whereHas('roles', fn ($roles) => $roles->where('code', 'instructor')))
                    ->orWhereHas('projectMemberships', fn ($query) => $query->where('project_id', $project->id)->where('function', 'instructor'))
                    ->orderBy('name')->get() : collect(),
        ]);
    }

    public function edit(Project $project): View
    {
        Gate::authorize('update', $project);

        return view('proyectos.editar', ['proyecto' => $project]);
    }

    public function update(UpdateProjectRequest $request, Project $project, UpdateProject $action): RedirectResponse
    {
        $action->execute($request->user(), $project, $request->validated());

        return to_route('proyectos.show', $project)->with('status', 'Proyecto actualizado.');
    }
}
