@extends('layouts.app')
@section('titulo', 'Proyectos — ARIS 2.0')
@section('titulo-panel', 'Proyectos')
@section('rol', 'Acceso por asignación')
@section('contenido')
    <div class="max-w-5xl">
        <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="aris-eyebrow">Trazabilidad pedagógica</p>
                <h2 class="mt-2 text-2xl font-bold">Tus proyectos</h2>
                <p class="mt-2 text-sm text-tenue">Proyectos disponibles según tu membresía y jurisdicción vigentes.</p>
            </div>
            @if ($puedeCrear)
                <a class="aris-btn aris-btn-verde" href="{{ route('proyectos.create') }}">Crear proyecto</a>
            @endif
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            @forelse ($proyectos as $proyecto)
                <article class="aris-tarjeta p-5">
                    <p class="text-xs text-tenue">{{ $proyecto->code }}</p>
                    <h3 class="mt-2 text-lg font-semibold break-words"><a class="aris-enlace" href="{{ route('proyectos.show', $proyecto) }}">{{ $proyecto->title }}</a></h3>
                    <p class="mt-3 text-sm text-tenue">{{ $proyecto->ficha?->especialidad->nombre ?? 'Sin especialidad vinculada' }}</p>
                    @if ($proyecto->ficha)
                        <p class="mt-1 text-xs text-tenue">Ficha {{ $proyecto->ficha->numero }} · {{ $proyecto->ficha->cohorte }}</p>
                    @endif
                    <span class="aris-chip mt-4">{{ ucfirst($proyecto->status) }}</span>
                </article>
            @empty
                <section class="aris-tarjeta-plana p-6 sm:col-span-2">
                    <h3 class="font-semibold">Aún no tienes proyectos disponibles</h3>
                    <p class="mt-2 text-sm text-tenue">Cuando la institución registre tu asignación, podrás consultar aquí los proyectos autorizados.</p>
                </section>
            @endforelse
        </div>
        <div class="mt-6">{{ $proyectos->links() }}</div>
    </div>
@endsection
