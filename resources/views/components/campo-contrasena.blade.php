{{--
    Campo de contraseña con botón «ojito» para mostrar u ocultar el valor.
    El botón declara aria-controls con el id del campo (resources/js/password-visibility.js).

    Uso:
    <x-campo-contrasena id="password" etiqueta="Contraseña" autocomplete="current-password" />
--}}
@props([
    'id' => 'password',
    'name' => null,
    'etiqueta' => 'Contraseña',
    'autocomplete' => 'current-password',
    'placeholder' => '••••••••',
    'invalido' => false,
    'descritoPor' => null,
    'autofocus' => false,
])

@php
    $name ??= $id;
@endphp

<div>
    <label for="{{ $id }}" class="aris-etiqueta">{{ $etiqueta }}</label>

    <div class="aris-campo-grupo">
        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="password"
            required
            autocomplete="{{ $autocomplete }}"
            placeholder="{{ $placeholder }}"
            @if ($autofocus) autofocus @endif
            @class(['aris-campo', 'aris-campo-con-accion', 'border-rojo/60' => $invalido])
            @if ($invalido) aria-invalid="true" @endif
            @if ($descritoPor) aria-describedby="{{ $descritoPor }}" @endif
        >

        <button
            type="button"
            class="aris-ojito"
            data-password-toggle
            aria-controls="{{ $id }}"
            aria-pressed="false"
            aria-label="Mostrar contraseña"
        >
            <svg data-icon="ver" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M2.5 12S6 5.8 12 5.8 21.5 12 21.5 12 18 18.2 12 18.2 2.5 12 2.5 12Z" />
                <circle cx="12" cy="12" r="2.9" />
            </svg>
            <svg data-icon="ocultar" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4 4l16 16" />
                <path d="M9.6 6.1A9.7 9.7 0 0 1 12 5.8c6 0 9.5 6.2 9.5 6.2a17.6 17.6 0 0 1-3.4 4" />
                <path d="M6.3 8.1A17.4 17.4 0 0 0 2.5 12S6 18.2 12 18.2a9.6 9.6 0 0 0 4-.85" />
                <path d="M10.2 10.4a2.7 2.7 0 0 0 3.6 3.8" />
            </svg>
        </button>
    </div>
</div>
