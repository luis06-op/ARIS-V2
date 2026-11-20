# ADR-018 — Correcciones obligatorias P1 y P2 (Fase 1)

- **Fecha:** 2026-11-19
- **Estado:** aceptada
- **Marco institucional:** PI-002 (versión 4), PI-001 (versión 2), PI-010 (pendiente, límites conservadores)

## Contexto

La auditoría anterior (commit `2913b8cd`) reportó seis pruebas que reproducían cinco defectos funcionales (P1 y P2) que debían corregirse con prioridad sobre cualquier mejora cosmética:

1. **P1.1 Privacidad de entregas:** el buscador mostraba entregas de cualquier proyecto al que el usuario tuviera acceso, sin distinguir autor vs. personal institucional.
2. **P1.2 Protección del último administrador:** `ManageInstitutionRole` no impedía retirar al único administrador vigente ni usaba `InstitutionalAuthority` como criterio de autoridad efectiva.
3. **P2.1 Filtros opcionales:** `SearchController` y `AuditController` rechazaban campos opcionales vacíos enviados por formularios HTML reales.
4. **P2.2 Archivos DOCX y XLSX:** `EvidenceValidator` confiaba solo en la firma ZIP (`PK\x03\x04`), insuficiente para rechazar ZIP genéricos con extensión cruzada.
5. **P2.3 Edición concurrente de guías:** `UpdateGuideVersion` no implementaba control de concurrencia optimista con revisión esperada.
6. **P2.4 Rendimiento del buscador:** la vista ejecutaba consultas N+1 sobre versiones y conteos.

## Decisión

1. **P1.1:** `SearchSubmissions::forUser` filtra entregas por `author_id` (el autor ve sus propias entregas) o por proyectos donde el usuario ejerce autoridad efectiva como personal institucional (administrador, instructor o evaluador con jurisdicción vigente). El criterio se comparte con `SubmissionVersionPolicy::view`.
2. **P1.2:** `ManageInstitutionRole` adquiere un bloqueo transaccional por institución (`Institution::lockForUpdate`) antes de consultar o modificar roles, usa `InstitutionalAuthority` como criterio de autoridad efectiva (usuario activo, institución activa, membresía vigente), y rechaza la retirada del último administrador vigente.
3. **P2.1:** Se añade `nullable` a todos los campos opcionales de validación en `SearchController` y `AuditController`, sin debilitar la validación de valores no vacíos ni los controles de autorización.
4. **P2.2:** `EvidenceValidator` valida la estructura OOXML de DOCX/XLSX (partes obligatorias `[Content_Types].xml`, `_rels/.rels`, parte principal del documento; tipo de contenido coherente; relación de empaquetado) sin extraer indiscriminadamente, con límites defensivos (≤3000 entradas, ≤60 MB descomprimido, ≤20 MB por entrada). Se elimina `application/zip` de los MIME permitidos para docx/xlsx en `config/aris.php`.
5. **P2.3:** Migración incremental `2026_11_19_000001_add_lock_version_to_activity_versions` añade `lock_version` a `activity_versions`. `UpdateGuideVersion` comprueba la revisión esperada dentro de la transacción; un formulario obsoleto es rechazado. Una versión publicada sigue siendo inmutable: editarla genera un borrador nuevo.
6. **P2.4:** La vista de búsqueda accede a la colección de versiones ya cargada con eager loading (`$entrega->versions`) en lugar de ejecutar consultas por fila.

## Consecuencias

- Positivas: privacidad de entregas reforzada, protección del último administrador, formularios HTML resilientes, validación estructural de archivos de oficina, control de concurrencia optimista y eliminación de N+1 en el buscador.
- Negativas: mayor complejidad en `EvidenceValidator` (justificada por seguridad de archivos); una migración adicional en el esquema.
- No se introdujeron nuevas políticas institucionales; todas las correcciones se basan en políticas ya aprobadas (PI-001, PI-002, PI-010 límites conservadores).

## Pruebas

Se añadieron 7 archivos de prueba de regresión (SearchPrivacyTest, ManageInstitutionRoleTest, SearchOptionalFiltersTest, AuditOptionalFiltersTest, EvidenceValidatorOoxmlTest, GuideVersionConcurrencyTest, SearchPerformanceTest) y se ajustó JurisdictionIntegrityTest por la nueva migración. Suite completa: 163 pruebas, 848 aserciones, todas aprobadas. Pint y lint PHP aprobados.
