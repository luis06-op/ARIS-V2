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
