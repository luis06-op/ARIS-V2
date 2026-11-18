@extends('layouts.publico')
@section('titulo', 'Acceso no autorizado — ARIS 2.0')
@section('contenido')
    <section class="aris-envoltura py-16">
        <div class="aris-tarjeta mx-auto max-w-xl p-8">
            <p class="aris-eyebrow">Acceso restringido</p>
            <h1 class="mt-3 text-3xl font-bold">No tienes autorización para esta operación</h1>
            <p class="mt-4 text-sm leading-relaxed text-tenue">La institución debe mantener vigentes tu membresía y la asignación correspondiente. Consulta al administrador si necesitas revisar tu acceso.</p>
            <a class="aris-btn aris-btn-verde mt-6" href="{{ route('cuenta') }}">Volver a mi cuenta</a>
        </div>
    </section>
@endsection
