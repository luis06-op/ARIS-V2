@extends('layouts.publico', ['conCabecera' => false, 'conPie' => false])

@section('titulo', 'Iniciar sesión — ARIS 2.0')

@section('contenido')
    <div class="relative z-10 flex min-h-dvh items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <a href="{{ route('inicio') }}" class="mx-auto mb-6 flex w-fit flex-col items-center gap-3 text-texto no-underline">
                <x-marca tipo="insignia" class="h-16 w-16 text-white" />
                <span class="text-xl font-extrabold tracking-[0.18em]">ARIS</span>
            </a>

            <div class="aris-tarjeta p-6 sm:p-8">
                <h1 class="text-center text-2xl font-bold tracking-tight">Bienvenido de nuevo</h1>
                <p class="mt-2 text-center text-sm text-tenue">Ingresa tus credenciales para continuar</p>

                @if (session('status'))
                    <p class="aris-aviso mt-5" role="status">{{ session('status') }}</p>
                @endif

                <form method="POST" action="{{ route('sesion.store') }}" class="mt-6 space-y-4" novalidate>
                    @csrf

                    <div>
                        <label for="email" class="aris-etiqueta">Correo electrónico</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="username"
                            autofocus
                            placeholder="tucorreo@aris.edu"
                            @class(['aris-campo', 'border-rojo/60' => $errors->has('email')])
                            @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                        >
                        @error('email')
                            <p id="email-error" class="aris-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-campo-contrasena
                        id="password"
                        etiqueta="Contraseña"
                        autocomplete="current-password"
                        :invalido="$errors->has('password')"
                        :descrito-por="$errors->has('password') ? 'password-error' : null"
                    />

                    @error('password')
                        <p id="password-error" class="aris-error">{{ $message }}</p>
                    @enderror

                    <button type="submit" class="aris-btn aris-btn-verde aris-btn-bloque py-3">
                        Iniciar sesión
                    </button>
                </form>

                <p class="mt-5 text-center text-sm text-tenue">
                    ¿No tienes cuenta?
                    <a href="{{ route('registro.create') }}" class="aris-enlace">Regístrate aquí</a>
                </p>

                <p class="mt-6 border-t border-white/5 pt-5 text-center text-sm">
                    <a href="{{ route('inicio') }}" class="aris-enlace-suave">← Volver al inicio</a>
                </p>
            </div>
        </div>
    </div>
@endsection
