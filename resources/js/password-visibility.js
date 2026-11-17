/**
 * Alterna la visibilidad de los campos de contraseña.
 * El botón debe declarar aria-controls con el id del campo.
 */
function iniciarOjitos() {
    document.querySelectorAll('[data-password-toggle]').forEach((boton) => {
        const campo = document.getElementById(boton.getAttribute('aria-controls') ?? '');

        if (!campo) {
            return;
        }

        const iconoVer = boton.querySelector('[data-icon="ver"]');
        const iconoOcultar = boton.querySelector('[data-icon="ocultar"]');

        boton.addEventListener('click', () => {
            const visible = campo.type === 'text';
            const mostrar = !visible;

            campo.type = mostrar ? 'text' : 'password';
            boton.setAttribute('aria-pressed', String(mostrar));
            boton.setAttribute('aria-label', mostrar ? 'Ocultar contraseña' : 'Mostrar contraseña');

            // Icono «ojo» cuando el valor está oculto; «ojo tachado» cuando ya se ve.
            iconoVer?.classList.toggle('hidden', mostrar);
            iconoOcultar?.classList.toggle('hidden', !mostrar);

            campo.focus({ preventScroll: true });

            const fin = campo.value.length;

            try {
                campo.setSelectionRange(fin, fin);
            } catch {
                // Algunos navegadores no permiten selección en type=password; no es crítico.
            }
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', iniciarOjitos);
} else {
    iniciarOjitos();
}
