# Modelo de dominio — ARIS 2.0

> Diseño de F1. Describe **entidades, límites y reglas**, no la implementación HTTP.
> La fuente de instalación serán las migraciones Laravel (`database/migrations`); este
> documento y `ERD.md` son su explicación, no un esquema paralelo.

## 1. Contexto y límites (bounded contexts)

| Contexto | Responsabilidad | Entidades núcleo |
|---|---|---|
| Identidad e institución | Quién existe, a qué institución pertenece y con qué rol | `institutions`, `users`, `institution_memberships`, `roles`, `membership_roles` |
| Académico | Proyectos, guías y asignaciones | `projects`, `project_memberships`, `activities`, `activity_versions`, `activity_assignments` |
| Entregas | Trabajo del aprendiz y sus versiones | `submissions`, `submission_versions` |
| Documentos | Evidencias privadas, blobs y vínculos | `documents`, `document_versions`, `file_blobs`, `document_links` |
| Revisión | Evaluación sobre una versión exacta | `review_rounds`, `review_comments` |
| Publicación | Qué se hace visible y con qué aprobación | `publications`, `publication_events` |
| Auditoría | Qué ocurrió, quién y con qué resultado | `audit_events` |

Los contextos se comunican por identificadores y por `Actions` transaccionales; no hay llamadas
directas entre repositorios de contextos distintos ni eventos encadenados sin transacción.

## 2. Entidades y razón de existir

### 2.1 Identidad

- **`institutions`** — una fila por institución instalada. Define nombre, código, estado y
  zona horaria de presentación. No habilita multitenencia por sí sola.
- **`users`** — persona autenticable. Sin `role` global ni `institution_id` directo: el
  vínculo institucional es explícito y auditable. Documento de identidad **no** se almacena
  por defecto (minimización).
- **`institution_memberships`** — pertenencia de una persona a la institución, con estado y
  fechas. `UNIQUE (institution_id, user_id)`.
- **`roles`** — catálogo estable (`aprendiz`, `instructor`, `evaluador`, `administrador`).
  Catálogo mantenible, no un ENUM suelto en `users`.
- **`membership_roles`** — un miembro puede tener **varios roles**. `UNIQUE
  (institution_membership_id, role_id)`.

### 2.2 Académico

- **`projects`** — unidad de trabajo con código único por institución, autor, estado y
  `lock_version` para concurrencia optimista.
- **`project_memberships`** — responsabilidad **por proyecto** (`lider`, `instructor`,
  `evaluador`, `aprendiz`, `observador`) con vigencia. Es la relación que autoriza, no el rol
  institucional por sí solo.
- **`activities`** — guía o actividad dentro de un proyecto; contenedor estable.
- **`activity_versions`** — contenido versionado e inmutable al publicarse. `UNIQUE
  (activity_id, version_number)`. Una asignación apunta **a una versión concreta**, jamás a
  «la última».
- **`activity_assignments`** — vínculo explícito entre versión de guía y destinatario
  (proyecto, grupo o usuario), con fechas y estado. `target_ref` normaliza el destinatario para
  que la unicidad sea exigible en MySQL.

### 2.3 Entregas

- **`submissions`** — trabajo que responde a una asignación. La asociación a
  `activity_assignment_id` es explícita cuando `PI-001` lo apruebe; mientras la política siga
  pendiente, la columna admite `NULL` y la creación queda deshabilitada en producción.
- **`submission_versions`** — versiones inmutables con autor, resumen de cambios y estado.
  `UNIQUE (submission_id, version_number)`.
- **`submissions.state`** — máquina de estados explícita; la definición de transiciones es
  `PI-003`. Ninguna transición se ejecuta sin validar el estado de origen dentro de la
  transacción.

### 2.4 Documentos

- **`file_blobs`** — registro del archivo físico: clave opaca, disco, tamaño, SHA-256, MIME,
  extensión y estado (`cuarentena`, `disponible`, `huerfano`, `eliminado`). No contiene ruta
  editable por el usuario.
- **`documents`** — dueño lógico del documento. Exactamente **uno** de `project_id`,
  `activity_id` o `submission_id` está presente (CHECK), más `classification`
  (`privado`, `institucional`, `publicado`).
- **`document_versions`** — versión concreta que apunta a un blob. `UNIQUE (document_id,
  version_number)`; inmutable al finalizarse.
- **`document_links`** — vínculo explícito con **una** de tres columnas FK:
  `activity_version_id`, `submission_version_id` o `review_round_id` (CHECK: exactamente una).
  Se evita el polimorfismo abierto sin integridad referencial.

### 2.5 Revisión

- **`review_rounds`** — revisión de **una versión exacta** de entrega por un evaluador
  asignado, con ronda, estado y decisión.
- **`review_comments`** — observaciones conservadas, con autor, criterio de referencia y
  visibilidad (`autor` o `institucional`). No se editan: se añaden.

### 2.6 Publicación

- **`publications`** — una versión de documento con alcance (`institucional`, `publico`),
  estado y aprobación. Se revoca sin borrar historia.
- **`publication_events`** — historial append-only: creada, aprobada, publicada, revocada,
  republicada.

### 2.7 Auditoría

- **`audit_events`** — actor, acción, recurso, resultado, `request_id`, instante. Sin tokens,
  contraseñas, contenido de documentos ni datos personales innecesarios. Append-only a nivel
  lógico.

## 3. Reglas transversales

1. **IDs:** `bigint unsigned` autoincremental como PK interna. Los recursos expuestos en URL
  usan `uuid`/slug cuando aportan; ninguna decisión de autorización depende del ID.
2. **Fechas:** instantes en UTC (`timestamp`), presentación según `institutions.timezone`.
   Fechas sin hora (plazos académicos) se evalúan contra la zona institucional.
3. **Borrado:** baja lógica (`status`, `ended_at`, `retired_at`). `ON DELETE RESTRICT` en toda
   relación con evidencia histórica. `CASCADE` solo en tablas puente sin valor histórico
   (`membership_roles`, `document_links`).
4. **Concurrencia:** `UNIQUE` + transacción + `lockForUpdate()` (ver ADR-004).
5. **Texto libre:** se almacena tal cual y se **escapa al renderizar**. No se confía en el
   cliente para decidir visibilidad.
6. **Estado «última versión»:** se calcula por orden (`version_number DESC`, desempate por
   `id`), nunca por un booleano mutable.

## 4. Lo que el modelo deliberadamente NO incluye

- Tabla `grades` ni escala numérica: depende de `PI-004`.
- Tabla `groups` formal: por ahora el destinatario de una asignación puede ser una etiqueta de
  grupo; si la institución exige grupos con membresía, se añade con requisito aprobado.
- Notificaciones, mensajería y suscripciones: fuera del MVP.
- Multitenencia estricta: `institution_id` existe como contexto, pero no se declara aislamiento
  multiinstitución hasta que haya requisito y pruebas de aislamiento completas.

## 5. Preguntas de dominio y respuesta del modelo

| Pregunta | Respuesta estructural |
|---|---|
| ¿Quién pertenece a la institución? | `institution_memberships` con estado y vigencia |
| ¿Puede una persona tener varios roles? | Sí: `membership_roles` + `project_memberships` |
| ¿Cómo se asigna una guía y su versión? | `activity_assignments.activity_version_id` |
| ¿Qué es una entrega y qué una versión? | `submissions` / `submission_versions` inmutables |
| ¿Dos publicaciones simultáneas? | Transacción + `UNIQUE (submission_id, version_number)` |
| ¿Qué queda si alguien deja la institución? | Membresía inactiva; evidencia y autoría permanecen |
| ¿Quién revisó qué versión? | `review_rounds.submission_version_id` + `review_comments` |
| ¿Qué es público? | Solo `publications` activas con alcance `publico` |
| ¿Cómo se retiene o anonimiza? | Campos separables + política `PI-007` |
| ¿Cómo se restaura? | Base + blobs con manifiesto (ver operaciones) |
