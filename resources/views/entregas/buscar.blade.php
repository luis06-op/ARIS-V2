@extends('layouts.app')
@section('titulo', 'Buscar entregas — ARIS 2.0')
@section('titulo-panel', 'Buscar entregas')
@section('rol', 'Búsqueda autorizada')
@section('contenido')
    <div class="max-w-5xl space-y-5">
        <section class="aris-tarjeta p-6">
            <h2 class="text-xl font-bold">Buscar entregas de sus proyectos permitidos</h2>
            <p class="mt-2 text-sm text-tenue">La búsqueda respeta su jurisdicción. Busca en título, resumen, guía asignada y nombres de archivo.</p>
            <x-errores-formulario />
            <form method="GET" action="{{ route('entregas.buscar') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="aris-etiqueta" for="q">Texto</label>
                    <input class="aris-campo" id="q" name="q" value="{{ old('q', $filtros['q'] ?? '') }}" maxlength="120" placeholder="Título, resumen, guía o archivo">
                </div>
                <div>
                    <label class="aris-etiqueta" for="estado">Estado</label>
                    <select class="aris-campo" id="estado" name="estado">
                        <option value="">Todos</option>
                        @foreach ($estados as $clave => $etiqueta)
                            <option value="{{ $clave }}" @selected(($filtros['estado'] ?? '') === $clave)>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="aris-etiqueta" for="ficha_id">Ficha</label>
                    <select class="aris-campo" id="ficha_id" name="ficha_id">
                        <option value="">Todas</option>
                        @foreach ($fichas as $ficha)
                            <option value="{{ $ficha->id }}" @selected(($filtros['ficha_id'] ?? '') == $ficha->id)>{{ $ficha->numero }} · {{ $ficha->especialidad->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="aris-etiqueta" for="desde">Desde</label>
                    <input class="aris-campo" id="desde" name="desde" type="date" value="{{ old('desde', $filtros['desde'] ?? '') }}">
                </div>
                <div>
                    <label class="aris-etiqueta" for="hasta">Hasta</label>
                    <input class="aris-campo" id="hasta" name="hasta" type="date" value="{{ old('hasta', $filtros['hasta'] ?? '') }}">
                </div>
                <div class="flex items-end gap-3 sm:col-span-2">
                    <button class="aris-btn aris-btn-verde" type="submit">Buscar</button>
                    <a class="aris-enlace" href="{{ route('entregas.buscar') }}">Limpiar filtros</a>
                </div>
            </form>
        </section>

        <section class="aris-tarjeta-plana p-6">
            <h3 class="font-semibold">Resultados ({{ $resultados->total() }})</h3>
            <ul class="mt-4 divide-y divide-white/5">
                @forelse ($resultados as $entrega)
                    <li class="py-3">
                        <a class="aris-enlace" href="{{ route('entregas.show', $entrega->versions()->orderByDesc('version_number')->firstOrFail()) }}">{{ $entrega->title }}</a>
                        <p class="mt-1 text-sm text-tenue">
                            {{ $entrega->project->title }} · {{ $entrega->status }} ·
                            {{ $entrega->project->ficha?->numero ?? 'Sin ficha' }} ·
                            {{ $entrega->versions()->count() }} versión(es)
                        </p>
                    </li>
                @empty
                    <li class="py-3 text-sm text-tenue">No hay entregas que coincidan con los filtros.</li>
                @endforelse
            </ul>
            <div class="mt-4">{{ $resultados->links() }}</div>
        </section>
    </div>
@endsection
