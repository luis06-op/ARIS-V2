# ADR-002 — Stack y versión de PHP

- **Estado:** propuesta · **requiere decisión del usuario**
- **Fecha:** 2026-11-11
- **Contexto de fase:** F0 (bloqueo B2)

## Contexto verificado

- El documento rector fija como objetivo **Laravel 13 + PHP ≥ 8.3**.
- El equipo de desarrollo tiene **PHP 8.2.12** (`/opt/lampp/bin/php`, cargado por
  `/home/luis/.local/bin/php`) y **no hay otra versión instalada**.
- **Composer no está instalado**, por lo que el starter no puede instalarse todavía (B1).
- Laravel 12 mantiene soporte sobre PHP 8.2; Laravel 13 exige 8.3 o superior.

## Decisión propuesta

Adoptar **Laravel 12 + PHP 8.2** como piso inmediato para desbloquear F1 sin modificar el
sistema operativo, y **planificar la migración a Laravel 13 + PHP ≥ 8.3** antes de F9
(hospedaje), que de todos modos exige ≥ 8.3 en el servidor.

## Alternativas

| Alternativa | Ventaja | Costo / riesgo |
|---|---|---|
| A. Laravel 12 + PHP 8.2 (propuesta) | Cero cambios de sistema, avance inmediato | Dos actualizaciones menores de framework antes de producción |
| B. Instalar PHP 8.3+ y usar Laravel 13 | Alineado al objetivo, una sola versión | Cambio a nivel de sistema (XAMPP/paquetes), requiere autorización |
| C. Contenedor con PHP 8.3 | Aislamiento, versión exacta | Añade Docker a un equipo que hoy no lo usa; complejidad operativa |

## Consecuencias

- Si se elige **A**, `composer.json` fijará `"php": "^8.2"` y `laravel/framework: ^12.0`, y el
  hosting de F9 deberá cumplir ≥ 8.3.
- Si se elige **B**, se documenta el procedimiento y se valida que XAMPP y el `php` del PATH
  apunten a la versión nueva **sin romper otros proyectos** que dependan de la actual.
- Ninguna opción autoriza a instalar servicios globales sin permiso ni a tocar proyectos
  existentes.

## Pendiente

1. El usuario decide A o B.
2. Autorizar (o no) la instalación local de Composer en `~/.local/bin`.
3. Registrar la decisión final en este ADR y en `docs/ESTADO-ARIS-2.0.md`.
