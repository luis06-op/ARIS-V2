# Funciones complementarias

## Búsqueda de entregas

`GET /entregas/buscar` admite texto, estado, ficha, fecha desde y fecha hasta. Busca en
título, resumen de versión, guía asignada y nombres de archivo. Los resultados están
restringidos a los proyectos permitidos por la política de visibilidad.

No existen los campos «competencia», «módulo» ni «fase del proyecto» en el esquema actual.
No se añaden valores supuestos: incorporarlos exige definir su fuente institucional y su
política de datos. La búsqueda actual cubre los metadatos académicos que el sistema registra.

## Visor de auditoría

`GET /auditoria`, visible solo para administradores. Filtra por institución, acción, actor
y rango de fechas. La tabla muestra un detalle limitado; las justificaciones y direcciones IP
quedan fuera de la interfaz. El evento de auditoría sigue siendo de solo inserción.

## Importación por lotes

`GET|POST /accesos/{institution}/importar`, solo administradores de esa institución.

1. Preparar un CSV con encabezado `email` y un correo por fila.
2. Pulsar «Ver vista previa»: se informan nuevas vinculaciones, ya existentes, duplicados,
   correos inválidos y cuentas sin cuenta activa, sin escribir nada.
3. Pulsar «Confirmar importación» con el mismo archivo para crear las membresías.

Límites: 1000 filas y 1 MB. No crea cuentas ni contraseñas; la persona debe registrarse antes.
No asigna roles: se hacen desde el panel de control de accesos. Cada alta queda auditada.

La importación con creación de cuentas, datos de identidad o aprovisionamiento de roles
depende de PI-002 y PI-009 y permanece sin activar hasta su aprobación.
