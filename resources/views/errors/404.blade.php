@extends('layouts.publico')
@section('titulo', 'Recurso no disponible — ARIS 2.0')
@section('contenido')
    <section class="aris-envoltura py-16">
        <div class="aris-tarjeta mx-auto max-w-xl p-8">
            <p class="aris-eyebrow">Recurso no disponible</p>
            <h1 class="mt-3 text-3xl font-bold">No encontramos esta página o archivo</h1>
            <p class="mt-4 text-sm text-tenue">Revisa el enlace o vuelve al inicio para continuar.</p>
            <a class="aris-btn aris-btn-verde mt-6" href="{{ route('inicio') }}">Volver al inicio</a>
        </div>
    </section>
@endsection
