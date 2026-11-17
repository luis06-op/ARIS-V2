{{--
    Fondo decorativo de círculos neon (landing, acceso y área privada).
    Es puramente estético, no captura eventos y se detiene con prefers-reduced-motion.
    Recibe $atenuado para reducir la intensidad dentro de los paneles.
--}}
@php
    $orbes = [
        ['tam' => 150, 'y' => '12%', 'x' => '3%', 'clase' => 'orb-verde', 'dur' => 26, 'ret' => 4],
        ['tam' => 60, 'y' => '5%', 'x' => '36%', 'clase' => '', 'dur' => 19, 'ret' => 9],
        ['tam' => 42, 'y' => '8%', 'x' => '50%', 'clase' => 'orb-tenue', 'dur' => 24, 'ret' => 2],
        ['tam' => 130, 'y' => '3%', 'x' => '72%', 'clase' => 'orb-verde', 'dur' => 30, 'ret' => 12],
        ['tam' => 44, 'y' => '14%', 'x' => '94%', 'clase' => '', 'dur' => 21, 'ret' => 6],
        ['tam' => 200, 'y' => '30%', 'x' => '18%', 'clase' => 'orb-verde orb-tenue', 'dur' => 34, 'ret' => 3],
        ['tam' => 52, 'y' => '34%', 'x' => '55%', 'clase' => '', 'dur' => 18, 'ret' => 11],
        ['tam' => 90, 'y' => '44%', 'x' => '83%', 'clase' => 'orb-verde orb-tenue', 'dur' => 27, 'ret' => 7],
        ['tam' => 28, 'y' => '53%', 'x' => '12%', 'clase' => 'orb-tenue', 'dur' => 22, 'ret' => 5],
        ['tam' => 34, 'y' => '55%', 'x' => '87%', 'clase' => '', 'dur' => 20, 'ret' => 13],
        ['tam' => 170, 'y' => '66%', 'x' => '68%', 'clase' => 'orb-verde orb-tenue', 'dur' => 36, 'ret' => 8],
        ['tam' => 46, 'y' => '70%', 'x' => '90%', 'clase' => 'orb-tenue', 'dur' => 23, 'ret' => 1],
        ['tam' => 220, 'y' => '78%', 'x' => '2%', 'clase' => 'orb-verde orb-tenue', 'dur' => 38, 'ret' => 10],
        ['tam' => 64, 'y' => '84%', 'x' => '42%', 'clase' => '', 'dur' => 25, 'ret' => 6],
        ['tam' => 120, 'y' => '88%', 'x' => '78%', 'clase' => 'orb-verde orb-tenue', 'dur' => 32, 'ret' => 3],
    ];
@endphp

<div @class(['aris-orbes', 'opacity-35' => $atenuado ?? false]) aria-hidden="true">
    @foreach ($orbes as $orb)
        <span
            class="orb {{ $orb['clase'] }}"
            style="width: {{ $orb['tam'] }}px; height: {{ $orb['tam'] }}px; top: {{ $orb['y'] }}; left: {{ $orb['x'] }}; animation-duration: {{ $orb['dur'] }}s; animation-delay: -{{ $orb['ret'] }}s;"
        ></span>
    @endforeach
    <span
        class="orb orb-verde orb-brillo"
        style="width: 420px; height: 420px; top: 18%; left: 62%; opacity: .35; animation-duration: 40s;"
    ></span>
</div>
