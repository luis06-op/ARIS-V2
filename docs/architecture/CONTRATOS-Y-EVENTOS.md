# Contratos de casos de uso y eventos

> Los casos de uso viven en `app/Actions` y son la única puerta de escritura del dominio. Un
> controlador no crea versiones, no calcula números de versión y no decide estados.

## 1. Contrato general de una Action

```text
Entrada:  DTO validado + actor (User) + contexto (institution/project)
Salida:   recurso creado/actualizado o excepción de dominio
Garantías:
  - autorización delegada a la Policy (nunca asumida)
  - una transacción por unidad de trabajo
  - validación de estado de origen dentro de la transacción
  - auditoría en el mismo commit cuando la acción es sensible
  - idempotencia donde se indique; en el resto, conflicto explícito
```

Excepciones de dominio: `DomainRuleViolation`, `VersionConflict`, `PermissionDenied`,
`InvalidStateTransition`. Se traducen a 422/409/403 sin exponer trazas.

## 2. Catálogo de Actions del MVP

| Action | Entrada | Efecto | Invariantes |
|---|---|---|---|
| `CreateProject` | institución, título, código | Crea proyecto en `borrador` + membresía `lider` | INV-003 |
| `UpdateProject` | proyecto, campos, `lock_version` | Actualiza con control optimista | INV-044 |
| `AssignProjectMember` | proyecto, usuario, función | Crea membresía con vigencia | INV-003 |
| `CreateActivity` | proyecto, título, código | Crea actividad `borrador` | INV-010 |
| `PublishActivityVersion` | actividad, contenido | Crea `activity_versions` y publica | INV-011, INV-015 |
| `AssignActivity` | versión, destinatario, plazo | Crea asignación explícita | INV-010, INV-012, INV-013 |
| `CreateSubmission` | asignación elegible, autor | Crea entrega `borrador` | INV-012 |
| `SubmitNewVersion` | entrega, resumen, archivos | Bloquea padre, crea versión | INV-014, INV-015 |
| `CloseSubmission` | entrega | Pasa a estado final según `PI-003` | INV-014 |
| `StorePrivateFile` | archivo, dueño lógico | Cuarentena + blob + verificación | INV-020…INV-025 |
| `AttachDocumentVersion` | documento, blob | Crea versión y vínculo | INV-021, INV-022 |
| `AuthorizeDocumentDownload` | documento, versión, actor | Autoriza o deniega | INV-020, INV-021 |
| `StartReviewRound` | versión de entrega, evaluador | Crea ronda sobre esa versión | INV-030, INV-031 |
| `RecordReviewDecision` | ronda, decisión, comentarios | Finaliza ronda, conserva historial | INV-030, INV-032 |
| `PublishDocumentVersion` | versión, alcance | Crea solicitud de publicación | INV-033, INV-034 |
| `RevokePublication` | publicación, motivo | Revoca sin borrar historia | INV-034 |

Ninguna Action se implementa antes de que exista su fase, su prueba y su política.

## 3. Eventos de dominio (solo con uso real)

| Evento | Emisor | Consumidor previsto | Nota |
|---|---|---|---|
| `SubmissionVersionCreated` | `SubmitNewVersion` | Auditoría, línea de tiempo | No dispara notificaciones en el MVP |
| `ReviewCompleted` | `RecordReviewDecision` | Auditoría, estado de la entrega | No modifica versiones previas |
| `DocumentVersionStored` | `AttachDocumentVersion` | Reconciliación de storage | Se emite después del commit |
| `PublicationRevoked` | `RevokePublication` | Auditoría | Conserva evidencia original |

Regla: un evento se añade cuando existe un consumidor concreto y probado. No se crea un bus de
eventos para «el futuro».

## 4. Diferencia entre auditoría y eventos de dominio

| Aspecto | `audit_events` | Eventos de dominio |
|---|---|---|
| Propósito | Trazabilidad de quién hizo qué y con qué resultado | Reacción dentro de la aplicación |
| Persistencia | Tabla append-only, consultable | En memoria / cola |
| Contenido | Actor, acción, recurso, resultado, instante | Datos del cambio |
| Uso | Investigación, soporte, cumplimiento | Efectos secundarios controlados |
| Retención | Política `PI-007` | No aplica |

No se registran eventos de dominio como sustituto de la auditoría ni viceversa.

## 5. Idempotencia y reintentos

- Cargas y envíos de versión **no** son idempotentes por defecto: un reintento del cliente
  puede duplicar. Se admite una clave de idempotencia opcional (`request_id`) en F4.
- La creación de auditoría tolera reintentos sin duplicar la operación de negocio.
- Las tareas diferidas usan la cola `database`; si el hosting no tiene cron, existirá un
  fallback síncrono documentado.
