# Decisiones institucionales pendientes (PI)

> Ninguna de estas decisiones puede tomarla un agente. El sistema prepara la estructura, las
> pruebas y, cuando aplica, la interfaz de configuración; pero **no activa el comportamiento
> con valores por defecto inventados**. Mientras una política siga `PENDIENTE`, la acción
> sensible se deniega y se informa el motivo.
>
> Estados posibles: `PENDIENTE` · `PROPUESTA` · `APROBADA` · `RECHAZADA`.
> Cada aprobación debe registrar fecha, responsable y versión del acuerdo.

Persona responsable sugerida: **rectoría o coordinación académica** de la institución, con
apoyo jurídico para `PI-007`, `PI-008` y `PI-009`. El nombre concreto lo define la institución.

---

## PI-001 — Vínculo entre actividad y entrega

**Pregunta:** ¿toda entrega debe estar asociada explícitamente a una actividad asignada y a
una versión concreta de la guía, o se admiten entregas independientes?

**Por qué importa:** define si ARIS puede garantizar «esto se entregó contra esta guía y esta
versión». Afecta validaciones, mensajes de error y la trazabilidad que promete el producto.

**Alternativas:**
1. Obligatorio siempre (recomendado técnicamente): máxima trazabilidad, menos flexibilidad.
2. Obligatorio con excepción autorizada y registrada en auditoría.
3. Libre: menor fricción, pero rompe la promesa de trazabilidad.

**Recomendación:** opción 2, con la excepción como acción auditada y limitada a un rol.
**Impacto si no se decide:** el flujo de creación de entrega queda construido pero deshabilitado
para proyectos reales.
**Estado:** PENDIENTE.

---

## PI-002 — Membresías y autoridad

**Pregunta:** ¿quién crea usuarios, asigna roles institucionales, designa responsables de
proyecto y autoriza evaluaciones?

**Por qué importa:** determina el modelo de confianza completo. Un error aquí produce escalada
de privilegios.

**Alternativas:**
1. Solo administración institucional crea cuentas; instructores asignan dentro de sus proyectos.
2. Aprovisionamiento por lotes desde un archivo institucional (CSV) con validación.
3. Autoregistro con aprobación posterior (mayor riesgo de datos no autorizados).

**Recomendación:** opción 1 en el MVP; opción 2 más adelante si la institución lo necesita.
**Impacto si no se decide:** registro abierto deshabilitado por defecto.
**Estado:** PENDIENTE.

---

## PI-003 — Estados académicos y transiciones

**Pregunta:** ¿cuándo puede entregarse, corregirse, cerrarse, reabrirse o retirarse una
entrega?

**Por qué importa:** define la máquina de estados (`SubmissionStateMachine`) y qué acciones
ve cada rol. Cambiarla después afecta datos históricos.

**Alternativas:**
1. Ciclo mínimo: `borrador → entregada → en_revision → ajustes_solicitados → revisada`, con
   reapertura auditada y límite de tiempo.
2. Ciclo ampliado con cierre por periodo académico.
3. Sin estados: solo versiones (pierde significado pedagógico).

**Recomendación:** opción 1, con plazos opcionales que la institución configura.
**Impacto si no se decide:** solo se implementan transiciones reversibles dentro del borrador.
**Estado:** PENDIENTE.

---

## PI-004 — Rúbricas y resultados

**Pregunta:** ¿se evalúa con escala numérica, niveles descriptivos o ambos? ¿Hay pesos,
apelaciones y límites de edición?

**Por qué importa:** una escala inventada produce datos que parecen oficiales y no lo son.

**Alternativas:**
1. Revisión descriptiva sin nota (recomendado para el MVP).
2. Niveles descriptivos configurables (p. ej. inicial / en proceso / logrado / destacado).
3. Escala numérica con pesos y rúbrica por criterio.

**Recomendación:** empezar por la opción 1; la estructura `rubrics/rubric_criteria/scores`
queda documentada pero **no se crea** hasta que exista decisión.
**Impacto si no se decide:** no hay campo de calificación en la interfaz.
**Estado:** PENDIENTE.

---

## PI-005 — Acceso histórico

**Pregunta:** ¿quién puede consultar versiones y revisiones después de finalizar el curso, del
retiro de una persona o del cierre de un proyecto?

**Por qué importa:** afecta la privacidad de estudiantes y la memoria institucional.

**Alternativas:**
1. El proyecto conserva su historial para instructor y administrador; el aprendiz conserva
   acceso a su propio trabajo.
2. Acceso congelado al cierre, salvo exportación autorizada.
3. Archivado con anonimización tras un plazo definido.

**Recomendación:** opción 1, con revisión a los 12 meses; combinable con `PI-007`.
**Impacto si no se decide:** el acceso se limita a la vigencia de la membresía.
**Estado:** PENDIENTE.

---

## PI-006 — Publicación y licencias

**Pregunta:** ¿quién autoriza publicar un trabajo, con qué licencia, con qué datos visibles y
cómo se revoca?

**Por qué importa:** publicar datos de menores o material sin permiso es un riesgo legal y
ético irreversible.

**Alternativas:**
1. Publicación con autorización escrita del autor y del acudiente cuando aplique, aprobada
   por coordinación; licencia explícita por obra.
2. Publicación institucional por defecto (requiere base legal y aviso claro).
3. Sin repositorio público en el MVP.

**Recomendación:** opción 1; si no hay proceso de autorización definido, opción 3.
**Impacto si no se decide:** el repositorio público no se activa; solo catálogo interno.
**Estado:** PENDIENTE.

---

## PI-007 — Retención, eliminación y derechos de los titulares

**Pregunta:** ¿cuánto se conserva, qué se anonimiza, cómo se atiende una solicitud de supresión
y qué pasa con los respaldos?

**Por qué importa:** los respaldos hacen que «borrar» no sea trivial; la ley exige procedimiento.

**Alternativas:**
1. Retención por ciclo académico + archivo con anonimización a los N años.
2. Retención indefinida con acceso restringido.
3. Borrado a solicitud con anonimización de evidencia pedagógica y conservación de metadatos.

**Recomendación:** definir con asesoría jurídica; técnicamente, separar dato personal de
evidencia pedagógica para permitir anonimización sin destruir trazabilidad.
**Impacto si no se decide:** no se implementa purga automática; solo exportación manual.
**Estado:** PENDIENTE.

---

## PI-008 — Recuperación de cuentas

**Pregunta:** ¿por qué canal se recupera una cuenta y qué ocurre si el estudiante no tiene
correo institucional?

**Por qué importa:** un flujo mal definido es un vector de toma de cuentas.

**Alternativas:**
1. Correo institucional con token de un solo uso y expiración corta.
2. Restablecimiento presencial por administración con verificación de identidad.
3. Códigos de recuperación entregados al alta.

**Recomendación:** opción 1 como principal y opción 2 como respaldo auditado.
**Impacto si no se decide:** solo se implementa el flujo estándar del framework por correo, con
envío real deshabilitado (mailer `log`).
**Estado:** PENDIENTE.

---

## PI-009 — Institución, menores y base legal

**Pregunta:** ¿cuál es la base legal del tratamiento, qué avisos se publican y quién responde
ante los titulares?

**Por qué importa:** los datos de menores exigen autorización y finalidad explícitas.

**Alternativas:** política de tratamiento propia, aviso de privacidad por rol, consentimiento
informado por proyecto.

**Recomendación:** las tres, revisadas por la institución y su asesoría jurídica.
**Impacto si no se decide:** no se habilita un piloto con datos reales de estudiantes.
**Estado:** PENDIENTE.

---

## PI-010 — Almacenamiento, cuotas y formatos

**Pregunta:** ¿qué tamaño máximo por archivo, cuota por proyecto y formatos se aprueban? ¿Se
admite video?

**Por qué importa:** define la capacidad real del hosting y el costo operativo.

**Alternativas:**
1. PDF, DOCX, XLSX, JPG, PNG; 10 MB por archivo; cuota por proyecto configurable.
2. Añadir video MP4 con límite alto (requiere evaluar antivirus, ancho de banda y respaldo).
3. Solo PDF e imágenes.

**Recomendación:** opción 1 en el MVP; video solo tras evaluación técnica y de privacidad.
**Impacto si no se decide:** se aplican límites conservadores y video permanece deshabilitado.
**Estado:** PENDIENTE.

---

## Registro de aprobaciones

| PI | Estado | Fecha | Responsable | Observaciones |
|---|---|---|---|---|
| PI-001 | PENDIENTE | — | — | — |
| PI-002 | PENDIENTE | — | — | — |
| PI-003 | PENDIENTE | — | — | — |
| PI-004 | PENDIENTE | — | — | — |
| PI-005 | PENDIENTE | — | — | — |
| PI-006 | PENDIENTE | — | — | — |
| PI-007 | PENDIENTE | — | — | — |
| PI-008 | PENDIENTE | — | — | — |
| PI-009 | PENDIENTE | — | — | — |
| PI-010 | PENDIENTE | — | — | — |
