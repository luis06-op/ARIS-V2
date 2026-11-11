# ADR-001 — Monolito modular con Laravel + Inertia + React

- **Estado:** aceptada
- **Fecha:** 2026-11-11
- **Contexto de fase:** F0–F1

## Contexto

ARIS 2.0 es una aplicación institucional con pocos usuarios concurrentes frente a un LMS
masivo, desplegada en hosting compartido o VPS modesto, operada por personal con poca
especialización técnica. El producto necesita autenticación, permisos por recurso,
transacciones, almacenamiento privado y una interfaz rica sin duplicar lógica.

## Decisión

Una sola aplicación **Laravel** con **Inertia.js** y **React + TypeScript**:

- Laravel resuelve routing, sesiones, CSRF, validación, autorización, migraciones y colas.
- Inertia entrega páginas React desde controladores, sin API REST paralela.
- Los assets se compilan con Vite; **Node no se ejecuta en producción**.
- El backend se organiza por capas donde aportan valor: `Http`, `Policies`, `Actions`,
  `Domain`, `Models`, `Services`.
- Una instalación por institución en el MVP; `institution_id` identifica contexto, **no**
  declara multitenencia.

## Alternativas descartadas

| Alternativa | Motivo del descarte |
|---|---|
| SPA con API REST separada | Duplica contratos, autenticación y despliegue sin beneficio en este tamaño |
| Microservicios / Kubernetes | Complejidad operativa y costo desproporcionados |
| Framework JS full-stack (Next.js) | Requiere Node en producción y complica el hosting educativo |
| CMS o LMS existente | No modela versiones inmutables ni trazabilidad de evidencias |

## Consecuencias

- (+) Un solo despliegue, un solo lenguaje de servidor, plantillas de autorización nativas.
- (+) El equipo puede auditar todo el código en un repositorio.
- (−) Acoplamiento al framework; se mitiga manteniendo el dominio en clases propias.
- (−) Requiere compilar assets antes de desplegar.

## Reglas derivadas

1. No se crea una API pública en el MVP.
2. El dominio no depende de HTTP ni de JSX.
3. Toda autorización se evalúa en el servidor, incluso si el cliente oculta el control.
