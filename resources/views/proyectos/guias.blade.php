<section class="aris-tarjeta-plana p-6">
    <h3 class="font-semibold">Guías y versiones asignadas</h3>
    <ul class="mt-4 space-y-3 text-sm">
        @forelse ($guias as $guia)
            <li><a class="aris-enlace" href="{{ route('guias.show', $guia) }}">{{ $guia->title }} · Versión {{ $guia->version_number }}</a> · {{ $guia->status }}</li>
        @empty
            <li class="text-tenue">No hay versiones de guía disponibles para tu acceso.</li>
        @endforelse
    </ul>
    @can('update', $proyecto)
        <h4 class="mt-6 font-semibold">Crear guía</h4>
        <x-errores-formulario />
        <form class="mt-4 space-y-4" method="POST" action="{{ route('guias.store', $proyecto) }}">
            @csrf
            <label class="aris-etiqueta" for="guia-code">Código</label><input class="aris-campo" id="guia-code" name="code" maxlength="40" required>
            <label class="aris-etiqueta" for="guia-title">Título</label><input class="aris-campo" id="guia-title" name="title" maxlength="180" required>
            <label class="aris-etiqueta" for="guia-content">Contenido de la guía</label><textarea class="aris-campo" id="guia-content" name="content" rows="6" maxlength="50000" required></textarea>
            <button class="aris-btn aris-btn-verde" type="submit">Crear borrador de guía</button>
        </form>
    @endcan
</section>
