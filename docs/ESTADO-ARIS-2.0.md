# Estado de ARIS 2.0 — bitácora acumulativa

> Documento único de transferencia entre sesiones. Se actualiza al cerrar cada sesión.
> Formato compacto: no generar informes paralelos.

**Última actualización:** 11 de noviembre de 2026
**Fase en curso:** F0 cerrada · F1 iniciada (esqueleto Laravel instalado; migraciones pendientes de MySQL)
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
| PHP del sistema | 8.2.12 (NTS) — `/home/luis/.local/bin/php` → `/opt/lampp/bin/php` (no se toca) |
| PHP de ARIS 2.0 | **8.4.23** estático (`static-php-cli` bulk) en `~/.local/php/8.4.23/`, usable como `php8.4` |
| php.ini de ARIS | `~/.local/php/8.4.23/php.ini`, cargado con `PHPRC` por el envoltorio `~/.local/bin/php8.4` |
| Extensiones verificadas | `intl`, `pdo_mysql`, `mysqli`, `zip`, `gd`, `fileinfo`, `openssl`, `mbstring`, `sodium`, `curl`, `sqlite3` |
| Composer | **2.11.2** en `~/.local/bin/composer` (SHA-384 verificado), ejecutado con `php8.4` |
| Laravel | **13.42.0** instalado en el repositorio (`vendor/` presente, no versionado) |
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
| ADR-002 | **Laravel 13.42.0 con PHP 8.4.23 local** (`php8.4`), sin tocar el PHP del sistema ni XAMPP | `docs/decisions/ADR-002-stack-y-version-php.md` |
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
- **Esqueleto de aplicación Laravel 13.42.0** integrado en la raíz (`artisan`, `app/`,
  `bootstrap/`, `config/`, `database/`, `public/`, `resources/`, `routes/`, `tests/`,
  `vendor/`), con `APP_KEY` generada en `.env` local (no versionado).
- El frontend del esqueleto es Blade con Vite y Tailwind 4. **Inertia + React + TypeScript se
  integran en F2**, cuando exista la primera pantalla real; `package.json` aún no los incluye.

## 4. Pruebas ejecutadas

| Prueba | Comando | Resultado |
|---|---|---|
| Enlaces y rutas `.md` de la documentación | `node scripts/verificar-enlaces-docs.mjs` | **APROBADA** — 33 archivos, 57 referencias, 0 rotas |
| Aislamiento del repositorio | `git status --short`, `git rev-parse --show-toplevel` | **APROBADA** — único repositorio, solo archivos propios |
| `.env` no versionable | `git check-ignore -v .env` | **APROBADA** — coincide con `.gitignore` |
| Evidencias fuera del control de versiones | `git check-ignore -v storage/app/private/x.pdf` | **APROBADA** — coincide con `.gitignore` |
| Integridad del instalador de Composer | comparación SHA-384 con `composer.github.io/installer.sig` | **APROBADA** — hashes idénticos |
| Versión de Laravel | `php8.4 artisan --version` | **APROBADA** — Laravel Framework 13.42.0 |
| Suite base del esqueleto | `php8.4 artisan test` | **APROBADA** — 2 pruebas, 2 aserciones, 299 ms |
| Migraciones del esqueleto | `php8.4 artisan migrate` (SQLite de laboratorio) | **APROBADA** — tablas `users`, `cache`, `jobs` |
| Migraciones de ARIS en MySQL | `php8.4 artisan migrate` sobre `aris2_dev` | **NO EJECUTADA** — sin servidor MySQL activo |
| Renderizado del diagrama Mermaid | `mmdc` / GitHub | **NO EJECUTADA** — herramienta no disponible |
| QA visual | Playwright / navegador | **NO EJECUTADA** — sin interfaz y sin Playwright |

## 5. Bloqueos activos

| # | Bloqueo | Impacto | Desbloqueo requerido |
|---|---|---|---|
| B1 | ~~Composer ausente~~ | **Resuelto**: Composer 2.11.2 local con SHA-384 verificado | — |
| B2 | ~~PHP 8.2.12 < 8.3~~ | **Resuelto**: PHP 8.4.23 local (`php8.4`), sin tocar el sistema | — |
| B3 | Sin servidor MySQL/MariaDB activo | No se pueden ejecutar las migraciones de ARIS ni las pruebas de integridad | Autorizar el arranque del MySQL de XAMPP o crear una instancia sintética dedicada `aris2_dev`/`aris2_test` |
| B4 | Registro npm no verificado | No se pueden instalar dependencias de frontend ni integrar Inertia/React | Reintentar con red estable; no bloquea las migraciones (backend) |

Ninguno de estos bloqueos autoriza a apuntar a una base existente, a desactivar controles ni
a tocar otros proyectos. La base local actual es **SQLite de laboratorio** en `database/database.sqlite`
(ignorada por Git): sirve para arrancar el esqueleto, **no** para validar el esquema de ARIS.

## 6. Decisiones que requieren aprobación humana

- **Stack:** decidido — Laravel 13.42.0 + PHP 8.4.23 local (ADR-002).
- **PI-001…PI-011** (`docs/product/DECISIONES-INSTITUCIONALES.md`): ninguna se implementa con
  valores por defecto inventados.
- **Publicación del repositorio:** público en `github.com/luis06-op/ARIS-V2` con autorización
  del usuario. Titularidad declarada del **SENA** (`AVISO-DE-DERECHOS.md`, ADR-006); la
  confirmación institucional de la publicación pública es `PI-011`. **Sin licencia de uso.**

## 7. Siguiente objetivo concreto

**Cerrar la puerta F1 sobre MySQL:** con la instancia sintética `aris2_dev` autorizada y
activa, escribir las migraciones del núcleo (`institutions`, `users`, `roles`, membresías,
`projects`) y sus modelos, ejecutar `php8.4 artisan migrate` desde cero y añadir el primer test
de integridad referencial (`tests/Feature/Database/MigrationIntegrityTest.php`). Nada de
interfaz todavía.
