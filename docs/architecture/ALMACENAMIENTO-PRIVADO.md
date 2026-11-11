# Almacenamiento privado de evidencias

> La base de datos y el sistema de archivos **no comparten transacción**. Este documento
> define el flujo que mantiene ambos coherentes y el modelo de amenaza que evita filtraciones.

## 1. Ubicaciones

| Ruta | Uso | Acceso |
|---|---|---|
| `storage/app/private/documents/` | Blobs definitivos con nombre opaco | Solo servidor |
| `storage/app/private/quarantine/` | Cargas en curso | Solo servidor |
| `storage/app/private/exports/` | Exportaciones temporales | Solo servidor, con expiración |
| `public/` | HTML, JS, CSS, imágenes de marca | Público |

Prohibido: `public/storage`, symlinks hacia `storage/`, `Storage::url()` para evidencias,
servir archivos por ruta directa y cualquier ruta construida con datos del usuario.

## 2. Límites iniciales (sujetos a `PI-010`)

| Parámetro | Valor inicial | Nota |
|---|---|---|
| Tamaño por archivo | 10 MB | Validado en PHP, en Laravel y por `php.ini` |
| Formatos | PDF, DOCX, XLSX, JPG, PNG | Whitelist por extensión **y** MIME real |
| Video | Deshabilitado | Requiere cuota, antivirus, ancho de banda y respaldo |
| Archivos por versión | 1..5 (propuesta) | Configurable; se valida en servidor |
| Cuota por proyecto | Configurable | Se documenta antes de un piloto |

Un formato fuera de la lista se rechaza con mensaje claro, no se «convierte» en silencio.

## 3. Validación de una carga

1. **Autorización primero:** la policy confirma que el actor puede adjuntar a esa entrega o
   versión.
2. **Tamaño:** `size <= límite`; un archivo vacío se rechaza.
3. **Extensión:** whitelist, comparación en minúsculas.
4. **MIME real:** `finfo`/`getimagesize` sobre el contenido, no la cabecera del cliente.
   PDF, DOCX y XLSX validan además su firma de contenedor (`%PDF-`, `PK\x03\x04`).
5. **Nombre:** se conserva como metadato escapado; **nunca** se usa como ruta.
6. **Cuarentena:** se escribe con un nombre generado por el servidor.
7. **Integridad:** se calcula SHA-256 y se compara con el archivo escrito.

No se ejecuta ninguna conversión, previsualización ni extracción de contenido en el MVP.

## 4. Secuencia transaccional

```text
1. Policy: ¿puede este actor adjuntar a esta versión/entrega?
2. Escritura en cuarentena con nombre opaco
3. Verificación de tamaño, MIME, firma y SHA-256
4. DB::transaction:
     - SELECT ... FOR UPDATE sobre el padre (document/submission)
     - siguiente version_number
     - insert file_blobs (cuarentena -> disponible)
     - insert document_versions
     - insert document_links
5. Commit
6. Mover el archivo a documents/ (rename atómico si es el mismo disco)
7. Registrar auditoría
```

Si falla el paso 4 o 5: se elimina el archivo de cuarentena y no queda metadato. Si falla el
paso 6: el blob queda `cuarentena` y el reconciliador lo resuelve; **nunca** se responde como
éxito con metadato sin archivo.

## 5. Descarga

```text
1. Policy(document, document_version) → denegar si no procede
2. Leer file_blobs por id interno (no por ruta del cliente)
3. Comprobar status = disponible y que el archivo exista
4. Verificar SHA-256 si el costo es aceptable (o en verificación programada)
5. StreamedResponse con:
     Content-Type: mime registrado
     Content-Disposition: attachment; filename="<nombre saneado>"
     X-Content-Type-Options: nosniff
     Cache-Control: private, no-store
6. Auditoría: actor, documento, versión, resultado
```

Sin rangos parciales en el MVP; si se añaden, se revalida la autorización en cada petición.
Los errores no revelan si el documento existe.

## 6. Reconciliación y huérfanos

- `php artisan aris:verificar-storage` (previsto en F4/F8) compara base y disco:
  - blob `disponible` sin archivo → incidente, se marca y se reporta;
  - archivo sin blob → se mueve a cuarentena, **no** se borra automáticamente;
  - blob `huerfano` sin referencias durante N días → candidato a limpieza con confirmación.
- Ningún borrado de blobs es automático en producción sin registro y respaldo.

## 7. Modelo de amenaza

| Amenaza | Control |
|---|---|
| IDOR en descarga | Policy sobre versión exacta + IDs internos |
| Traversal (`../../etc/passwd`) | Nombre generado; ruta nunca compuesta con entrada |
| MIME falso | Detección por contenido y firma de formato |
| Ejecución de archivos subidos | Almacenamiento fuera de `public/`; sin ejecución en el directorio |
| Enumeración de IDs | UUID/opaco en superficies públicas; 404 uniforme |
| Filtración por symlink | Prohibido `public/storage`; revisión en F8/F9 |
| Fuga por copia de seguridad | Respaldos cifrados y con permisos restringidos |
| Doble versión por carrera | Transacción + `UNIQUE (padre_id, version_number)` |
| Disco lleno | Comprobación previa, límites por archivo y cuota |

## 8. Respaldos

Un respaldo coherente incluye **base + blobs + configuración no secreta**, con manifiesto de
checksums y procedimiento de restauración probado. Restaurar solo la base produce metadatos sin
archivos: se considera respaldo inválido. Ver `docs/operations/BACKUP-Y-RESTAURACION.md`.
