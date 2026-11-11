# ERD — ARIS 2.0

> Diagrama lógico del núcleo. Las **migraciones Laravel** son la fuente de verdad; este
> diagrama se actualiza con ellas. Nombres en minúscula en la base; aquí en mayúscula por
> convención de Mermaid.
>
> Alcance: 21 entidades propias. Las tablas del framework (`password_reset_tokens`,
> `sessions`, `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`) **no** se
> duplican ni se documentan aquí.

## 1. Diagrama

```mermaid
erDiagram
    INSTITUTIONS ||--o{ INSTITUTION_MEMBERSHIPS : "tiene"
    INSTITUTIONS ||--o{ PROJECTS : "agrupa"
    INSTITUTIONS ||--o{ DOCUMENTS : "contextualiza"
    INSTITUTIONS ||--o{ AUDIT_EVENTS : "registra"

    USERS ||--o{ INSTITUTION_MEMBERSHIPS : "participa"
    USERS ||--o{ PROJECT_MEMBERSHIPS : "colabora"
    USERS ||--o{ PROJECTS : "crea"
    USERS ||--o{ ACTIVITY_VERSIONS : "publica"
    USERS ||--o{ SUBMISSIONS : "entrega"
    USERS ||--o{ SUBMISSION_VERSIONS : "escribe"
    USERS ||--o{ DOCUMENT_VERSIONS : "carga"
    USERS ||--o{ REVIEW_ROUNDS : "revisa"
    USERS ||--o{ REVIEW_COMMENTS : "comenta"
    USERS ||--o{ AUDIT_EVENTS : "actua"

    INSTITUTION_MEMBERSHIPS ||--o{ MEMBERSHIP_ROLES : "ostenta"
    ROLES ||--o{ MEMBERSHIP_ROLES : "define"

    PROJECTS ||--o{ PROJECT_MEMBERSHIPS : "asigna"
    PROJECTS ||--o{ ACTIVITIES : "contiene"
    PROJECTS ||--o{ ACTIVITY_ASSIGNMENTS : "destino"
    PROJECTS ||--o{ SUBMISSIONS : "recibe"
    PROJECTS ||--o{ DOCUMENTS : "posee"

    ACTIVITIES ||--o{ ACTIVITY_VERSIONS : "versiona"
    ACTIVITY_VERSIONS ||--o{ ACTIVITY_ASSIGNMENTS : "asigna"
    ACTIVITY_VERSIONS ||--o{ DOCUMENT_LINKS : "adjunta"

    ACTIVITY_ASSIGNMENTS ||--o{ SUBMISSIONS : "origina"
    SUBMISSIONS ||--o{ SUBMISSION_VERSIONS : "versiona"
    SUBMISSION_VERSIONS ||--o{ REVIEW_ROUNDS : "se revisa"
    SUBMISSION_VERSIONS ||--o{ DOCUMENT_LINKS : "adjunta"
    SUBMISSIONS ||--o{ DOCUMENTS : "posee"

    DOCUMENTS ||--o{ DOCUMENT_VERSIONS : "versiona"
    DOCUMENTS ||--o{ DOCUMENT_LINKS : "vincula"
    FILE_BLOBS ||--o{ DOCUMENT_VERSIONS : "almacena"
    DOCUMENT_VERSIONS ||--o{ DOCUMENT_LINKS : "referencia"
    DOCUMENT_VERSIONS ||--o{ PUBLICATIONS : "publica"

    REVIEW_ROUNDS ||--o{ REVIEW_COMMENTS : "contiene"
    REVIEW_ROUNDS ||--o{ DOCUMENT_LINKS : "adjunta"

    PUBLICATIONS ||--o{ PUBLICATION_EVENTS : "historia"

    INSTITUTIONS {
        bigint id PK
        varchar name
        varchar code UK
        enum status
        varchar timezone
    }
    USERS {
        bigint id PK
        varchar name
        varchar email UK
        varchar password
        enum status
        timestamp last_login_at
    }
    INSTITUTION_MEMBERSHIPS {
        bigint id PK
        bigint institution_id FK
        bigint user_id FK
        enum status
        date joined_at
        date left_at
    }
    ROLES {
        bigint id PK
        varchar code UK
        varchar name
        boolean is_system
    }
    MEMBERSHIP_ROLES {
        bigint id PK
        bigint institution_membership_id FK
        bigint role_id FK
        bigint assigned_by FK
    }
    PROJECTS {
        bigint id PK
        bigint institution_id FK
        varchar code UK
        varchar title
        bigint owner_id FK
        enum status
        int lock_version
    }
    PROJECT_MEMBERSHIPS {
        bigint id PK
        bigint project_id FK
        bigint user_id FK
        enum function
        date assigned_at
        date ended_at
    }
    ACTIVITIES {
        bigint id PK
        bigint project_id FK
        varchar code
        varchar title
        enum status
    }
    ACTIVITY_VERSIONS {
        bigint id PK
        bigint activity_id FK
        int version_number
        text content
        bigint author_id FK
        timestamp published_at
        char checksum
    }
    ACTIVITY_ASSIGNMENTS {
        bigint id PK
        bigint activity_version_id FK
        bigint project_id FK
        enum target_type
        varchar target_ref
        bigint target_user_id FK
        timestamp due_at
        enum status
    }
    SUBMISSIONS {
        bigint id PK
        bigint project_id FK
        bigint activity_assignment_id FK
        bigint author_id FK
        varchar title
        enum status
        int lock_version
    }
    SUBMISSION_VERSIONS {
        bigint id PK
        bigint submission_id FK
        int version_number
        bigint author_id FK
        varchar summary
        enum status
        timestamp finalized_at
    }
    FILE_BLOBS {
        bigint id PK
        char uuid UK
        varchar storage_key UK
        bigint size_bytes
        char sha256
        varchar mime
        enum status
    }
    DOCUMENTS {
        bigint id PK
        bigint institution_id FK
        bigint project_id FK
        bigint activity_id FK
        bigint submission_id FK
        enum classification
        enum status
    }
    DOCUMENT_VERSIONS {
        bigint id PK
        bigint document_id FK
        int version_number
        bigint blob_id FK
        bigint author_id FK
        varchar original_name
        char sha256
        enum status
    }
    DOCUMENT_LINKS {
        bigint id PK
        bigint document_id FK
        bigint document_version_id FK
        bigint activity_version_id FK
        bigint submission_version_id FK
        bigint review_round_id FK
    }
    REVIEW_ROUNDS {
        bigint id PK
        bigint submission_version_id FK
        bigint evaluator_id FK
        smallint round_number
        enum status
        enum decision
    }
    REVIEW_COMMENTS {
        bigint id PK
        bigint review_round_id FK
        bigint author_id FK
        text body
        varchar criterion_ref
        enum visibility
    }
    PUBLICATIONS {
        bigint id PK
        bigint document_version_id FK
        bigint published_by FK
        bigint approved_by FK
        enum scope
        enum status
        timestamp published_at
        timestamp revoked_at
    }
    PUBLICATION_EVENTS {
        bigint id PK
        bigint publication_id FK
        enum event
        bigint actor_id FK
        timestamp occurred_at
    }
    AUDIT_EVENTS {
        bigint id PK
        bigint institution_id FK
        bigint actor_id FK
        varchar action
        varchar resource_type
        varchar resource_id
        enum result
        char request_id
        timestamp occurred_at
    }
```

## 2. Cardinalidades clave

| Relación | Cardinalidad | Nota |
|---|---|---|
| Institución ↔ miembros | 1 : N | `UNIQUE (institution_id, user_id)` |
| Miembro ↔ roles | N : M vía `membership_roles` | Varios roles por persona |
| Proyecto ↔ miembros | 1 : N | Responsabilidad con vigencia (`ended_at`) |
| Actividad ↔ versiones | 1 : N | `UNIQUE (activity_id, version_number)` |
| Asignación ↔ versión de guía | N : 1 | La asignación fija la versión exacta |
| Entrega ↔ versiones | 1 : N | `UNIQUE (submission_id, version_number)` |
| Documento ↔ versiones | 1 : N | `UNIQUE (document_id, version_number)` |
| Versión de documento ↔ blob | N : 1 | Un blob puede reutilizarse (mismo checksum) |
| Versión de entrega ↔ revisiones | 1 : N | Cada ronda apunta a una versión concreta |
| Revisión ↔ comentarios | 1 : N | Comentarios append-only |
| Versión de documento ↔ publicación | 1 : N | Solo una activa por documento (regla de aplicación) |
| Publicación ↔ eventos | 1 : N | Historial append-only |

## 3. Restricciones que el diagrama no muestra

- `CHECK`: exactamente uno de (`project_id`, `activity_id`, `submission_id`) en `documents`.
- `CHECK`: exactamente uno de (`activity_version_id`, `submission_version_id`,
  `review_round_id`) en `document_links`.
- `CHECK`: `version_number >= 1` en todas las tablas versionadas.
- `CHECK`: `size_bytes > 0`, `sha256` con 64 caracteres hexadecimales.
- `UNIQUE (activity_version_id, target_type, target_ref)` en asignaciones, para que un mismo
  destinatario no reciba dos veces la misma versión de guía.
- `UNIQUE (submission_version_id, evaluator_id, round_number)` en revisiones.
- Índices: `(institution_id, status)`, `(project_id, status)`, `(project_id, created_at)`,
  `(submission_id, version_number)`, `(document_id, version_number)`,
  `(institution_id, occurred_at)` para auditoría.

## 4. Verificación del diagrama

El bloque Mermaid debe renderizar sin errores en un renderizador compatible (GitHub, VS Code,
`mmdc`). **Estado:** pendiente de renderizado automatizado en F1; no se declara verificado
hasta ejecutarlo con la herramienta disponible. La equivalencia con las migraciones se
comprobará tabla por tabla al generarlas.
