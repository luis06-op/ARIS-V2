{{--
    Menú de accesibilidad del sitio público.
    Preferencias locales (localStorage); no se envían al servidor.
    Controlado por resources/js/accessibility.js.
--}}
<div class="aris-a11y">
    <button
        type="button"
        class="aris-a11y-boton"
        data-a11y-toggle
        aria-expanded="false"
        aria-controls="panel-accesibilidad"
    >
        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="12" cy="5" r="1.6" />
            <path d="M4.5 8.5c2.4.9 4.9 1.4 7.5 1.4s5.1-.5 7.5-1.4" />
            <path d="M12 10v4.2" />
            <path d="M9 20.5l3-6.3 3 6.3" />
        </svg>
        <span class="sr-only">Opciones de accesibilidad</span>
    </button>

    <div
        id="panel-accesibilidad"
        class="aris-a11y-panel"
        data-a11y-panel
        role="dialog"
        aria-modal="false"
        aria-labelledby="accesibilidad-titulo"
        hidden
    >
        <div class="flex items-center justify-between gap-3">
            <h2 id="accesibilidad-titulo" class="aris-a11y-titulo">Accesibilidad</h2>
            <button type="button" class="aris-a11y-cerrar" data-a11y-close aria-label="Cerrar opciones de accesibilidad">
                <span aria-hidden="true">✕</span>
            </button>
        </div>

        <p class="mt-1 text-xs leading-relaxed text-tenue">
            Ajustes que se guardan en este navegador. No modifican tu cuenta.
        </p>

        <div class="aris-a11y-grupo" role="group" aria-labelledby="a11y-tamano">
            <span id="a11y-tamano" class="aris-a11y-grupo-titulo">Tamaño del texto</span>
            <div class="aris-a11y-opciones">
                <button type="button" class="aris-a11y-opcion" data-a11y-option="size:normal" aria-pressed="true">Normal</button>
                <button type="button" class="aris-a11y-opcion" data-a11y-option="size:grande" aria-pressed="false">Grande</button>
                <button type="button" class="aris-a11y-opcion" data-a11y-option="size:muy-grande" aria-pressed="false">Muy grande</button>
            </div>
        </div>

        <div class="aris-a11y-grupo" role="group" aria-labelledby="a11y-contraste">
            <span id="a11y-contraste" class="aris-a11y-grupo-titulo">Contraste</span>
            <div class="aris-a11y-opciones">
                <button type="button" class="aris-a11y-opcion" data-a11y-option="contrast:normal" aria-pressed="true">Normal</button>
                <button type="button" class="aris-a11y-opcion" data-a11y-option="contrast:alto" aria-pressed="false">Alto</button>
            </div>
        </div>

        <div class="aris-a11y-grupo" role="group" aria-labelledby="a11y-movimiento">
            <span id="a11y-movimiento" class="aris-a11y-grupo-titulo">Movimiento</span>
            <div class="aris-a11y-opciones">
                <button type="button" class="aris-a11y-opcion" data-a11y-option="motion:normal" aria-pressed="true">Normal</button>
                <button type="button" class="aris-a11y-opcion" data-a11y-option="motion:reducido" aria-pressed="false">Reducido</button>
            </div>
        </div>

        <div class="aris-a11y-grupo" role="group" aria-labelledby="a11y-enlaces">
            <span id="a11y-enlaces" class="aris-a11y-grupo-titulo">Enlaces</span>
            <div class="aris-a11y-opciones">
                <button type="button" class="aris-a11y-opcion" data-a11y-option="links:normal" aria-pressed="true">Normales</button>
                <button type="button" class="aris-a11y-opcion" data-a11y-option="links:subrayado" aria-pressed="false">Subrayados</button>
            </div>
        </div>

        <button type="button" class="aris-a11y-reinicio" data-a11y-reset>
            Restablecer preferencias
        </button>

        <p class="sr-only" role="status" aria-live="polite" data-a11y-live></p>
    </div>
</div>
