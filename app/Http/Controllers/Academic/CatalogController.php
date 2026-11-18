<?php

namespace App\Http\Controllers\Academic;

use App\Actions\Academic\CreateFicha;
use App\Actions\Academic\CreateSpecialty;
use App\Http\Controllers\Controller;
use App\Http\Requests\Academic\CatalogRequest;
use App\Models\Especialidad;
use App\Models\Institution;
use Illuminate\Http\RedirectResponse;

class CatalogController extends Controller
{
    public function __invoke(CatalogRequest $request, Institution $institution): RedirectResponse
    {
        if ($request->validated('operation') === 'especialidad') {
            app(CreateSpecialty::class)->execute($request->user(), $institution, $request->validated());
        } else {
            app(CreateFicha::class)->execute($request->user(), Especialidad::where('institution_id', $institution->id)->findOrFail($request->validated('especialidad_id')), $request->validated());
        }

        return to_route('accesos.show', $institution)->with('status', 'Catálogo académico actualizado.');
    }
}
