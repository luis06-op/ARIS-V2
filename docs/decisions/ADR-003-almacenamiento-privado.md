# ADR-003 — Almacenamiento privado de evidencias

- **Estado:** aceptada
- **Fecha:** 2026-11-11
- **Contexto de fase:** F1 (diseño) · se implementa en F4

## Contexto

Las evidencias académicas pueden contener datos personales de estudiantes, incluidos menores.
Una URL pública, un `public/storage` o un symlink convierten un error de configuración en una
filtración permanente e indetectable.

## Decisión

1. Los blobs viven en **`storage/app/private/documents/`** (disco `local` de Laravel), fuera
   del DocumentRoot.
2. El nombre físico es un **identificador opaco generado por el servidor**; el nombre original
   del archivo se guarda solo como metadato de presentación.
3. Cada blob registra `sha256`, tamaño, MIME detectado, extensión validada y estado de
   almacenamiento.
4. La descarga pasa por un controlador que autoriza **documento + versión exacta** y responde
   con `Content-Disposition` y `X-Content-Type-Options: nosniff`.
5. Existe una **cuarentena** (`storage/app/private/quarantine/`) para cargas en curso y un
   reconciliador de huérfanos.
6. No se crea `public/storage`, no se sirven evidencias por `Storage::url()` y no se acepta
   ninguna ruta proporcionada por el usuario.

## Flujo de escritura (resumen)

1. Autorizar actor y validar tamaño, extensión y MIME real.
2. Guardar en cuarentena con nombre generado.
3. Calcular SHA-256 y verificar que el archivo persistido coincide.
4. En transacción: bloquear secuencia del padre, crear `document_versions` y `file_blobs`.
5. Confirmar estado final; si algo falla, revertir metadatos y limpiar el temporal.
6. Registrar el evento en auditoría.

## Consecuencias

- (+) Ninguna evidencia es accesible por HTTP directo.
- (+) Integridad verificable por checksum.
- (−) Toda descarga consume PHP; aceptable para el volumen previsto.
- (−) El respaldo debe incluir base **y** blobs de forma coherente (ver
  `docs/operations/BACKUP-Y-RESTAURACION.md`).

## Pendiente

Escaneo de malware: solo si existe una herramienta fiable instalada. No se declarará
«escaneado» lo que no se haya escaneado (`PI-010`).
