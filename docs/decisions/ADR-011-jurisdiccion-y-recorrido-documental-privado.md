# ADR-011 — Jurisdicción y recorrido documental privado

- **Fecha:** 2026-11-18
- **Estado:** aceptada
- **Políticas:** PI-002 versión 4; PI-001 versión 2, en `docs/product/DECISIONES-INSTITUCIONALES.md`.
- **Continuidad:** amplía `ADR-010-autorizacion-institucional-explicita.md`.

## Contexto

El usuario autorizó continuar las fases con B3 pendiente. La jurisdicción del evaluador y la
habilitación de creación del instructor ya están aprobadas. Las transiciones académicas,
calificaciones y publicación pública no lo están. Se necesita un recorrido privado verificable
sin activar esos comportamientos.

## Decisiones

1. **Jurisdicción relacional:** una especialidad pertenece a una institución y una ficha a una
   especialidad, con número y cohorte explícitos. La jurisdicción relaciona una membresía
   institucional con una ficha exacta, sin comodines ni alcance global. La ficha determina
   también la especialidad: no se unen listas independientes con una condición OR.
2. **Compatibilidad:** `projects.ficha_id` es nullable únicamente para conservar registros
   anteriores sin inventar vínculos. Los proyectos creados por HTTP requieren ficha autorizada.
   La institución se deriva de la ficha en servidor; no se acepta del formulario. La ficha y la
   institución no se cambian en la edición de metadatos.
3. **Creación por instructor:** `vinculos_especialidad` exige membresía con rol instructor y
   permiso explícito activo en esa especialidad. Crear un proyecto no designa automáticamente
   tutor ni concede acceso por ser propietario: el administrador debe efectuar la designación.
4. **Consultas y policies:** `VisibleProjects` y `CreatableFichas` restringen SQL antes de paginar
   o mostrar opciones. Las policies reutilizan esas reglas y comprueban estados y vigencia. Un
   evaluador no hereda gestión de tutores. El instructor tutor puede vincular aprendices de la
   misma institución; la importación institucional por lotes sigue pendiente de implementación.
5. **Guías:** las versiones en borrador solo son visibles para quienes pueden gestionarlas.
   Publicar y asignar al proyecto es una transacción sobre la versión exacta; no es publicación
   pública de PI-006. Editar una versión publicada crea un borrador nuevo sin reasignar las
   entregas existentes. La edición vuelve a leer bajo bloqueo y recalcula SHA-256.
6. **Entregas en borrador:** solo aprendices asignados crean borradores contra asignaciones
   vigentes de tipo proyecto. Autor, institución y estado se fijan en servidor. Las asignaciones
   individuales/grupales no se habilitan por aproximación. El resumen obligatorio conserva el
   límite real del esquema (500 caracteres).
7. **Versiones y archivos:** cada versión nueva empieza sin archivos; no se copian vínculos
   implícitos. Solo la versión más reciente en borrador admite cargas. Las anteriores conservan
   sus bytes y metadatos. Numeración y límite de archivos se comprueban con bloqueo del padre.
   El servicio existente aplica los formatos y límites conservadores de PI-010.
8. **Autorización exacta:** el aprendiz consulta sus propias entregas con asignación vigente;
   instructores asignados, administradores y evaluadores dentro de jurisdicción pueden consultar
   el proyecto correspondiente. Una descarga debe tener un `document_link` hacia la versión
   exacta autorizada de entrega y coherencia documento/entrega/proyecto/institución. Otra versión
   del mismo documento no hereda autorización por compartir su padre. Los documentos de guías
   y revisiones todavía no tienen rutas de descarga en este incremento.
9. **PI-001:** la excepción verifica Gate también en la acción interna. La ruta crea un borrador
   para un aprendiz vigente del proyecto, exige justificación de 20–2000 caracteres y registra
   autorizador, beneficiario, proyecto, timestamp, UUID de transacción e IP obtenida del request
   del servidor. La IP aprobada por PI-001 se conserva en `metadata.ip`; no se utiliza un campo
   enviado por el cliente ni se cambia el esquema de auditoría. No se altera PI-003 ni se aprueba
   una transición extemporánea por defecto.
10. **Interfaz:** Blade + Vite + Tailwind 4 + JS vanilla; reutilizar tokens y layouts existentes,
    estados vacíos reales, errores y paginación en español. El menú puede mostrar enlaces, pero
    la autoridad siempre se verifica en servidor. Se corrige la descripción desactualizada de
    frontend y comandos en AGENTS.md.
11. **Autoridad inicial:** `setup_admin.php` es una entrada exclusivamente CLI al comando
    `aris:inicializar-administracion`. Solicita institución y persona reales, contraseña oculta y
    confirmación; no contiene credenciales ni registros predeterminados. Crea institución,
    cuenta, membresía, rol y auditoría en una transacción. Bloquea la repetición si existe un
    administrador o el evento de inicialización, incluso tras retirar el rol. No modifica una
    cuenta existente ni ejecuta migraciones. Las cuentas adicionales se registran y luego el
    administrador las vincula y les asigna roles.

## Límites y consecuencias

- Las cinco migraciones nuevas se verifican solo en SQLite en memoria. No se migró la base local
  existente ni MySQL; la validación del motor objetivo y la concurrencia real siguen pendientes.
- Se gestionan membresías, catálogos y permisos mediante administración autorizada.
  El aprovisionamiento inicial está implementado y probado en memoria; no se ejecutó sobre la
  instalación local ni sobre servidores externos.
- No hay borrado de proyectos, transiciones académicas, revisión/calificación ni publicación
  pública. F2–F4 no se declaran completas por este incremento.
- El sistema de archivos no participa en la transacción SQL. Un fallo entre promoción y commit
  puede dejar un archivo sin metadatos; `aris:verificar-storage` permite reconciliarlo. La
  recuperación de fallos de infraestructura y restauración integral siguen en F8, sin afirmar
  atomicidad distribuida ni cierre de INV-025.
- Las nuevas pruebas HTTP usan SQLite en memoria y almacenamiento falso. El renderizado de
  vistas se comprueba en pruebas; el QA visual en navegador de estas pantallas queda pendiente.
