# Respaldo y restauración

> Un respaldo no está probado hasta que se ha restaurado en un entorno aislado. **Ninguna
> restauración se ha ejecutado en este repositorio** porque aún no existe base ni aplicación.

## 1. Qué compone un respaldo válido

| Elemento | Contenido | Sin él |
|---|---|---|
| Base de datos | Esquema + datos (`mysqldump --single-transaction`) | Se pierden metadatos, autoría y estados |
| Blobs privados | `storage/app/private/documents/` | Metadatos sin archivos: evidencia inútil |
| Configuración no secreta | `.env.example`, `config/aris.php`, versión desplegada | Imposible reconstruir el entorno |
| Manifiesto | Lista de archivos + SHA-256 + fecha + versión | No se puede verificar integridad |

Los secretos (`.env` real, `APP_KEY`) **no** se incluyen en el paquete de respaldo estándar; se
custodian por separado. Si se respaldan, van cifrados y con acceso restringido.

## 2. Coherencia entre base y archivos

El respaldo debe tomarse con la aplicación en modo mantenimiento o con una ventana sin cargas:

1. Activar mantenimiento (`php artisan down`) o acordar ventana.
2. Volcar la base con transacción consistente.
3. Copiar blobs **sin modificar** el directorio de origen.
4. Calcular manifiesto SHA-256 de ambos conjuntos.
5. Registrar en el manifiesto la versión del código y la fecha UTC.

Un respaldo tomado con cargas activas puede contener metadatos de archivos que no llegaron a
escribirse; se documenta como **no consistente** y no se usa para restauración formal.

## 3. Frecuencia propuesta (sujeta a `PI-007`)

| Tipo | Frecuencia | Retención |
|---|---|---|
| Base + blobs | Diario | 14 días |
| Semanal | Semanal | 8 semanas |
| Mensual | Mensual | 12 meses |
| Previo a migración | Antes de cada despliegue con cambios de esquema | Hasta validar el despliegue |

Cifrado en reposo, permisos restringidos y almacenamiento fuera del servidor de aplicación
cuando el hosting lo permita.

## 4. Restauración en laboratorio (procedimiento)

1. Crear una base **vacía y aislada** `aris2_restore`.
2. Restaurar el volcado y verificar el conteo de tablas esperado.
3. Copiar los blobs a una ruta de laboratorio, **nunca** sobre el directorio de producción.
4. Comparar el manifiesto SHA-256 con los archivos restaurados.
5. Ejecutar `php artisan aris:verificar-storage` (previsto en F8) y revisar huérfanos.
6. Iniciar la aplicación apuntando a la base restaurada y recorrer:
   - login;
   - proyecto → guía → entrega → versión;
   - descarga autorizada de una evidencia;
   - revisión de una versión concreta;
   - acceso anónimo al repositorio público.
7. Registrar fecha, responsable, duración y resultado. Un restore sin recorrido es un restore
   no verificado.

## 5. Objetivos (pendientes de definir con la institución)

| Métrica | Valor | Estado |
|---|---|---|
| RPO (pérdida máxima aceptable) | Por definir | PENDIENTE |
| RTO (tiempo máximo de recuperación) | Por definir | PENDIENTE |
| Responsable de ejecutar el restore | Por definir | PENDIENTE |
| Custodia de copias y secretos | Por definir | PENDIENTE |

No se promete ningún RPO/RTO hasta que exista una prueba cronometrada en el hosting real.

## 6. Prohibiciones

- No restaurar sobre una base en uso sin confirmación explícita.
- No sobrescribir el directorio de blobs de producción durante una prueba.
- No usar `migrate:fresh` como estrategia de recuperación.
- No considerar válido un respaldo que solo contenga la base.
