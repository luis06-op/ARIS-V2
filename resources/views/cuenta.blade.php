@extends('layouts.app')

@section('titulo', 'Mi cuenta — ARIS 2.0')
@section('titulo-panel', 'Mi cuenta')
@section('ambito', $rolInstitucional['etiqueta'])
@section('rol', $rolInstitucional['etiqueta'])

@section('contenido')
    @if (session('status'))
        <p class="aris-aviso mb-5 max-w-4xl" role="status">{{ session('status') }}</p>
    @endif

    <section class="aris-tarjeta max-w-4xl p-6">
        <p class="aris-eyebrow">{{ $rolInstitucional['etiqueta'] }}</p>
        <h2 class="mt-3 text-2xl font-bold tracking-tight sm:text-3xl">
            Bienvenido, {{ \Illuminate\Support\Str::of(auth()->user()->name)->explode(' ')->first() }}
        </h2>
        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-tenue">
            Tu cuenta está activa. Aquí verás únicamente información real de tu perfil y de tu
            vínculo con la institución. En Proyectos puedes consultar las fichas a las que tienes
            acceso por asignación o jurisdicción vigente.
        </p>
    </section>

    <div class="mt-5 grid max-w-4xl gap-5 lg:grid-cols-2">
        <section class="aris-tarjeta-plana p-5">
            <h3 class="text-base font-semibold">Datos de la cuenta</h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex flex-wrap justify-between gap-2">
                    <dt class="text-tenue">Nombre</dt>
                    <dd class="font-medium">{{ auth()->user()->name }}</dd>
                </div>
                <div class="flex flex-wrap justify-between gap-2">
                    <dt class="text-tenue">Correo</dt>
                    <dd class="font-medium break-all">{{ auth()->user()->email }}</dd>
                </div>
                <div class="flex flex-wrap justify-between gap-2">
                    <dt class="text-tenue">Estado</dt>
                    <dd>
                        <span @class([
                            'rounded-full px-2.5 py-0.5 text-xs font-semibold',
                            'bg-verde/10 text-verde' => auth()->user()->status === 'activo',
                            'bg-ambar/10 text-ambar' => auth()->user()->status === 'inactivo',
                            'bg-rojo/10 text-rojo' => auth()->user()->status === 'bloqueado',
                        ])>{{ ucfirst(auth()->user()->status) }}</span>
                    </dd>
                </div>
                <div class="flex flex-wrap justify-between gap-2">
                    <dt class="text-tenue">Idioma</dt>
                    <dd class="font-medium">{{ auth()->user()->locale ?? 'es' }}</dd>
                </div>
                <div class="flex flex-wrap justify-between gap-2">
                    <dt class="text-tenue">Último inicio de sesión</dt>
                    <dd class="font-medium">
                        {{ auth()->user()->last_login_at?->timezone(config('app.timezone'))->translatedFormat('d M Y · H:i') ?? 'Este es tu primer acceso' }}
                    </dd>
                </div>
            </dl>
        </section>

        <section class="aris-tarjeta-plana p-5">
            <h3 class="text-base font-semibold">Vínculo institucional</h3>

            @if ($rolInstitucional['membresia'])
                <p class="mt-3 text-sm text-tenue">
                    Miembro de <span class="font-medium text-texto">{{ $rolInstitucional['membresia']->institution->name }}</span>
                    desde el {{ $rolInstitucional['membresia']->joined_at?->translatedFormat('d M Y') ?? 'sin fecha registrada' }}.
                </p>
                <p class="mt-3 text-sm text-tenue">
                    Roles asignados:
                    <span class="font-medium text-texto">{{ $rolInstitucional['roles']->pluck('name')->join(', ') ?: 'ninguno' }}</span>
                </p>
            @else
                <p class="mt-3 text-sm leading-relaxed text-tenue">
                    Todavía no tienes una membresía institucional. Los roles y la pertenencia a
                    proyectos los asigna la institución; no se eligen al registrarse.
                </p>
            @endif

            <p class="mt-4 rounded-lg border border-white/5 bg-white/[0.02] p-3 text-xs leading-relaxed text-tenue">
                Solo el administrador asigna roles y tutores. Los permisos se verifican en cada
                operación según tu membresía y las asignaciones vigentes.
            </p>
        </section>
    </div>

    <section class="aris-tarjeta-plana mt-5 max-w-4xl p-5">
        <h3 class="text-base font-semibold">Qué sigue</h3>
        <ol class="mt-3 space-y-2 text-sm text-tenue">
            <li><span class="font-medium text-texto">F2 — Identidad y permisos:</span> sesión, roles y autorización por recurso.</li>
            <li><span class="font-medium text-texto">F3 — Proyectos y guías:</span> creación de proyectos, guías y asignaciones verificables.</li>
            <li><span class="font-medium text-texto">F4 — Entregas y archivos:</span> evidencias privadas, versiones inmutables y descargas autorizadas.</li>
        </ol>
    </section>
@endsection
