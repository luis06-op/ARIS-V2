<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#09111e">
    <title>@yield('titulo', 'Mi cuenta — ARIS 2.0')</title>
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh bg-noche text-texto antialiased">
    <div class="relative isolate min-h-dvh">
        @include('componentes.orbs', ['atenuado' => true])
        <div class="relative z-10 flex min-h-dvh flex-col md:flex-row">
            <aside class="border-b border-white/5 bg-noche-alta/85 backdrop-blur md:flex md:min-h-dvh md:w-64 md:flex-col md:border-r md:border-b-0">
                <div class="flex flex-wrap items-center justify-between gap-3 p-4 md:flex-1 md:flex-col md:items-stretch md:gap-6">
                    <a href="{{ route('inicio') }}" class="flex items-center gap-2.5 text-texto no-underline">
                        <x-marca tipo="insignia" class="h-10 w-10 text-white" />
                        <span class="text-lg font-extrabold tracking-tight">ARIS</span>
                    </a>

                    <div class="hidden md:block">
                        <p class="text-[0.68rem] font-semibold tracking-[0.14em] text-tenue uppercase">
                            @yield('ambito', 'Cuenta')
                        </p>
                        <p class="mt-1 text-sm font-semibold">{{ auth()->user()->name }}</p>
                    </div>

                    <nav class="flex w-full items-center gap-1 md:mt-2 md:w-auto md:flex-1 md:flex-col md:items-stretch" aria-label="Navegación principal">
                        <a href="{{ route('cuenta') }}" class="aris-nav-enlace" @if (request()->routeIs('cuenta')) aria-current="page" @endif>
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M3 10.5 12 3l9 7.5" />
                                <path d="M5 9.5V21h14V9.5" />
                            </svg>
                            Mi cuenta
                        </a>

                        <form method="POST" action="{{ route('sesion.destroy') }}" class="md:mt-auto">
                            @csrf
                            <button type="submit" class="aris-nav-enlace w-full text-left">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <path d="M15 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3" />
                                    <path d="M10 17l5-5-5-5" />
                                    <path d="M15 12H3" />
                                </svg>
                                Cerrar sesión
                            </button>
                        </form>
                    </nav>
                </div>

                <p class="hidden px-4 pb-4 text-xs text-tenue/70 md:block">© {{ date('Y') }} ARIS</p>
            </aside>

            <div class="flex min-w-0 flex-1 flex-col">
                <header class="flex flex-wrap items-center justify-between gap-3 border-b border-white/5 px-5 py-4">
                    <div class="flex items-center gap-3">
                        <h1 class="text-lg font-bold tracking-tight">@yield('titulo-panel', 'Mi cuenta')</h1>
                        <span class="aris-chip">@yield('rol', 'Sin rol institucional')</span>
                    </div>

                    <div class="flex items-center gap-3 text-sm text-tenue">
                        <span class="hidden sm:inline">{{ auth()->user()->email }}</span>
                        <span class="flex h-9 w-9 items-center justify-center rounded-full border border-verde/30 bg-verde/10 text-xs font-bold text-verde" aria-hidden="true">
                            {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 2)) }}
                        </span>
                    </div>
                </header>

                <main class="flex-1 p-5 sm:p-7">
                    @yield('contenido')
                </main>
            </div>
        </div>

        <x-accesibilidad />
    </div>
</body>
</html>
