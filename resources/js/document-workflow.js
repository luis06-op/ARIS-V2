// Mejora progresiva: selector de archivos y reapertura funcionan también sin JavaScript.
document.querySelectorAll('[data-dropzone]').forEach((zone) => {
    const input = zone.querySelector('input[type="file"]');
    const message = zone.querySelector('[data-file-status]');
    const announce = () => {
        message.textContent = input.files.length ? `Archivo seleccionado: ${input.files[0].name}` : 'Ningún archivo seleccionado.';
    };
    input.addEventListener('change', announce);
    zone.addEventListener('dragover', (event) => {
        if (!event.dataTransfer.types.includes('Files')) return;
        event.preventDefault();
        zone.dataset.dragging = 'true';
    });
    zone.addEventListener('dragleave', () => delete zone.dataset.dragging);
    zone.addEventListener('drop', (event) => {
        event.preventDefault();
        delete zone.dataset.dragging;
        if (event.dataTransfer.files.length !== 1) {
            message.textContent = 'Selecciona un solo archivo por carga.';
            return;
        }
        input.files = event.dataTransfer.files;
        announce();
        input.focus();
    });
});

document.querySelectorAll('[data-reopen-panel]').forEach((panel) => {
    if (typeof HTMLDialogElement === 'undefined' || !HTMLDialogElement.prototype.showModal) return;
    const dialog = document.createElement('dialog');
    dialog.className = 'aris-dialog';
    dialog.id = 'reapertura-dialogo';
    dialog.setAttribute('aria-labelledby', 'reapertura-titulo');
    const heading = document.createElement('h3');
    heading.id = 'reapertura-titulo';
    heading.className = 'text-lg font-bold';
    heading.textContent = 'Reabrir entrega';
    const opener = document.createElement('button');
    opener.type = 'button';
    opener.className = 'aris-btn aris-btn-fantasma mt-4';
    opener.textContent = 'Reabrir entrega';
    opener.setAttribute('aria-haspopup', 'dialog');
    opener.setAttribute('aria-controls', dialog.id);
    const close = document.createElement('button');
    close.type = 'button';
    close.className = 'aris-btn aris-btn-fantasma mt-4';
    close.textContent = 'Cancelar reapertura';
    panel.before(opener, dialog);
    dialog.append(heading, panel, close);
    opener.addEventListener('click', () => {
        dialog.showModal();
        panel.querySelector('textarea').focus();
    });
    close.addEventListener('click', () => dialog.close());
    dialog.addEventListener('close', () => opener.focus());
});
