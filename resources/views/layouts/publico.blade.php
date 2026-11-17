<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#09111e">
    <title>@yield('titulo', 'ARIS 2.0 — Archivo académico vivo')</title>
    <meta name="description" content="@yield('descripcion', 'ARIS 2.0 conserva, relaciona y hace comprensible el trabajo académico: proyectos, guías, entregas, versiones, revisiones y publicación institucional.')">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh bg-noche text-texto antialiased">
    <div class="relative isolate min-h-dvh overflow-hidden">
        @include('componentes.orbs')

        <div class="relative z-10 flex min-h-dvh flex-col">
            @if ($conCabecera ?? true)
                <header class="border-b border-white/5 bg-noche/40 backdrop-blur">
                    <div class="aris-envoltura flex h-16 items-center justify-between gap-4">
                        <a href="{{ route('inicio') }}" class="flex items-center gap-2.5 text-texto no-underline">
                            <x-marca tipo="insignia" class="h-10 w-10 text-white" />
                            <span class="text-lg font-extrabold tracking-tight">ARIS</span>
                        </a>

                        <nav class="flex items-center gap-2 sm:gap-4" aria-label="Acceso">
                            @auth
                                <a href="{{ route('cuenta') }}" class="aris-enlace-suave text-sm">Mi cuenta</a>
                                <form method="POST" action="{{ route('sesion.destroy') }}">
                                    @csrf
                                    <button type="submit" class="aris-btn aris-btn-verde">Cerrar sesión</button>
                                </form>
                            @else
                                <a href="{{ route('sesion.create') }}" class="aris-enlace-suave text-sm">Iniciar sesión</a>
                                <a href="{{ route('registro.create') }}" class="aris-btn aris-btn-verde">Registrarme</a>
                            @endauth
                        </nav>
                    </div>
                </header>
            @endif

            <main class="flex-1">
                @if (session('status'))
                    <div class="aris-envoltura pt-5">
                        <p class="aris-aviso" role="status">{{ session('status') }}</p>
                    </div>
                @endif

                @yield('contenido')
            </main>

            <footer class="@if (! ($conPie ?? true)) hidden @else border-t border-white/5 py-8 @endif">
                <div class="aris-envoltura flex flex-col gap-2 text-sm text-tenue sm:flex-row sm:items-center sm:justify-between">
                    <p>© {{ date('Y') }} ARIS 2.0 · Obra del SENA. Todos los derechos reservados.</p>
                    <p>Trazabilidad pedagógica con versiones inmutables.</p>
                </div>
            </footer>
        </div>

        <x-accesibilidad />
    </div>
</body>
</html>
