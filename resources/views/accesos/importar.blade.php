@extends('layouts.app')
@section('titulo', 'Importar membresías — ARIS 2.0')
@section('titulo-panel', 'Importación por lotes')
@section('rol', 'Administración')
@section('contenido')
    <div class="max-w-4xl space-y-5">
        <a class="aris-enlace" href="{{ route('accesos.show', $institucion) }}">Volver a la institución</a>
        <section class="aris-tarjeta p-6">
            <h2 class="text-xl font-bold">Vincular cuentas existentes — {{ $institucion->name }}</h2>
            <p class="mt-2 text-sm text-tenue">
                El CSV debe tener una columna <code>email</code>. Solo vincula cuentas activas ya registradas:
                no crea cuentas, no genera contraseñas, no asigna roles ni modifica membresías existentes.
                Máximo 1000 filas y 1 MB. Cada alta queda auditada.
            </p>
            <x-errores-formulario />
            <form method="POST" action="{{ route('accesos.importar.store', $institucion) }}" enctype="multipart/form-data" class="mt-5 space-y-4">
                @csrf
                <label class="aris-etiqueta" for="archivo">Archivo CSV</label>
                <input class="aris-campo" id="archivo" name="archivo" type="file" accept=".csv,text/csv" required>
                <div class="flex flex-wrap gap-3">
                    <button class="aris-btn aris-btn-fantasma" type="submit" name="operacion" value="vista_previa">Ver vista previa</button>
                    <button class="aris-btn aris-btn-verde" type="submit" name="operacion" value="confirmar">Confirmar importación</button>
                </div>
            </form>
        </section>

        @if ($informe)
            <section class="aris-tarjeta-plana p-6">
                <h3 class="font-semibold">Informe</h3>
                <ul class="mt-4 space-y-2 text-sm">
                    <li>Nuevas vinculaciones: {{ $informe['creadas'] }}</li>
                    <li>Ya estaban vinculadas: {{ $informe['existentes'] }}</li>
                    <li>Sin cuenta activa: {{ count($informe['sin_cuenta']) }}</li>
                    <li>Correos no válidos: {{ count($informe['invalidas']) }}</li>
                    <li>Duplicados en el archivo: {{ count($informe['duplicados']) }}</li>
                </ul>
                @if ($informe['sin_cuenta'])
                    <p class="mt-4 text-sm text-tenue">Sin cuenta activa: {{ implode(', ', $informe['sin_cuenta']) }}</p>
                @endif
                @if ($informe['invalidas'])
                    <p class="mt-2 text-sm text-tenue">{{ implode('; ', $informe['invalidas']) }}</p>
                @endif
                @if ($informe['duplicados'])
                    <p class="mt-2 text-sm text-tenue">Duplicados: {{ implode(', ', $informe['duplicados']) }}</p>
                @endif
            </section>
        @endif
    </div>
@endsection
