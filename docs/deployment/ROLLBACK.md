# Rollback

> Revertir código es fácil; revertir datos no. La estrategia distingue ambos casos y exige
> respaldo verificado antes de cualquier migración destructiva.

## 1. Rollback de código (sin cambios de esquema)

1. Identificar el commit/etiqueta estable anterior.
2. Activar mantenimiento: `php artisan down --retry=60`.
3. Desplegar el artefacto anterior (mismo procedimiento que el despliegue).
4. Regenerar caches (`config:cache`, `route:cache`, `view:cache`).
5. Desactivar mantenimiento y verificar `/login` y una descarga autorizada.
6. Registrar el motivo y el resultado.

Tiempo objetivo: el de una restauración de artefacto; no se promete un número hasta medirlo.

## 2. Rollback con migraciones

`php artisan migrate:rollback` **no** es una estrategia de recuperación general:

- Un `down()` puede destruir datos (drop de columnas o tablas).
- Una migración con datos transformados no siempre es reversible con exactitud.
- Un rollback parcial deja el esquema en un estado intermedio no probado.

Procedimiento correcto:

1. Antes de migrar: respaldo consistente de base **y** blobs, con manifiesto.
2. Si la migración falla: detener, restaurar el respaldo en una base de laboratorio, corregir
   la migración y volver a probar.
3. Si el fallo ocurre en producción y hay pérdida de datos: restaurar base + blobs del mismo
   punto de consistencia; no intentar «arreglar» filas a mano.
4. Documentar la causa y añadir una prueba que cubra el caso.

Solo se permite `migrate:rollback` en una base de **pruebas** inequívocamente identificada.

## 3. Rollback de publicación

Revocar una publicación (`RevokePublication`) es un rollback de negocio: oculta la obra sin
borrar la evidencia ni el historial. No se elimina el registro; se registra el evento de
revocación con actor, fecha y motivo.

## 4. Rollback de almacenamiento

- Un blob restaurado debe corresponder al mismo punto de consistencia que la base.
- Si un blob no se puede restaurar, el documento se marca como no disponible y se informa; no
  se sustituye por otro archivo.
- Nunca se sobrescribe `storage/app/private/documents/` de producción durante una prueba.

## 5. Criterios de abortar un despliegue

- Migración fallida o más lenta de lo previsto con bloqueos.
- Errores 500 en rutas críticas.
- Descarga de evidencia accesible sin autorización.
- `.env` o `storage/` expuestos por HTTP.
- Cualquier señal de pérdida de integridad de versiones.

En estos casos: mantenimiento, rollback de código, restaurar datos si es necesario, y revisión
antes de reintentar.
