<?php

namespace App\Http\Controllers\Submissions;

use App\Http\Controllers\Controller;
use App\Models\Ficha;
use App\Queries\SearchSubmissions;
use App\Queries\VisibleProjects;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request, SearchSubmissions $search, VisibleProjects $projects): View
    {
        $filters = $request->validate([
            'q' => ['sometimes', 'nullable', 'string', 'max:120'],
            'estado' => ['sometimes', 'nullable', 'string', 'in:borrador,entregada,en_revision,ajustes_solicitados,revisada,retirada'],
            'ficha_id' => ['sometimes', 'nullable', 'integer', 'exists:fichas,id'],
            'desde' => ['sometimes', 'nullable', 'date_format:Y-m-d'],
            'hasta' => ['sometimes', 'nullable', 'date_format:Y-m-d', 'after_or_equal:desde'],
        ]);

        $fichas = Ficha::whereIn('id', $projects->forUser($request->user())
            ->with('ficha')->get()->pluck('ficha_id')->filter()->unique())->with('especialidad')->orderBy('numero')->get();

        return view('entregas.buscar', [
            'resultados' => $search->forUser($request->user(), $filters)->paginate(15)->withQueryString(),
            'filtros' => $filters,
            'fichas' => $fichas,
            'estados' => ['borrador' => 'Borrador', 'entregada' => 'Enviada', 'en_revision' => 'En revisión',
                'ajustes_solicitados' => 'Requiere Ajustes', 'revisada' => 'Revisada', 'retirada' => 'Retirada'],
        ]);
    }
}
