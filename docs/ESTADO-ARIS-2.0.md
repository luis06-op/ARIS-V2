# Estado de ARIS 2.0 — bitácora acumulativa

> Documento único de transferencia entre sesiones. Se actualiza al cerrar cada sesión.
> Formato compacto: no generar informes paralelos.

**Última actualización:** 17 de noviembre de 2026
**Fase en curso:** F1 con esquema completo en SQLite (B3 pendiente por instrucción) · F2 iniciada
(acceso, registro, sesión y recuperación de contraseña) · F3/F4 preparadas en dominio y servicio ·
F6 adelantada (marca, sistema visual y pantallas base)
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
- **Esquema F1 completo (17-11-2026):** 14 migraciones y modelos adicionales para actividades,
  guías y asignaciones, entregas y versiones, blobs, documentos y sus vínculos, revisiones,
  publicaciones y auditoría. `ADR-009` documenta por qué los `CHECK` se difieren a dominio.
- **Recuperación de contraseña (PI-008):** flujo estándar de Laravel con mailer `log`, respuesta
  uniforme para correos existentes y no existentes, límite de 5 solicitudes por minuto y
  contraseña de 8+ con letras y números (ADR implícito en la política aprobada).
- **Almacenamiento privado (F4):** `EvidenceValidator`, `EvidenceStorage` y el comando
  `aris:verificar-storage`. Validación por MIME real y firma, nombre opaco, cuarentena, SHA-256,
  descarga con `nosniff`/`no-store` y reconciliación sin borrados automáticos. El disco `local`
  queda con `serve => false`, sin `public/storage` ni URLs firmadas.
- **Reglas de dominio (PI-001):** `SubmissionLink` exige asignación vigente y versión publicada;
  `CreateSubmission` audita la excepción; `UpdateGuideVersion` conserva las versiones publicadas
  creando una nueva. Sin interfaz ni autorización HTTP: `PI-002` sigue pendiente.

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
| Suite completa (17-11-2026) | `php8.4 artisan test` | **APROBADA** — 54 pruebas, 197 aserciones |
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
| Esquema extendido en SQLite (17-11-2026) | `php8.4 artisan migrate --no-interaction` | **APROBADA** — 14 migraciones nuevas, 23 migraciones propias en total |
| Integridad del esquema extendido (17-11-2026) | `php8.4 artisan test` | **APROBADA** — 31 pruebas, 114 aserciones |
| Recuperación de contraseña (17-11-2026) | `php8.4 artisan test` + Playwright | **APROBADA** — 5 pruebas nuevas; enlace visible, envío con mailer `log` y ojitos en el formulario de restablecimiento |
| Reglas PI-001 (17-11-2026) | `php8.4 artisan test` | **APROBADA** — 7 pruebas nuevas, con rechazo de asignación ajena/revocada, guía no publicada, excepción auditada e inmutabilidad de versión publicada |
| Almacenamiento privado (17-11-2026) | `php8.4 artisan test` | **APROBADA** — 11 pruebas nuevas: MIME falso, formato no permitido, vacío, tamaño, traversal, descarga byte a byte, 404 y reconciliación con `dry-run` |

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
- **Aprobadas el 17-11-2026:** `PI-001` (entrega vinculada a versión concreta de guía, con
  excepción autorizada y auditada) y `PI-008` (recuperación por correo con token estándar y mailer
  `log`, sin envío real).
- **Pendientes:** `PI-002` (autoridad para asignar roles y responsables), `PI-003`, `PI-004`,
  `PI-005`, `PI-006`, `PI-007`, `PI-009`, `PI-010` y `PI-011`. Ninguna se implementa con valores
  por defecto inventados; sus flujos permanecen deshabilitados.
- **Publicación del repositorio:** público en `github.com/luis06-op/ARIS-V2` con autorización
  del usuario. Titularidad declarada del **SENA** (`AVISO-DE-DERECHOS.md`, ADR-006); la
  confirmación institucional de la publicación pública es `PI-011`. **Sin licencia de uso.**

## 7. Siguiente objetivo concreto

**B3 queda pendiente por instrucción expresa del responsable (17-11-2026).** No se conecta ni se
migra ninguna instancia MySQL, por lo que la puerta F1 no se declara cerrada. El avance a F2+ fue
autorizado en la misma sesión. El siguiente objetivo concreto es resolver `PI-002` (quién asigna
roles y responsables) para poder implementar `Policies`/`Gates` y retirar las excepciones de
dominio que hoy solo se ejecutan desde acciones internas. Después vendrá el CRUD de F3/F4 con la
interfaz real, siempre sin datos de demostración.

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

---

## 9. Sesión 2026-11-17 (continuación) — Esquema F1 completo, B3 pendiente

**Fase:** F1 completada en SQLite; puerta F1 **no cerrada** por instrucción de dejar B3 pendiente.
No se escribió código de F2+.

**Implementado:**

- 14 migraciones nuevas (`2026_11_17_000001` … `000014`) con el resto del esquema:
  `activities`, `activity_versions`, `activity_assignments`, `submissions`,
  `submission_versions`, `file_blobs`, `documents`, `document_versions`, `document_links`,
  `review_rounds`, `review_comments`, `publications`, `publication_events` y `audit_events`.
  Todas las claves foráneas son explícitas; las de evidencia histórica usan `RESTRICT`.
- 14 modelos Eloquent con relaciones, `fillable` y casts de JSON/fecha donde aplica.
- `tests/Feature/Database/ExtendedSchemaIntegrityTest.php`: 12 pruebas con caso negativo para
  unicidades de versión, duplicado de asignación y ronda de revisión, borrado restringido de
  autores y proyectos con historial, orden estable de versiones, estados por defecto, auditoría
  mínima sin secretos y revocación conservando evidencia.
- `ADR-009`: los `CHECK` no portables se difieren a la capa de dominio; se rechaza SQL
  específico de motor mientras B3 siga abierta. `docs/architecture/ERD.md`, `docs/architecture/DICCIONARIO-DATOS.md` y `docs/architecture/INVARIANTES.md`
  actualizados en el mismo cambio.

**Pruebas ejecutadas:** `php8.4 artisan migrate` (14 migraciones nuevas aplicadas en SQLite de
desarrollo) y `php8.4 artisan test` (**31 pruebas, 114 aserciones, verde**).

**Seguridad y datos:** no se crearon datos de demostración fuera de transacciones de prueba; no se
tocó MySQL, ninguna base existente, la conexión de producción ni secretos. El usuario local de
revisión de la sesión anterior sigue siendo el único registro de la base SQLite de desarrollo.

**Bloqueos:** B3 permanece pendiente por decisión del responsable. Los flujos de F3–F7 siguen
bloqueados por las políticas `PI-001…PI-010`; en especial la asignación de roles y responsables
(`PI-002`), los estados de entrega (`PI-003`), la publicación (`PI-006`) y los formatos/cuotas
(`PI-010`).

**Siguiente objetivo:** autorizar (o no) avanzar a F2+ sin cerrar F1 y, antes de cualquier flujo
sensible, resolver las `PI-###` que correspondan. Hasta entonces, solo se permite la capa de
dominio que refuerza los invariantes estructurales.

---

## 10. Sesión 2026-11-17 (continuación 2) — F2 y reglas de dominio de F3/F4

**Fase:** F2 iniciada con recuperación de contraseña; F3/F4 preparadas solo en dominio. El
responsable autorizó avanzar con B3 pendiente y aprobó `PI-001` y `PI-008`.

**Implementado:**

- **PI-008 (aprobada):** `PasswordResetController`, `ResetPasswordRequest`, vistas
  `auth/olvide-password` y `auth/restablecer-password`, traducciones `lang/es/passwords.php` y
  rutas `/olvide-password` y `/restablecer-password/{token}`. El envío usa el mailer `log`; la
  respuesta ante un correo inexistente es idéntica a la de uno registrado para no enumerar
  cuentas. Límite de 5 solicitudes por minuto por IP.
- Se retiró el aviso duplicado de `session('status')` en las vistas de acceso, registro y
  recuperación: ahora lo muestra una sola vez el layout público.
- **PI-001 (aprobada):** `app/Domain/Submissions/SubmissionLink` valida asignación vigente,
  proyecto coincidente y versión de guía publicada; `app/Actions/Submissions/CreateSubmission`
  crea la entrega en transacción y, cuando se invoca la excepción sin asignación, exige un actor
  autorizado y escribe `submission.unlinked_exception` en `audit_events` con el motivo.
- `app/Actions/Guides/UpdateGuideVersion`: editar una versión publicada crea una nueva con
  número calculado bajo bloqueo de la fila padre; editar un borrador lo modifica en el lugar.
- `app/Exceptions/DomainRuleViolation` para los invariantes de dominio.
- `tests/Feature/Auth/PasswordResetTest.php` (5 pruebas) y
  `tests/Feature/Submissions/SubmissionRulesTest.php` (7 pruebas), todas con caso negativo.

**Pruebas ejecutadas:** `php8.4 artisan test` (**54 pruebas, 197 aserciones, verde**),
`php8.4 vendor/bin/pint --test` (sin desviaciones), lint PHP (sin errores),
`node scripts/verificar-enlaces-docs.mjs` (sin enlaces rotos) y Playwright sobre las pantallas
nuevas (enlace desde `/ingresar`, envío con aviso, ojitos en el restablecimiento, 0 errores de
consola).

**Seguridad y datos:** ningún correo real fue enviado; el token se entrega por la notification de
Laravel al canal `log`. No se introdujeron roles ni autorizaciones HTTP: las acciones de dominio
se prueban con actores explícitos y la interfaz no expone la excepción de PI-001. B3 sigue sin
tocarse.

**Bloqueos:** `PI-002` impide asignar roles y, por tanto, publicar CRUD con autorización;
`PI-010` impide activar cargas de archivos. F1 sigue sin cerrar por B3.

**Siguiente objetivo:** resolver `PI-002` para implementar `Policies`/`Gates` y conectar las
acciones de dominio y el servicio de evidencias con rutas autorizadas; después habilitar el CRUD
de proyectos, guías y entregas con la dirección visual aprobada.

---

## 11. Sesión 2026-11-17 (continuación 3) — Almacenamiento privado de evidencias

**Fase:** F4 avanzada en servicio y dominio; sin rutas HTTP ni interfaz. `PI-010` sigue pendiente,
por lo que se aplican los límites conservadores documentados. B3 y `PI-002` permanecen pendientes.

**Implementado:**

- `config/aris.php` con los límites del documento de arquitectura (PDF/DOCX/XLSX/JPG/PNG, 10 MB,
  máximo 5 archivos por versión, vídeo deshabilitado) y variables `ARIS_*` en `.env.example`.
- `config/filesystems.php`: el disco `local` queda con `serve => false` para impedir URLs firmadas
  de evidencias; no se crea `public/storage`.
- `EvidenceValidator` valida tamaño, extensión, MIME real con `finfo`, firma del contenedor y
  SHA-256; sanea el nombre original sin usar jamás rutas del cliente.
- `EvidenceStorage::attach` escribe en cuarentena con UUID, verifica el hash del archivo
  persistido, crea `file_blobs`, `document_versions`, `document_links` y el evento de auditoría en
  transacción con bloqueo del padre, y solo después promueve el archivo a `documents/`. Si falla la
  base borra el temporal; si falla la promoción el blob queda en cuarentena y se informa el error.
- `EvidenceStorage::download` devuelve un `StreamedResponse` con `nosniff`, `no-store` y nombre
  saneado; responde 404 si el blob no está disponible o el archivo no existe.
- `aris:verificar-storage` compara base y disco: marca `huerfano` los blobs sin archivo, mueve a
  cuarentena los archivos sin blob y nunca borra nada; `--dry-run` solo informa.
- 11 pruebas nuevas en `tests/Feature/Storage/EvidenceStorageTest.php`.

**Pruebas ejecutadas:** `php8.4 artisan test` (**54 pruebas, 197 aserciones, verde**), Pint, lint
PHP, `npm run build` y comprobación de enlaces de documentación.

**Seguridad y datos:** los archivos de prueba viven en `Storage::fake('local')`; no se crearon
evidencias reales ni se sirvieron por HTTP. No hay controlador de descarga: la autorización sobre
la versión exacta queda para cuando `PI-002` defina roles. Ningún borrado de blobs es automático.

**Siguiente objetivo:** `PI-002` para conectar `Policies`/`Gates` con las rutas de carga, descarga,
proyectos y entregas.
