@extends('layouts.app')
@section('titulo', $proyecto->title.' — ARIS 2.0')
@section('titulo-panel', 'Proyecto')
@section('rol', 'Acceso autorizado')
@section('contenido')
    <div class="max-w-4xl space-y-5">
        <a class="aris-enlace" href="{{ route('proyectos.index') }}">Volver a proyectos</a>
        <section class="aris-tarjeta p-6">
            <p class="aris-eyebrow">{{ $proyecto->code }}</p>
            <h2 class="mt-3 text-2xl font-bold break-words">{{ $proyecto->title }}</h2>
            <p class="mt-4 whitespace-pre-wrap text-sm text-tenue break-words">{{ $proyecto->description ?: 'Este proyecto todavía no tiene descripción.' }}</p>
            @if ($proyecto->ficha)
                <p class="mt-4 text-sm">{{ $proyecto->ficha->especialidad->nombre }} · Ficha {{ $proyecto->ficha->numero }} · {{ $proyecto->ficha->cohorte }}</p>
            @endif
            @can('update', $proyecto)
                <a class="aris-btn aris-btn-verde mt-5" href="{{ route('proyectos.edit', $proyecto) }}">Editar proyecto</a>
            @endcan
        </section>
        @include('proyectos.guias')
        @include('proyectos.entregas')
        @include('proyectos.aprendices')
        @can('manageTutors', $proyecto)
            <section class="aris-tarjeta-plana p-6">
                <h3 class="font-semibold">Designación de tutor</h3>
                <x-errores-formulario />
                <form class="mt-4 space-y-4" method="POST" action="{{ route('proyectos.tutor', $proyecto) }}">
                    @csrf
                    <label class="aris-etiqueta" for="user_id">Instructor</label>
                    <select class="aris-campo" name="user_id" id="user_id" required>
                        <option value="">Selecciona un instructor de la institución</option>
                        @foreach ($instructores as $instructor)
                            <option value="{{ $instructor->id }}">{{ $instructor->name }} · {{ $instructor->email }}</option>
                        @endforeach
                    </select>
                    <label class="aris-etiqueta" for="active">Operación</label>
                    <select class="aris-campo" name="active" id="active"><option value="1">Asignar tutor</option><option value="0">Retirar tutor</option></select>
                    <button class="aris-btn aris-btn-verde" type="submit">Guardar designación</button>
                </form>
            </section>
        @endcan
    </div>
@endsection
