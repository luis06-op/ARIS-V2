# QA reproducible del recorrido académico

```bash
npm ci --ignore-scripts
npx playwright install chromium
npm run build
ARIS_PHP=php8.4 ARIS_QA_TMP=/directorio/temporal/aprobado npm run test:browser
```

El ejecutor crea SQLite y almacenamiento nuevos en `aris-browser-*`, con credenciales
sintéticas aleatorias, correo en memoria y servidor PHP ligado a loopback en puerto libre.
No usa la base de `.env`. Los escenarios se encuentran únicamente bajo `tests/Browser`;
el empaquetado de fuentes los excluye. No sirven para inicializar una instalación real.

Se revisa la vista de entrega con cuatro roles a 390, 768 y 1440 px mediante Chromium y
axe (WCAG A/AA). Se comprueban desbordamiento, salto al contenido mediante teclado,
arrastrar un archivo, carga HTTP, envío congelado, revisión y reapertura con diálogo nativo,
motivo obligatorio, Escape y retorno del foco. Capturas, resultados JSON y log del servidor
quedan en el directorio temporal impreso. Se cierra el navegador y servidor al terminar.

El selector nativo sigue disponible; arrastrar no es obligatorio. Sin JavaScript permanece
el formulario de reapertura. El diálogo reutiliza ese mismo formulario y sus validaciones.
No se incorporan un visor de documentos ni publicación de archivos privados.

Esta evidencia cubre las pantallas y estados indicados, no constituye certificación WCAG
integral ni sustituye la revisión con tecnologías de asistencia y otros navegadores.
