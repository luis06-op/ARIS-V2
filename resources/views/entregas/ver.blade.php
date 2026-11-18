@extends('layouts.app')
@section('titulo', $version->submission->title.' — ARIS 2.0')
@section('titulo-panel', 'Versión de entrega')
@section('rol', 'Acceso autorizado')
@section('contenido')
    <div class="max-w-4xl space-y-5">
        <a class="aris-enlace" href="{{ route('proyectos.show', $version->submission->project) }}">Volver al proyecto</a>
        <section class="aris-tarjeta p-6">
            <p class="aris-eyebrow">Versión {{ $version->version_number }} · {{ $version->status === 'finalizada' ? 'Enviada · contenido congelado' : 'Borrador' }}</p>
            <h2 class="mt-3 text-2xl font-bold">{{ $version->submission->title }}</h2>
            <p class="mt-4 whitespace-pre-wrap text-sm text-tenue">{{ $version->summary }}</p>
            @if ($version->submission->assignment)
                <p class="mt-4 text-sm">Guía de referencia: {{ $version->submission->assignment->activityVersion->title }} · Versión {{ $version->submission->assignment->activityVersion->version_number }}</p>
            @endif
            <p class="aris-aviso mt-4">Estado de la entrega: {{ $version->submission->status }}. Las versiones enviadas conservan su contenido. La evaluación es cualitativa y los plazos todavía no bloquean acciones.</p>
            <x-errores-formulario />
            @can('send', $version)
                <form method="POST" action="{{ route('entregas.enviar', $version) }}" class="mt-4">
                    @csrf
                    <p class="mb-3 text-sm">Al enviar, esta versión queda congelada. Las correcciones requieren un nuevo intento autorizado.</p>
                    <button class="aris-btn aris-btn-verde" type="submit">Enviar versión</button>
                </form>
            @endcan
            @can('startReview', $version)
                <form method="POST" action="{{ route('entregas.revision.iniciar', $version) }}" class="mt-4">
                    @csrf
                    <button class="aris-btn aris-btn-verde" type="submit">Iniciar revisión</button>
                </form>
            @endcan
            @can('completeReview', $version)
                <form method="POST" action="{{ route('entregas.revision.completar', $version) }}" class="mt-4 space-y-4">
                    @csrf
                    <label class="aris-etiqueta" for="resultado">Resultado cualitativo</label>
                    <select class="aris-campo" id="resultado" name="resultado" required>
                        <option value="">Seleccionar resultado</option>
                        <option value="cumple">Cumple</option>
                        <option value="requiere_ajustes">Requiere Ajustes</option>
                    </select>
                    <label class="aris-etiqueta" for="retroalimentacion">Retroalimentación obligatoria</label>
                    <textarea class="aris-campo" id="retroalimentacion" name="retroalimentacion" rows="5" maxlength="10000" required>{{ old('retroalimentacion') }}</textarea>
                    <button class="aris-btn aris-btn-verde" type="submit">Registrar evaluación</button>
                </form>
            @endcan
            @can('reopen', $version)
                <form method="POST" action="{{ route('entregas.reabrir', $version) }}" class="mt-4 space-y-4" data-reopen-panel>
                    @csrf
                    <label class="aris-etiqueta" for="motivo">Motivo obligatorio de reapertura</label>
                    <textarea class="aris-campo" id="motivo" name="motivo" rows="3" maxlength="5000" required>{{ old('motivo') }}</textarea>
                    <button class="aris-btn aris-btn-fantasma" type="submit">Reabrir para nuevo intento</button>
                </form>
            @endcan
        </section>
        <section class="aris-tarjeta-plana p-6">
            <h3 class="font-semibold">Evaluación de esta versión</h3>
            @forelse ($version->reviewRounds as $revision)
                <article class="mt-4 border-t pt-4">
                    <p class="font-semibold">Revisión {{ $revision->round_number }} · {{ $revision->decision === 'aprobada' ? 'Cumple' : ($revision->decision === 'ajustes' ? 'Requiere Ajustes' : 'En revisión') }}</p>
                    @foreach ($revision->comments as $comentario)
                        <p class="mt-3 whitespace-pre-wrap text-sm">{{ $comentario->body }}</p>
                    @endforeach
                </article>
            @empty
                <p class="mt-3 text-sm text-tenue">No hay revisiones para esta versión.</p>
            @endforelse
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
                <form method="POST" action="{{ route('evidencias.store', $version) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                    @csrf
                    <div data-dropzone class="aris-dropzone">
                    <label class="aris-etiqueta" for="archivo">Adjuntar evidencia</label>
                    <input class="aris-campo" id="archivo" type="file" name="archivo" accept=".pdf,.docx,.xlsx,.jpg,.jpeg,.png" required aria-describedby="limites-archivo">
                    <p class="mt-3 text-sm text-tenue">Arrastra un archivo aquí o utiliza el selector de archivos.</p>
                    <p class="mt-2 text-sm" data-file-status role="status">Ningún archivo seleccionado.</p>
                    </div>
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
            @can('createVersion', $version)
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
