<?php

namespace App\Http\Controllers\Identity;

use App\Actions\Identity\ImportInstitutionMembers;
use App\Http\Controllers\Controller;
use App\Models\Institution;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MembershipImportController extends Controller
{
    public function show(Institution $institution): View
    {
        Gate::authorize('manageRoles', $institution);

        return view('accesos.importar', ['institucion' => $institution, 'informe' => null]);
    }

    public function store(Request $request, Institution $institution, ImportInstitutionMembers $action): View|RedirectResponse
    {
        Gate::authorize('manageRoles', $institution);
        $data = $request->validate([
            'archivo' => ['required', 'file', 'mimes:csv,txt', 'max:1024'],
            'operacion' => ['required', 'in:vista_previa,confirmar'],
        ]);
        if (! str_ends_with(mb_strtolower($request->file('archivo')->getClientOriginalName()), '.csv')) {
            return back()->withErrors(['archivo' => 'El archivo debe tener extensión .csv.']);
        }
        $path = $request->file('archivo')->getRealPath();
        try {
            $informe = $action->preview($request->user(), $institution, $path);
            if ($data['operacion'] === 'confirmar') {
                $informe = $action->execute($request->user(), $institution, $path);
            }
        } catch (\RuntimeException $error) {
            return back()->withErrors(['archivo' => $error->getMessage()]);
        }

        return view('accesos.importar', ['institucion' => $institution, 'informe' => $informe]);
    }
}
