@extends('layouts.publico', ['conCabecera' => false, 'conPie' => false])

@section('titulo', 'Crear cuenta — ARIS 2.0')

@section('contenido')
    <div class="relative z-10 flex min-h-dvh items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">
            <a href="{{ route('inicio') }}" class="mx-auto mb-6 flex w-fit flex-col items-center gap-3 text-texto no-underline">
                <x-marca tipo="insignia" class="h-16 w-16 text-white" />
                <span class="text-xl font-extrabold tracking-[0.18em]">ARIS</span>
            </a>

            <div class="aris-tarjeta p-6 sm:p-8">
                <h1 class="text-center text-2xl font-bold tracking-tight">Crea tu cuenta</h1>
                <p class="mt-2 text-center text-sm text-tenue">
                    Regístrate para consultar el repositorio y participar en tus proyectos
                </p>

                <form method="POST" action="{{ route('registro.store') }}" class="mt-6 space-y-4" novalidate>
                    @csrf

                    <div>
                        <label for="name" class="aris-etiqueta">Nombre completo</label>
                        <input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            required
                            autocomplete="name"
                            autofocus
                            placeholder="Nombre y apellidos"
                            @class(['aris-campo', 'border-rojo/60' => $errors->has('name')])
                            @error('name') aria-invalid="true" aria-describedby="name-error" @enderror
                        >
                        @error('name')
                            <p id="name-error" class="aris-error">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="aris-etiqueta">Correo electrónico</label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="username"
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
                        autocomplete="new-password"
                        placeholder="Mínimo 8 caracteres"
                        :invalido="$errors->has('password')"
                        :descrito-por="$errors->has('password') ? 'password-error' : 'password-ayuda'"
                    />

                    @error('password')
                        <p id="password-error" class="aris-error">{{ $message }}</p>
                    @else
                        <p id="password-ayuda" class="aris-ayuda">Al menos 8 caracteres, con letras y números. Se guarda cifrada.</p>
                    @enderror

                    <x-campo-contrasena
                        id="password_confirmation"
                        etiqueta="Confirmar contraseña"
                        autocomplete="new-password"
                        placeholder="Repite la contraseña"
                    />

                    <button type="submit" class="aris-btn aris-btn-verde aris-btn-bloque py-3">
                        Registrarme
                    </button>
                </form>

                <p class="mt-5 text-center text-sm text-tenue">
                    ¿Ya tienes cuenta?
                    <a href="{{ route('sesion.create') }}" class="aris-enlace">Inicia sesión</a>
                </p>

                <p class="mt-6 border-t border-white/5 pt-5 text-center text-sm">
                    <a href="{{ route('inicio') }}" class="aris-enlace-suave">← Volver al inicio</a>
                </p>
            </div>
        </div>
    </div>
@endsection
