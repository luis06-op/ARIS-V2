@extends('layouts.app')
@section('titulo', $version->submission->title.' — ARIS 2.0')
@section('titulo-panel', 'Versión de entrega')
@section('rol', 'Acceso autorizado')
@section('contenido')
    <div class="max-w-4xl space-y-5">
        <a class="aris-enlace" href="{{ route('proyectos.show', $version->submission->project) }}">Volver al proyecto</a>
        <section class="aris-tarjeta p-6">
            <p class="aris-eyebrow">Versión {{ $version->version_number }} · {{ $version->status }}</p>
            <h2 class="mt-3 text-2xl font-bold">{{ $version->submission->title }}</h2>
            <p class="mt-4 whitespace-pre-wrap text-sm text-tenue">{{ $version->summary }}</p>
            @if ($version->submission->assignment)
                <p class="mt-4 text-sm">Guía de referencia: {{ $version->submission->assignment->activityVersion->title }} · Versión {{ $version->submission->assignment->activityVersion->version_number }}</p>
            @endif
            <p class="aris-aviso mt-4">Trabajo en borrador. El envío académico, la revisión y la calificación esperan las políticas institucionales correspondientes.</p>
        </section>
        <section class="aris-tarjeta-plana p-6">
            <h3 class="font-semibold">Evidencias de esta versión</h3>
            <ul class="mt-4 space-y-3 text-sm">
                @forelse ($version->documentLinks as $vinculo)
                    @can('download', $vinculo->documentVersion)
                        <li><a class="aris-enlace break-all" href="{{ route('evidencias.download', $vinculo->documentVersion) }}">{{ $vinculo->documentVersion->original_name }}</a></li>
                    @endcan
                @empty
                    <li class="text-tenue">Esta versión todavía no contiene archivos.</li>
                @endforelse
            </ul>
            @can('upload', $version)
                <x-errores-formulario />
                <form method="POST" action="{{ route('evidencias.store', $version) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                    @csrf
                    <label class="aris-etiqueta" for="archivo">Adjuntar evidencia</label>
                    <input class="aris-campo" id="archivo" type="file" name="archivo" accept=".pdf,.docx,.xlsx,.jpg,.jpeg,.png" required aria-describedby="limites-archivo">
                    <p id="limites-archivo" class="text-xs text-tenue">PDF, DOCX, XLSX, JPG o PNG. Máximo {{ (int) (config('aris.files.max_bytes') / 1024 / 1024) }} MB por archivo y {{ config('aris.files.max_per_version') }} archivos por versión.</p>
                    <button class="aris-btn aris-btn-verde" type="submit">Guardar evidencia privada</button>
                </form>
            @endcan
        </section>
        <section class="aris-tarjeta-plana p-6">
            <h3 class="font-semibold">Historial de versiones</h3>
            <ul class="mt-4 flex flex-wrap gap-4 text-sm">
                @foreach ($versiones as $historica)
                    <li><a class="aris-enlace" href="{{ route('entregas.show', $historica) }}" @if ($historica->id === $version->id) aria-current="page" @endif>Versión {{ $historica->version_number }}</a></li>
                @endforeach
            </ul>
            @can('upload', $version)
                <form method="POST" action="{{ route('entregas.version', $version) }}" class="mt-5 space-y-4">
                    @csrf
                    <label class="aris-etiqueta" for="summary">Resumen de la nueva versión</label>
                    <textarea class="aris-campo" id="summary" name="summary" rows="3" maxlength="500" required></textarea>
                    <p class="text-xs text-tenue">La nueva versión empieza sin archivos. Esta versión conservará sus archivos y dejará de admitir nuevas cargas.</p>
                    <button class="aris-btn aris-btn-fantasma" type="submit">Crear nueva versión</button>
                </form>
            @endcan
        </section>
    </div>
@endsection
