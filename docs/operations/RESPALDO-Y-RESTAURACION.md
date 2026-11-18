# Respaldo y restauración aislada

Los comandos operan sobre MySQL 8.x y el disco privado. No incluyen `.env` ni `APP_KEY`:
esa clave debe custodiarse por separado y conservarse tal cual en una restauración.

## Respaldo

Requiere modo mantenimiento, sin workers ni escrituras externas. El destino debe ser nuevo,
estar en un directorio existente y quedar fuera del disco privado y de `public/`.

```bash
php artisan down
php artisan aris:respaldar /ruta/privada/aris.zip --mysqldump=/ruta/mysqldump
php artisan up
```

El ZIP contiene `database.sql` (mysqldump `--single-transaction`, con triggers y hex-blob),
`private/...` con copias de los archivos y `MANIFIESTO.json` con ruta, tamaño y SHA-256.
Antes de cerrar se comprueba que cada blob `disponible` coincide con su copia y se verifica
el paquete completo. Nada se sobreescribe; un fallo elimina solo el ZIP recién creado.
Límites: 2 GiB y 10.000 archivos privados. Conservar el SHA-256 impreso por un canal distinto
del propio paquete: sin esa referencia externa el hash no demuestra procedencia.

## Verificación y restauración

```bash
sha256sum /ruta/privada/aris.zip
php artisan aris:restaurar-respaldo /ruta/privada/aris.zip aris_restore_ejemplo \
    /directorio/nuevo/disco-privado --sha256=<hash-confiable> --mysql=/ruta/mysql
```

 El verificador interno exige `database.sql`, inventario exacto, rutas permitidas, entradas
regulares y hashes coincidentes. La verificación usa una copia local del ZIP, no la ruta
original, para impedir sustitución entre comprobación e importación.

La restauración exige MySQL y un nombre `aris_restore_*` nuevo. No conecta con la instalación
actual: crea una base nueva, importa el volcado y extrae los archivos a un directorio nuevo y
privado. Contrasta cada blob disponible contra su archivo restaurado antes de informar éxito.
Si algo falla, elimina la base y el directorio creados. La contraseña se pasa al cliente MySQL
mediante un archivo de opciones temporal 0600, nunca en la línea de comandos.

El directorio destino de la restauración es la **raíz del disco privado**, no `storage/`:
`private/documents/a.pdf` se escribe como `<destino>/documents/a.pdf`.

Para poner en marcha la copia restaurada todavía hay que apuntar una instalación de la
aplicación a esa base, conservar la `APP_KEY` original y ejecutar el recorrido HTTP de
aceptación. Una restauración correcta de base y archivos no certifica por sí sola el servicio.

## Evidencia de esta sesión

Se ejecutó sobre MySQL Community 8.4.13 aislado (socket privado, sin red), con una evidencia
sintética y una base nueva exclusiva:

- Respaldo verificado, SHA-256 `0a4816d8fd2ce08199fbccfcc225bb16f754c8283673d6029a22f2e8ea76286f`.
- Restauración en `aris_restore_e2e` y directorio nuevo: 1 blob, SHA-256 coincidente.
- Rechazo por hash distinto y por falta de modo mantenimiento, sin artefactos residuales.
- Nueve pruebas unitarias del verificador ZIP, incluidas manipulaciones y entradas no regulares.

El cliente `mysql` de la distribución MySQL requería `libncurses.so.6`, ausente en el equipo:
se usó un enlace local a la biblioteca del sistema para la ejecución, sin modificar el sistema
ni el repositorio. `mysqldump` no requirió ese ajuste.
*** Add File: /home/luis/Proyectos/github/ARIS-V2/docs/decisions/ADR-016-respaldo-y-restauracion.md
+# ADR-016 — Respaldo y restauración aislada
+
+- Fecha: 2026-11-18.
+- Estado: aceptada.
+
+`aris:respaldar` exige MySQL 8.x, modo mantenimiento y destino nuevo fuera de `public/` y del
+disco privado. Produce `database.sql`, copias `private/...` y `MANIFIESTO.json` con tamaños y
+hashes, sin `.env` ni `APP_KEY`. Verifica blobs disponibles contra sus copias y el ZIP contra
+su propio manifiesto antes de informar éxito. No sobrescribe; elimina el ZIP si falla.
+
+`aris:restaurar-respaldo` exige un hash externo confiable, una base `aris_restore_*` nueva y un
+directorio nuevo. Verifica una copia privada del ZIP y valida la integridad de los blobs
+restaurados. Nunca modifica la instalación actual; revierte base y directorio si falla.
+El hash del ZIP y la contraseña del cliente son datos sensibles de operación, no del repositorio.
+
+El verificador rechaza manifiesto ausente o inválido, rutas no permitidas, duplicados, entradas
+no regulares, tamaños y hashes discordantes y archivos extra/ausentes. Los límites son 2 GiB
+y 10.000 archivos privados. Las copias no sustituyen a la custodia de la `APP_KEY` ni a la
+prueba del recorrido HTTP sobre la copia restaurada.
+
+Evidencia: respaldo y restauración ejecutados en base sintética MySQL 8.4.13 aislada con un blob
+y su archivo; rechazos por hash incorrecto y por ausencia de mantenimiento sin artefactos
+residuales; nueve pruebas unitarias del formato ZIP. No se probó un volcado con datos
+institucionales ni una conmutación de producción.
*** Update File: /home/luis/Proyectos/github/ARIS-V2/docs/ESTADO-ARIS-2.0.md
@@
+## 21. Sesión 2026-11-18 — Respaldo y restauración F8
+
+Implementados `aris:respaldar` y `aris:restaurar-respaldo` con manifiesto, SHA-256,
+verificación de blobs disponibles y reversión. `BackupArchive` valida formato ZIP sin extraer
+en producción; `MySqlClient` pasa credenciales por archivo de opciones temporal 0600.
+ADR-016 y `RESPALDO-Y-RESTAURACION.md` documentan alcance, límites y custodia de APP_KEY.
+
+Ejecución real en MySQL 8.4.13 aislado: respaldo verificado
+(`0a4816d8fd2ce08199fbccfcc225bb16f754c8283673d6029a22f2e8ea76286f`), restauración en base
+y directorio nuevos con 1 blob y SHA-256 coincidente. Rechazo correcto de hash distinto y de
+ejecución sin mantenimiento, sin residuos. Nueve pruebas unitarias del verificador aprobadas.
+El cliente `mysql` requirió un enlace local a `libncursesw`; no se modificó el sistema.
+
+Siguiente objetivo: funcionalidades complementarias permitidas (visor de auditoría, búsqueda
+por metadatos e importación por lotes) y actualización del paquete F9 con guía Hostinger.
+PI-005/006/007/009/010/011 siguen pendientes. No se declara cerrada F8: falta conmutación
+integral sobre una copia de la aplicación y validación en el proveedor real.
*** End Patch