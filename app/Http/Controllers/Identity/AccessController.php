<?php

namespace App\Http\Controllers\Identity;

use App\Actions\Identity\ConfigureEvaluatorScope;
use App\Actions\Identity\ConfigureInstructorSpecialty;
use App\Actions\Identity\ManageInstitutionRole;
use App\Domain\Identity\InstitutionalAuthority;
use App\Http\Controllers\Controller;
use App\Http\Requests\Identity\AccessRequest;
use App\Models\Especialidad;
use App\Models\Ficha;
use App\Models\Institution;
use App\Models\InstitutionMembership;
use App\Models\JurisdiccionEvaluador;
use App\Models\VinculoEspecialidad;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class AccessController extends Controller
{
    public function index(Request $request, InstitutionalAuthority $authority): View
    {
        return view('accesos.indice', ['instituciones' => Institution::whereIn('id', $authority->memberships($request->user(), 'administrador')->select('institution_id'))->get()]);
    }

    public function show(Institution $institution): View
    {
        Gate::authorize('manageRoles', $institution);
        $memberships = InstitutionMembership::where('institution_id', $institution->id)->with('user', 'roles')->get();

        return view('accesos.ver', [
            'institucion' => $institution,
            'membresias' => $memberships,
            'especialidades' => Especialidad::where('institution_id', $institution->id)->orderBy('nombre')->get(),
            'fichas' => Ficha::whereHas('especialidad', fn ($query) => $query->where('institution_id', $institution->id))->with('especialidad')->get(),
            'jurisdicciones' => JurisdiccionEvaluador::whereIn('institution_membership_id', $memberships->modelKeys())->whereNull('revoked_at')->get(),
            'vinculos' => VinculoEspecialidad::whereIn('institution_membership_id', $memberships->modelKeys())->whereNull('revoked_at')->get(),
        ]);
    }

    public function store(AccessRequest $request, Institution $institution): RedirectResponse
    {
        $membership = InstitutionMembership::where('institution_id', $institution->id)->findOrFail($request->validated('membership_id'));
        match ($request->validated('operation')) {
            'rol' => app(ManageInstitutionRole::class)->execute($request->user(), $membership, $request->validated('role'), $request->boolean('active')),
            'instructor' => app(ConfigureInstructorSpecialty::class)->execute($request->user(), $membership, Especialidad::findOrFail($request->validated('especialidad_id')), $request->boolean('active')),
            'evaluador' => app(ConfigureEvaluatorScope::class)->execute($request->user(), $membership, Ficha::findOrFail($request->validated('ficha_id')), $request->boolean('active')),
        };

        return to_route('accesos.index')->with('status', 'Permisos actualizados y auditados.');
    }
}
