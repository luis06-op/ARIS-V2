<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function create(): View
    {
        return view('auth.olvide-password');
    }

    /**
     * Siempre responde lo mismo para no revelar qué correos están registrados (PI-008).
     */
    public function store(Request $request): RedirectResponse
    {
        $validado = $request->validate([
            'email' => ['required', 'string', 'email'],
        ], [], ['email' => 'correo electrónico']);

        $correo = mb_strtolower(trim($validado['email']));

        if (User::where('email', $correo)->exists()) {
            Password::sendResetLink(['email' => $correo]);
        }

        return back()->with('status', __(Password::RESET_LINK_SENT));
    }

    public function createReset(string $token): View
    {
        return view('auth.restablecer-password', [
            'token' => $token,
            'email' => request()->string('email')->value(),
        ]);
    }

    public function storeReset(ResetPasswordRequest $request): RedirectResponse
    {
        $estado = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        return $estado === Password::PASSWORD_RESET
            ? redirect()->route('sesion.create')->with('status', __($estado))
            : back()->withInput($request->only('email'))->withErrors(['email' => __($estado)]);
    }
}
