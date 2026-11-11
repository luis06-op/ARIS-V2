# ARIS 2.0 · ARIS-V2

**Sistema de Gestión Documental y Repositorio Digital Inteligente para la Trazabilidad Pedagógica.**

ARIS 2.0 conserva, relaciona y hace comprensible el trabajo académico de una comunidad
educativa: desde la idea de un proyecto hasta sus evidencias, versiones, revisiones,
decisiones y publicación institucional. Su especialidad no es la gestión de cursos ni el
almacenamiento genérico de archivos, sino **la memoria académica trazable**: qué se
entregó, cuándo, por quién, contra qué guía y qué versión exacta se revisó.

> «ARIS permite que el conocimiento construido por estudiantes y docentes no desaparezca
> al finalizar una entrega: conserva su historia, sus revisiones y su contexto para que
> pueda seguir aportando a la institución».

Repositorio: <https://github.com/luis06-op/ARIS-V2> · Estado: ver `docs/ESTADO-ARIS-2.0.md`.

## Estado actual del repositorio

El repositorio contiene la base documental de F0 y el **esqueleto de aplicación Laravel 13**
de F1. La aplicación arranca y su suite base pasa, pero **el esquema de ARIS aún no existe**:
las migraciones del núcleo requieren una instancia MySQL sintética que todavía no está activa.
Ver `docs/ESTADO-ARIS-2.0.md` y `docs/decisions/ADR-002-stack-y-version-php.md`.

| Fase | Estado |
|---|---|
| F0 — Reconocimiento y seguridad del entorno | **COMPLETA** |
| F1 — Modelado, base de datos y migraciones | **PARCIAL**: esqueleto instalado y probado; migraciones pendientes de MySQL |
| F2–F10 | No iniciadas |

## Estructura del repositorio

```text
ARIS-V2/
├── app/ bootstrap/ config/ database/ public/ resources/ routes/ storage/ tests/ vendor/
├── artisan  composer.json  composer.lock  package.json  phpunit.xml  vite.config.js
├── README.md
├── AGENTS.md
├── AVISO-DE-DERECHOS.md
├── scripts/verificar-enlaces-docs.mjs
└── docs/
    ├── ESTADO-ARIS-2.0.md          # bitácora compacta: fase, pruebas, bloqueos, siguiente paso
    ├── AGENTES-DISPONIBLES.md      # inventario real de agentes/CLI verificados en el equipo
    ├── PLAN-DE-FASES.md            # roadmap, puertas de calidad y checklists por fase
    ├── product/                    # visión, roles, decisiones institucionales pendientes
    ├── architecture/               # dominio, ERD, diccionario, invariantes, permisos, storage
    ├── design/                     # dirección visual e inventario de pantallas
    ├── decisions/                  # ADR breves
    ├── operations/                 # instalación local, respaldo y restauración
    └── deployment/                 # requisitos de hosting y checklist de seguridad
```

La estructura de llegada completa está descrita en `docs/PLAN-DE-FASES.md` §2; se construye
**solo al implementar funcionalidad real**. Las carpetas del esqueleto de Laravel son las del
starter oficial.

## Requisitos

| Componente | Requisito | Estado en el equipo de desarrollo |
|---|---|---|
| PHP | ≥ 8.3 (Laravel 13) | **8.4.23** local como `php8.4`; el `php` del sistema sigue en 8.2.12 |
| Extensiones PHP | `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `intl`, `zip`, `gd`, `curl` | Verificadas en `php8.4` |
| Composer | 2.x | **2.11.2** en `~/.local/bin/composer` |
| Framework | Laravel 13 | **13.42.0** |
| Node / npm | 20 LTS o superior | Node 26.13.0 / npm 11.20.0; registro npm **no verificado** |
| MySQL / MariaDB | 8.0 / 10.6+ con `utf8mb4` | Servidor **no activo**; SQLite solo como laboratorio |
| Almacenamiento privado | Disco local fuera de `public/` | Configurado el directorio; flujo en F4 |

## Puesta en marcha

```bash
composer install
cp .env.example .env
php8.4 artisan key:generate
php8.4 artisan migrate          # requiere MySQL activo (aris2_dev)
npm install
npm run dev
php8.4 artisan serve --host=127.0.0.1 --port=8099
```

En este equipo, sustituir `php` por `php8.4` (PHP 8.4.23 local) y `composer` por
`php8.4 ~/.local/bin/composer`. La instalación del esqueleto y `php8.4 artisan test` ya se
ejecutaron; las migraciones de ARIS **no**, porque no hay servidor MySQL activo.

## Reglas que no se negocian

1. **Ningún otro proyecto, base de datos, respaldo o configuración externa se modifica.**
2. Las **migraciones de Laravel** son la única fuente editable del esquema; no se mantiene
   un `schema.sql` paralelo.
3. Las **versiones son inmutables**: se corrige creando otra versión, nunca sobrescribiendo.
4. Las **evidencias son privadas**: nunca en `public/`, nunca con URL directa, siempre
   servidas por una ruta autorizada.
5. Las **políticas institucionales no se inventan**: se registran como `PI-###` y se
   aprueban con la institución antes de activar el comportamiento.
6. **No se declara terminado** nada sin pruebas ejecutadas; lo no ejecutado se marca
   explícitamente como NO EJECUTADO.

## Documentación de referencia

- `docs/PLAN-DE-FASES.md` — qué se construye en cada fase y cómo se aprueba.
- `docs/product/VISION-Y-ALCANCE.md` — propósito, alcance del MVP y exclusiones.
- `docs/product/DECISIONES-INSTITUCIONALES.md` — las 11 políticas pendientes.
- `docs/architecture/INVARIANTES.md` — lo que el sistema no puede permitir.
- `docs/ESTADO-ARIS-2.0.md` — punto de entrada para retomar el trabajo.

## Titularidad y licencia

La obra es **titularidad del SENA** (Servicio Nacional de Aprendizaje, Colombia), año 2026.
No se concede licencia de uso, copia, modificación ni distribución sin autorización escrita
del SENA. Ver `AVISO-DE-DERECHOS.md`, `docs/decisions/ADR-006-titularidad-sena.md` y la
decisión pendiente `PI-011` (autorización de publicación pública).

La disponibilidad pública del repositorio **no constituye** licencia de uso.
