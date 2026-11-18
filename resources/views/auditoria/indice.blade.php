@extends('layouts.app')
@section('titulo', 'Auditoría — ARIS 2.0')
@section('titulo-panel', 'Auditoría')
@section('rol', 'Administración')
@section('contenido')
    <div class="max-w-6xl space-y-5">
        <section class="aris-tarjeta p-6">
            <h2 class="text-xl font-bold">Eventos de auditoría</h2>
            <p class="mt-2 text-sm text-tenue">Solo eventos de instituciones que administra. No se muestran direcciones IP ni justificaciones personales.</p>
            <x-errores-formulario />
            <form method="GET" action="{{ route('auditoria.index') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="aris-etiqueta" for="institucion_id">Institución</label>
                    <select class="aris-campo" id="institucion_id" name="institucion_id">
                        @foreach ($instituciones as $institucion)
                            <option value="{{ $institucion->id }}" @selected($institucion->id === $institucionId)>{{ $institucion->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="aris-etiqueta" for="accion">Acción</label>
                    <input class="aris-campo" id="accion" name="accion" value="{{ old('accion', $filtros['accion'] ?? '') }}" maxlength="120" list="acciones-conocidas">
                    <datalist id="acciones-conocidas">
                        @foreach ($acciones as $accion)<option value="{{ $accion }}">@endforeach
                    </datalist>
                </div>
                <div>
                    <label class="aris-etiqueta" for="actor_id">ID del actor</label>
                    <input class="aris-campo" id="actor_id" name="actor_id" type="number" min="1" value="{{ old('actor_id', $filtros['actor_id'] ?? '') }}">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="aris-etiqueta" for="desde">Desde</label>
                        <input class="aris-campo" id="desde" name="desde" placeholder="AAAA-MM-DD HH:MM" value="{{ old('desde', $filtros['desde'] ?? '') }}">
                    </div>
                    <div>
                        <label class="aris-etiqueta" for="hasta">Hasta</label>
                        <input class="aris-campo" id="hasta" name="hasta" placeholder="AAAA-MM-DD HH:MM" value="{{ old('hasta', $filtros['hasta'] ?? '') }}">
                    </div>
                </div>
                <div class="flex gap-3 sm:col-span-2">
                    <button class="aris-btn aris-btn-verde" type="submit">Filtrar</button>
                    <a class="aris-enlace" href="{{ route('auditoria.index') }}">Limpiar</a>
                </div>
            </form>
        </section>

        <section class="aris-tarjeta-plana p-6">
            <h3 class="font-semibold">Eventos ({{ $eventos->total() }})</h3>
            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="text-tenue"><tr><th class="p-2">Fecha</th><th class="p-2">Acción</th><th class="p-2">Actor</th><th class="p-2">Recurso</th><th class="p-2">Detalle</th></tr></thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($eventos as $evento)
                            <tr>
                                <td class="p-2">{{ $evento->occurred_at?->format('Y-m-d H:i:s') }}</td>
                                <td class="p-2 break-all">{{ $evento->action }}</td>
                                <td class="p-2">{{ $evento->actor?->name ?? '—' }}</td>
                                <td class="p-2 break-all">{{ $evento->resource_type }} #{{ $evento->resource_id }}</td>
                                <td class="p-2 break-words">{{ $evento->safeDetail() }}</td>
                            </tr>
                        @empty
                            <tr><td class="p-2 text-tenue" colspan="5">No hay eventos para los filtros indicados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $eventos->links() }}</div>
        </section>
    </div>
@endsection
