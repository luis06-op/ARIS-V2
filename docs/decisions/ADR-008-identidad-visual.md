# ADR-008 — Identidad visual ARIS 2.0 (noche, verde y cian)

- **Estado:** aceptada
- **Fecha:** 2026-11-17
- **Decidida por:** responsable del proyecto (aprobación en sesión de trabajo)
- **Sustituye:** propuesta visual de `docs/design/DIRECCION-VISUAL.md` (versión del 11-11-2026)

## Contexto

La dirección visual anterior era una propuesta editorial en papel cálido (`#FBF9F4`, verde
profundo `#2F5D50`) sin logotipo propio, pendiente de validación. El responsable del proyecto
entregó un logotipo (búho lineal) y seis pantallas de referencia (portada, acceso, registro y
paneles por rol) con una estética oscura, verde neón y cian. No había aprobación institucional
previa de la propuesta anterior.

## Decisión

1. **Marca.** El logotipo entregado se vectoriza y se usa como marca oficial en tres formas:
   - `public/assets/brand/aris-isotipo.svg` — búho solo.
   - `public/assets/brand/aris-logotipo.svg` — búho + palabra «ARIS» vertical.
   - `public/assets/brand/aris-insignia.svg` — insignia circular para cabecera, acceso y pie.
   - `public/favicon.svg` — búho sobre cuadrado noche para la pestaña del navegador.
   Los SVG usan `currentColor`, de modo que heredan el color del texto que los contiene.
2. **Paleta.** Fondo noche `#09111E`, superficie `#0A1624`, borde `#1A2C40`, texto `#F4F8FF`,
   texto tenue `#93A7BD`, verde de acción `#2FDC70`, cian informativo `#3596FF` y cian claro
   `#7FE7FF`. Los tokens viven en `resources/css/app.css` (`@theme`) y no se escriben colores
   sueltos en las vistas.
3. **Tipografía.** Instrument Sans autoalojada (OFL) compilada por Vite en el propio servidor;
   no hay CDN tipográfico en tiempo de ejecución. Títulos con peso 700–800 y `tracking-tight`.
4. **Movimiento.** Círculos neon de fondo con animación lenta, solo decorativos
   (`aria-hidden`), sin capturar eventos y detenidos por `prefers-reduced-motion`. La animación
   se admite también en el área privada atenuada, porque forma parte de la identidad aprobada;
   se reduce a opacidad 35 % para no competir con el contenido.
5. **Alcance de esta sesión.** Portada pública, acceso, registro y «Mi cuenta» (armazón de la
   aplicación). Las pantallas de panel con métricas por rol no se implementan hasta que existan
   datos reales (F3), para no mostrar cifras inventadas.

## Consecuencias

- `docs/design/DIRECCION-VISUAL.md` se reescribe con esta dirección; no quedan dos propuestas
  vivas.
- La portada no muestra contadores ni proyectos ficticios: el estado vacío es real.
- El registro abierto se implementa porque `PI-002` fue aprobado parcialmente en la misma
  sesión; el resto de la política (quién asigna roles y autoriza evaluaciones) sigue pendiente.
- El logotipo se reconstruyó por vectorización del arte entregado; si la institución posee el
  archivo vectorial original, debe reemplazar los SVG y registrarse la fuente.
