<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        $membership = $user->institutionMemberships()
            ->with(['institution', 'roles'])
            ->where('status', 'activa')
            ->whereDate('joined_at', '<=', today())
            ->where(fn ($query) => $query->whereNull('left_at')->orWhereDate('left_at', '>', today()))
            ->whereHas('institution', fn ($query) => $query->where('status', 'activa'))
            ->whereHas('user', fn ($query) => $query->where('status', 'activo'))
            ->first();

        $roles = $membership ? $membership->roles : collect();

        $etiqueta = $roles->isNotEmpty()
            ? $roles->pluck('name')->implode(' · ')
            : 'Sin rol institucional';

        return view('cuenta', [
            'rolInstitucional' => [
                'membresia' => $membership,
                'roles' => $roles,
                'etiqueta' => $etiqueta,
            ],
        ]);
    }
}
