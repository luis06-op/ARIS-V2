@extends('layouts.publico', ['conCabecera' => false, 'conPie' => false])

@section('titulo', 'Restablecer contraseña — ARIS 2.0')

@section('contenido')
    <div class="relative z-10 flex min-h-dvh items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <a href="{{ route('inicio') }}" class="mx-auto mb-6 flex w-fit flex-col items-center gap-3 text-texto no-underline">
                <x-marca tipo="insignia" class="h-16 w-16 text-white" />
                <span class="text-xl font-extrabold tracking-[0.18em]">ARIS</span>
            </a>

            <div class="aris-tarjeta p-6 sm:p-8">
                <h1 class="text-center text-2xl font-bold tracking-tight">Nueva contraseña</h1>
                <p class="mt-2 text-center text-sm text-tenue">Elige una contraseña que no hayas usado antes</p>

                @error('email')
                    <p class="aris-error mt-5" role="alert">{{ $message }}</p>
                @enderror

                <form method="POST" action="{{ route('clave.actualizar') }}" class="mt-6 space-y-4" novalidate>
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ old('email', $email) }}">

                    <x-campo-contrasena
                        id="password"
                        etiqueta="Nueva contraseña"
                        autocomplete="new-password"
                        placeholder="Mínimo 8 caracteres"
                        :invalido="$errors->has('password')"
                        :descrito-por="$errors->has('password') ? 'password-error' : 'password-ayuda'"
                    />

                    @error('password')
                        <p id="password-error" class="aris-error">{{ $message }}</p>
                    @else
                        <p id="password-ayuda" class="aris-ayuda">Al menos 8 caracteres, con letras y números.</p>
                    @enderror

                    <x-campo-contrasena
                        id="password_confirmation"
                        etiqueta="Confirmar contraseña"
                        autocomplete="new-password"
                        placeholder="Repite la contraseña"
                    />

                    <button type="submit" class="aris-btn aris-btn-verde aris-btn-bloque py-3">
                        Guardar contraseña
                    </button>
                </form>

                <p class="mt-6 border-t border-white/5 pt-5 text-center text-sm">
                    <a href="{{ route('sesion.create') }}" class="aris-enlace-suave">← Volver a iniciar sesión</a>
                </p>
            </div>
        </div>
    </div>
@endsection
