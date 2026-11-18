@can('manageLearners', $proyecto)
    <section class="aris-tarjeta-plana p-6">
        <h3 class="font-semibold">Integrantes aprendices</h3>
        <x-errores-formulario />
        <form method="POST" action="{{ route('proyectos.aprendiz', $proyecto) }}" class="mt-4 space-y-4">
            @csrf
            <label class="aris-etiqueta" for="integrante">Aprendiz de la institución</label>
            <select class="aris-campo" id="integrante" name="user_id" required>
                <option value="">Selecciona un aprendiz</option>
                @foreach ($candidatosAprendices as $aprendiz)
                    <option value="{{ $aprendiz->id }}">{{ $aprendiz->name }} · {{ $aprendiz->email }}</option>
                @endforeach
            </select>
            <label class="aris-etiqueta" for="integrante-activo">Operación</label>
            <select class="aris-campo" id="integrante-activo" name="active"><option value="1">Vincular al proyecto</option><option value="0">Retirar del proyecto</option></select>
            <button class="aris-btn aris-btn-verde" type="submit">Guardar vinculación</button>
        </form>
    </section>
@endcan
