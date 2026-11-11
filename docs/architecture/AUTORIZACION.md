# Autorización

> Principio único: **el servidor decide**. La interfaz puede ocultar controles, pero ningún
> permiso se concede por nombre de pantalla, campo del formulario, menú visible o parámetro de
> la petición.

## 1. Capas de autorización

1. **Rol institucional** (`roles` + `membership_roles`) — habilita un tipo de acción:
   crear proyectos, publicar guías, revisar, administrar.
2. **Responsabilidad en el proyecto** (`project_memberships` con `function` y vigencia) —
   acota el rol al proyecto concreto.
3. **Relación con el recurso** — autoría, asignación de revisión o publicación.
4. **Estado del recurso** — una guía archivada, una entrega retirada o una membresía inactiva
   cambian el resultado.

Un permiso se concede solo si las cuatro capas lo permiten.

## 2. Políticas por recurso (previstas en F2)

| Policy | Métodos | Regla base |
|---|---|---|
| `InstitutionPolicy` | `view`, `update` | Pertenencia activa + rol `administrador` |
| `UserPolicy` | `view`, `update`, `deactivate` | Propio perfil; administrador para terceros |
| `ProjectPolicy` | `view`, `create`, `update`, `archive`, `manageMembers` | `lider`/`instructor` vigente; creación según rol institucional |
| `ActivityPolicy` | `view`, `create`, `publish`, `archive` | Instructor responsable del proyecto |
| `ActivityVersionPolicy` | `view`, `publish` | Autor o instructor del proyecto; publicada = solo lectura |
| `ActivityAssignmentPolicy` | `view`, `create`, `revoke` | Instructor del proyecto destino |
| `SubmissionPolicy` | `view`, `create`, `update`, `withdraw` | Autor vigente; instructor del proyecto; evaluador asignado |
| `SubmissionVersionPolicy` | `view`, `create`, `finalize` | Autor de la entrega; inmutable tras finalizar |
| `DocumentPolicy` | `view`, `upload`, `archive` | Autor o instructor del proyecto; clasificación aplicada |
| `DocumentVersionPolicy` | `view`, `download` | Autorización sobre **esa versión** |
| `ReviewPolicy` | `view`, `assign`, `decide` | Evaluador asignado; instructor puede asignar |
| `PublicationPolicy` | `view`, `request`, `approve`, `revoke` | Solicita instructor; aprueba rol autorizado (`PI-006`) |

## 3. Reglas de decisión

1. **Denegar por defecto.** Si falta cualquier condición, se deniega.
2. **No filtrar existencia.** Para un actor sin acceso, un recurso privado responde igual que
   uno inexistente (404) o 403 uniforme, sin metadatos.
3. **Sin autorización en el cliente.** El frontend recibe un booleano para la UI; el backend
   vuelve a evaluar en cada petición.
4. **Autorización y consulta en la misma petición.** No se consulta el recurso, se responde, y
   luego se comprueba; se comprueba antes de materializar la respuesta.
5. **Vigencia temporal.** Una membresía con `ended_at` en el pasado no autoriza, aunque el rol
   institucional siga activo.
6. **Acciones masivas.** Cada elemento se autoriza individualmente; no se confía en el filtro
   de la lista.
7. **Descargas.** La policy recibe `document` y `document_version`; nunca se sirve por ID de
   blob.

## 4. Matriz de pruebas negativas obligatorias (F2)

| Escenario | Resultado esperado |
|---|---|
| Aprendiz A solicita entrega del aprendiz B | 403/404, sin datos |
| Instructor no asignado solicita proyecto ajeno | 403/404 |
| Evaluador solicita revisión no asignada | 403/404 |
| Anónimo solicita cualquier recurso interno | Redirección a login o 404 |
| Usuario autenticado cambia `project_id` en el payload | Rechazo de validación + policy |
| Instructor de proyecto A asigna actividad en proyecto B | 403 |
| Administrador sin función de soporte abre documento privado | 403 salvo política expresa |
| Solicitud con `_method=DELETE` a ruta de solo lectura | 405 |
| Descarga con ruta relativa manipulada | 404, sin lectura de archivo |

## 5. Relación con la auditoría

Toda denegación sensible (descarga, cambio de permisos, publicación, exportación) se registra
en `audit_events` con `result = denegado`, sin incluir el contenido solicitado ni datos
personales innecesarios. La auditoría **no** se usa para vigilar la actividad de estudiantes:
registra operaciones sobre recursos, no comportamiento individual.
