# ADR-016 — Respaldo y restauración aislada

- Fecha: 2026-11-18.
- Estado: aceptada.

`aris:respaldar` exige MySQL 8.x, modo mantenimiento y destino nuevo fuera de `public/` y del
disco privado. Produce `database.sql`, copias `private/...` y `MANIFIESTO.json` con tamaños y
hashes, sin `.env` ni `APP_KEY`. Verifica blobs disponibles contra sus copias y el ZIP contra
su propio manifiesto antes de informar éxito. No sobrescribe; elimina el ZIP si falla.

`aris:restaurar-respaldo` exige un hash externo confiable, una base `aris_restore_*` nueva y un
directorio nuevo. Verifica una copia privada del ZIP y valida la integridad de los blobs
restaurados. Nunca modifica la instalación actual; revierte base y directorio si falla.
El hash del ZIP y la contraseña del cliente son datos sensibles de operación, no del repositorio.

El verificador rechaza manifiesto ausente o inválido, rutas no permitidas, duplicados, entradas
no regulares, tamaños y hashes discordantes y archivos extra/ausentes. Los límites son 2 GiB
y 10.000 archivos privados. Las copias no sustituyen a la custodia de la `APP_KEY` ni a la
prueba del recorrido HTTP sobre la copia restaurada.

Evidencia: respaldo y restauración ejecutados en base sintética MySQL 8.4.13 aislada con un blob
y su archivo; rechazos por hash incorrecto y por ausencia de mantenimiento sin artefactos
residuales; nueve pruebas unitarias del formato ZIP. No se probó un volcado con datos
institucionales ni una conmutación de producción.
