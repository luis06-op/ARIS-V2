# ADR-017 — Funciones complementarias sobre datos existentes

- Fecha: 2026-11-18.
- Estado: aceptada.

## Búsqueda de entregas

La búsqueda consulta únicamente campos que ya existen en el esquema: título y etiqueta de la
entrega, resumen de cada versión, título y contenido de la guía asignada y nombre original de
los archivos. Los filtros de estado, ficha y fecha usan columnas reales. El resultado se limita
mediante `VisibleProjects`, con las mismas reglas de pertenencia y jurisdicción que el resto
del recorrido. No se crean campos de competencias, módulos ni fases: no existe una fuente
institucional aprobada para esos datos y PI-010 sigue pendiente.

El texto se escapa para `%` y `_` antes de construir `LIKE`, y los filtros se validan en
servidor. No se expone información de entregas fuera de los proyectos permitidos.

## Visor de auditoría

Solo administradores, limitado a las instituciones que administran. Muestra fecha, acción,
actor, recurso y un subconjunto no sensible de `metadata`. No muestra direcciones IP,
justificaciones personales ni correos. Las políticas se revalúan en cada solicitud y la
institución solicitada se contrasta con la lista permitida; un valor ajeno no amplía el alcance.

El visor no altera el carácter append-only de `audit_events`. No constituye exportación ni
certificación de integridad: para ello existe el comando de verificación de integridad y el
respaldo firmado por hash.

## Importación por lotes

CSV con columna `email`, máximo 1000 filas y 1 MB. Hace vista previa antes de confirmar,
detecta correos inválidos, duplicados internos, cuentas inexistentes o inactivas y membresías
ya presentes. Solo vincula cuentas activas preexistentes: **no crea usuarios, no genera
contraseñas ni asigna roles**. Cada alta se registra en auditoría. Esta decisión evita
inventar credenciales o atribuir autoridad fuera de PI-002. La creación institucional de
cuentas y la importación con datos de identidad quedan sujetas a PI-002 y PI-009.

## Evidencia

Tres pruebas HTTP: búsqueda con aislamiento y filtros, visor con denegación por rol y
institución y ocultamiento de datos sensibles, e importación con vista previa, duplicados,
cuenta inactiva y auditoría. Suite completa: 135 pruebas y 791 aserciones.
