# Estado de ARIS 2.0 — bitácora acumulativa

> Documento único de transferencia entre sesiones. Se actualiza al cerrar cada sesión.
> Formato compacto: no generar informes paralelos.

**Última actualización:** 18 de noviembre de 2026
**Fase en curso:** F1 con esquema verificado en SQLite de pruebas (B3 pendiente) · F2 en desarrollo
(identidad, roles, jurisdicción, policies y aprovisionamiento CLI) · F3/F4 iniciadas con recorrido
HTTP privado en borrador · F6 con pantallas Blade del recorrido (QA visual nuevo pendiente)
· F5 iniciada con PI-003/004 aprobadas y recorrido cualitativo · F8 iniciada en integridad
de evidencias · F9 iniciada en empaquetado de fuentes
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
- El frontend real es **Blade + Vite + Tailwind 4 + JavaScript vanilla**. No se incorporaron
  Inertia, React ni TypeScript. AGENTS.md quedó alineado con este stack el 18-11-2026.
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
  creando una nueva. La conexión HTTP autorizada se implementó el 18-11-2026 (véase sección 14).

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
se aplicaron las cinco migraciones nuevas de jurisdicción sobre la base local en esta
continuación; se verificaron únicamente en SQLite en memoria. El núcleo previo sí fue migrado
en las sesiones del 17-11-2026, según las entradas históricas.

## 6. Decisiones que requieren aprobación humana

- **Stack:** decidido — Laravel 13.42.0 + PHP 8.4.23 local (ADR-002).
- **Aprobadas el 17-11-2026:** `PI-001` (entrega vinculada a versión concreta de guía, con
  excepción autorizada y auditada) y `PI-008` (recuperación por correo con token estándar y mailer
  `log`, sin envío real).
- **PI-002 aprobada (18-11-2026, versión 4):** solo administrador gestiona roles y tutores;
  Coordinación propone evaluadores; el evaluador autoriza PI-001 solo dentro de su cohorte y
  especialidad. Permiso explícito de creación para instructores vinculados a la especialidad.
- **Pendientes:** `PI-003`, `PI-004`,
  `PI-005`, `PI-006`, `PI-007`, `PI-009`, `PI-010` y `PI-011`. Ninguna se implementa con valores
  por defecto inventados; sus flujos permanecen deshabilitados.
- **Publicación del repositorio:** público en `github.com/luis06-op/ARIS-V2` con autorización
  del usuario. Titularidad declarada del **SENA** (`AVISO-DE-DERECHOS.md`, ADR-006); la
  confirmación institucional de la publicación pública es `PI-011`. **Sin licencia de uso.**

## 7. Siguiente objetivo concreto

**B3 queda pendiente por instrucción expresa del responsable (17-11-2026).** No se conecta ni se
migra ninguna instancia MySQL, por lo que la puerta F1 no se declara cerrada. El avance a F2+ fue
autorizado en la misma sesión. PI-002 quedó aprobada en su versión 4 (18-11-2026) y el recorrido
privado F2–F4 está implementado y probado en borrador. **Siguiente objetivo concreto:** resolver
PI-003 (estados, actores y condiciones de entrega/corrección/cierre) para implementar las
transiciones académicas sin inventarlas. PI-004 será necesaria antes de activar evaluación y
PI-006 antes de publicación pública. La activación local de las cinco migraciones nuevas y del
administrador inicial requiere autorización; no se ejecutó en esta sesión.

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

---

## 12. Sesión 2026-11-17 (continuación 4) — Autoridad institucional, acuerdo versión 2

**Fase:** F2; F1 continúa abierta por B3 y F3/F4 siguen preparadas sin rutas sensibles.

**Implementado:** documentación del acuerdo institucional versión 2 en
`docs/product/DECISIONES-INSTITUCIONALES.md`. Aprobados cuatro roles (administrador, evaluador,
instructor y aprendiz), facultades de gestión y designación de la autoridad inicial durante la
instalación. PI-001 incorpora autorizadores, motivo mínimo de 20 caracteres, beneficiario,
fecha y hora, IP y código de transacción. Estos requisitos nuevos todavía no están implementados
en código. PI-002 sigue parcial por las precisiones de alcance y la contradicción entre la
asignación exclusiva por administrador y la alternativa de Coordinación Académica.

**Verificaciones:** lectura de los cuatro documentos rectores y comprobación Git mediante
`git status --short --branch`, `git branch --list`, `git log -1 --oneline` y
`git rev-parse origin/main`: al inicio, árbol limpio, única rama `main`, HEAD y referencia local
`origin/main` en `4acffb0`. No se consultó el remoto. Suite PHP, Pint, lint y compilación frontend
no ejecutados en esta continuación documental; los resultados anteriores son históricos.
`node scripts/verificar-enlaces-docs.mjs`: APROBADA, 36 documentos y 61 enlaces sin roturas.
`git diff --check`: APROBADA, sin errores de espacios.

**Seguridad y datos:** no se tocaron bases ni conexiones, no se crearon cuentas, no se expusieron
rutas ni se enviaron correos. No se ejecutó despliegue, push ni commit. Los cambios documentales
quedan pendientes de commit; se requiere petición del usuario para realizarlo.

**Bloqueos y decisiones pendientes:** B3; precisiones de PI-002; PI-003, PI-004, PI-005, PI-006,
PI-007, PI-009, PI-010 y PI-011 continúan pendientes. La mención de calificaciones, formatos y
control administrativo de MySQL no modifica esos bloqueos.

**Siguiente objetivo concreto:** confirmar quién autoriza la creación de proyectos por instructores
y cómo se asigna y limita el alcance de evaluadores (incluyendo tutores, sustentaciones y PI-001),
así como aclarar el papel de Coordinación Académica en la asignación del rol.

### Continuación del acuerdo — versión 3

**Fase:** F2. Registrada la autorización exclusiva del administrador para habilitar
`can_create_projects` a solicitud de Coordinación Académica, exigiendo vinculación del
instructor a la especialidad. Registrada la matriz de jurisdicción de evaluadores por
programa/especialidad y ficha/cohorte, asignada por el administrador, con filtrado estricto
de consultas y sin acceso global predeterminado. Implementación de permisos aún pendiente.

**Verificaciones:** cambio exclusivamente documental; suite PHP, Pint, lint y build no ejecutados
en esta continuación. No se accedió a bases ni se crearon datos o cuentas. Sin commit ni push.
`node scripts/verificar-enlaces-docs.mjs`: APROBADA (36 documentos, 61 enlaces sin roturas).
`git diff --check`: APROBADA.

**Bloqueos:** PI-002 sigue parcial; B3 y las demás políticas pendientes conservan su estado.

**Siguiente objetivo concreto:** confirmar si Coordinación propone evaluadores y el administrador
formaliza su rol, y si la jurisdicción del evaluador también limita la asignación de tutores y
la autorización de excepciones PI-001; con ello completar la definición de autorización F2.

---

## 13. Sesión 2026-11-18 — PI-002 aprobada y primeras policies institucionales

**Fase:** F2 en desarrollo; no cerrada. F1 permanece abierta por B3.

**Acuerdo:** PI-002 versión 4 aprobada por el responsable. Coordinación propone evaluadores y
solo el administrador gestiona su rol. Solo el administrador asigna, cambia o remueve tutores;
esta confirmación sustituye la facultad anterior del evaluador. El evaluador autoriza PI-001
exclusivamente dentro de su cohorte y especialidad, con justificación y auditoría obligatorias.

**Implementado:**

- `app/Domain/Identity/InstitutionalAuthority.php`: consulta de autoridad administrativa en
  la institución del recurso, con usuario e institución activos y membresía vigente.
- `app/Policies/InstitutionPolicy.php`: `manageRoles` exclusiva del administrador.
- `app/Policies/ProjectPolicy.php`: `manageTutors` exclusiva del administrador. Ser propietario
  del proyecto no concede esta facultad.
- Descubrimiento convencional de policies por Laravel y evaluación mediante Gate. Sin bypass
  global para administradores. Revocaciones y bloqueos se consultan en base aunque existan
  relaciones previamente cargadas.
- `tests/Feature/Auth/InstitutionalAuthorizationTest.php`: 7 pruebas nuevas (42 aserciones)
  sobre permisos positivos, denegación por rol, anónimo, propietario sin membresía, recurso de
  otra institución, revocación, vigencia, bloqueos y ausencia de permisos implícitos.
- `docs/decisions/ADR-010-autorizacion-institucional-explicita.md` documenta la decisión técnica.

**Verificaciones ejecutadas:**

| Comando | Resultado |
|---|---|
| `php8.4 artisan test` | APROBADA — 61 pruebas, 239 aserciones |
| `php8.4 vendor/bin/pint --test` | APROBADA |
| `find app config database routes tests -name '*.php' -print0 \| xargs -0 -r -n1 php8.4 -l` | APROBADA — sin errores |
| `npm run build` | APROBADA — aviso informativo del paquete opcional `fontaine` |
| `node scripts/verificar-enlaces-docs.mjs` | APROBADA — 37 documentos, 63 enlaces sin roturas |
| `git diff --check` | APROBADA |

**Seguridad y datos:** PHPUnit usa SQLite en memoria; no se migró ni consultó la base de
desarrollo ni MySQL. Los registros sintéticos existen únicamente en pruebas. Sin cuentas
semilla, rutas HTTP nuevas, correos reales, commit, push ni despliegue. El árbol conserva los
cambios de esta sesión y la documentación pendiente de las confirmaciones anteriores.

**Pendiente de implementación:** matriz de jurisdicción, permiso explícito de creación,
administración efectiva de roles y tutores, aprovisionamiento inicial, autorización sobre
versiones exactas, ampliación de auditoría PI-001 y conexión HTTP. Estas primeras policies
no constituyen todavía protección del recorrido HTTP ni cierre de la puerta F2.

**Bloqueos:** B3 y PI-003, PI-004, PI-005, PI-006, PI-007, PI-009, PI-010 y PI-011 conservan su
estado. PI-002 ya no bloquea el desarrollo de autorización; la calificación y los plazos
académicos no quedan aprobados por la confirmación de autoridad.

**Siguiente objetivo concreto:** añadir la persistencia de especialidad/ficha, jurisdicción
asignada por administrador y permiso de creación para instructores, con pruebas negativas de
cruce de cohorte y especialidad; después completar las policies sobre versiones exactas.

---

## 14. Sesión 2026-11-18 (continuación) — Recorrido privado F2/F3/F4 y pantallas F6

**Fase / autorización:** el usuario pidió continuar las demás fases. Se avanzó en F2–F4 y sus
pantallas F6 con B3 pendiente. No se declaran cerradas las puertas de fase ni se activan F5/F7.

**Implementado:**

- Cinco migraciones: especialidades, fichas con cohorte, vínculos de instructor con permiso
  explícito, jurisdicciones de evaluador y `projects.ficha_id` nullable para registros previos.
  La cadena actual tiene **28 archivos de migración (25 ARIS y 3 base)**; se corrige así el
  cómputo histórico que denominaba «propias» a las 23 migraciones anteriores.
- Consultas `VisibleProjects` y `CreatableFichas`: filtrado SQL por institución, rol, membresía
  y jurisdicción antes de mostrar listados u opciones. Sin alcance global del evaluador.
- `/accesos`: vinculación de cuentas registradas, roles, catálogo de especialidades/fichas,
  permiso de creación por instructor y jurisdicción del evaluador. Solo administrador; cambios
  transaccionales y auditados.
- `/proyectos`: listado paginado, creación con ficha autorizada, consulta y edición de título y
  descripción con `lock_version`. Institución, propietario y estado no se aceptan del cliente.
  Crear no autodesigna tutor; solo el administrador lo designa o retira. El instructor tutor
  vincula o retira aprendices de su institución. No hay borrado ni archivo de proyectos.
- Guías: creación de borrador, lectura por versión, edición, publicación interna y asignación
  al proyecto. Una versión publicada permanece inmutable; editarla crea otro borrador. Se
  corrigió la relectura bajo bloqueo y el checksum de las ediciones de borrador.
- Entregas: creación de borradores contra asignación vigente y versión exacta de guía,
  consulta de historial, nuevas versiones y cargas privadas. Solo la última versión en borrador
  admite archivos; las anteriores no se sobrescriben. Los estados académicos no se activan.
- `DocumentVersionPolicy`: descarga HTTP de la versión exacta vinculada a una versión de
  entrega autorizada, verificando coherencia de documento, entrega, proyecto e institución.
  No se heredan permisos de otra versión del mismo documento.
- Excepción PI-001: ruta específica y Gate en la acción interna; justificación de 20–2000
  caracteres, autorizador y beneficiario, proyecto, timestamp, UUID de transacción e IP obtenida
  del request del servidor. Evaluador limitado a su ficha/especialidad; aprendiz no se autoriza
  a sí mismo. La excepción produce un borrador sin guía, no una transición extemporánea.
- `setup_admin.php` exclusivamente CLI y `aris:inicializar-administracion`: institución y
  autoridad reales introducidas por el operador, contraseña oculta y confirmada, hash y auditoría
  transaccionales; bloqueo de reinicialización incluso si después se retira el rol inicial.
  Implementado y probado en memoria; no se ejecutó para crear una cuenta real.
- Pantallas Blade con tokens existentes, estados vacíos reales, formularios CSRF, texto escapado,
  navegación adaptable, paginación y errores 403/404 en español. «Mi cuenta» refleja vigencia.
- ADR-011, diccionario, ERD, invariantes, plan, políticas e instalación actualizados. AGENTS.md
  corregido para reflejar el frontend real y sus comandos disponibles.

**Pruebas y verificaciones reales:**

| Comando / comprobación | Resultado |
|---|---|
| `php8.4 artisan test` | **APROBADA — 91 pruebas, 533 aserciones** |
| `php8.4 vendor/bin/pint --test` | **APROBADA** |
| `find app config database routes tests lang -name '*.php' -print0 \| xargs -0 -r -n1 php8.4 -l` | **APROBADA** — sin errores |
| `php8.4 -l setup_admin.php` | **APROBADA** |
| `php8.4 setup_admin.php --help` | **APROBADA** — solo ayuda, no inicialización |
| `npm run build` | **APROBADA** — aviso informativo del paquete opcional `fontaine` |
| `node scripts/verificar-enlaces-docs.mjs` | **APROBADA** — 38 documentos, 65 enlaces sin roturas |
| `git diff --check` | **APROBADA** |
| Rollback de las cinco migraciones, reaplicación y ejecución idempotente | **APROBADA** dentro de `JurisdictionIntegrityTest`, solo SQLite en memoria |
| QA visual nuevo en navegador | **NO EJECUTADA** — las pruebas HTTP sí renderizan las nuevas vistas |

Las 30 pruebas añadidas sobre la suite de la sección 13 cubren gestión de accesos, jurisdicción,
IDOR por recurso y versión, tutor no asignado, aprendiz ajeno, anónimo, revocación, bloqueo,
concurrencia optimista de edición, límites de archivo, XSS, bytes/hash de descarga, PI-001 y
aprovisionamiento inicial. Durante el desarrollo falló el recorrido al admitir resumen nulo:
se corrigió validación/formulario/acción para respetar el esquema existente (obligatorio, 500
caracteres) y la suite final está en verde. La concurrencia real en MySQL no se verificó.

**Seguridad y datos:** fixtures únicamente en SQLite en memoria y archivos en `Storage::fake`.
No se migró la base local existente, no se accedió a MySQL, no se crearon cuentas o catálogos
reales ni se enviaron correos. El instalador no contiene credenciales predeterminadas; los
secretos sintéticos de sus pruebas están solo en tests. Sin commit, push, PR ni despliegue.
El árbol conserva tanto los cambios de esta continuación como los cambios previos autorizados
de documentación y policies, pendientes de petición de commit.

**Pendientes y bloqueos:**

- B3 sigue pendiente; las cinco migraciones nuevas deben aplicarse únicamente cuando se autorice
  su activación local y se verifique la conexión. No presentar estas pantallas como activadas en
  la base local actual. F1 no está cerrada.
- F2: verificación de correo diferida por la política vigente, importación institucional por lotes
  y aprovisionamiento real autorizado. El código de inicialización sí está implementado.
- F3/F4: asignaciones individuales/grupales, bajas/archivo, pruebas de infraestructura y
  concurrencia MySQL. Sin cierre integral de F3/F4.
- F5: PI-003 y PI-004 pendientes. F7: PI-006 pendiente. PI-005, PI-007, PI-009, PI-010 y PI-011
  mantienen su estado; no hay nuevos formatos, cuotas ni tratamiento de datos reales aprobado.
- F6: QA visual/accesibilidad integral pendiente. F8/F9: respaldo/restore y empaquetado/despliegue
  todavía no implementados en este incremento. F10 permanece fuera de alcance.

**Siguiente objetivo concreto:** obtener la definición institucional de PI-003 (estados,
transiciones, actores y condiciones), registrarla y construir su máquina de estados con pruebas
negativas antes de habilitar envío, corrección, cierre o revisión.

---

## 15. Sesión 2026-11-18 (continuación) — Publicación de commits autorizada

**Autorización:** el usuario pidió expresamente «sube commit por commit y sigue con las demás
fases». Se autoriza commit y push a `origin/main`; no despliegue, migración local/MySQL ni
aprobación de políticas institucionales pendientes.

**Commits publicados, uno por uno:**

- `94dce25` — `feat: incorporar jurisdicción y dominio documental autorizado`.
- `d706734` — `feat: habilitar proyectos y evidencias privadas por versión`.

**Verificación por commit:** se exportó el contenido exacto del índice Git a una copia aislada,
con dependencias locales y clave exclusivamente sintética de pruebas. Primer commit: **67
pruebas / 275 aserciones**. Segundo: **91 pruebas / 533 aserciones**. Ambos con Pint, lint PHP,
build y enlaces documentales correctos para su snapshot. No se copió `.env` ni se usaron bases
existentes. La primera prueba aislada detectó que faltaba `bootstrap/cache`; se añadió su
`.gitignore` al primer commit y se verificó de nuevo el arranque y la suite con éxito.

**Incidencias del entorno:** SSH global falló por permisos del archivo de configuración de
systemd. Se utilizó `GIT_SSH_COMMAND='ssh -F ~/.ssh/config'` (ruta expandida en la ejecución),
sin modificar el sistema. Git no tenía identidad configurada: se reutilizó por comando la
identidad de los últimos commits, sin cambiar configuración global. Los dos pushes finalizaron
correctamente. La referencia remota estaba alineada antes de comenzar.

**Siguiente incremento seguro:** F8, verificación de integridad física de evidencias privadas
con pruebas de corrupción y rechazo de descarga; después, preparar verificaciones de entrega
F9. F5/F7 permanecen bloqueadas por PI-003/PI-004/PI-006 y B3 sigue pendiente.

## 16. Sesión 2026-11-18 (continuación) — Integridad operativa F8

**Implementado:** `EvidenceIntegrity`, comprobación de bytes y metadatos antes de descargar,
stream temporal verificado sin reapertura del archivo original y comando
`aris:verificar-integridad` de solo lectura, con salida 1 ante incidentes. No cambia estados,
archivos ni auditoría de evidencias. ADR-012 documenta el mecanismo y sus límites.

**Verificación:** `php8.4 artisan test` — **99 pruebas, 563 aserciones**, todas aprobadas.
Ocho pruebas nuevas: corrupción del mismo tamaño, truncado/ausencia, metadatos, rutas,
cuarentena vigente, copia estable y rechazo HTTP. SQLite en memoria y disco falso.
También aprobados: `php8.4 vendor/bin/pint --test`, lint de 159 archivos PHP,
`npm run build`, `node scripts/verificar-enlaces-docs.mjs` (39 documentos, 65 enlaces) y
`git diff --check`. El build conserva el aviso informativo del paquete opcional `fontaine`.

**Publicación previa:** los commits `94dce25`, `d706734` y `48445d4` fueron subidos individualmente
a `origin/main` con autorización expresa. Se mantiene una única rama local `main`.

**Bloqueos:** B3, PI-003/004/005/006/007/009/010/011; respaldo y restore aún no implementados.
No se ejecutó verificación de blobs sobre datos existentes, migraciones locales, inicialización
real, despliegue ni correos. Las pruebas no equivalen a certificar la instalación local.

**Siguiente objetivo concreto:** preparar un paquete reproducible de fuentes con manifiesto
SHA-256 y verificador independiente, sin incluir secretos, datos, fixtures ni dependencias
locales; iniciar F9 sin presentar el paquete como despliegue autorizado.
## 17. Sesión 2026-11-18 — F9: fuentes reproducibles

Implementados constructor y verificador de paquetes ZIP con manifiesto SHA-256,
lista permitida, límites técnicos, lectura desde HEAD y rechazo de árbol sucio o
destino existente. ADR-013 registra alcance y procedencia. F9 iniciada, no cerrada.

Pruebas focalizadas: **13 pruebas / 27 aserciones aprobadas**, con repositorios
sintéticos temporales; reproducibilidad, exclusiones y paquetes manipulados.
Suite completa: **112 pruebas / 590 aserciones aprobadas**. También aprobados Pint,
lint PHP, build Vite, enlaces documentales (40 documentos / 65 enlaces) y diff check.
El build conserva el aviso informativo de `fontaine` opcional.

Siguen pendientes B3 y PI-003/004/005/006/007/009/010/011, QA visual, instalación
integral, backup/restore y concurrencia MySQL. F1 no está cerrada. F5/F7 bloqueadas.
**Ejecución real posterior al push:** commit `7f0c8911763ba5708a8898c97b78e93a37682c85`.
Se generaron `aris-7f0c891-a.zip` y `aris-7f0c891-b.zip` en el directorio temporal
aprobado del entorno, fuera del repositorio. Ambos contienen **215 archivos** más
manifiesto. `cmp` confirmó igualdad byte a byte; `aris:verificar-paquete` terminó
con código 0. SHA-256 de ambos:

```text
9b755c7e07af25de9bbe8c541c3fba520ad287558fd549ee036b90950a6c54cf
```

Los paquetes son artefactos locales de fuentes del commit indicado, no se subieron
al remoto. No se ejecutaron migraciones ni operaciones sobre bases existentes.
Siguiente objetivo concreto: ensayo aislado de instalación del paquete y diseño
del simulacro de respaldo/restore; resolver PI-003/004 para desbloquear F5.
## 18. Sesión 2026-11-18 — PI-003/004 aprobadas y recorrido académico

El responsable aprobó envío con congelamiento estricto, revisión cualitativa Cumple/Requiere
Ajustes con retroalimentación obligatoria, nuevos intentos para corregir y reapertura por
administrador o personal con asignación/jurisdicción vigente. Motivo y auditoría obligatorios.
Fechas límite nullable en BD; activación de su lógica diferida. Los bloqueos PI-003/004
consignados en entradas anteriores quedan resueltos por esta confirmación.

Implementados cuatro casos de uso transaccionales y rutas Blade: enviar, iniciar revisión,
registrar resultado y reabrir. La creación de versión admite corrección autorizada y conserva
contenido/revisión anteriores. Se reutiliza el esquema existente con correspondencias
documentadas en ADR-014. Los triggers de auditoría rechazan UPDATE/DELETE directo.

Dos migraciones nuevas: fechas límite en `submissions` y protección de `audit_events`.
No se ejecutan sobre bases locales ni MySQL; se prueban con SQLite en memoria. Se amplió
la prueba de rollback/reaplicación para las siete migraciones del 18 de noviembre.

Verificación de implementación: `php8.4 artisan test` — **121 pruebas / 707 aserciones**
aprobadas. Nueve pruebas nuevas cubren recorrido HTTP, corrección, feedback obligatorio,
escape HTML, jurisdicción y revocación, reapertura y motivo/IP, plazos inactivos,
protección SQL de auditoría y reversión si falla su registro. La primera ejecución detectó
que el test de rollback esperaba cinco migraciones; se actualizó a siete y pasó la suite.
También aprobados: Pint, lint PHP, build Vite, enlaces documentales (41 documentos,
65 enlaces) y `git diff --check`. Se mantiene el aviso informativo de `fontaine` opcional.
La prueba de corrección recorre también el envío y evaluación del segundo intento y
comprueba que no hereda ni altera la retroalimentación del primero.

Pendientes: B3, PI-005/006/007/009/010/011, QA visual, prueba real de concurrencia MySQL,
privilegios/triggers en MySQL, backup/restore e instalación integral. No se cierra F1 ni F5.
No se habilitan nota numérica, reasignación de revisiones en curso ni reglas de plazos.

Siguiente objetivo concreto: QA visual y accesibilidad del recorrido académico, seguido del
ensayo aislado de instalación y respaldo/restore. F7 sigue pendiente de PI-006.
## 19. Sesión 2026-11-18 — QA Blade y ejecución por fases

El usuario confirmó conservar Laravel 13 + Blade + Tailwind 4 + JavaScript vanilla.
El prompt inicial citaba Laravel 11/React e identificadores SQL distintos; se aclaró antes
de modificar el esquema. Autoriza validación MySQL 8.x sobre entorno aislado y commits por fase.

F6: añadidos arrastrar/soltar progresivo y diálogo nativo de reapertura con formulario sin JS,
salto al contenido, foco reforzado y ajuste responsive de cabecera. Corregido contraste del
pie lateral (axe detectó 4,2:1 frente a 4,5:1 requerido) y texto del botón cian.

QA ejecutado con Chromium y axe: cuatro roles a 390/768/1440 px, envío congelado y modal,
14 capturas/análisis sin infracciones detectadas ni desbordamientos; teclado, carga real
por HTTP y reapertura comprobados. Artefactos locales en `aris-browser-os57YL`, dentro del
directorio temporal aprobado. Se inspeccionaron visualmente las capturas móvil y modal.
La primera ejecución requirió instalar el Chromium correspondiente a Playwright.
Procedimiento reproducible en `docs/operations/QA-NAVEGADOR.md`.

MySQL: el cliente preexistente es MariaDB 10.4 de XAMPP y no se usará para certificar B3.
Se descargó MySQL Community 8.4.13 en el directorio temporal, con checksum coincidente con
el publicado por el proveedor; resta iniciar el servidor aislado y ejecutar verificaciones.

Siguiente objetivo: validar migraciones y suite en MySQL, después respaldo/restore y módulos
complementarios. No se han tocado bases existentes ni servicios globales.
