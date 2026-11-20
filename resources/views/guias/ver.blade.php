@extends('layouts.app')
@section('titulo', $version->title.' — ARIS 2.0')
@section('titulo-panel', 'Versión de guía')
@section('rol', 'Acceso autorizado')
@section('contenido')
    <div class="max-w-4xl space-y-5">
        <a class="aris-enlace" href="{{ route('proyectos.show', $version->activity->project) }}">Volver al proyecto</a>
        <section class="aris-tarjeta p-6">
            <p class="aris-eyebrow">Versión {{ $version->version_number }} · {{ $version->status }}</p>
            <h2 class="mt-3 text-2xl font-bold">{{ $version->title }}</h2>
            <p class="mt-5 whitespace-pre-wrap text-sm leading-relaxed break-words">{{ $version->content }}</p>
        </section>
        @can('update', $version)
            <section class="aris-tarjeta-plana p-6">
                <h3 class="font-semibold">Editar guía</h3>
                <p class="mt-2 text-sm text-tenue">Si esta versión ya fue publicada, se creará un nuevo borrador.</p>
                <x-errores-formulario />
                <form method="POST" action="{{ route('guias.update', $version) }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="lock_version" value="{{ $version->lock_version }}">
                    <label class="aris-etiqueta" for="title">Título</label><input class="aris-campo" id="title" name="title" value="{{ old('title', $version->title) }}" maxlength="180" required>
                    <label class="aris-etiqueta" for="content">Contenido</label><textarea class="aris-campo" id="content" name="content" rows="10" maxlength="50000" required>{{ old('content', $version->content) }}</textarea>
                    <button class="aris-btn aris-btn-verde" type="submit">Guardar guía</button>
                </form>
                @if ($version->status === 'borrador')
                    <form method="POST" action="{{ route('guias.publish', $version) }}" class="mt-5">
                        @csrf
                        <button class="aris-btn aris-btn-cian" type="submit">Publicar esta versión y asignarla al proyecto</button>
                    </form>
                @endif
            </section>
        @endcan
    </div>
@endsection
