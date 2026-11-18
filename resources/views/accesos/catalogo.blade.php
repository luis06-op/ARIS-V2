<div class="grid gap-5 lg:grid-cols-3">
    <section class="aris-tarjeta-plana p-5">
        <h3 class="font-semibold">Vincular cuenta registrada</h3>
        <form class="mt-4 space-y-4" method="POST" action="{{ route('miembros.store', $institucion) }}">
            @csrf
            <label class="aris-etiqueta" for="miembro-email">Correo de la persona</label>
            <input class="aris-campo" id="miembro-email" name="email" type="email" required>
            <button class="aris-btn aris-btn-verde" type="submit">Vincular sin roles</button>
        </form>
    </section>
    <section class="aris-tarjeta-plana p-5">
        <h3 class="font-semibold">Registrar especialidad</h3>
        <form class="mt-4 space-y-4" method="POST" action="{{ route('catalogo.store', $institucion) }}">
            @csrf
            <input type="hidden" name="operation" value="especialidad">
            <label class="aris-etiqueta" for="especialidad-codigo">Código institucional</label><input class="aris-campo" id="especialidad-codigo" name="codigo" maxlength="80" required>
            <label class="aris-etiqueta" for="especialidad-nombre">Nombre oficial</label><input class="aris-campo" id="especialidad-nombre" name="nombre" maxlength="255" required>
            <button class="aris-btn aris-btn-verde" type="submit">Registrar especialidad</button>
        </form>
    </section>
    <section class="aris-tarjeta-plana p-5">
        <h3 class="font-semibold">Registrar ficha</h3>
        <form class="mt-4 space-y-4" method="POST" action="{{ route('catalogo.store', $institucion) }}">
            @csrf
            <input type="hidden" name="operation" value="ficha">
            <label class="aris-etiqueta" for="ficha-especialidad">Especialidad</label><select class="aris-campo" id="ficha-especialidad" name="especialidad_id" required>
                <option value="">Selecciona una especialidad</option>
                @foreach ($especialidades as $especialidad)
                    <option value="{{ $especialidad->id }}">{{ $especialidad->nombre }}</option>
                @endforeach
            </select>
            <label class="aris-etiqueta" for="ficha-numero">Número de ficha</label><input class="aris-campo" id="ficha-numero" name="numero" maxlength="80" required>
            <label class="aris-etiqueta" for="ficha-cohorte">Cohorte académica</label><input class="aris-campo" id="ficha-cohorte" name="cohorte" maxlength="120" required>
            <button class="aris-btn aris-btn-verde" type="submit">Registrar ficha</button>
        </form>
    </section>
</div>
