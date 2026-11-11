# ADR-004 — Versionado inmutable y control de concurrencia

- **Estado:** aceptada
- **Fecha:** 2026-11-11
- **Contexto de fase:** F1 (diseño) · se implementa en F4

## Contexto

Dos usuarios pueden enviar una nueva versión de la misma entrega casi al mismo tiempo. Si la
numeración se calcula con `MAX(version_number) + 1` sin protección, se producen versiones
duplicadas o se sobrescribe contenido histórico.

## Decisión

1. Toda entidad versionable (`activity_versions`, `submission_versions`, `document_versions`)
   usa `version_number` entero positivo y **`UNIQUE (padre_id, version_number)`**.
2. Una versión publicada o finalizada es **inmutable**: no se edita su contenido ni sus
   metadatos funcionales. Corregir implica crear otra versión y relacionarla.
3. La creación de versión ocurre dentro de una **transacción** con
   `SELECT … FOR UPDATE` sobre la fila padre (o control optimista con `lock_version`), y el
   índice único actúa como última defensa ante carreras.
4. «Última versión» se **calcula** por orden estable (`version_number DESC`); no se guarda un
   booleano `is_latest` editable.
5. El error de conflicto se traduce a un mensaje comprensible y no a un 500.

## Modelo de referencia

```text
submissions(id, project_id, activity_assignment_id, author_id, state, lock_version, …)
submission_versions(id, submission_id, version_number, author_id, summary,
                    state, created_at, …)
  UNIQUE (submission_id, version_number)
```

Ninguna FK desde `users` hacia versiones usa `ON DELETE CASCADE`.

## Consecuencias

- (+) Historial íntegro y reconstruible.
- (+) Concurrencia segura sin depender de la aplicación.
- (−) Corregir un error exige un paso adicional (nueva versión), deliberadamente.
- (−) Las vistas deben paginar historiales largos.

## Pruebas previstas

- `SubmissionConcurrencyTest`: dos inserciones simultáneas → una gana, la otra recibe
  conflicto controlado, nunca dos filas con el mismo número.
- `VersionSequenceTest`: numeración contigua, sin huecos por reintentos fallidos.
- `DocumentIntegrityTest`: bytes y checksum idénticos tras descarga.
