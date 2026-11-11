# Estado de ARIS 2.0 — bitácora acumulativa

> Documento único de transferencia entre sesiones. Se actualiza al cerrar cada sesión.
> Formato compacto: no generar informes paralelos.

**Última actualización:** 11 de noviembre de 2026
**Fase en curso:** F0 cerrada · F1 en preparación
**Repositorio:** `ARIS-V2` (producto: ARIS 2.0)

---

## 1. Entorno verificado (F0)

Inventario real de la sesión de creación. Nada de esta tabla es supuesto.

| Elemento | Resultado verificado |
|---|---|
| Sistema operativo | CachyOS Linux (Arch-based), kernel de escritorio |
| Directorio del proyecto | `/home/luis/Proyectos/github/ARIS-V2` |
| Aislamiento | Carpeta nueva, fuera de cualquier otro repositorio; no es enlace simbólico |
| Git | 2.56.0 · rama `main` · remoto `origin` público: `github.com/luis06-op/ARIS-V2` |
| PHP | 8.2.12 (NTS) — `/home/luis/.local/bin/php` → `/opt/lampp/bin/php` |
| php.ini cargado | `/opt/lampp/etc/php.ini` |
| Extensiones PHP | `pdo_mysql`, `mysqli`, `mbstring`, `openssl`, `fileinfo`, `intl`, `zip`, `gd`, `curl`, `sqlite3` presentes |
| Composer | **No instalado** |
| Node / npm | 26.13.0 / 11.20.0 |
| MySQL / MariaDB | Cliente `mysql` y binarios MariaDB de XAMPP presentes; **ningún servidor escuchando en 3306** |
| XAMPP | Instalado en `/opt/lampp`; MySQL no iniciado en esta sesión |
| Red saliente | `getcomposer.org` y `repo.packagist.org` responden 200 |
| Registro npm | `npx` agotó tiempo contra el registro → **no verificado** |
| Playwright | No disponible como dependencia local; `npx playwright` falló |
| Agentes CLI | `codex` 0.166.0 y `hermes` v0.22.5 presentes; `antigravity`, `gemini`, `claude`, `ollama` ausentes |
| Disco / memoria | 380 GB libres en `/home` · 13 GB RAM |

Detalle de agentes en `docs/AGENTES-DISPONIBLES.md`.

---

## 2. Decisiones tomadas en esta sesión

| ID | Decisión | Documento |
|---|---|---|
| ADR-001 | Monolito modular Laravel + Inertia/React; sin microservicios ni API duplicada | `docs/decisions/ADR-001-arquitectura-monolito-modular.md` |
| ADR-002 | Stack objetivo Laravel 13/PHP 8.3; **bloqueo**: solo hay PHP 8.2.12 y no hay Composer | `docs/decisions/ADR-002-stack-y-version-php.md` |
| ADR-003 | Evidencias en disco privado local, servidas por controlador autorizado | `docs/decisions/ADR-003-almacenamiento-privado.md` |
| ADR-004 | Versionado inmutable con `UNIQUE (padre_id, version_number)` y bloqueo transaccional | `docs/decisions/ADR-004-versionado-inmutable.md` |
| ADR-005 | Directorio `ARIS-V2`, producto «ARIS 2.0», repositorio público en `origin` | `docs/decisions/ADR-005-nombre-y-ubicacion.md` |
| ADR-006 | Titularidad del SENA; sin licencia permisiva mientras no se autorice por escrito | `docs/decisions/ADR-006-titularidad-sena.md` |

## 3. Implementado

- Estructura documental completa de F0: producto, arquitectura, diseño, decisiones,
  operación y despliegue.
- Modelo de dominio, ERD del núcleo académico (21 tablas propias + tablas del framework),
  diccionario de datos e invariantes con pruebas previstas.
- Catálogo `PI-001…PI-011` de decisiones institucionales pendientes (incluye titularidad y
  publicación del código, `PI-011`).
- `AVISO-DE-DERECHOS.md`: titularidad del SENA, sin licencia de uso concedida.
- Brief de diseño y checklist de QA visual preparados (no ejecutados: Antigravity y Playwright
  no están disponibles).
- `.gitignore`, `.gitattributes`, `.editorconfig`, `.env.example` sin secretos.
- Script local `scripts/verificar-enlaces-docs.mjs` (Node sin dependencias) para comprobar
  que los enlaces y las rutas `.md` citadas resuelven.

## 4. Pruebas ejecutadas

| Prueba | Comando | Resultado |
|---|---|---|
| Enlaces y rutas `.md` de la documentación | `node scripts/verificar-enlaces-docs.mjs` | **APROBADA** — 31 archivos, 47 referencias, 0 rotas |
| Aislamiento del repositorio | `git status --short`, `git rev-parse --show-toplevel` | **APROBADA** — único repositorio, solo archivos propios |
| `.env` no versionable | `git check-ignore -v .env` | **APROBADA** — coincide con `.gitignore:10` |
| Evidencias fuera del control de versiones | `git check-ignore -v storage/app/private/x.pdf` | **APROBADA** — coincide con `.gitignore:26` |
| Instalación de esquema (F1) | `php artisan migrate` | **NO EJECUTADA** — sin Composer ni servidor MySQL |
| Renderizado del diagrama Mermaid | `mmdc` / GitHub | **NO EJECUTADA** — herramienta no disponible |
| QA visual | Playwright / navegador | **NO EJECUTADA** — sin interfaz y sin Playwright |

## 5. Bloqueos activos

| # | Bloqueo | Impacto | Desbloqueo requerido |
|---|---|---|---|
| B1 | Composer ausente | No se puede instalar el starter de Laravel ni generar `composer.lock` | Autorizar instalación local de Composer en `~/.local/bin` (sin `sudo`) o instalarlo el usuario |
| B2 | PHP 8.2.12 < 8.3 | Laravel 13 no es instalable | Decidir: usar Laravel 12 con PHP 8.2 **o** actualizar PHP a ≥8.3 (cambio de sistema, requiere autorización) |
| B3 | Sin servidor MySQL/MariaDB activo | No se pueden ejecutar migraciones ni pruebas de integridad | Autorizar el arranque del MySQL de XAMPP o crear una instancia sintética dedicada |
| B4 | Registro npm no verificado | No se puede confirmar la instalación de dependencias de frontend | Reintentar con red estable; no bloquea F1 (backend) |

Ninguno de estos bloqueos autoriza a apuntar a una base existente, a desactivar controles ni
a tocar otros proyectos.

## 6. Decisiones que requieren aprobación humana

- **Stack (B2):** Laravel 12 hoy vs. actualizar PHP y usar Laravel 13.
- **PI-001…PI-010** (`docs/product/DECISIONES-INSTITUCIONALES.md`): ninguna se implementa con
  valores por defecto inventados.
- **Publicación del repositorio:** público en `github.com/luis06-op/ARIS-V2` con autorización
  del usuario. Titularidad declarada del **SENA** (`AVISO-DE-DERECHOS.md`, ADR-006); la
  confirmación institucional de la publicación pública es `PI-011`. **Sin licencia de uso.**

## 7. Siguiente objetivo concreto

**Desbloquear F1 en una sola tanda:** instalar Composer local, crear el proyecto Laravel con
la versión aprobada, definir la base sintética `aris2_dev`, escribir la primera migración
(`institutions`, `users` y membresías) y ejecutar `php artisan migrate` + test de integridad
en esa base. Nada de interfaz todavía.
