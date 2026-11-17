# Dirección visual — ARIS 2.0

> **Estado: aprobada el 2026-11-17** por el responsable del proyecto (ADR-008). Sustituye la
> propuesta editorial en papel cálido del 11-11-2026. La marca y los tokens están implementados
> en `public/assets/brand/` y `resources/css/app.css`.

## 1. Concepto

ARIS se presenta como un **archivo vivo**: un espacio nocturno, silencioso y preciso donde el
trabajo académico brilla —literalmente— con luz propia. El búho es el guardián del archivo: mira
de frente, no adorna.

La metáfora de fondo son **órbitas**: círculos de luz que rodean el conocimiento y se conectan
sin jerarquía visual. No es un panel SaaS genérico ni un aula virtual infantilizada.

## 2. Principios

1. **Memoria visible** — la versión, la fecha y la autoría forman parte del contenido.
2. **Contraste nocturno** — superficies oscuras, texto claro y acentos de luz; nada de grises
   indistinguibles.
3. **Luz con significado** — verde para acción y confirmación, cian para información y enlaces.
   El color de estado no se usa como decoración.
4. **Movimiento lento** — órbitas de fondo a 20–40 s, sin rebotes ni parallax; se detienen con
   `prefers-reduced-motion`.
5. **Accesibilidad primero** — contraste AA verificado, foco visible, navegación por teclado.

## 3. Paleta

| Token | Valor | Uso |
|---|---|---|
| `--color-noche` | `#09111E` | Fondo base de toda la aplicación |
| `--color-noche-alta` | `#0B1826` | Barras y superficies de navegación |
| `--color-superficie` | `#0A1624` | Tarjetas |
| `--color-superficie-2` | `#0E1F33` | Hover y superficies elevadas |
| `--color-borde` | `#1A2C40` | Bordes y separadores |
| `--color-texto` | `#F4F8FF` | Texto principal |
| `--color-tenue` | `#93A7BD` | Texto secundario |
| `--color-verde` | `#2FDC70` | Acción principal, confirmación, marca |
| `--color-verde-hondo` | `#0F3D26` | Texto sobre verde (contraste) |
| `--color-cian` | `#3596FF` | Acción secundaria e información |
| `--color-cian-claro` | `#7FE7FF` | Enlaces y foco |
| `--color-rojo` | `#FF6B6B` | Error y acciones destructivas |
| `--color-ambar` | `#F5B544` | Advertencia y estados intermedios |

Contraste verificado (WCAG 2.1):

| Combinación | Ratio | Resultado |
|---|---|---|
| `#F4F8FF` sobre `#09111E` | 17,75:1 | AA / AAA |
| `#93A7BD` sobre `#09111E` | 7,66:1 | AA / AAA |
| `#2FDC70` sobre `#09111E` | 10,44:1 | AA / AAA |
| `#04240F` sobre `#2FDC70` (botón) | 9,17:1 | AA / AAA |
| `#7FE7FF` sobre `#09111E` | 13,31:1 | AA / AAA |
| `#FF6B6B` sobre `#09111E` | 6,81:1 | AA |
| `#6D8599` sobre `#081220` (placeholder) | 4,89:1 | AA |

## 4. Tipografía

- **Familia:** Instrument Sans (SIL Open Font License), autoalojada y compilada por Vite; no hay
  CDN tipográfico en tiempo de ejecución. Pesos 400, 500, 600, 700 y 800.
- **Títulos:** peso 700–800, `letter-spacing: -0.025em`, altura de línea 1,05–1,1.
- **Texto:** 16 px base, 0,95–1,05 rem en interfaz, `line-height` 1,6.
- **Etiquetas:** mayúsculas de 0,68–0,72 rem con `letter-spacing` 0,1–0,18 em.
- **Datos:** cifras tabulares cuando existan tablas reales.

## 5. Espaciado, radios y elevación

- Escala en múltiplos de 4 px: 4, 8, 12, 16, 24, 32, 48, 64.
- Radios: 10 px (controles), 16 px (tarjetas), 999 px (botones y chips).
- Elevación: sombra suave `0 18px 40px rgba(0,0,0,.28)` en tarjetas y halo de color solo en
  botones y órbitas.
- Retícula: ancho máximo 1200 px, gutter 20–32 px según viewport.

## 6. Marca e iconografía

- Archivos: `public/assets/brand/aris-isotipo.svg`, `aris-logotipo.svg`, `aris-insignia.svg`;
  favicon en `public/favicon.svg`. Todos usan `currentColor`.
- El logotipo se vectorizó del arte entregado por el responsable (ADR-008).
- Iconografía lineal de 24×24 px, trazo 1,6–1,7 px, esquinas redondeadas; se dibuja en línea
  cuando hace falta un icono nuevo. No se mezclan packs externos.

## 7. Estructura visual implementada

1. **Portada pública** — cabecera con marca y acceso; héroe con titular, párrafo, dos acciones
   y composición orbital de marca; sección «Proyectos publicados» con estado vacío real;
   bloque «Qué conserva ARIS» con tres tarjetas (versiones, revisión, publicación).
2. **Acceso y registro** — tarjeta centrada sobre fondo orbital, sin cabecera ni pie.
3. **Mi cuenta** — armazón privado con barra lateral de marca, chip de rol, datos reales del
   perfil y aviso explícito de lo que aún no existe.
4. **Campos de contraseña** — componente `campo-contrasena` con botón «ojito» que alterna
   `type=password` / `type=text`, actualiza `aria-pressed` y la etiqueta accesible, y devuelve el
   foco al campo.
5. **Menú de accesibilidad** — botón flotante en el sitio público y en el área privada. Ofrece
   tamaño del texto (normal, grande, muy grande), contraste alto, movimiento reducido y enlaces
   subrayados. Se guarda en `localStorage` (`aris.accesibilidad`), no se envía al servidor y se
   puede restablecer. El panel cierra con `Escape` o clic fuera y anuncia los cambios con una
   región `aria-live`.

No se muestran métricas, usuarios ni proyectos de demostración. Cuando una sección no tiene
datos, se explica por qué.

## 8. Tonos de voz

- Claro, respetuoso, sin infantilizar ni dramatizar.
- Sin superlativos ni promesas («infalible», «revolucionario», «IA que califica»).
- Mensajes de error que explican **qué pasó y qué hacer**, sin culpar al usuario.
- Español neutro institucional; fechas según la zona horaria configurada.

## 9. Lo que queda prohibido

- Copiar la estética de Moodle, Google Classroom, Notion o cualquier producto reconocible.
- Ilustraciones genéricas de «estudiantes felices», gráficos de stock o emojis decorativos.
- Contadores, testimonios o métricas sin datos reales detrás.
- Animar fondos que tapen contenido o bloqueen la lectura; ignorar `prefers-reduced-motion`.
- Introducir un segundo logotipo, paleta o familia tipográfica.

## 10. Validación ejecutada (2026-11-17)

- [x] Contraste AA de los pares token/fondo documentados en §3.
- [x] Capturas reales a 1440×900 y 390×844 de portada, acceso, registro y «Mi cuenta».
- [x] `prefers-reduced-motion: reduce` detiene las órbitas (`animation-name: none` verificado).
- [x] Navegación con enlaces reales; sin botones que simulen operaciones inexistentes.
- [x] Menú de accesibilidad probado en navegador: tamaño (16→18→20 px), contraste alto, órbitas
      detenidas (`animation-name: none`), enlaces subrayados, persistencia entre páginas,
      restablecimiento, cierre con `Escape` y retorno de foco al botón.
- [x] Botón de visibilidad de contraseña probado: alterna el tipo del campo, conserva el valor,
      actualiza `aria-pressed` y la etiqueta, e intercambia el icono.
- [ ] Revisión con zoom al 200 % y lector de pantalla en todas las pantallas (pendiente F6).
- [ ] Prueba en 360, 768 y 1024 px con contenido real de proyectos (pendiente F3/F6).
