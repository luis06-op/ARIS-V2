@extends('layouts.app')
@section('titulo', 'Permisos institucionales — ARIS 2.0')
@section('titulo-panel', 'Permisos institucionales')
@section('rol', 'Administrador')
@section('contenido')
    <div class="max-w-5xl space-y-5">
        <h2 class="text-2xl font-bold">{{ $institucion->name }}</h2>
        <x-errores-formulario />
        @include('accesos.catalogo')
        <section class="aris-tarjeta p-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h3 class="font-semibold">Membresías registradas</h3>
                <a class="aris-enlace" href="{{ route('accesos.importar', $institucion) }}">Importar por lotes</a>
            </div>
            <ul class="mt-4 space-y-3 text-sm">
                @forelse ($membresias as $membresia)
                    <li>{{ $membresia->user->name }} · {{ $membresia->status }} · {{ $membresia->roles->pluck('name')->join(', ') ?: 'Sin roles' }}</li>
                @empty
                    <li>No hay membresías registradas.</li>
                @endforelse
            </ul>
        </section>
        <div class="grid gap-5 lg:grid-cols-3">
            @foreach (['rol' => 'Roles institucionales', 'instructor' => 'Creación de proyectos', 'evaluador' => 'Jurisdicción de evaluadores'] as $operacion => $titulo)
                <section class="aris-tarjeta-plana p-5">
                    <h3 class="font-semibold">{{ $titulo }}</h3>
                    <form class="mt-4 space-y-4" method="POST" action="{{ route('accesos.store', $institucion) }}">
                        @csrf
                        <input type="hidden" name="operation" value="{{ $operacion }}">
                        <div>
                            <label class="aris-etiqueta" for="miembro-{{ $operacion }}">Membresía</label>
                            <select class="aris-campo" name="membership_id" id="miembro-{{ $operacion }}" required>
                                <option value="">Selecciona una persona</option>
                                @foreach ($membresias as $membresia)
                                    <option value="{{ $membresia->id }}">{{ $membresia->user->name }} · {{ $membresia->user->email }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if ($operacion === 'rol')
                            <div><label class="aris-etiqueta" for="role">Rol</label><select class="aris-campo" name="role" id="role">
                                @foreach (['administrador', 'evaluador', 'instructor', 'aprendiz'] as $rol)
                                    <option value="{{ $rol }}">{{ ucfirst($rol) }}</option>
                                @endforeach
                            </select></div>
                        @elseif ($operacion === 'instructor')
                            <div><label class="aris-etiqueta" for="especialidad_id">Especialidad</label><select class="aris-campo" name="especialidad_id" id="especialidad_id" required>
                                <option value="">Selecciona una especialidad</option>
                                @foreach ($especialidades as $especialidad)
                                    <option value="{{ $especialidad->id }}">{{ $especialidad->nombre }}</option>
                                @endforeach
                            </select></div>
                            <p class="text-xs text-tenue">Habilitación a solicitud de Coordinación Académica.</p>
                        @else
                            <div><label class="aris-etiqueta" for="ficha_id">Ficha y especialidad</label><select class="aris-campo" name="ficha_id" id="ficha_id" required>
                                <option value="">Selecciona una ficha</option>
                                @foreach ($fichas as $ficha)
                                    <option value="{{ $ficha->id }}">{{ $ficha->numero }} · {{ $ficha->cohorte }} · {{ $ficha->especialidad->nombre }}</option>
                                @endforeach
                            </select></div>
                        @endif
                        <div><label class="aris-etiqueta" for="activo-{{ $operacion }}">Operación</label><select class="aris-campo" name="active" id="activo-{{ $operacion }}"><option value="1">Habilitar</option><option value="0">Revocar</option></select></div>
                        <button class="aris-btn aris-btn-verde" type="submit">Guardar permiso</button>
                    </form>
                </section>
            @endforeach
        </div>
        <section class="aris-tarjeta-plana p-5">
            <h3 class="font-semibold">Jurisdicciones vigentes</h3>
            <ul class="mt-3 space-y-2 text-sm text-tenue">
                @forelse ($jurisdicciones as $jurisdiccion)
                    @php($ficha = $fichas->firstWhere('id', $jurisdiccion->ficha_id))
                    <li>{{ $membresias->firstWhere('id', $jurisdiccion->institution_membership_id)->user->name }} · {{ $ficha?->especialidad->nombre }} · Ficha {{ $ficha?->numero }} · {{ $ficha?->cohorte }}</li>
                @empty
                    <li>No hay jurisdicciones asignadas.</li>
                @endforelse
            </ul>
            <h3 class="mt-5 font-semibold">Habilitaciones de creación</h3>
            <ul class="mt-3 space-y-2 text-sm text-tenue">
                @forelse ($vinculos as $vinculo)
                    <li>{{ $membresias->firstWhere('id', $vinculo->institution_membership_id)->user->name }} · {{ $especialidades->firstWhere('id', $vinculo->especialidad_id)?->nombre }} · {{ $vinculo->can_create_projects ? 'Habilitada' : 'Deshabilitada' }}</li>
                @empty
                    <li>No hay habilitaciones registradas.</li>
                @endforelse
            </ul>
        </section>
    </div>
@endsection
