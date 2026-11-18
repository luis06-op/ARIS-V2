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
**Estado:** **APROBADA por el responsable del proyecto el 2026-11-17** (alternativa 2). Toda
entrega debe declarar la asignación y, por lo tanto, la versión exacta de la guía. Se admite una
excepción solo si la registra un actor autorizado como evento de auditoría
(`submission.unlinked_exception`) con motivo; el rol concreto que puede autorizar depende de
`PI-002`. En la ampliación del acuerdo del 2026-11-17, el responsable autoriza al instructor
del proyecto, al administrador y al evaluador. PI-002 versión 4 confirma que el evaluador solo
puede autorizar dentro de su cohorte y especialidad asignadas. Desde el 18-11-2026 la interfaz
permite autorizar un borrador sin guía para un aprendiz vigente del proyecto, con auditoría
completa. Esta autorización no define plazos ni transiciones de PI-003.

**Ampliación aprobada (versión 2, 2026-11-17, responsable del proyecto):** la justificación es
obligatoria, con un mínimo de 20 caracteres. El evento debe registrar el ID del autorizador,
el ID del aprendiz o proyecto beneficiado, la fecha y hora exactas, la justificación, la IP y
un código de transacción. La mención de auditoría en MySQL describe el motor objetivo; no
autoriza conexiones ni migraciones mientras B3 siga pendiente. Estos requisitos están
implementados y probados en SQLite en memoria mediante la acción y ruta de excepción PI-001
(18-11-2026); el motor MySQL sigue pendiente.

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
**Estado vigente:** **APROBADA — versión 4, 2026-11-18, responsable del proyecto.** Las
ampliaciones siguientes conservan la evolución del acuerdo; la versión 4 prevalece sobre
cualquier facultad anterior incompatible.

**Acuerdo inicial (2026-11-17, aprobación parcial):** se aprueba el
**autoregistro abierto** (alternativa 3): cualquier persona puede crear su cuenta con nombre,
correo y contraseña. Las cuentas nuevas no reciben rol institucional ni acceso a proyectos;
la institución sigue siendo la única que asigna roles, designa responsables de proyecto y
autoriza evaluaciones, y esa parte de la política continúa **PENDIENTE**. La verificación de
correo queda pendiente hasta que exista un servicio de envío configurado; mientras tanto, una
cuenta sin membresía no tiene privilegios.

### Ampliación del acuerdo — versión 2 (2026-11-17)

**Responsable:** responsable del proyecto, mediante instrucción expresa en la sesión.
**Estado:** PARCIAL; quedan aprobadas las siguientes facultades y pendientes las precisiones
de alcance enumeradas al final. El registro abierto aprobado se conserva.

| Rol institucional aprobado | Facultades definidas por el responsable |
|---|---|
| Administrador | Control total del sistema, configuración de seguridad, almacenamiento y auditoría; gestión de usuarios y asignación, modificación y revocación de roles; creación de proyectos y asignación de instructores; vinculación de aprendices mediante listas institucionales |
| Evaluador (antes Coordinador) | Analítica global, indicadores, auditoría y reportes; asignación de instructores tutores; revisión y evaluación transversal dentro de su cohorte o especialidad técnica y de entregas de sustentación final |
| Instructor | Gestión de proyectos asignados; creación de proyectos cuando esté autorizado; vinculación de aprendices a sus proyectos; evaluación y retroalimentación de sus grupos y entregas asignados |
| Aprendiz | Carga de evidencias, consulta del estado y calificaciones y control de versiones de su proyecto |

**Asignación de roles:** exclusiva del administrador según la definición general del acuerdo.
La referencia posterior a «Administrador (o Coordinación Académica)» para autorizar evaluadores
requiere aclaración antes de conceder a otra autoridad capacidad de asignación. «Coordinación
Académica» no se convierte automáticamente en un quinto rol.

**Autoridad inicial:** el responsable solicita una cuenta inicial de administrador creada durante
la instalación mediante `setup_admin.php`, con credenciales entregadas al Coordinador del
Programa de Articulación con la Media Técnica (SENA / CFMA) o al administrador de TI designado
por la institución. Desde esa autoridad se habilitan cuentas individuales de administradores
e instructores. Implementado el 18-11-2026 como entrada CLI interactiva, con contraseñas ocultas
y bloqueo de reinicialización; probado únicamente con SQLite en memoria. No se creó una cuenta
real ni se versionaron credenciales. El despliegue y B3 siguen sujetos a autorización
explícita; este acuerdo no permite ejecutar operaciones sobre MySQL.

**Precisiones que quedaron pendientes en la versión 2 (resueltas en la versión 4):**

1. Si Coordinación Académica propone evaluadores y el administrador asigna el rol, o si se
   pretende autorizar otra vía de asignación, modificando la exclusividad indicada arriba.
2. Si la asignación de tutores y la excepción PI-001 por un evaluador se limitan también a
   los proyectos de su cohorte o especialidad autorizada.

### Precisiones aprobadas — versión 3 (2026-11-17)

**Responsable:** responsable del proyecto, mediante instrucción expresa en la sesión.
**Estado:** PARCIAL; quedan pendientes únicamente las dos precisiones anteriores.

- **Creación de proyectos por instructores:** el administrador es la única autoridad que
  habilita el permiso explícito `can_create_projects`, a solicitud de Coordinación Académica
  del SENA. La creación requiere permiso activo y vinculación del instructor a la especialidad
  del proyecto. Sin ambas condiciones, el servidor debe denegar la creación. No se introduce
  un quinto rol de «Instructor Líder de Ficha»: el acuerdo permite expresar la habilitación
  mediante el permiso explícito.
- **Jurisdicción de evaluadores:** el administrador asigna una matriz de jurisdicción por
  especialidad técnica/programa y número de ficha/cohorte académica. No existe alcance global
  predeterminado. La visibilidad, auditoría y evaluación se limitan estrictamente a proyectos
  y entregables de las fichas y especialidades formalmente asignadas. Esta regla se aplica
  en las consultas de listados, revisión y analítica, además de la autorización individual
  de cada recurso y versión. La definición no concede una excepción de alcance a las
  sustentaciones finales.
- Los números de ficha y nombres de programas usados como ejemplos en la instrucción no
  constituyen datos autorizados para poblar la aplicación.

Al registrar esta versión 3, el permiso y la matriz de jurisdicción aún no estaban implementados.
Su implementación se incorporó el 18-11-2026, después de la confirmación versión 4.
No se activa calificación mientras PI-004 siga pendiente.

### Confirmación definitiva — versión 4 (2026-11-18)

**Responsable:** responsable del proyecto, mediante confirmación expresa en la sesión.
**Estado:** APROBADA.

- **Evaluadores:** Coordinación Académica propone candidatos (docentes o jurados); única y
  exclusivamente el administrador asigna, activa y gestiona el rol en la plataforma.
- **Tutores:** solo el administrador puede asignar, cambiar o remover tutores. Se revoca
  expresamente la facultad de asignación atribuida al evaluador en la versión 2. La jurisdicción
  del evaluador delimita los proyectos y tutores que puede visualizar, auditar y evaluar;
  no concede gestión de tutores.
- **Excepción PI-001:** el evaluador solo puede autorizarla para proyectos dentro de su cohorte
  y especialidad asignadas, con justificación obligatoria y auditoría conforme a PI-001 versión 2.
  Se conservan los autorizadores previamente aprobados: instructor del proyecto y administrador.
- Se mantienen los cuatro roles y la habilitación explícita de creación de proyectos por
  instructores de la versión 3, así como el autoregistro sin privilegios y la autoridad inicial.

La aprobación resuelve la política de autoridad; no equivale a completar toda F2. Desde el
18-11-2026 hay administración HTTP de membresías, roles, especialidades/fichas, habilitación de
instructores y jurisdicciones. Las policies protegen proyectos, guías, versiones de entrega y
descargas privadas. Solo el administrador gestiona tutores; el instructor tutor vincula
aprendices de la institución. El aprovisionamiento inicial CLI está implementado pero no se ha
ejecutado en una instalación real. Faltan la importación institucional por lotes y la verificación
de correo, entre otros pendientes de fase.

**Límites del acuerdo:** las facultades describen la autoridad institucional, no una aprobación
de funcionalidades dependientes de otras políticas. Las calificaciones, transiciones académicas,
acceso histórico, publicación, formatos adicionales y cuotas siguen sujetos a PI-003, PI-004,
PI-005, PI-006 y PI-010. El control administrativo sobre MySQL no levanta B3 ni concede al
agente permiso para tocar bases existentes. Los permisos deben comprobarse en servidor sobre
el recurso y su versión exacta, con membresía vigente; las rutas sensibles se exponen únicamente
con reglas de alcance explícitas y pruebas negativas.

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
**Estado:** **APROBADA por el responsable del proyecto el 2026-11-17** (alternativa 1: correo con
token de un solo uso y expiración corta). Se implementa el flujo estándar de Laravel con el mailer
`log` del entorno local; **no se envían correos reales** hasta que la institución configure un
servicio y resuelva el respaldo presencial. La tasa de solicitudes se limita en la ruta.

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
**Estado:** PENDIENTE. Mientras tanto se implementaron esos límites conservadores en
`config/aris.php` (PDF/DOCX/XLSX/JPG/PNG, 10 MB, sin vídeo); no se ha aprobado una cuota por
proyecto ni ningún formato adicional.

---

## PI-011 — Titularidad y publicación del código

**Pregunta:** ¿autoriza el SENA que el código y la documentación de ARIS 2.0 se mantengan en un
repositorio **público** y bajo qué condiciones (licencia, aceptación de contribuciones, uso por
terceros)?

**Por qué importa:** la obra es titularidad del SENA. Un repositorio público sin licencia no
cede derechos, pero puede generar confusión y no permite contribuciones externas legítimas.

**Alternativas:**
1. Mantener el repositorio público con aviso de derechos y sin licencia (situación actual,
   recomendada solo como transición).
2. Publicar bajo una licencia institucional definida por el SENA.
3. Restringir el repositorio a privado hasta que exista formalización.

**Recomendación:** obtener confirmación escrita; si no llega, pasar a la opción 3.
**Impacto si no se decide:** no se aceptan contribuciones externas, no se declara reutilización
permitida y el repositorio no se usa como base de productos de terceros.
**Estado:** PENDIENTE — decisión del usuario ya registrada (titularidad SENA, repositorio
público); falta la formalización institucional.

---

## Registro de aprobaciones

| PI | Estado | Fecha | Responsable | Observaciones |
|---|---|---|---|---|
| PI-001 | APROBADA | 2026-11-17 | Responsable del proyecto | Versión 2: instructor del proyecto, administrador o evaluador; motivo de al menos 20 caracteres, beneficiario, fecha, IP y código de transacción; PI-002 versión 4 confirma la jurisdicción del evaluador |
| PI-002 | APROBADA | 2026-11-18 | Responsable del proyecto | Versión 4: Coordinación propone evaluadores y solo administrador gestiona roles y tutores; evaluador autoriza PI-001 únicamente dentro de su cohorte y especialidad |
| PI-003 | PENDIENTE | — | — | — |
| PI-004 | PENDIENTE | — | — | — |
| PI-005 | PENDIENTE | — | — | — |
| PI-006 | PENDIENTE | — | — | — |
| PI-007 | PENDIENTE | — | — | — |
| PI-008 | APROBADA | 2026-11-17 | Responsable del proyecto | Recuperación por correo con token estándar de Laravel; envío real deshabilitado (mailer log) |
| PI-009 | PENDIENTE | — | — | — |
| PI-010 | PENDIENTE | — | — | — |
| PI-011 | PENDIENTE | — | — | Titularidad SENA declarada; falta formalizar publicación pública |
