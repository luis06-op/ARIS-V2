# Plan de fases y puertas de calidad — ARIS 2.0

> Documento operativo. Define **qué se construye en cada fase**, **cómo se demuestra** y
> **qué impide avanzar**. Sustituye a cualquier informe narrativo de avance: el estado real
> se consulta en `docs/ESTADO-ARIS-2.0.md`.

Principio rector: **fundamentos → seguridad → flujos completos → experiencia → distribución**.
Una función solo se declara terminada cuando pasan persistencia, permisos, errores y pruebas.

---

## 1. Resumen de fases

| Fase | Nombre | Puerta de calidad (resumen) | Estado |
|---|---|---|---|
| F0 | Reconocimiento y seguridad del entorno | Entorno verificado, proyecto aislado, stack decidido | **COMPLETA** (2 bloqueos registrados) |
| F1 | Modelado y base de datos | Instalación desde cero con una sola cadena de migraciones | **PARCIAL** (diseño cerrado; migraciones pendientes) |
| F2 | Identidad, permisos y aislamiento | Ningún acceso por adivinar IDs o alterar parámetros | No iniciada |
| F3 | Proyectos, guías y asignaciones | Asignación verificable e infalsificable | No iniciada |
| F4 | Entregas, archivos privados y versionado | Flujo real proyecto → guía → entrega → archivo → versión | No iniciada |
| F5 | Revisiones y trazabilidad | Revisión de versión exacta sin alterar historial | No iniciada |
| F6 | Diseño y experiencia integral | Todas las pantallas del recorrido, responsivas y accesibles | No iniciada |
| F7 | Repositorio, búsqueda y publicación | El visitante ve solo lo explícitamente publicado | No iniciada |
| F8 | Operación, respaldo y seguridad | Restore probado y sin archivos privados accesibles por HTTP | No iniciada |
| F9 | Hosting y entrega candidata | Paquete reproducible con manifiesto y simulacro | No iniciada |
| F10 | Piloto institucional | Autorización expresa de la institución | **Fuera de alcance actual** |

Regla de avance: **una fase a la vez**. Se permite preparar documentación de una fase
futura, pero no escribir código de F(n+1) antes de cerrar la puerta de F(n) salvo
investigación de solo lectura.

---

## 2. Estructura de llegada del repositorio

El árbol completo del producto (app/, bootstrap/, config/, database/, public/, resources/,
routes/, storage/, tests/, scripts/, docs/) está especificado en el documento rector
§17 y se reproduce aquí como **mapa de llegada**, no como obligación de crear archivos
vacíos. Reglas:

- Se crea cada carpeta **cuando se implementa la funcionalidad que la habita**.
- Se respetan las convenciones del starter oficial de Laravel + React/Inertia de la versión
  efectivamente instalada; si difieren, se documenta la diferencia.
- No se duplican responsabilidades: controlador = HTTP, FormRequest = validación,
  Policy = autorización, Action = transacción, Domain = invariante, Model = relaciones.
- `database/migrations` es la única fuente editable del esquema.
- `docs/` y `tests/` se crean desde F0 (este repositorio ya los contiene).

### 2.1 Orden de construcción

| Fase | Qué se añade al árbol |
|---|---|
| F0 | Raíz, `.gitignore`, `.env.example`, `README.md`, `AGENTS.md`, `docs/` |
| F1 | `composer.json`, `artisan`, `config/`, `database/migrations`, `app/Models`, `app/Domain` mínimo, `tests/Feature/Database` |
| F2 | `app/Policies`, `app/Actions/Identity`, autenticación del starter, pantallas mínimas |
| F3 | `App/Actions/Projects`, `Activities`, `app/Http/*` de proyectos y guías |
| F4 | `app/Services/Storage`, `app/Actions/Documents`, `storage/app/private`, tests de archivos |
| F5 | `app/Actions/Reviews`, `app/Models/Review*`, tests de trazabilidad |
| F6 | `resources/css`, `resources/js`, `public/assets`, componentes y páginas |
| F7 | `app/Services/Search`, publicación y repositorio público |
| F8 | `app/Console/Commands` de verificación, exportación, auditoría y backup |
| F9 | `scripts/`, empaquetado, documentación de despliegue |
| F10 | Operación con la institución (no la ejecuta el agente) |

---

## 3. Detalle por fase

### FASE 0 — Reconocimiento, seguridad del entorno y requisitos

**Objetivo:** saber dónde se trabaja, con qué herramientas reales y qué está prohibido tocar.

Entregables:
- `docs/ESTADO-ARIS-2.0.md` con el inventario del entorno.
- `docs/AGENTES-DISPONIBLES.md` con lo verificado (no lo deseado).
- Decisión de stack registrada (`ADR-002`).
- Repositorio Git local inicializado y publicado en `origin` (público) con autorización del
  usuario.

Puerta F0: entorno verificado, carpeta aislada, stack decidido o bloqueo explícito.

Estado: **COMPLETA**. Bloqueos: Composer ausente y PHP 8.2.12 frente al objetivo 8.3.

---

### FASE 1 — Cimientos: modelo y base de datos

**Objetivo:** que el esquema explique los flujos antes de existir una pantalla.

Entregables:
- Migraciones Laravel del núcleo (identidad, académico, documentos, revisión, publicación,
  auditoría) y modelos Eloquent con relaciones.
- `docs/architecture/MODELO-DOMINIO.md`, `docs/architecture/ERD.md`,
  `docs/architecture/DICCIONARIO-DATOS.md`, `docs/architecture/INVARIANTES.md`.
- `docs/product/DECISIONES-INSTITUCIONALES.md` con las políticas `PI-###`.
- Factories/seeders **solo** para pruebas; `DatabaseSeeder` vacío por defecto.
- Tests: instalación desde cero, re-ejecución idempotente, rollback en base de prueba,
  integridad referencial, unicidades, concurrencia de versiones, denegación de borrados
  históricos.

Puerta F1: `php artisan migrate` limpio en una base sintética nueva + suite de integridad en
verde + versionado explicado.

Estado: **PARCIAL**. El diseño está cerrado y documentado; las migraciones requieren Composer
y un MySQL/MariaDB activo, ausentes en la sesión de creación.

---

### FASE 2 — Identidad, usuarios, permisos y aislamiento

- Autenticación oficial mantenida por el framework (login, logout, reset por token, CSRF,
  rate limit, rotación de sesión).
- Aprovisionamiento institucional (sin registro abierto universal: `PI-002`).
- Roles institucionales + roles por proyecto; `Policies`/`Gates` por recurso.
- Pruebas negativas: aprendiz contra proyecto ajeno, instructor no asignado, evaluador no
  autorizado, anónimo, IDOR por cambio de ID.

Puerta F2: ningún recurso accesible adivinando IDs ni alterando el request.

---

### FASE 3 — Proyectos, guías y asignaciones explícitas

- CRUD de proyectos con estado y membresías.
- Versiones de guía publicadas e inmutables; asignación explícita a proyecto/grupo/usuario.
- Errores definidos para actividad inexistente, inactiva, no asignada o de otra institución.
- La obligatoriedad de guía por entrega queda tras `PI-001`; la estructura la soporta y la
  acción se deshabilita mientras la política siga pendiente.

Puerta F3: un actor autorizado crea proyecto y guía y asigna; otro no puede falsificarlo.

---

### FASE 4 — Entregas, archivos privados y versionado inmutable

- Carga a `storage/app/private` con cuarentena, validación de tamaño/MIME/extensión,
  nombre físico opaco y SHA-256.
- Formatos iniciales: PDF, DOCX, XLSX, JPG, PNG. Video: **no** hasta evaluar cuota,
  antivirus y servidor.
- Versionado con transacción + bloqueo + índice único; reconciliación de blobs huérfanos.
- Descarga mediante policy sobre la **versión exacta**; rechazo de traversal e IDOR.

Puerta F4: recorrido real de punta a punta sin sobrescritura histórica.

---

### FASE 5 — Revisiones, evaluación y trazabilidad

- Revisión sobre versión concreta; comentarios conservados.
- Estados explícitos de la entrega; una nueva versión no altera una revisión anterior.
- Revisión descriptiva primero; rúbricas numéricas solo con `PI-004` aprobada.
- Auditoría de autor, acción y fecha, separada del dato pedagógico.

Puerta F5: línea de tiempo verificable y revisión inmutable.

---

### FASE 6 — Diseño y experiencia integral

- Dirección artística «Conocimiento que deja huella», tokens y componentes reutilizables.
- Landing pública narrativa con fondo animado aislado y `prefers-reduced-motion`.
- Layouts por rol: aprendiz, instructor, evaluador, administrador, visitante.
- Estados vacíos, de error, de carga y de permiso denegado con datos reales.
- QA en 360/390/768/1024/1440 px, teclado, zoom 200 %, contraste AA.

Puerta F6: coherencia y accesibilidad del recorrido completo; sin UI que simule operaciones
inexistentes.

---

### FASE 7 — Repositorio, búsqueda y publicación responsable

- Catálogo institucional privado separado de la publicación pública aprobada.
- Publicación de versión explícita revisada, con revocación trazable.
- Búsqueda indexada por metadatos autorizados, sin filtrar datos ni enumerar IDs privados.

Puerta F7: lo que ve el visitante es exactamente lo publicado y autorizado.

---

### FASE 8 — Operación, respaldo, retención y seguridad integral

- `.env` por entorno, `APP_DEBUG=false` en producción, logs sin datos privados.
- Respaldo coherente de base **y** blobs; restauración probada en laboratorio aislado.
- Retención, exportación y anonimización sujetas a política institucional y asesoría legal.
- Pruebas focalizadas: IDOR, traversal, XSS, CSRF, concurrencia, enumeración, borrado.

Puerta F8: restore exitoso y flujos críticos en verde; ningún archivo privado accesible por HTTP.

---

### FASE 9 — Hosting y entrega candidata

- Verificación del plan real: PHP, extensiones, Composer/SSH, cron, límites, DocumentRoot
  apuntando solo a `public/`.
- Assets compilados fuera del servidor; Node no corre en producción.
- Paquete limpio con manifiesto SHA-256, sin fixtures, sin secretos, con simulacro de
  instalación y rollback documentado.

Puerta F9: candidata reproducible. **No** se declara «lista para producción» sin F8 cerrada,
QA visual ejecutado y autorización del usuario.

---

### FASE 10 — Piloto institucional

Solo con autorización expresa: políticas aprobadas, responsable de datos, soporte, cuentas y
datos autorizados, métricas verificables. **El agente no inicia esta fase.**

---

## 4. Evidencia exigida por fase

Cada cierre de fase debe registrar en `docs/ESTADO-ARIS-2.0.md`:

```text
Fase / objetivo (≤ 5 líneas)
Implementado (archivos y comportamiento)
Pruebas: nombre + comando + resultado real (APROBADA / FALLIDA / NO EJECUTADA)
Seguridad y datos: qué se comprobó
Bloqueos y decisión requerida
Siguiente objetivo concreto (una sola tarea verificable)
```

Mentir sobre una prueba invalida la fase completa. Un fallo de navegador, una API opcional o
un subagente ausente **no** bloquean la sesión: se documentan y se sigue por otra ruta.
