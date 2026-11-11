# Agentes y herramientas disponibles

> Inventario **verificado** en la sesión del 11 de noviembre de 2026. No es una lista de
> deseos: si no se ejecutó `command -v` y una comprobación de versión, figura como ausente.
> Se actualiza cuando el entorno cambie.

## 1. Inventario

| Agente / herramienta | Disponible | Acceso verificado | Misión asignada | Restricción |
|---|---|---|---|---|
| OpenCode (este agente) | Sí | Sesión activa | Orquestación, código, documentación, pruebas | Sin push, despliegue ni acciones destructivas sin autorización |
| Codex CLI 0.166.0 | Sí | `codex --version` | Revisión focalizada de backend y pruebas si se delega | No decide políticas ni toca bases ajenas |
| Hermes Agent v0.22.5 | Sí | `hermes --version` | Asesoría de modelo de datos, transacciones y seguridad de almacenamiento | Propuestas y parches en este repositorio únicamente |
| Antigravity CLI | **No** | `command -v antigravity` vacío | Dirección visual y handoff de diseño (cuando exista) | Sin él, el diseño lo asume OpenCode con aprobación del usuario |
| Gemini CLI | **No** | `command -v gemini` vacío | Revisión documental transversal | No se simula su intervención |
| Claude CLI | **No** | `command -v claude` vacío | — | — |
| Ollama / modelos locales | **No** | `command -v ollama` vacío | — | No se instala por defecto |
| Playwright | **No operativo** | `npx playwright` falló (registro npm sin respuesta) | QA real de navegador en F6 | Se reportará como NO EJECUTADO mientras no funcione |
| Node / npm | Sí | 26.13.0 / 11.20.0 | Build de Vite y scripts locales | npm registry no verificado en esta sesión |
| MySQL / MariaDB | Cliente sí, servidor **no activo** | Sin socket en 3306 | Base sintética de desarrollo y pruebas | Requiere autorización para arrancar el servicio |

## 2. Reglas de delegación

1. Un agente principal persistente (OpenCode). Subagentes solo con tarea delimitada y
   beneficio claro.
2. Ningún agente escribe simultáneamente en `database/migrations`, `routes/web.php` o el
   mismo componente.
3. Lecturas e investigaciones pueden ir en paralelo; escrituras se separan por archivo o
   directorio con contrato explícito.
4. No se envían archivos privados, credenciales, bases ni datos de estudiantes a herramientas
   externas.
5. Si un agente no está disponible, se continúa con OpenCode: **la ausencia de un agente no
   detiene una fase**.

## 3. Contrato mínimo para delegar

```text
Misión: <ID y objetivo verificable>
Proyecto: <ruta absoluta de este repositorio>
Contexto mínimo: <archivos autorizados>
Alcance de escritura: <rutas exactas o SOLO LECTURA>
Fuera de alcance: otros proyectos, bases existentes, secretos, push, despliegue
Criterios de aceptación: <pruebas y artefactos>
Entregables: archivos, resumen, pruebas y bloqueos
Prohibido atribuir finalización sin pruebas verificables.
```

## 4. Pendiente de explorar (cuando esté disponible)

- Antigravity: capacidades reales, skills, conexión Figma/Canvas y modo no interactivo.
- Playwright: instalar como dependencia de desarrollo del proyecto y comprobar que el
  navegador arranca en este entorno.
- Hermes: confirmar si admite modo no interactivo con ámbito de escritura restringido.
