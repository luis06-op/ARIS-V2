# Roles y recorridos

> Este documento describe **qué necesita cada rol**, no qué permisos tendrá en producción.
> Los permisos efectivos dependen de las políticas `PI-002`, `PI-003`, `PI-005` y `PI-006`,
> hoy pendientes de aprobación.

## 1. Roles funcionales

| Rol | Qué es | Qué hace en ARIS |
|---|---|---|
| Aprendiz / estudiante | Autor principal del trabajo | Entiende sus proyectos y tareas, entrega evidencias, recibe retroalimentación, consulta su historial y conserva autoría |
| Instructor / docente / tutor | Responsable pedagógico | Publica guías, asigna actividades, observa avances, revisa versiones y retroalimenta |
| Evaluador / coordinador | Revisor autorizado | Comprueba hitos, revisiones y resultados sin alterar evidencia histórica |
| Administrador institucional | Gestión de la plataforma | Usuarios, membresías, configuración, exportaciones, retención y auditoría |
| Visitante / investigador | Público externo | Consulta únicamente material expresamente publicado |

**Principio:** el rol habilita un tipo de acción; **la relación con el recurso** determina si
la acción está autorizada. Un instructor no ve automáticamente todos los proyectos: solo
aquellos donde tiene responsabilidad vigente. Un administrador no obtiene por defecto lectura
de cualquier documento confidencial: solo accede por función legítima de soporte y política
expresa.

Una persona puede tener **varios roles**: instructor en un proyecto, evaluador en otro y
aprendiz en un tercero. Por eso el modelo usa `membership_roles` (roles institucionales) y
`project_memberships` (responsabilidad por proyecto), no un único campo `role` en `users`.

## 2. Recorrido del aprendiz

1. Inicia sesión y ve **sus** proyectos y entregas próximas (no un panel de métricas vacías).
2. Abre una actividad asignada y lee la **versión exacta** de la guía.
3. Crea una entrega o una nueva versión con descripción del cambio.
4. Sube evidencias privadas (PDF, DOCX, XLSX, JPG, PNG) con validación de tamaño y tipo.
5. Consulta la línea de tiempo: versiones, fechas, autoría y revisiones recibidas.
6. Lee la retroalimentación asociada a la versión revisada, no a «la última».

Estados de la entrega visibles para el aprendiz: borrador propio, entregada, en revisión,
ajustes solicitados, revisada. La definición exacta de cada transición depende de `PI-003`.

## 3. Recorrido del instructor

1. Crea o actualiza un proyecto y asigna responsables (`PI-002`).
2. Publica una guía como **nueva versión**; la anterior queda inmutable.
3. Asigna la actividad a un proyecto, grupo o usuario concreto.
4. Revisa la bandeja de entregas del proyecto y abre la versión exacta a evaluar.
5. Registra decisión y comentarios; el historial no se reescribe.
6. Propone una versión para publicación; publicar exige aprobación (`PI-006`).

## 4. Recorrido del evaluador

1. Ve solo las revisiones que le fueron asignadas.
2. Consulta la evidencia exacta, sus metadatos y el historial de versiones.
3. Registra observaciones y decisión según criterios aprobados (`PI-004`).
4. No puede modificar entregas, archivos ni revisiones previas.

## 5. Recorrido del administrador

1. Gestiona membresías y roles institucionales con registro de auditoría.
2. Configura parámetros permitidos (zona horaria, límites de carga, retención aprobada).
3. Consulta el estado del sistema y las verificaciones de integridad.
4. Ejecuta exportaciones y tareas de conservación con confirmación explícita.
5. Las acciones destructivas requieren confirmación reforzada y quedan auditadas.

## 6. Recorrido del visitante

1. Llega a la landing pública: qué es ARIS y cómo funciona el ciclo de un proyecto.
2. Consulta el repositorio público: **solo** material publicado y aprobado.
3. No puede inferir la existencia de recursos internos: las respuestas no distinguen entre
   «no existe» y «no autorizado».

## 7. Matriz de capacidades (nivel de producto)

Marcas: **Sí** = previsto en el MVP · **Cond.** = depende de política aprobada · **No** = fuera
de alcance.

| Capacidad | Aprendiz | Instructor | Evaluador | Admin | Visitante |
|---|---|---|---|---|---|
| Ver proyecto propio | Sí | Sí | Cond. | Cond. | No |
| Crear proyecto | No | Sí | No | Cond. | No |
| Publicar versión de guía | No | Sí | No | No | No |
| Asignar actividad | No | Sí | No | No | No |
| Crear entrega / versión | Sí (proyecto propio) | Cond. | No | No | No |
| Subir evidencia | Sí (entrega propia) | Cond. | No | No | No |
| Descargar evidencia | Sí (propia) | Sí (proyecto asignado) | Sí (revisión asignada) | Cond. | No |
| Registrar revisión | No | Sí | Sí (asignada) | No | No |
| Publicar en repositorio | No | Propone | No | Aprueba (Cond.) | No |
| Ver publicado | Sí | Sí | Sí | Sí | Sí |
| Gestionar usuarios | No | No | No | Sí | No |
| Ver auditoría | No | Cond. | No | Sí | No |

Cada fila se convierte en una política del servidor (`app/Policies`) y en pruebas positivas y
negativas en F2–F5. La interfaz solo oculta; **nunca** autoriza.
