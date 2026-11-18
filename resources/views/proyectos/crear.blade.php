@extends('layouts.app')
@section('titulo', 'Crear proyecto — ARIS 2.0')
@section('titulo-panel', 'Crear proyecto')
@section('rol', 'Creación autorizada')
@section('contenido')
    <section class="aris-tarjeta max-w-3xl p-6">
        <p class="aris-eyebrow">Una ficha, un contexto verificable</p>
        <h2 class="mt-2 text-2xl font-bold">Nuevo proyecto</h2>
        <p class="mt-3 text-sm text-tenue">La designación del instructor tutor corresponde al administrador.</p>
        <x-errores-formulario />
        <form method="POST" action="{{ route('proyectos.store') }}" class="mt-6 space-y-5">
            @csrf
            <div>
                <label class="aris-etiqueta" for="ficha_id">Ficha y especialidad</label>
                <select class="aris-campo" id="ficha_id" name="ficha_id" required>
                    <option value="">Selecciona una ficha autorizada</option>
                    @foreach ($fichas as $ficha)
                        <option value="{{ $ficha->id }}" @selected(old('ficha_id') == $ficha->id)>{{ $ficha->especialidad->institution->name }} · {{ $ficha->especialidad->nombre }} · {{ $ficha->numero }} · {{ $ficha->cohorte }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="aris-etiqueta" for="code">Código institucional del proyecto</label>
                <input class="aris-campo" id="code" name="code" value="{{ old('code') }}" maxlength="40" required>
            </div>
            @include('proyectos.campos')
            <div class="flex flex-wrap items-center gap-4">
                <button class="aris-btn aris-btn-verde" type="submit">Crear proyecto</button>
                <a class="aris-enlace" href="{{ route('proyectos.index') }}">Volver a proyectos</a>
            </div>
        </form>
    </section>
@endsection
