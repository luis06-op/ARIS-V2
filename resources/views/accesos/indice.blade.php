@extends('layouts.app')
@section('titulo', 'Control de accesos — ARIS 2.0')
@section('titulo-panel', 'Control de accesos')
@section('rol', 'Administración institucional')
@section('contenido')
    <section class="aris-tarjeta max-w-4xl p-6">
        <h2 class="text-2xl font-bold">Instituciones administradas</h2>
        <ul class="mt-5 space-y-3">
            @forelse ($instituciones as $institucion)
                <li><a class="aris-enlace" href="{{ route('accesos.show', $institucion) }}">{{ $institucion->name }}</a></li>
            @empty
                <li class="text-sm text-tenue">No tienes autoridad administrativa vigente en ninguna institución.</li>
            @endforelse
        </ul>
    </section>
@endsection
