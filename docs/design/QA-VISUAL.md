# QA visual

> **Estado: NO EJECUTADO.** No existe interfaz todavía y Playwright no está operativo en este
> entorno (`npx playwright` falló por falta de respuesta del registro npm). Este documento
> define qué se debe probar y cómo registrar la evidencia; no afirma que se haya observado
> ninguna captura.

## 1. Matriz de anchos

| Ancho | Dispositivo de referencia | Qué se comprueba |
|---|---|---|
| 360 px | Móvil modesto | Sin scroll horizontal, botones ≥ 44 px, texto legible |
| 390 px | Móvil actual | Navegación, formularios, carga de archivos |
| 768 px | Tableta | Rejilla de dos columnas, tablas con scroll |
| 1024 px | Portátil pequeño | Sidebar colapsable, densidad |
| 1440 px | Escritorio | Ancho máximo, márgenes, jerarquía |

## 2. Comprobaciones obligatorias

- [ ] Navegación completa por teclado (Tab, Shift+Tab, Enter, Esc).
- [ ] Foco visible en todos los controles interactivos.
- [ ] Zoom al 200 % sin pérdida de contenido ni solapamientos.
- [ ] Contraste AA en texto normal y grande; medido, no estimado.
- [ ] `prefers-reduced-motion`: sin animaciones de fondo ni transiciones largas.
- [ ] Estados vacíos, de error, de carga y de permiso denegado en cada pantalla.
- [ ] Mensajes de error asociados al campo y anunciados a lectores de pantalla.
- [ ] Formularios con etiquetas explícitas, no solo placeholder.
- [ ] Descarga y carga de archivos usables en móvil.
- [ ] Sin texto truncado en tablas de versiones.

## 3. Herramientas

| Herramienta | Estado | Uso |
|---|---|---|
| Playwright | No operativo | Automatización de flujos y capturas |
| Navegador manual | Disponible por el usuario | Revisión visual real |
| Axe / Lighthouse | Por confirmar | Accesibilidad y rendimiento |
| Lector de pantalla | Por confirmar | Verificación semántica |

## 4. Registro de evidencia

Por cada pantalla y ancho:

```text
Pantalla: <ruta>
Ancho: <px>
Fecha y ejecutor:
Resultado: APROBADA / FALLIDA / NO EJECUTADA
Hallazgos: <lista breve>
Captura: <ruta local, si existe>
```

Sin captura o sin registro, la comprobación se considera **NO EJECUTADA**.

## 5. Criterio de cierre de F6

1. Todas las pantallas del recorrido F4/F5 revisadas en los cinco anchos.
2. Ningún hallazgo crítico de accesibilidad abierto.
3. Fondo animado medido: sin bloqueo del hilo principal y con fallback estático.
4. Enlaces y controles funcionan contra datos reales de la base sintética.
5. Sin UI que simule operaciones inexistentes.
