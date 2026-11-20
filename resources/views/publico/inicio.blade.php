@extends('layouts.publico')

@section('titulo', 'ARIS 2.0 — Plataforma académica de trazabilidad')

@section('contenido')
    <section class="aris-envoltura grid items-center gap-12 pt-14 pb-20 lg:grid-cols-[1.15fr_0.85fr] lg:pt-20">
        <div>
            <p class="aris-eyebrow">Plataforma académica</p>

            <h1 class="mt-5 text-4xl leading-[1.06] font-extrabold tracking-tight sm:text-5xl lg:text-6xl">
                Un archivo
                <span class="bg-gradient-to-r from-verde via-verde to-cian-claro bg-clip-text text-transparent">vivo</span>
                de lo que los aprendices construyen.
            </h1>

            <p class="mt-6 max-w-xl text-base leading-relaxed text-tenue sm:text-lg">
                ARIS reúne los proyectos formativos entregados por los aprendices y las guías
                publicadas por los instructores, en un mismo lugar, con validación de evaluadores
                y trazabilidad completa.
            </p>

            <div class="mt-9 flex flex-wrap items-center gap-3">
                <a href="{{ route('registro.create') }}" class="aris-btn aris-btn-verde px-6 py-3">
                    Crear una cuenta
                </a>
                <a href="#proyectos" class="aris-btn aris-btn-fantasma px-6 py-3">
                    Explorar proyectos
                </a>
            </div>

            <p class="mt-6 text-sm text-tenue">
                ¿Ya tienes cuenta?
                <a href="{{ route('sesion.create') }}" class="aris-enlace">Inicia sesión</a>
            </p>
        </div>

        {{-- Composición de marca: decorativa, sin datos ficticios. --}}
        <div class="relative mx-auto hidden aspect-square w-full max-w-md lg:block" aria-hidden="true">
            <span class="absolute inset-0 rounded-full border border-verde/30 shadow-[0_0_80px_rgba(47,220,112,.18)]"></span>
            <span class="absolute inset-8 rounded-full border border-cian/25"></span>
            <span class="absolute inset-16 rounded-full border border-verde/20"></span>

            <div class="absolute inset-0 flex items-center justify-center">
                <div class="flex h-44 w-44 items-center justify-center rounded-full bg-noche-alta shadow-[0_0_70px_rgba(47,220,112,.35)] ring-1 ring-white/10">
                    <x-marca tipo="insignia" class="h-36 w-36 text-white" />
                </div>
            </div>

            <x-marca tipo="insignia" class="absolute top-6 right-2 h-14 w-14 text-cian-claro/80" />
            <x-marca tipo="insignia" class="absolute bottom-10 left-0 h-16 w-16 text-white/80" />
            <span class="absolute top-1/3 left-6 h-10 w-10 rounded-full border border-cian/40"></span>
            <span class="absolute right-10 bottom-1/4 h-6 w-6 rounded-full border border-verde/50"></span>
        </div>
    </section>

    <section id="proyectos" class="aris-envoltura pb-24">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div class="flex items-center gap-3">
                <x-marca tipo="insignia" class="h-10 w-10 text-white" />
                <h2 class="text-2xl font-bold tracking-tight sm:text-3xl">Proyectos publicados</h2>
            </div>
            <p class="text-sm text-tenue">Sin publicaciones todavía</p>
        </div>

        <div class="aris-tarjeta mt-6 px-6 py-14 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full border border-verde/25 bg-verde/5">
                <svg class="h-6 w-6 text-verde" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 7.5A1.5 1.5 0 0 1 4.5 6h4l2 2.5h7A1.5 1.5 0 0 1 19 10v7.5A1.5 1.5 0 0 1 17.5 19h-13A1.5 1.5 0 0 1 3 17.5Z" />
                    <path d="M8 13h8" />
                </svg>
            </div>
            <h3 class="mt-5 text-lg font-semibold">Todavía no hay proyectos publicados</h3>
            <p class="mx-auto mt-2 max-w-xl text-sm leading-relaxed text-tenue">
                Un proyecto aparece aquí solo cuando su versión fue revisada y aprobada para
                publicación. La publicación es explícita y puede revocarse sin borrar el historial.
            </p>
        </div>
    </section>

    <section class="aris-envoltura pb-24">
        <p class="aris-eyebrow">Qué conserva ARIS</p>
        <h2 class="mt-4 max-w-2xl text-2xl font-bold tracking-tight sm:text-3xl">
            La memoria del trabajo académico, no solo la última entrega.
        </h2>

        <div class="mt-8 grid gap-4 md:grid-cols-3">
            <article class="aris-tarjeta-plana p-6">
                <span class="aris-chip">Versiones</span>
                <h3 class="mt-4 text-lg font-semibold">Cada entrega conserva su historia</h3>
                <p class="mt-2 text-sm leading-relaxed text-tenue">
                    Las versiones son inmutables y numeradas: una revisión siempre apunta a la
                    versión exacta que se evaluó.
                </p>
            </article>

            <article class="aris-tarjeta-plana p-6">
                <span class="aris-chip">Revisión</span>
                <h3 class="mt-4 text-lg font-semibold">Quién revisó, cuándo y sobre qué</h3>
                <p class="mt-2 text-sm leading-relaxed text-tenue">
                    Las observaciones quedan registradas con su autor y su fecha. Nada se
                    reescribe: se añade.
                </p>
            </article>

            <article class="aris-tarjeta-plana p-6">
                <span class="aris-chip">Publicación</span>
                <h3 class="mt-4 text-lg font-semibold">Lo público es lo explícitamente aprobado</h3>
                <p class="mt-2 text-sm leading-relaxed text-tenue">
                    El repositorio institucional y la publicación pública son alcances distintos;
                    el visitante ve solo lo autorizado.
                </p>
            </article>
        </div>
    </section>
@endsection
