{{--
    Marca ARIS en línea. Se incrusta el SVG para que herede el color del texto.
    Uso: <x-marca tipo="insignia" class="h-12 w-12" />
--}}
@props(['tipo' => 'insignia'])

@php
    $archivos = [
        'insignia' => 'assets/brand/aris-insignia.svg',
        'isotipo' => 'assets/brand/aris-isotipo.svg',
        'logotipo' => 'assets/brand/aris-logotipo.svg',
    ];

    $ruta = public_path($archivos[$tipo] ?? $archivos['insignia']);
    $svg = is_file($ruta) ? file_get_contents($ruta) : '';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex shrink-0']) }} aria-hidden="true">
    {!! $svg !!}
</span>
