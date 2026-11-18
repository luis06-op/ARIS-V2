# ADR-014 — Recorrido académico cualitativo y reapertura

- **Fecha:** 2026-11-18
- **Estado:** aceptada
- **Fundamento:** PI-003 y PI-004 versión 1, aprobadas por el responsable del proyecto.

## Persistencia compatible

Se reutilizan `submissions`, `submission_versions`, `review_rounds`, `review_comments` y
`audit_events`; no se crea un segundo esquema de evidencias ni de auditoría.

| Operación | Estado de entrega | Estado de versión | Resultado interno |
|---|---|---|---|
| Enviar borrador | entregada | finalizada, finalized_at registrado | — |
| Iniciar revisión | en_revision | sin cambios | ronda en_curso |
| Cumple | revisada | sin cambios | aprobada |
| Requiere Ajustes | ajustes_solicitados | sin cambios | ajustes |
| Reabrir revisada | ajustes_solicitados | sin cambios | evaluación anterior conservada |
| Crear nuevo intento | borrador | nueva versión borrador | historial anterior conservado |

Los rótulos públicos son Enviada, Cumple y Requiere Ajustes; los códigos existentes evitan
modificar enums y registros históricos. Las fechas límite se agregan a la entrega mediante
migración nueva, nullable y sin entrada HTTP de configuración ni bloqueo por vencimiento.

## Autorización y concurrencia

Toda mutación bloquea primero la entrega padre y después relee la versión; la policy se
revalida dentro de la transacción. Solo la última versión permite avanzar el recorrido.
Enviar exige autor aprendiz vigente; revisar/reabrir exige personal autorizado por las
consultas institucionales existentes. Administrador tiene alcance institucional sin ficha;
instructor debe ser tutor vigente y evaluador debe tener jurisdicción vigente.

La ronda iniciada pertenece al revisor que la inició: otro actor no puede completar en su
nombre. No se agrega reasignación implícita si se revoca su acceso. No se activa retiro,
apelación, calificación numérica o edición de evaluaciones finalizadas.

## Auditoría y congelamiento

Envío, inicio, evaluación y reapertura generan eventos en la misma transacción que el cambio.
Si la auditoría falla, el cambio se revierte. La reapertura exige motivo no vacío y registra
`REAPERTURA_ENTREGA`, entrega, versión, actor, timestamp, IP del servidor y UUID de transacción.
El cliente no suministra la identidad ni la IP auditada.

Triggers impiden UPDATE/DELETE sobre `audit_events`, incluso mediante SQL directo. Esta
garantía no protege frente a una autoridad de base de datos capaz de eliminar triggers,
truncar o sustituir tablas; los privilegios de producción y backup/restore siguen pendientes.
La migración MySQL requiere permiso de creación de triggers y debe validarse tras B3.

Las rutas no permiten alterar contenido enviado ni cambiar revisiones finalizadas. Una nueva
versión empieza sin archivos y la evaluación previa permanece ligada a su versión original.
La protección del contenido académico se aplica en acciones/policies; no se certifica aquí
inmutabilidad frente a administradores SQL. Los archivos mantienen la verificación SHA-256.

## Evidencia

Pruebas HTTP de envío, doble envío, corrección, reapertura y doble ejecución; retroalimentación
obligatoria y escapada; rechazo de resultados numéricos, actor ajeno y jurisdicción revocada;
conservación de versiones/revisiones y descarga histórica. Pruebas de SQL de auditoría,
rollback transaccional ante fallo de auditoría y plazos vencidos deliberadamente inactivos.
SQLite en memoria exclusivamente. Concurrencia real y migraciones MySQL siguen pendientes.
