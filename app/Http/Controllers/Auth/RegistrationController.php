<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegistrationRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.registro');
    }

    public function store(RegistrationRequest $request): RedirectResponse
    {
        $user = DB::transaction(fn (): User => User::create([
            'name' => $request->string('name')->trim()->value(),
            'email' => Str::lower($request->string('email')->trim()->value()),
            'password' => $request->string('password')->value(),
            'status' => 'activo',
            'locale' => app()->getLocale(),
        ]));

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()
            ->route('cuenta')
            ->with('status', 'Tu cuenta fue creada. Aún no tiene rol institucional: la institución lo asigna.');
    }
}
