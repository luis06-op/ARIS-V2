# ADR-007 — Base única de aplicación

- **Estado:** aceptada
- **Fecha:** 2026-11-16
- **Decidida por:** responsable del proyecto

## Contexto

La especificación de reconstrucción entregada por el responsable nombra `aris_v2_db` como la
base MySQL de ARIS. El repositorio ya utiliza PHPUnit con SQLite en memoria para aislar las
pruebas automatizadas, sin una base de pruebas persistente.

## Decisión

- La aplicación ARIS usa una única base MySQL/MariaDB llamada `aris_v2_db` por instalación.
- PHPUnit conserva su conexión SQLite `:memory:` efímera, solo durante cada ejecución de pruebas.
- El esquema se crea exclusivamente mediante migraciones Laravel; no se importa el DDL del
  documento externo como una segunda fuente.
- La base aún debe verificarse como sintética y dedicada antes de ejecutar migraciones.

## Consecuencias

- `.env.example` y la guía de instalación nombran `aris_v2_db`.
- Las credenciales y el host permanecen en `.env` local y no se solicitan ni registran aquí.
- Las migraciones de ARIS no se consideran verificadas hasta ejecutarlas desde cero en MySQL.
