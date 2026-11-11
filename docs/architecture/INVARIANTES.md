# Invariantes del sistema

> Un invariante es una afirmación que **no puede dejar de cumplirse**, con independencia de la
> interfaz, del orden de las peticiones o del rol del actor. Cada uno tiene una prueba prevista.
> Mientras una prueba no se haya ejecutado, la columna «Estado» dice **NO EJECUTADA**.

Estados: `PENDIENTE` (prueba escrita, no ejecutada) · `APROBADA` · `FALLIDA`.

## 1. Identidad y acceso

| ID | Invariante | Prueba prevista | Fase | Estado |
|---|---|---|---|---|
| INV-001 | Un usuario no puede autenticarse con estado `inactivo` o `bloqueado`, aunque su contraseña sea correcta | `AuthenticationTest::usuario_inactivo_no_puede_entrar` | F2 | PENDIENTE |
| INV-002 | La sesión se regenera tras autenticarse y se invalida por completo al cerrar sesión | `AuthenticationTest::rotacion_de_sesion_y_logout` | F2 | PENDIENTE |
| INV-003 | Un actor solo accede a un recurso si su membresía en el proyecto está vigente y su rol lo permite | `ProjectAccessTest::acceso_cruzado_denegado` | F2 | PENDIENTE |
| INV-004 | Cambiar un ID en la URL no concede acceso; la respuesta es 403/404 sin filtrar datos | `ProjectAccessTest::idor_por_id_ajeno` | F2 | PENDIENTE |
| INV-005 | Una persona puede tener varios roles sin que uno sobrescriba a otro | `RoleAuthorizationTest::roles_multiples` | F2 | PENDIENTE |
| INV-006 | El aprovisionamiento de usuarios no está abierto al público; solo un rol autorizado crea cuentas | `RoleAuthorizationTest::registro_abierto_deshabilitado` | F2 | PENDIENTE |

## 2. Integridad académica

| ID | Invariante | Prueba prevista | Fase | Estado |
|---|---|---|---|---|
| INV-010 | Una asignación apunta a una versión de guía concreta; publicar una versión nueva no altera asignaciones existentes | `ActivityAssignmentTest::asignacion_fija_version` | F3 | PENDIENTE |
| INV-011 | Una versión publicada de guía es inmutable: editarla crea otra versión | `ActivityAssignmentTest::version_publicada_inmutable` | F3 | PENDIENTE |
| INV-012 | Una entrega no puede asociarse a una actividad de otro proyecto ni a una asignación revocada | `ActivityAssignmentTest::asignacion_ajena_o_revocada` | F3 | PENDIENTE |
| INV-013 | Ninguna asignación se aplica de forma implícita «a la última guía» | `ActivityAssignmentTest::sin_asignacion_implicita` | F3 | PENDIENTE |
| INV-014 | Una versión de entrega finalizada no se modifica; corregir exige una nueva versión | `SubmissionVersioningTest::version_finalizada_inmutable` | F4 | PENDIENTE |
| INV-015 | Nunca existen dos versiones con el mismo número para el mismo padre | `SubmissionConcurrencyTest::dos_envios_simultaneos` | F4 | PENDIENTE |

## 3. Documentos y almacenamiento

| ID | Invariante | Prueba prevista | Fase | Estado |
|---|---|---|---|---|
| INV-020 | Ningún archivo de evidencia es accesible por URL directa ni por `public/storage` | `AuthorizedDownloadTest::sin_url_publica` | F4 | PENDIENTE |
| INV-021 | La descarga exige autorización sobre la versión exacta del documento | `AuthorizedDownloadTest::descarga_version_ajena_denegada` | F4 | PENDIENTE |
| INV-022 | El archivo servido coincide byte a byte con el cargado y su SHA-256 es igual | `DocumentIntegrityTest::checksum_y_bytes` | F4 | PENDIENTE |
| INV-023 | El nombre físico no deriva del nombre original y no admite `..`, rutas absolutas ni separadores | `PrivateUploadTest::traversal_y_nombre_opaco` | F4 | PENDIENTE |
| INV-024 | Un tipo no permitido se rechaza por MIME real, no solo por extensión | `PrivateUploadTest::mime_falso_rechazado` | F4 | PENDIENTE |
| INV-025 | Si falla la escritura del archivo, no queda metadato huérfano; si falla la transacción, el archivo se marca para limpieza | `PrivateUploadTest::fallo_parcial_sin_huerfanos` | F4 | PENDIENTE |
| INV-026 | Un blob sin referencias se marca `huerfano` y no se borra sin verificación | `DocumentIntegrityTest::reconciliacion_de_huerfanos` | F4 | PENDIENTE |

## 4. Revisión y publicación

| ID | Invariante | Prueba prevista | Fase | Estado |
|---|---|---|---|---|
| INV-030 | Una revisión queda ligada a una versión concreta y no se recalcula al aparecer otra versión | `ReviewPermissionsTest::revision_inmutable` | F5 | PENDIENTE |
| INV-031 | Solo un evaluador asignado (o rol autorizado) registra decisión sobre esa revisión | `ReviewPermissionsTest::evaluador_no_asignado_denegado` | F5 | PENDIENTE |
| INV-032 | Los comentarios de revisión no se editan ni se borran; se añaden nuevos | `ReviewPermissionsTest::comentarios_append_only` | F5 | PENDIENTE |
| INV-033 | Un visitante anónimo solo obtiene publicaciones activas con alcance `publico` | `PublicVisibilityTest::anonimo_solo_ve_publicado` | F7 | PENDIENTE |
| INV-034 | Revocar una publicación la oculta pero conserva su historial y la evidencia original | `PublicVisibilityTest::revocacion_trazable` | F7 | PENDIENTE |
| INV-035 | Un listado público no enumera recursos privados ni revela su existencia | `PublicVisibilityTest::sin_enumeracion` | F7 | PENDIENTE |

## 5. Historia, borrado y auditoría

| ID | Invariante | Prueba prevista | Fase | Estado |
|---|---|---|---|---|
| INV-040 | Desactivar o retirar un usuario no elimina proyectos, entregas, versiones ni revisiones | `MigrationIntegrityTest::borrado_de_usuario_restringido` | F1 | PENDIENTE |
| INV-041 | Ningún `ON DELETE CASCADE` alcanza a evidencia histórica | `MigrationIntegrityTest::revisar_cascadas` | F1 | PENDIENTE |
| INV-042 | Toda acción sensible registra actor, acción, recurso, resultado e instante | `AuditIntegrityTest::evento_minimo` | F5 | PENDIENTE |
| INV-043 | La auditoría no contiene contraseñas, tokens, contenido de archivos ni datos personales innecesarios | `AuditIntegrityTest::sin_secretos` | F5 | PENDIENTE |
| INV-044 | «Última versión» se calcula por orden estable y no por un booleano mutable | `VersionSequenceTest::ultima_version_por_orden` | F1 | PENDIENTE |
| INV-045 | Las fechas se almacenan en UTC y se presentan en la zona de la institución | `MigrationIntegrityTest::zona_horaria` | F1 | PENDIENTE |

## 6. Reglas de aplicación

- El invariante se implementa **en la capa más baja posible**: `CHECK`/`UNIQUE`/FK en la base,
  transacción en la `Action`, y la `Policy` para la autorización.
- Una prueba de invariante debe incluir el caso negativo; probar solo el camino feliz no
  demuestra nada.
- Si un invariante depende de una política pendiente (`PI-###`), la estructura existe pero la
  acción permanece **deshabilitada** hasta la aprobación; se documenta en el propio test.
