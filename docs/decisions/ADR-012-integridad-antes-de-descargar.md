# ADR-012 — Integridad antes de descargar evidencias

- **Fecha:** 2026-11-18
- **Estado:** aceptada
- **Fase:** F8 iniciada; F4 reforzada.

## Decisión

Después de autorizar la versión exacta, la descarga comprueba el estado actual del blob, su
disco privado, ruta confinada, tamaño, SHA-256 y coincidencia con los metadatos de la versión.
Un incidente devuelve 404 sin entregar el archivo. No se marca, borra ni modifica la evidencia.

`EvidenceIntegrity` copia los bytes a un stream temporal, con hasta 2 MB en memoria y derrame
temporal administrado por PHP. Verifica la copia y sirve esa misma copia; no vuelve a abrir la
ruta original después de verificar. Así, sustituir el archivo entre la verificación y la
emisión no sustituye los bytes entregados. La copia se cierra al terminar o fallar la respuesta.
Se aplican los límites conservadores configurados; no se amplían formatos o cuotas.

El comando `aris:verificar-integridad` recorre blobs `disponible` por lotes, comprueba bytes y
metadatos de sus versiones y devuelve código 1 ante incidentes, 0 en caso contrario. Es de
solo lectura y no imprime nombres originales ni contenidos. No certifica blobs en cuarentena
ni sustituye la detección de archivos huérfanos de `aris:verificar-storage`.

## Evidencia y límites

Pruebas con SQLite en memoria y disco falso: corrupción del mismo tamaño, truncado, ausencia,
metadatos discordantes, traversal, enlace simbólico, disco no privado, estado en cuarentena
tras precargar una relación y sustitución posterior a la verificación. También se prueba el
rechazo HTTP de una evidencia corrupta para un usuario con acceso válido.

Esto inicia F8 en integridad, pero no completa respaldos, restore, retención ni respuesta a
incidentes. No se ejecutó el comando sobre bases o evidencias existentes. B3 y las decisiones
institucionales pendientes se mantienen.
