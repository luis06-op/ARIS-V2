# AGENTS.md — Instrucciones para agentes que trabajen en ARIS-V2

Este archivo es el contrato operativo del repositorio. El documento rector del producto es
`docs/PLAN-DE-FASES.md` junto con `docs/product/` y `docs/architecture/`. Si algo aquí
contradice una política institucional aprobada, gana la política aprobada.

## 1. Contexto del proyecto

- **Producto:** ARIS 2.0 — gestión documental y repositorio digital para trazabilidad
  pedagógica.
- **Repositorio:** `ARIS-V2` (nombre corto del directorio; el producto se llama ARIS 2.0).
- **Raíz del proyecto (`PROJECT_ROOT`):** la ruta absoluta del clon actual. **Nunca**
  codificar rutas absolutas en código, tests, configuración o scripts.
- **Aislamiento:** este repositorio es independiente. No comparte base de datos, archivos,
  credenciales ni configuración con proyectos anteriores del equipo. Esos proyectos son,
  como máximo, de solo lectura y están **fuera de alcance**.

## 2. Reglas de autonomía

| Acción | Comportamiento |
|---|---|
| Leer/crear archivos dentro de `PROJECT_ROOT` | Autónomo |
| Instalar dependencias locales del proyecto (composer/npm) sin privilegios | Autónomo, si es reproducible y se revisan los scripts |
| Crear una base **sintética nueva** y ejecutar migraciones sobre ella | Autónomo tras verificar servidor, nombre y propiedad |
| `git commit` local | Autónomo solo si el usuario lo pidió en la sesión |
| `git push`, remotos, PR, despliegue, correos reales, secretos | **Detener y pedir autorización** |
| `sudo`, servicios globales, firewall, cambios del sistema | **Detener y pedir autorización** |
| Tocar bases, respaldos o archivos de otros proyectos | **Prohibido** |
| Resolver una política institucional, datos de menores o publicación pública | **Detener solo esa acción** |

Un bloqueo no termina la sesión: se documenta en `docs/ESTADO-ARIS-2.0.md` y se continúa
con la tarea segura siguiente.

## 3. Organización del código (cuando exista)

- `app/Models` → relaciones Eloquent únicamente.
- `app/Http/Controllers` → HTTP; `app/Http/Requests` → validación; `app/Policies` →
  autorización; `app/Actions` → casos de uso transaccionales; `app/Domain` → invariantes
  complejos. Una responsabilidad por archivo.
- `database/migrations` → **única fuente editable del esquema**. Nada de `schema.sql`
  paralelo; los snapshots se generan y se etiquetan como derivados.
- `resources/js` → React + TypeScript + Inertia; componentes tipados, sin lógica de
  autorización en el cliente (el cliente solo oculta; el servidor decide).
- `storage/app/private` → evidencias, cuarentena y exportaciones. **Nunca** en `public/`.
- `tests/` → evidencia reproducible. Una función no está lista sin prueba positiva y
  negativa.

## 4. Convenciones

- **Idioma:** documentación, mensajes de interfaz, commits y nombres de dominio en español;
  código y API de framework en inglés.
- **Base de datos:** `utf8mb4`, `utf8mb4_unicode_ci`, claves foráneas explícitas, `NOT NULL`
  justificado, índices por institución/proyecto/estado/fecha.
- **Borrado:** baja lógica o `RESTRICT`. Nunca `ON DELETE CASCADE` desde `users` hacia
  evidencia histórica.
- **Versiones:** inmutables, numeración entera positiva, `UNIQUE (padre_id, version_number)`,
  sin `MAX(version)+1` sin bloqueo.
- **Autorización:** siempre del lado del servidor y sobre la **versión exacta** del recurso.
  Ocultar un botón no es un control de seguridad.
- **Archivos:** nombre físico opaco generado por el servidor, MIME y extensión validados,
  SHA-256 registrado, descarga mediante policy.
- **XSS:** el contenido de estudiantes es dato no confiable; sin HTML enriquecido sin
  sanitizador explícito.

## 5. Verificación mínima antes de cada commit

```bash
# 1. Sintaxis PHP (cuando exista código)
find app database routes config tests -name '*.php' -print0 2>/dev/null \
  | xargs -0 -r -n1 php -l

# 2. Pruebas (cuando exista suite)
php artisan test

# 3. Tipos y estilo del frontend (cuando exista)
npm run typecheck && npm run lint

# 4. Nada sensible versionado
git status --short
```

`git status` no debe incluir `.env`, `config/*local*`, logs, `storage/app/private/*`,
`vendor/`, `node_modules/` ni archivos académicos de prueba.

## 6. Documentación obligatoria al cerrar una sesión

Actualizar `docs/ESTADO-ARIS-2.0.md` con: fase, qué se implementó, comandos de prueba y su
resultado real, bloqueos, decisiones pendientes y **siguiente objetivo concreto**. Sin
informes paralelos ni duplicados.

Toda decisión técnica relevante se registra como `ADR-###` en `docs/decisions/`. Toda
política institucional se registra como `PI-###` en
`docs/product/DECISIONES-INSTITUCIONALES.md` y **no se implementa con valores por defecto
inventados**.

## 7. Prohibiciones explícitas

- No crear datos de demostración, cifras, testimonios ni usuarios ficticios que puedan
  llegar a producción.
- No afirmar que una prueba, captura o auditoría se ejecutó si no se ejecutó.
- No introducir servicios de pago, telemetría, analítica de terceros ni CDN obligatorios
  sin aprobación.
- No activar video, IA, rúbricas numéricas ni publicación pública: son decisiones
  pendientes.
- No desactivar controles (CSRF, policies, validación, HTTPS) para "hacer que funcione".
