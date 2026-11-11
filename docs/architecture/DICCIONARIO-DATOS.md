# Diccionario de datos — ARIS 2.0

> Referencia de columnas, tipos y restricciones. **Fuente editable:** las migraciones
> Laravel. Este diccionario se actualiza en el mismo cambio que la migración; si divergen,
> la migración gana.
>
> Convenciones generales: `id` = `bigint unsigned` PK autoincremental; `created_at` y
> `updated_at` en todas las tablas mutables salvo indicación; `utf8mb4_unicode_ci`; instantes
> en UTC. La columna «Sensibilidad» clasifica el dato: **B** (bajo), **M** (medio: identifica
> a una persona), **A** (alto: dato personal de menor o evidencia privada).

## 1. Identidad e institución

### `institutions`

| Columna | Tipo | Nulo | Restricción | Sens. | Retención |
|---|---|---|---|---|---|
| id | bigint unsigned | no | PK | B | Permanente |
| name | varchar(150) | no | — | B | Permanente |
| code | varchar(32) | sí | UNIQUE | B | Permanente |
| status | enum('activa','inactiva') | no | default `activa` | B | Permanente |
| timezone | varchar(64) | no | default `America/Bogota` | B | Permanente |
| created_at / updated_at | timestamp | sí | — | B | Permanente |

### `users`

| Columna | Tipo | Nulo | Restricción | Sens. | Retención |
|---|---|---|---|---|---|
| id | bigint unsigned | no | PK | B | Permanente |
| name | varchar(150) | no | — | M | Mientras exista la cuenta + historial de autoría |
| email | varchar(255) | no | UNIQUE (normalizado a minúsculas) | M | Ídem |
| email_verified_at | timestamp | sí | — | B | Ídem |
| password | varchar(255) | no | hash Argon2id/bcrypt | A | Ídem; nunca se exporta |
| status | enum('activo','inactivo','bloqueado') | no | default `activo` | M | Ídem |
| locale | varchar(10) | sí | default `es` | B | Ídem |
| last_login_at | timestamp | sí | — | M | 12 meses (propuesta) |
| remember_token | varchar(100) | sí | — | A | Se invalida al cerrar sesiones |
| created_at / updated_at | timestamp | sí | — | B | — |

> No se almacena documento de identidad, fecha de nacimiento ni dirección: minimización por
> defecto. Si la institución los exige (`PI-009`), se añaden con justificación y control de
> acceso propios.

### `institution_memberships`

| Columna | Tipo | Nulo | Restricción | Sens. | Retención |
|---|---|---|---|---|---|
| id | bigint unsigned | no | PK | B | Permanente |
| institution_id | bigint unsigned | no | FK → institutions, RESTRICT | B | Permanente |
| user_id | bigint unsigned | no | FK → users, RESTRICT | M | Permanente |
| status | enum('activa','inactiva','egresado') | no | default `activa` | M | Permanente |
| joined_at | date | no | — | B | Permanente |
| left_at | date | sí | `>= joined_at` | M | Permanente |
| created_at / updated_at | timestamp | sí | — | B | — |
| — | — | — | UNIQUE (institution_id, user_id) | — | — |

### `roles`

| Columna | Tipo | Nulo | Restricción | Sens. | Retención |
|---|---|---|---|---|---|
| id | bigint unsigned | no | PK | B | Permanente |
| code | varchar(40) | no | UNIQUE (`aprendiz`, `instructor`, `evaluador`, `administrador`) | B | Permanente |
| name | varchar(80) | no | — | B | Permanente |
| description | varchar(255) | sí | — | B | Permanente |
| is_system | boolean | no | default `true` | B | Permanente |
| created_at / updated_at | timestamp | sí | — | B | — |

### `membership_roles`

| Columna | Tipo | Nulo | Restricción | Sens. | Retención |
|---|---|---|---|---|---|
| id | bigint unsigned | no | PK | B | Permanente |
| institution_membership_id | bigint unsigned | no | FK → institution_memberships, CASCADE | M | Permanente |
| role_id | bigint unsigned | no | FK → roles, RESTRICT | B | Permanente |
| assigned_by | bigint unsigned | sí | FK → users, RESTRICT | M | Permanente |
| assigned_at | timestamp | no | default `now()` | B | Permanente |
| — | — | — | UNIQUE (institution_membership_id, role_id) | — | — |

## 2. Académico

### `projects`

| Columna | Tipo | Nulo | Restricción | Sens. | Retención |
|---|---|---|---|---|---|
| id | bigint unsigned | no | PK | B | Permanente |
| institution_id | bigint unsigned | no | FK → institutions, RESTRICT | B | Permanente |
| code | varchar(40) | no | UNIQUE (institution_id, code) | B | Permanente |
| slug | varchar(120) | no | UNIQUE (institution_id, slug) | B | Permanente |
| title | varchar(180) | no | — | B | Permanente |
| description | text | sí | — | B | Permanente |
| owner_id | bigint unsigned | no | FK → users, RESTRICT | M | Permanente |
| status | enum('borrador','activo','archivado') | no | default `borrador` | B | Permanente |
| lock_version | int unsigned | no | default 0 | B | — |
| created_at / updated_at | timestamp | sí | — | B | — |

### `project_memberships`

| Columna | Tipo | Nulo | Restricción | Sens. | Retención |
|---|---|---|---|---|---|
| id | bigint unsigned | no | PK | B | Permanente |
| project_id | bigint unsigned | no | FK → projects, RESTRICT | B | Permanente |
| user_id | bigint unsigned | no | FK → users, RESTRICT | M | Permanente |
| function | enum('lider','instructor','evaluador','aprendiz','observador') | no | — | M | Permanente |
| assigned_at | date | no | — | B | Permanente |
| ended_at | date | sí | `>= assigned_at` | M | Permanente |
| assigned_by | bigint unsigned | sí | FK → users, RESTRICT | M | Permanente |
| created_at / updated_at | timestamp | sí | — | B | — |
| — | — | — | UNIQUE (project_id, user_id, function) | — | — |

### `activities`

| Columna | Tipo | Nulo | Restricción | Sens. | Retención |
|---|---|---|---|---|---|
| id | bigint unsigned | no | PK | B | Permanente |
| project_id | bigint unsigned | no | FK → projects, RESTRICT | B | Permanente |
| code | varchar(40) | no | UNIQUE (project_id, code) | B | Permanente |
| title | varchar(180) | no | — | B | Permanente |
| description | text | sí | — | B | Permanente |
| created_by | bigint unsigned | no | FK → users, RESTRICT | M | Permanente |
| status | enum('borrador','publicada','archivada') | no | default `borrador` | B | Permanente |
| created_at / updated_at | timestamp | sí | — | B | — |

### `activity_versions`

| Columna | Tipo | Nulo | Restricción | Sens. | Retención |
|---|---|---|---|---|---|
| id | bigint unsigned | no | PK | B | Permanente |
| activity_id | bigint unsigned | no | FK → activities, RESTRICT | B | Permanente |
| version_number | int unsigned | no | `>= 1`, UNIQUE (activity_id, version_number) | B | Permanente |
| title | varchar(180) | no | — | B | Permanente |
| content | longtext | sí | Markdown/HTML sanitizado al renderizar | B | Permanente |
| author_id | bigint unsigned | no | FK → users, RESTRICT | M | Permanente |
| checksum | char(64) | no | SHA-256 del contenido | B | Permanente |
| status | enum('borrador','publicada') | no | default `borrador` | B | Permanente |
| published_at | timestamp | sí | obligatorio si `status = publicada` | B | Permanente |
| created_at / updated_at | timestamp | sí | — | B | — |

### `activity_assignments`

| Columna | Tipo | Nulo | Restricción | Sens. | Retención |
|---|---|---|---|---|---|
| id | bigint unsigned | no | PK | B | Permanente |
| activity_version_id | bigint unsigned | no | FK → activity_versions, RESTRICT | B | Permanente |
| project_id | bigint unsigned | no | FK → projects, RESTRICT | B | Permanente |
| target_type | enum('proyecto','grupo','usuario') | no | — | B | Permanente |
| target_ref | varchar(64) | no | `proyecto` \| etiqueta de grupo \| id de usuario | M | Permanente |
| target_user_id | bigint unsigned | sí | FK → users, RESTRICT; obligatorio si `target_type = usuario` | M | Permanente |
| assigned_by | bigint unsigned | no | FK → users, RESTRICT | M | Permanente |
| assigned_at | timestamp | no | default `now()` | B | Permanente |
| due_at | timestamp | sí | — | B | Permanente |
| status | enum('activa','revocada') | no | default `activa` | B | Permanente |
| created_at / updated_at | timestamp | sí | — | B | — |
| — | — | — | UNIQUE (activity_version_id, target_type, target_ref) | — | — |

## 3. Entregas

### `submissions`

| Columna | Tipo | Nulo | Restricción | Sens. | Retención |
|---|---|---|---|---|---|
| id | bigint unsigned | no | PK | B | Permanente |
| project_id | bigint unsigned | no | FK → projects, RESTRICT | B | Permanente |
| activity_assignment_id | bigint unsigned | sí | FK → activity_assignments, RESTRICT; `NOT NULL` cuando `PI-001` se apruebe | B | Permanente |
| author_id | bigint unsigned | no | FK → users, RESTRICT | M | Permanente |
| team_label | varchar(80) | sí | — | B | Permanente |
| title | varchar(180) | no | — | B | Permanente |
| status | enum('borrador','entregada','en_revision','ajustes_solicitados','revisada','retirada') | no | default `borrador` | B | Permanente |
| lock_version | int unsigned | no | default 0 | B | — |
| created_at / updated_at | timestamp | sí | — | B | — |

### `submission_versions`

| Columna | Tipo | Nulo | Restricción | Sens. | Retención |
|---|---|---|---|---|---|
| id | bigint unsigned | no | PK | B | Permanente |
| submission_id | bigint unsigned | no | FK → submissions, RESTRICT | B | Permanente |
| version_number | int unsigned | no | `>= 1`, UNIQUE (submission_id, version_number) | B | Permanente |
| author_id | bigint unsigned | no | FK → users, RESTRICT | M | Permanente |
| summary | varchar(500) | no | resumen del cambio | B | Permanente |
| status | enum('borrador','finalizada') | no | default `borrador` | B | Permanente |
| finalized_at | timestamp | sí | obligatorio si `status = finalizada` | B | Permanente |
| created_at / updated_at | timestamp | sí | — | B | — |

### `file_blobs`

| Columna | Tipo | Nulo | Restricción | Sens. | Retención |
|---|---|---|---|---|---|
| id | bigint unsigned | no | PK | B | Permanente (metadato) |
| uuid | char(36) | no | UNIQUE, opaco | B | Permanente |
| disk | varchar(32) | no | default `local` | B | Permanente |
| storage_key | varchar(255) | no | UNIQUE; ruta interna generada, nunca provista por el usuario | A | Permanente mientras exista referencia |
| size_bytes | bigint unsigned | no | `> 0` | B | Permanente |
| sha256 | char(64) | no | hex, indexado | B | Permanente |
| mime | varchar(127) | no | detectado en servidor | B | Permanente |
| extension | varchar(16) | no | whitelist | B | Permanente |
| status | enum('cuarentena','disponible','huerfano','eliminado') | no | default `cuarentena` | B | Permanente (estado) |
| created_at / updated_at | timestamp | sí | — | B | — |

### `documents`

| Columna | Tipo | Nulo | Restricción | Sens. | Retención |
|---|---|---|---|---|---|
| id | bigint unsigned | no | PK | B | Permanente |
| institution_id | bigint unsigned | no | FK → institutions, RESTRICT | B | Permanente |
| project_id | bigint unsigned | sí | FK → projects, RESTRICT | B | Permanente |
| activity_id | bigint unsigned | sí | FK → activities, RESTRICT | B | Permanente |
| submission_id | bigint unsigned | sí | FK → submissions, RESTRICT | B | Permanente |
| classification | enum('privado','institucional','publicado') | no | default `privado` | M | Permanente |
| created_by | bigint unsigned | no | FK → users, RESTRICT | M | Permanente |
| status | enum('activo','archivado') | no | default `activo` | B | Permanente |
| created_at / updated_at | timestamp | sí | — | B | — |
| — | — | — | CHECK: exactamente uno de project_id/activity_id/submission_id | — | — |

### `document_versions`

| Columna | Tipo | Nulo | Restricción | Sens. | Retención |
|---|---|---|---|---|---|
| id | bigint unsigned | no | PK | B | Permanente |
| document_id | bigint unsigned | no | FK → documents, RESTRICT | B | Permanente |
| version_number | int unsigned | no | `>= 1`, UNIQUE (document_id, version_number) | B | Permanente |
| blob_id | bigint unsigned | no | FK → file_blobs, RESTRICT | A | Permanente |
| author_id | bigint unsigned | no | FK → users, RESTRICT | M | Permanente |
| original_name | varchar(255) | no | metadato no confiable, se escapa al mostrar | M | Permanente |
| mime | varchar(127) | no | detectado | B | Permanente |
| extension | varchar(16) | no | validada | B | Permanente |
| size_bytes | bigint unsigned | no | copia del blob al momento de la versión | B | Permanente |
| sha256 | char(64) | no | copia verificable | B | Permanente |
| status | enum('borrador','final') | no | default `borrador` | B | Permanente |
| finalized_at | timestamp | sí | — | B | Permanente |
| created_at / updated_at | timestamp | sí | — | B | — |

### `document_links`

| Columna | Tipo | Nulo | Restricción | Sens. | Retención |
|---|---|---|---|---|---|
| id | bigint unsigned | no | PK | B | Permanente |
| document_id | bigint unsigned | no | FK → documents, CASCADE | B | Permanente |
| document_version_id | bigint unsigned | sí | FK → document_versions, RESTRICT | A | Permanente |
| activity_version_id | bigint unsigned | sí | FK → activity_versions, RESTRICT | B | Permanente |
| submission_version_id | bigint unsigned | sí | FK → submission_versions, RESTRICT | A | Permanente |
| review_round_id | bigint unsigned | sí | FK → review_rounds, RESTRICT | B | Permanente |
| created_at | timestamp | sí | — | B | — |
| — | — | — | CHECK: exactamente uno de los tres vínculos | — | — |

## 4. Revisión

### `review_rounds`

| Columna | Tipo | Nulo | Restricción | Sens. | Retención |
|---|---|---|---|---|---|
| id | bigint unsigned | no | PK | B | Permanente |
| submission_version_id | bigint unsigned | no | FK → submission_versions, RESTRICT | A | Permanente |
| evaluator_id | bigint unsigned | no | FK → users, RESTRICT | M | Permanente |
| round_number | smallint unsigned | no | `>= 1` | B | Permanente |
| status | enum('asignada','en_curso','finalizada','cancelada') | no | default `asignada` | B | Permanente |
| decision | enum('aprobada','ajustes','rechazada') | sí | obligatorio al finalizar | M | Permanente |
| started_at | timestamp | sí | — | B | Permanente |
| completed_at | timestamp | sí | — | B | Permanente |
| created_at / updated_at | timestamp | sí | — | B | — |
| — | — | — | UNIQUE (submission_version_id, evaluator_id, round_number) | — | — |

### `review_comments`

| Columna | Tipo | Nulo | Restricción | Sens. | Retención |
|---|---|---|---|---|---|
| id | bigint unsigned | no | PK | B | Permanente |
| review_round_id | bigint unsigned | no | FK → review_rounds, RESTRICT | B | Permanente |
| author_id | bigint unsigned | no | FK → users, RESTRICT | M | Permanente |
| body | text | no | texto plano, escapado al mostrar | M | Permanente |
| criterion_ref | varchar(120) | sí | referencia a criterio aprobado | B | Permanente |
| document_version_id | bigint unsigned | sí | FK → document_versions, RESTRICT | A | Permanente |
| visibility | enum('autor','institucional') | no | default `autor` | M | Permanente |
| created_at | timestamp | sí | — | B | Permanente |

## 5. Publicación

### `publications`

| Columna | Tipo | Nulo | Restricción | Sens. | Retención |
|---|---|---|---|---|---|
| id | bigint unsigned | no | PK | B | Permanente |
| document_version_id | bigint unsigned | no | FK → document_versions, RESTRICT | A | Permanente |
| requested_by | bigint unsigned | no | FK → users, RESTRICT | M | Permanente |
| approved_by | bigint unsigned | sí | FK → users, RESTRICT; obligatorio al aprobar | M | Permanente |
| scope | enum('institucional','publico') | no | — | M | Permanente |
| status | enum('pendiente','aprobada','activa','revocada','rechazada') | no | default `pendiente` | M | Permanente |
| approved_at | timestamp | sí | — | B | Permanente |
| published_at | timestamp | sí | — | B | Permanente |
| revoked_at | timestamp | sí | — | M | Permanente |
| revocation_reason | varchar(255) | sí | — | M | Permanente |
| created_at / updated_at | timestamp | sí | — | B | — |

### `publication_events`

| Columna | Tipo | Nulo | Restricción | Sens. | Retención |
|---|---|---|---|---|---|
| id | bigint unsigned | no | PK | B | Permanente |
| publication_id | bigint unsigned | no | FK → publications, RESTRICT | B | Permanente |
| event | enum('creada','aprobada','rechazada','publicada','revocada','republicada') | no | — | B | Permanente |
| actor_id | bigint unsigned | sí | FK → users, RESTRICT | M | Permanente |
| occurred_at | timestamp | no | default `now()` | B | Permanente |
| reason | varchar(255) | sí | — | M | Permanente |
| metadata | json | sí | mínimo, sin contenido del documento | B | Permanente |

## 6. Auditoría

### `audit_events`

| Columna | Tipo | Nulo | Restricción | Sens. | Retención |
|---|---|---|---|---|---|
| id | bigint unsigned | no | PK | B | 24 meses (propuesta) |
| institution_id | bigint unsigned | no | FK → institutions, RESTRICT | B | Ídem |
| actor_id | bigint unsigned | sí | FK → users, RESTRICT; nulo si el actor fue eliminado | M | Ídem |
| action | varchar(80) | no | verbo estable, p. ej. `document.download` | B | Ídem |
| resource_type | varchar(60) | no | entidad afectada | B | Ídem |
| resource_id | varchar(64) | sí | id o uuid como texto | B | Ídem |
| result | enum('exito','denegado','error') | no | — | M | Ídem |
| request_id | char(36) | sí | correlación de solicitud | B | Ídem |
| ip_hash | char(64) | sí | hash con sal del sistema, no IP en claro | M | Ídem |
| metadata | json | sí | sin secretos ni contenido de archivos | B | Ídem |
| occurred_at | timestamp | no | default `now()`; índice (institution_id, occurred_at) | B | Ídem |

## 7. Sensibilidad y exportación

| Clasificación | Ejemplos | Exportación |
|---|---|---|
| B | Códigos, estados, conteos | Libre dentro de la institución |
| M | Nombre, correo, autoría, membresías | Con autorización y registro de auditoría |
| A | Evidencias, hashes de archivo, contraseñas, tokens | Nunca en bloque sin procedimiento aprobado; contraseñas y tokens **jamás** |

Toda retención marcada como «propuesta» depende de `PI-007` y de la asesoría jurídica de la
institución.
