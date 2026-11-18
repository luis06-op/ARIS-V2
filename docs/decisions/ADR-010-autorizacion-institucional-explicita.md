# ADR-010 — Autorización institucional explícita y sin privilegio global

- **Fecha:** 2026-11-18
- **Estado:** aceptada
- **Marco institucional:** PI-002 versión 4, en `docs/product/DECISIONES-INSTITUCIONALES.md`.

## Contexto

La institución aprobó cuatro roles y reservó la gestión de roles y tutores al administrador.
El esquema existente representa los roles mediante membresías institucionales. Un rol no
puede interpretarse como autoridad sobre otra institución ni como permiso para funcionalidades
que siguen sujetas a políticas pendientes.

## Decisión

- Usar policies descubiertas por Laravel y evaluadas mediante Gate; no usar un `Gate::before`
  que conceda todas las acciones al administrador.
- `InstitutionPolicy::manageRoles` y `ProjectPolicy::manageTutors` autorizan únicamente al
  administrador con membresía vigente en la institución del recurso.
- Centralizar esa comprobación en `InstitutionalAuthority`, fuera de los modelos Eloquent.
  Consultar el estado persistido en cada evaluación, sin reutilizar relaciones cargadas que
  puedan ocultar una revocación o bloqueo.
- Exigir usuario activo, institución activa y membresía activa. En los campos de fecha
  existentes, representar la vigencia como intervalo `[joined_at, left_at)`: la fecha de alta
  está incluida y la fecha de baja ya no concede acceso; `left_at` nulo no fija fin. Usar la
  fecha del servidor de aplicación. No se concede acceso histórico adicional (PI-005 pendiente).
- Las acciones todavía no implementadas carecen de autorización implícita. No crear cuentas,
  catálogos ni rutas HTTP como efecto secundario de añadir las policies.

## Consecuencias y siguiente incremento

Las pruebas con SQLite en memoria verifican Gate y el descubrimiento real de policies,
aislamiento institucional, denegación por rol, usuario anónimo, vigencia y revocación.
Este incremento no implementa la modificación de roles ni tutores: establece su autorización.
La matriz de jurisdicción, la habilitación de instructores y la autorización sobre versiones
exactas requieren un incremento posterior antes de conectar proyectos o evidencias a HTTP.
El aprovisionamiento inicial solicitado se implementará separadamente, sin credenciales
versionadas ni exposición web del mecanismo de instalación. B3 permanece pendiente.
