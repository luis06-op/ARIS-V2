# Dirección visual — «Conocimiento que deja huella»

> **Estado: propuesta.** No hay aprobación de la dirección visual ni de la institución.
> Antigravity no está disponible en el entorno (ver `docs/AGENTES-DISPONIBLES.md`), por lo que
> esta propuesta la asume OpenCode y requiere validación humana antes de F6.
>
> No se reutiliza ningún logotipo, color o material de proyectos anteriores.

## 1. Concepto

ARIS debe sentirse como una **biblioteca académica viva**: un archivo editorial contemporáneo
donde el trabajo de estudiantes y docentes se percibe como una contribución valiosa. No un
panel SaaS genérico, no un aula virtual infantilizada.

Metáfora rectora: **folios, hitos y vínculos**. La evolución de un proyecto se lee como una
secuencia de versiones con marcas de tiempo y autoría, no como una línea de tiempo decorativa.

## 2. Principios

1. **Memoria visible** — la versión, la fecha y el autor son parte del contenido, no un detalle
   escondido en un tooltip.
2. **Jerarquía editorial** — tipografía con contraste fuerte, márgenes deliberados, retícula
   de 12 columnas, densidad controlada.
3. **Calidez sin infantilizar** — color cálido en acentos, superficies neutras, sin
   ilustraciones caricaturescas.
4. **Movimiento con propósito** — transiciones breves de estado; animación de fondo **solo** en
   la landing.
5. **Accesibilidad primero** — contraste AA, foco visible, navegación por teclado, respeto a
   `prefers-reduced-motion`.

## 3. Paleta propuesta

Los valores son un punto de partida, no una decisión final. Deben validarse con contraste real.

| Token | Valor propuesto | Uso |
|---|---|---|
| `--aris-ink` | `#1B1A17` | Texto principal |
| `--aris-ink-soft` | `#55514A` | Texto secundario |
| `--aris-paper` | `#FBF9F4` | Fondo base (papel cálido) |
| `--aris-surface` | `#FFFFFF` | Tarjetas y superficies elevadas |
| `--aris-line` | `#E2DDD1` | Bordes y separadores |
| `--aris-accent` | `#2F5D50` | Acento institucional (verde profundo) |
| `--aris-accent-soft` | `#E7EFEA` | Fondos de acento suave |
| `--aris-highlight` | `#C9A227` | Marca de hito o versión destacada |
| `--aris-danger` | `#A63A2E` | Error y acciones destructivas |
| `--aris-success` | `#2E6B4F` | Confirmación |
| `--aris-info` | `#3A5A78` | Información y estados neutros |

El color de estado se reserva para significado real: no se usa como decoración.

## 4. Tipografía

- **Títulos:** serif editorial con fallback local (`Georgia, 'Times New Roman', serif`) o una
  fuente variable autoalojada si se aprueba su licencia y peso.
- **Texto e interfaz:** sans-serif de sistema (`system-ui, -apple-system, 'Segoe UI', Roboto,
  sans-serif`).
- **Datos y versiones:** sans con cifras tabulares (`font-variant-numeric: tabular-nums`).
- **Sin dependencia de Google Fonts ni CDN tipográficos** en producción.

Escala propuesta (rem): 0.75 · 0.875 · 1 · 1.125 · 1.25 · 1.5 · 1.875 · 2.25 · 3.

## 5. Espaciado, radios y elevación

- Escala de espaciado en múltiplos de 4 px: 4, 8, 12, 16, 24, 32, 48, 64.
- Radios: 4 px (controles), 8 px (tarjetas), 12 px (contenedores), 999 px (chips).
- Elevación sobria: una sola sombra (`0 1px 2px rgba(27,26,23,.06), 0 8px 24px rgba(27,26,23,.06)`);
  el resto se resuelve con bordes.
- Retícula: 12 columnas, ancho máximo 1200 px, gutter 24 px, márgenes laterales 16/24/32 px.

## 6. Iconografía

Set lineal propio de 24×24 px, trazo 1.5 px, esquinas redondeadas 2 px, un solo estilo:
`project`, `activity`, `submission`, `document`, `version`, `review`, `publication`,
`institution`. Se entregan como SVG en `public/assets/icons/` cuando exista F6. No se mezclan
packs.

## 7. Landing pública

Secciones (contenido real, sin cifras inventadas):

1. **Hero** — propuesta de valor, ejemplo visual propio, entrada a sesión.
2. **Problema** — pérdida de seguimiento y memoria académica.
3. **Ciclo del conocimiento** — proyecto → guía → entrega → versión → revisión → preservación.
4. **Beneficios por rol** — aprendiz, docente, institución.
5. **Repositorio público** — solo si hay material publicado; si no, explicación honesta.
6. **Privacidad, seguridad y accesibilidad** — afirmaciones demostrables.
7. **Preguntas frecuentes y pie institucional.**

**Fondo animado (solo landing):** SVG/CSS ligero con folios y trazos que se conectan. Debe:
- pausarse con `prefers-reduced-motion` y con `document.hidden`;
- limitar el trabajo por fotograma y usar `requestAnimationFrame` con throttle;
- mantener contraste del texto y no capturar eventos;
- tener fallback estático y no superar un presupuesto de CPU definido en F6.

No se anima ningún fondo en los paneles internos.

## 8. Tonos de voz

- Claro, respetuoso, sin infantilizar ni dramatizar.
- Sin superlativos ni promesas («infalible», «revolucionario», «IA que califica»).
- Mensajes de error que explican **qué pasó y qué hacer**, sin culpar al usuario.
- Español neutro institucional; fechas y nombres según la zona horaria configurada.

## 9. Lo que queda prohibido

- Copiar la estética de Moodle, Google Classroom, Notion o cualquier producto reconocible.
- Ilustraciones genéricas de «estudiantes felices» o gráficos de stock.
- Animaciones decorativas continuas, parallax en dashboards, contadores falsos.
- Mostrar métricas sin datos reales detrás.

## 10. Validación pendiente (F6)

- [ ] Contraste AA de cada combinación token/fondo.
- [ ] Prueba en 360, 390, 768, 1024 y 1440 px.
- [ ] Navegación completa por teclado y foco visible.
- [ ] Zoom al 200 % sin pérdida de contenido.
- [ ] `prefers-reduced-motion` respetado.
- [ ] Revisión con lector de pantalla en las pantallas principales.
