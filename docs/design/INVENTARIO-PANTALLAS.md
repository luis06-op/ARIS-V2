# Inventario de pantallas

> Mapa de superficies de ARIS 2.0, con la fase en que se construyen y los datos reales que las
> alimentan. Ninguna pantalla se implementa antes de que exista su flujo en el backend; no se
> crean prototipos con contenido inventado que puedan confundirse con funcionalidad.

## 1. Público

| Pantalla | Ruta prevista | Datos | Fase |
|---|---|---|---|
| Landing | `/` | Contenido editorial aprobado | F6 |
| Repositorio público | `/repositorio` | Publicaciones activas con alcance `publico` | F7 |
| Ver obra publicada | `/repositorio/{slug}` | Versión publicada + metadatos autorizados | F7 |
| Iniciar sesión | `/entrar` | Credenciales | F2 |
| Recuperar contraseña | `/recuperar` | Correo institucional | F2 |
| Restablecer contraseña | `/restablecer/{token}` | Token de un solo uso | F2 |

## 2. Autenticado — aprendiz

| Pantalla | Ruta prevista | Datos | Fase |
|---|---|---|---|
| Panel personal | `/panel` | Entregas próximas, proyectos propios, actividad reciente | F6 |
| Mis proyectos | `/proyectos` | Membresías vigentes del usuario | F3 |
| Proyecto | `/proyectos/{slug}` | Actividades asignadas, equipo, estado | F3 |
| Actividad asignada | `/actividades/{id}` | Versión exacta de la guía + plazo | F3 |
| Mis entregas | `/entregas` | Entregas propias con estado | F4 |
| Entrega | `/entregas/{id}` | Versiones, archivos, revisiones recibidas | F4/F5 |
| Nueva versión | `/entregas/{id}/versiones/nueva` | Resumen de cambio + archivos | F4 |
| Historial de la entrega | `/entregas/{id}/historial` | Línea de tiempo real | F5 |
| Mi perfil | `/perfil` | Datos propios y seguridad | F2 |

## 3. Autenticado — instructor

| Pantalla | Ruta prevista | Datos | Fase |
|---|---|---|---|
| Panel docente | `/docencia` | Entregas por revisar, proyectos activos | F6 |
| Proyectos que dirijo | `/docencia/proyectos` | Proyectos con responsabilidad vigente | F3 |
| Crear/editar proyecto | `/docencia/proyectos/nuevo` | Formulario validado | F3 |
| Miembros del proyecto | `/docencia/proyectos/{id}/miembros` | Membresías y funciones | F3 |
| Guías del proyecto | `/docencia/proyectos/{id}/actividades` | Actividades y versiones | F3 |
| Publicar versión de guía | `/docencia/actividades/{id}/versiones/nueva` | Contenido + checksum | F3 |
| Asignar actividad | `/docencia/actividades/{id}/asignar` | Destinatarios y plazo | F3 |
| Bandeja de revisión | `/docencia/revisiones` | Revisiones asignadas y pendientes | F5 |
| Revisar entrega | `/docencia/revisiones/{id}` | Versión exacta + comentarios | F5 |
| Proponer publicación | `/docencia/documentos/{id}/publicar` | Versión + alcance (`PI-006`) | F7 |

## 4. Autenticado — evaluador

| Pantalla | Ruta prevista | Datos | Fase |
|---|---|---|---|
| Mis revisiones | `/evaluacion` | Solo rondas asignadas al evaluador | F5 |
| Revisión | `/evaluacion/{id}` | Evidencia exacta e historial de versiones | F5 |
| Registrar decisión | `/evaluacion/{id}/decision` | Decisión + comentarios | F5 |

## 5. Autenticado — administración

| Pantalla | Ruta prevista | Datos | Fase |
|---|---|---|---|
| Resumen institucional | `/administracion` | Estado real del sistema, sin cifras inventadas | F6 |
| Personas y membresías | `/administracion/personas` | Membresías y roles | F2 |
| Roles | `/administracion/roles` | Catálogo de roles | F2 |
| Configuración institucional | `/administracion/configuracion` | Zona horaria, límites aprobados | F8 |
| Auditoría | `/administracion/auditoria` | `audit_events` filtrados | F5/F8 |
| Exportaciones | `/administracion/exportaciones` | Tareas autorizadas | F8 |
| Estado del almacenamiento | `/administracion/almacenamiento` | Verificación de blobs | F8 |

## 6. Transversales

| Pantalla / componente | Uso | Fase |
|---|---|---|
| `AppShell` (sidebar + topbar + breadcrumbs) | Navegación autenticada | F6 |
| `PublicShell` | Landing y repositorio | F6 |
| `PermissionDenied` | 403 uniforme sin filtrar existencia | F2 |
| `NotFound` | 404 uniforme | F2 |
| `EmptyState` | Listas sin datos reales | F6 |
| `ErrorState` | Fallo de carga con reintento | F6 |
| `Skeleton` | Carga de listados y paneles | F6 |
| `ConfirmDialog` | Acciones destructivas | F5/F8 |
| `VersionTimeline` | Historial de cualquier entidad versionada | F5 |
| `FileDropzone` | Carga privada con validación visible | F4 |
| `StatusChip` | Estado de entrega, revisión o publicación | F6 |

## 7. Reglas

1. **Sin datos ficticios** en pantallas de producción. Los datos sintéticos viven en tests y
   en una base de laboratorio, nunca en el paquete de distribución.
2. **Sin acciones simuladas.** Un botón que no ejecuta nada no se publica.
3. **Estados obligatorios** por pantalla: cargando, vacío, error, sin permiso.
4. **Móvil primero** en las pantallas del aprendiz; densidad mayor en las de administración.
5. Cada pantalla se prueba con teclado y con los datos de la base sintética correspondiente.
