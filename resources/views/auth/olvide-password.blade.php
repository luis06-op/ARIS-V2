@extends('layouts.publico', ['conCabecera' => false, 'conPie' => false])

@section('titulo', 'Recuperar contraseña — ARIS 2.0')

@section('contenido')
    <div class="relative z-10 flex min-h-dvh items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <a href="{{ route('inicio') }}" class="mx-auto mb-6 flex w-fit flex-col items-center gap-3 text-texto no-underline">
                <x-marca tipo="insignia" class="h-16 w-16 text-white" />
                <span class="text-xl font-extrabold tracking-[0.18em]">ARIS</span>
            </a>

            <div class="aris-tarjeta p-6 sm:p-8">
                <h1 class="text-center text-2xl font-bold tracking-tight">Recupera tu contraseña</h1>
                <p class="mt-2 text-center text-sm text-tenue">
                    Ingresa tu correo y te enviaremos un enlace de un solo uso
                </p>

                <form method="POST" action="{{ route('clave.enviar') }}" class="mt-6 space-y-4" novalidate>
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

                    <button type="submit" class="aris-btn aris-btn-verde aris-btn-bloque py-3">
                        Enviar enlace de recuperación
                    </button>
                </form>

                <p class="mt-6 border-t border-white/5 pt-5 text-center text-sm">
                    <a href="{{ route('sesion.create') }}" class="aris-enlace-suave">← Volver a iniciar sesión</a>
                </p>
            </div>
        </div>
    </div>
@endsection
