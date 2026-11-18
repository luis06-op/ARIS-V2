@extends('layouts.app')
@section('titulo', 'Editar proyecto — ARIS 2.0')
@section('titulo-panel', 'Editar proyecto')
@section('rol', 'Gestión autorizada')
@section('contenido')
    <section class="aris-tarjeta max-w-3xl p-6">
        <h2 class="text-2xl font-bold">Editar datos del proyecto</h2>
        <x-errores-formulario />
        <form method="POST" action="{{ route('proyectos.update', $proyecto) }}" class="mt-6 space-y-5">
            @csrf
            @method('PATCH')
            <input type="hidden" name="lock_version" value="{{ old('lock_version', $proyecto->lock_version) }}">
            @include('proyectos.campos')
            <button class="aris-btn aris-btn-verde" type="submit">Guardar cambios</button>
            <a class="aris-enlace" href="{{ route('proyectos.show', $proyecto) }}">Volver al proyecto</a>
        </form>
    </section>
@endsection
