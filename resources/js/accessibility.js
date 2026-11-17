/**
 * Menú de accesibilidad de ARIS 2.0.
 * Preferencias locales (no se envían al servidor) guardadas en localStorage.
 */
const STORAGE_KEY = 'aris.accesibilidad';

const DEFAULTS = {
    size: 'normal',
    contrast: 'normal',
    motion: 'normal',
    links: 'normal',
};

const ETIQUETAS = {
    size: { normal: 'Texto normal', grande: 'Texto grande', 'muy-grande': 'Texto muy grande' },
    contrast: { normal: 'Contraste normal', alto: 'Contraste alto' },
    motion: { normal: 'Movimiento normal', reducido: 'Movimiento reducido' },
    links: { normal: 'Enlaces normales', subrayado: 'Enlaces subrayados' },
};

function leerEstado() {
    try {
        const guardado = JSON.parse(localStorage.getItem(STORAGE_KEY) ?? '{}');

        return { ...DEFAULTS, ...guardado };
    } catch {
        return { ...DEFAULTS };
    }
}

function guardarEstado(estado) {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(estado));
    } catch {
        // Almacenamiento bloqueado: la preferencia dura solo esta página.
    }
}

const estado = leerEstado();

function aplicarEstado() {
    const root = document.documentElement;

    root.dataset.a11ySize = estado.size;
    root.dataset.a11yContrast = estado.contrast;
    root.dataset.a11yMotion = estado.motion;
    root.dataset.a11yLinks = estado.links;

    document.querySelectorAll('[data-a11y-option]').forEach((boton) => {
        const [clave, valor] = boton.dataset.a11yOption.split(':');
        boton.setAttribute('aria-pressed', String(estado[clave] === valor));
    });
}

function anunciar(mensaje) {
    const region = document.querySelector('[data-a11y-live]');

    if (region) {
        region.textContent = mensaje;
    }
}

function iniciar() {
    const panel = document.querySelector('[data-a11y-panel]');
    const abridor = document.querySelector('[data-a11y-toggle]');

    aplicarEstado();

    if (!panel || !abridor) {
        return;
    }

    const abrir = () => {
        panel.hidden = false;
        abridor.setAttribute('aria-expanded', 'true');
        panel.querySelector('[data-a11y-option]')?.focus();
    };

    const cerrar = (devolverFoco = true) => {
        panel.hidden = true;
        abridor.setAttribute('aria-expanded', 'false');

        if (devolverFoco) {
            abridor.focus();
        }
    };

    abridor.addEventListener('click', () => {
        if (panel.hidden) {
            abrir();
        } else {
            cerrar();
        }
    });

    panel.querySelectorAll('[data-a11y-close]').forEach((boton) => {
        boton.addEventListener('click', () => cerrar());
    });

    panel.addEventListener('click', (evento) => {
        const boton = evento.target.closest('[data-a11y-option]');

        if (!boton) {
            return;
        }

        const [clave, valor] = boton.dataset.a11yOption.split(':');
        estado[clave] = valor;
        guardarEstado(estado);
        aplicarEstado();
        anunciar(`${ETIQUETAS[clave]?.[valor] ?? 'Preferencia actualizada'}.`);
    });

    panel.querySelector('[data-a11y-reset]')?.addEventListener('click', () => {
        Object.assign(estado, DEFAULTS);
        guardarEstado(estado);
        aplicarEstado();
        anunciar('Preferencias de accesibilidad restablecidas.');
    });

    document.addEventListener('keydown', (evento) => {
        if (evento.key === 'Escape' && !panel.hidden) {
            cerrar();
        }
    });

    document.addEventListener('click', (evento) => {
        if (panel.hidden) {
            return;
        }

        if (!panel.contains(evento.target) && !abridor.contains(evento.target)) {
            cerrar(false);
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', iniciar);
} else {
    iniciar();
}
