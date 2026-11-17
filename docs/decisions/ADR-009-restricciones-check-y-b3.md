# ADR-009 — Restricciones CHECK y validez de B3

- **Estado:** aceptada
- **Fecha:** 2026-11-17
- **Decidida por:** responsable del proyecto (instrucción de sesión: continuar fases dejando B3 pendiente)

## Contexto

El ERD describe restricciones `CHECK` que Laravel no puede expresar en migraciones:
`version_number >= 1`, `size_bytes > 0`, SHA-256 de 64 hexadecimales y, sobre todo,
«exactamente uno de» en `documents` (`project_id` / `activity_id` / `submission_id`) y en
`document_links` (`document_version_id` / `activity_version_id` / `submission_version_id` /
`review_round_id`). `Illuminate\Database\Schema\Blueprint` no expone `check()` en Laravel 13.42.0
y SQLite no admite `ALTER TABLE … ADD CONSTRAINT`.

B3 (ausencia de MySQL sintética) permanece **pendiente por decisión del responsable**. Por eso no
se puede verificar un SQL de restricción específico de MySQL contra el motor objetivo.

## Decisión

1. Las migraciones aplican lo que sí es portable y verificable hoy: `NOT NULL`, `UNIQUE`,
   claves foráneas explícitas con `RESTRICT` (salvo el enlace `document_links → documents`, que
   sigue el diccionario con `CASCADE` porque solo borra el vínculo, no la evidencia), enums y
   índices.
2. Las reglas «exactamente uno de», `version_number >= 1`, `size_bytes > 0` y el formato del hash
   se implementan en la capa de dominio (`app/Domain` / `app/Actions`) cuando se construyan F3–F5,
   con prueba negativa. No se inventa un `DEFAULT` que las satisfaga.
3. No se añade SQL crudo de `CHECK` dependiente del motor mientras B3 siga abierta. Cuando se
   abra B3, se evaluará una migración de refuerzo por motor y se verificará en SQLite **y** en
   MySQL antes de declararla válida.
4. Esta decisión no cierra F1: la puerta de instalación limpia en MySQL sintética sigue pendiente.

## Consecuencias

- El esquema completo de F1 existe y se aplica desde cero en SQLite; las pruebas cubren
  unicidades, restricciones de borrado, estados por defecto y orden de versiones.
- Los invariantes `INV-040`, `INV-041` y `INV-044` quedan verificados en SQLite.
- Los invariantes que exigen `CHECK` de base de datos siguen marcados como pendientes hasta que
  exista la capa de dominio o se cierre B3.
- No se ha tocado MySQL, ninguna base existente ni la conexión de producción.
