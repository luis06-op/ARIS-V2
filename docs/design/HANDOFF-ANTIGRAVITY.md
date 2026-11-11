# Handoff para Antigravity

> **Estado: preparado, no entregado.** Antigravity no está instalado en el entorno verificado
> (`command -v antigravity` vacío). Este brief queda listo para cuando exista acceso real; no
> se simula una delegación.

## 1. Misión

Producir la dirección visual y el sistema de diseño de ARIS 2.0, y entregar especificaciones
implementables para que OpenCode las integre en F6.

## 2. Contexto mínimo autorizado

| Recurso | Ruta |
|---|---|
| Visión y alcance | `docs/product/VISION-Y-ALCANCE.md` |
| Roles y recorridos | `docs/product/ROLES-Y-RECORRIDOS.md` |
| Inventario de pantallas | `docs/design/INVENTARIO-PANTALLAS.md` |
| Propuesta visual vigente | `docs/design/DIRECCION-VISUAL.md` |
| Componentes núcleo | `docs/PLAN-DE-FASES.md` (F6) |

No se autoriza acceso a base de datos, `.env`, `storage/app/private/` ni credenciales.

## 3. Alcance de escritura

- Permitido: `docs/design/`, `resources/css/`, `resources/js/components/`,
  `public/assets/branding/`, `public/assets/icons/`, `public/assets/illustrations/`.
- **Solo lectura:** todo lo demás.
- **Prohibido:** `database/migrations`, `app/Policies`, `app/Actions`, `routes/`, `.env`,
  cualquier proyecto fuera de este repositorio.

## 4. Entregables esperados

1. **2–3 direcciones visuales** pertinentes, originales, sin copiar productos reconocibles.
2. **Tokens** en CSS y JSON: color, tipografía, espaciado, radios, bordes, elevación, motion.
3. **Sistema de componentes** con estados: reposo, hover, foco, activo, deshabilitado, error,
   carga, vacío.
4. **Landing** con narrativa visual y fondo animado aislado (SVG/CSS/Canvas ligero).
5. **Layouts por rol**: aprendiz, instructor, evaluador, administrador.
6. **Componentes académicos**: `VersionTimeline`, `ReviewPanel`, `FileDropzone`,
   `PublicationStatus`.
7. **Iconografía propia** en SVG, un solo estilo.
8. **Especificaciones responsivas**: 360, 390, 768, 1024, 1440 px.
9. **Riesgos de accesibilidad** identificados con propuesta de mitigación.

## 5. Criterios de aceptación

- Tokens aplicables sin CSS ad hoc por página.
- Contraste AA verificado o declarado como pendiente con medición.
- `prefers-reduced-motion` contemplado y documentado.
- Componentes tipados (props) y sin lógica de autorización.
- Ninguna pantalla muestra datos, cifras o testimonios inventados.
- Handoff implementable: nombres de archivo, tokens y estructura de props.

## 6. Límites

- No decide reglas académicas ni de privacidad.
- No modifica esquema, permisos ni configuración.
- No instala dependencias globales ni servicios de pago.
- No entrega solo imágenes: debe entregar tokens y especificaciones.

## 7. Regreso a OpenCode

Al recibir el handoff, OpenCode verificará: compatibilidad con el stack, accesibilidad,
rendimiento del fondo animado, consistencia con los datos reales disponibles y ausencia de
contenido ficticio. Los cambios de diseño se integran con pruebas visuales cuando el navegador
automatizado esté operativo.
