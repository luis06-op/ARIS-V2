<section class="aris-tarjeta-plana p-6">
    <h3 class="font-semibold">Borradores de entrega</h3>
    <ul class="mt-4 space-y-3 text-sm">
        @forelse ($entregas as $entrega)
            @if ($entrega->versions->isNotEmpty())
                <li><a class="aris-enlace" href="{{ route('entregas.show', $entrega->versions->first()) }}">{{ $entrega->title }}</a> · {{ $entrega->status }}</li>
            @endif
        @empty
            <li class="text-tenue">No hay entregas disponibles para tu acceso.</li>
        @endforelse
    </ul>
    @can('submit', $proyecto)
        <h4 class="mt-6 font-semibold">Preparar una entrega</h4>
        <x-errores-formulario />
        <form method="POST" action="{{ route('entregas.store', $proyecto) }}" class="mt-4 space-y-4">
            @csrf
            <label class="aris-etiqueta" for="activity_assignment_id">Asignación y versión exacta de guía</label>
            <select class="aris-campo" id="activity_assignment_id" name="activity_assignment_id" required>
                <option value="">Selecciona una asignación vigente</option>
                @foreach ($asignaciones as $asignacion)
                    <option value="{{ $asignacion->id }}">{{ $asignacion->activityVersion->title }} · Versión {{ $asignacion->activityVersion->version_number }}</option>
                @endforeach
            </select>
            <label class="aris-etiqueta" for="entrega-title">Título</label><input class="aris-campo" id="entrega-title" name="title" maxlength="180" required>
            <label class="aris-etiqueta" for="entrega-summary">Resumen inicial</label><textarea class="aris-campo" id="entrega-summary" name="summary" rows="3" maxlength="500" required></textarea>
            <button class="aris-btn aris-btn-verde" type="submit">Crear borrador de entrega</button>
        </form>
    @endcan
    @can('authorizeUnlinkedException', $proyecto)
        <details class="mt-6">
            <summary class="cursor-pointer font-semibold">Autorizar excepción PI-001</summary>
            <p class="mt-3 text-sm text-tenue">Permite preparar un borrador sin asignación de guía. Se registrarán tu identidad, la persona beneficiada, la fecha, la IP y el motivo.</p>
            <form method="POST" action="{{ route('entregas.excepcion', $proyecto) }}" class="mt-4 space-y-4">
                @csrf
                <label class="aris-etiqueta" for="beneficiario">Aprendiz del proyecto</label>
                <select class="aris-campo" id="beneficiario" name="user_id" required>
                    <option value="">Selecciona un aprendiz</option>
                    @foreach ($aprendices as $aprendiz)
                        <option value="{{ $aprendiz->id }}">{{ $aprendiz->name }}</option>
                    @endforeach
                </select>
                <label class="aris-etiqueta" for="excepcion-title">Título</label><input class="aris-campo" id="excepcion-title" name="title" maxlength="180" required>
                <label class="aris-etiqueta" for="excepcion-summary">Resumen</label><textarea class="aris-campo" id="excepcion-summary" name="summary" maxlength="500" required></textarea>
                <label class="aris-etiqueta" for="motivo_excepcion">Justificación (mínimo 20 caracteres)</label><textarea class="aris-campo" id="motivo_excepcion" name="motivo_excepcion" minlength="20" maxlength="2000" required></textarea>
                <button class="aris-btn aris-btn-fantasma" type="submit">Autorizar y registrar excepción</button>
            </form>
        </details>
    @endcan
</section>
