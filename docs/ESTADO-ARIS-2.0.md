# Estado de ARIS 2.0 — bitácora acumulativa

> Documento único de transferencia entre sesiones. Se actualiza al cerrar cada sesión.
> Formato compacto: no generar informes paralelos.

**Última actualización:** 17 de noviembre de 2026
**Fase en curso:** F1 parcial (núcleo migrado en SQLite; falta MySQL) · F2 iniciada (acceso,
registro y sesión) · F6 adelantada (marca, sistema visual y pantallas base)
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
- Preparadas migraciones para `institutions`, `users`, `roles`, `institution_memberships`,
  `membership_roles`, `projects` y `project_memberships`, con claves únicas, índices y FKs.
- Añadidos modelos y relaciones Eloquent para institución, usuario, roles, membresías y
  proyectos. No se añadieron catálogos ni usuarios de producción.
- `users` incorpora estado, idioma y último inicio de sesión. El diccionario indica que la
  coherencia entre fechas de membresía debe validarse en la capa de dominio.

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
| Migraciones de ARIS en MySQL | `php8.4 artisan migrate` sobre `aris_v2_db` | **NO EJECUTADA** — sin servidor MySQL activo |
| Integridad del núcleo en SQLite de pruebas | `php8.4 artisan test` | **APROBADA** — 4 pruebas, 6 aserciones; PHPUnit usa `:memory:` |
| Sintaxis PHP | `find app database routes config tests -name '*.php' -print0 \| xargs -0 -r -n1 php8.4 -l` | **APROBADA** — todos los archivos revisados sin errores |
| Enlaces de documentación | `node scripts/verificar-enlaces-docs.mjs` | **APROBADA** — 33 archivos, 57 referencias, 0 rotas |
| Enlaces de documentación (16-11-2026) | `node scripts/verificar-enlaces-docs.mjs` | **APROBADA** — 34 archivos, 57 referencias, 0 rotas |
| Estado de migraciones de aplicación (16-11-2026) | `php8.4 artisan migrate:status` | **NO VALIDADO** — la conexión local SQLite no tiene tabla `migrations` |
| Disponibilidad MySQL (16-11-2026) | `mysqladmin --protocol=tcp -h 127.0.0.1 -P 3306 ping` | **NO DISPONIBLE** — no hay servidor escuchando |
| Renderizado del diagrama Mermaid | `mmdc` / GitHub | **NO EJECUTADA** — herramienta no disponible |
| QA visual | Playwright / navegador | **APROBADA (17-11-2026)** — capturas reales de portada, acceso, registro y «Mi cuenta» a 1440×900 y 390×844 |
| Suite completa (17-11-2026) | `php8.4 artisan test` | **APROBADA** — 19 pruebas, 77 aserciones |
| Menú de accesibilidad (17-11-2026) | Playwright: abrir, aplicar, persistir, restablecer, `Escape` | **APROBADA** — 4 grupos, 9 opciones, foco devuelto al botón |
| Botón de visibilidad de contraseña (17-11-2026) | Playwright: clic en `[data-password-toggle]` | **APROBADA** — `password` → `text`, valor conservado, `aria-pressed` y etiqueta actualizados |
| Acceso con cuenta local (17-11-2026) | Playwright: formulario `/ingresar` | **APROBADA** — redirección a `/cuenta` y datos reales del perfil |
| Sintaxis PHP (17-11-2026) | `find app database routes config tests -name '*.php' -print0 \| xargs -0 -r -n1 php8.4 -l` | **APROBADA** — sin errores |
| Estilo PHP (17-11-2026) | `php8.4 vendor/bin/pint --test` | **APROBADA** — sin desviaciones |
| Compilación de assets (17-11-2026) | `npm run build` | **APROBADA** — CSS 56,5 kB · JS 0,02 kB · fuentes Instrument Sans autohospedadas |
| Migraciones del núcleo en SQLite de desarrollo (17-11-2026) | `php8.4 artisan migrate` | **APROBADA** — 9 migraciones aplicadas desde cero |
| Contraste AA de tokens (17-11-2026) | cálculo de luminancia relativa WCAG 2.1 | **APROBADA** — 7 combinaciones ≥ 4,5:1 |
| Movimiento reducido (17-11-2026) | Playwright `reducedMotion: reduce` + `getComputedStyle(.orb)` | **APROBADA** — `animation-name: none` |
| Enlaces de documentación (17-11-2026) | `node scripts/verificar-enlaces-docs.mjs` | **APROBADA** — 35 archivos, 58 enlaces, 0 rotos |

## 5. Bloqueos activos

| # | Bloqueo | Impacto | Desbloqueo requerido |
|---|---|---|---|
| B1 | ~~Composer ausente~~ | **Resuelto**: Composer 2.11.2 local con SHA-384 verificado | — |
| B2 | ~~PHP 8.2.12 < 8.3~~ | **Resuelto**: PHP 8.4.23 local (`php8.4`), sin tocar el sistema | — |
| B3 | Sin servidor MySQL/MariaDB activo y conexión única sin confirmar | No se puede validar el esquema con el motor objetivo ni cambiar de forma segura la conexión de aplicación | Confirmar motor, nombre y que la base sea sintética y dedicada; credenciales solo en `.env` local |
| B4 | ~~Registro npm no verificado~~ | **Resuelto (17-11-2026)**: `npm ping` responde y `npm install` + `npm run build` funcionan | — |

Ninguno de estos bloqueos autoriza a apuntar a una base existente, a desactivar controles ni
a tocar otros proyectos. La conexión actual del `.env` local es SQLite; PHPUnit usa SQLite
`:memory:` de forma aislada para pruebas. El documento de arquitectura entregado por el usuario
define `aris_v2_db`; `.env.example` e instalación local ya usan ese nombre. El `.env` local aún
usa SQLite y no se ha cambiado porque el endpoint y credenciales MySQL no están disponibles. No
se ha ejecutado una migración de ARIS en la base local.

## 6. Decisiones que requieren aprobación humana

- **Stack:** decidido — Laravel 13.42.0 + PHP 8.4.23 local (ADR-002).
- **PI-001…PI-011** (`docs/product/DECISIONES-INSTITUCIONALES.md`): ninguna se implementa con
  valores por defecto inventados.
- **Publicación del repositorio:** público en `github.com/luis06-op/ARIS-V2` con autorización
  del usuario. Titularidad declarada del **SENA** (`AVISO-DE-DERECHOS.md`, ADR-006); la
  confirmación institucional de la publicación pública es `PI-011`. **Sin licencia de uso.**

## 7. Siguiente objetivo concreto

**Cerrar F1 en el motor objetivo:** iniciar (con autorización) o conectar una instancia MySQL
sintética `aris_v2_db`, aplicar la cadena completa de migraciones desde cero y repetir la suite
de integridad. Después, completar el esquema F1 restante (actividades, asignaciones, entregas,
documentos, revisión, publicación y auditoría).

---

## 8. Sesión 2026-11-17 — Identidad visual, portada y acceso

**Fase:** F6 adelantada por petición expresa del responsable; F2 iniciada en su parte de sesión.

**Implementado:**

- **Marca vectorial** a partir del logotipo entregado: `public/assets/brand/aris-isotipo.svg`,
  `aris-logotipo.svg`, `aris-insignia.svg` y `public/favicon.svg`. Vectorizados con contornos
  propios y `currentColor` (ADR-008).
- **Sistema visual** en `resources/css/app.css`: tokens de color noche/verde/cian, tipografía
  Instrument Sans autohospedada, botones, tarjetas, campos, chips, avisos, enlaces, navegación
  lateral y órbitas animadas con `prefers-reduced-motion`.
- **Vistas:** `layouts/publico`, `layouts/app`, componentes `marca` y `orbs`; portada
  `publico/inicio` con héroe, sección «Proyectos publicados» en estado vacío real y bloque «Qué
  conserva ARIS»; `auth/ingresar`; `auth/registro`; `cuenta`.
- **Rutas y controladores:** `/` (`InicioController`), `/ingresar`, `/registro`, `/salir`,
  `/cuenta`; `SessionController`, `RegistrationController`, `AccountController`.
- **Formularios:** `LoginRequest` con límite de 5 intentos por correo+IP y bloqueo de cuentas no
  activas; `RegistrationRequest` con correo normalizado, unicidad y contraseña de 8+ con letras y
  números.
- **Mensajes en español:** `lang/es/validation.php` para las reglas en uso.
- **Redirecciones:** invitado → `/ingresar`; autenticado → `/cuenta` (`bootstrap/app.php`).
- **Campos de contraseña:** componente `components/campo-contrasena` con botón «ojito» que muestra
  u oculta el valor sin perderlo; `resources/js/password-visibility.js` mantiene `aria-pressed`,
  la etiqueta accesible y el icono sincronizados.
- **Menú de accesibilidad:** componente `components/accesibilidad` disponible en el sitio público y
  en el área privada, con tamaño del texto, contraste alto, movimiento reducido y enlaces
  subrayados. Las preferencias viven en `localStorage` (`aris.accesibilidad`), nunca en el
  servidor; `resources/js/accessibility.js` gestiona panel, foco, `Escape`, clic fuera y región
  `aria-live`.
- **`PI-002` aprobada parcialmente:** autoregistro abierto autorizado en esta sesión; la
  asignación de roles y la autoridad de evaluación siguen pendientes.
- Se eliminó `resources/views/welcome.blade.php` (vista de bienvenida del starter, sin uso tras
  la nueva portada).

**Decisiones:** ADR-008 (identidad visual) sustituye la propuesta de papel cálido;
`docs/design/DIRECCION-VISUAL.md` reescrita con la dirección aprobada.

**Datos:** no se crearon usuarios, proyectos ni métricas de demostración en el código ni en las
pruebas. El responsable del proyecto autorizó una cuenta local de revisión
(`revision@aris.local`) en la base SQLite de desarrollo para comprobar el acceso; su contraseña
no se versiona y la cuenta no tiene membresía ni rol institucional.

**Bloqueos:** B3 (MySQL) sigue abierto. La verificación de correo y la recuperación de contraseña
no se implementaron porque no hay servicio de envío configurado.

**Riesgo asumido:** el registro abierto crea cuentas sin verificar correo; no otorgan privilegios
porque no existe membresía institucional ni rol asignado.

**Siguiente objetivo:** F1 en MySQL sintética; luego completar el esquema antes de continuar F2
(roles, policies y recuperación de contraseña).
