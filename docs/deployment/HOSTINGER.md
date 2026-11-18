# Preparación de instalación en Hostinger — Laravel 13 y Blade

Este procedimiento prepara una instalación autorizada; no acredita que un plan de Hostinger
concreto cumpla los requisitos ni constituye evidencia de despliegue ejecutado.

## Requisitos del alojamiento

- PHP y extensiones compatibles con `composer.lock`; ejecutar `composer check-platform-reqs`.
  El entorno validado de desarrollo usa PHP 8.4. No usar Laravel 11 como referencia.
- Acceso SSH/Composer o posibilidad de preparar dependencias compatibles en un entorno de build.
- MySQL 8.x y permisos suficientes para las migraciones y triggers de auditoría.
- HTTPS, espacio privado fuera del DocumentRoot y escritura en `storage` y `bootstrap/cache`.
- Confirmar en hPanel el motor real (MySQL y MariaDB no son equivalentes), versión PHP,
  extensiones, SSH, restricciones de triggers y configuración disponible del directorio público.

## Estructura

El directorio público del dominio debe apuntar exclusivamente a `aris/public`:

```text
directorio-privado/aris/
  app/
  bootstrap/
  config/
  storage/app/private/
  vendor/
  .env
  public/                 ← DocumentRoot del dominio
    index.php
    build/
```

Si el plan obliga a usar `public_html`, conservar el proyecto fuera de ese directorio y
publicar solo el contenido de `public`, ajustando las rutas de `index.php` al directorio
privado. Comprobar esa adaptación en staging. No mover `.env`, `vendor` ni `storage/app/private`
a `public_html`; no crear enlaces públicos al disco de evidencias.

## Compilación y dependencias

Desde el commit aprobado y el entorno de build:

```bash
npm ci --ignore-scripts
npm run build
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
composer check-platform-reqs
```

Revisar los scripts de Composer antes de ejecutarlos. No usar `composer run setup` en hosting:
incluye una migración automática. El paquete de fuentes excluye dependencias y `public/build`;
estos deben prepararse mediante el proceso anterior y verificarse en staging.

## Configuración e instalación

Configurar `.env` fuera del directorio público con APP_ENV=production, APP_DEBUG=false,
APP_URL con HTTPS, conexión propia a MySQL y cookies seguras. Generar APP_KEY únicamente
en una instalación nueva; una restauración conserva su clave original.

Tras autorizar y respaldar el destino, ejecutar explícitamente:

```bash
php artisan migrate --force
php setup_admin.php
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

`setup_admin.php` es CLI e interactivo; no se publica como endpoint web. Las migraciones no
son una aprobación para poblar datos de demostración. Configurar correo y trabajadores solo
cuando se autoricen sus servicios y funcionalidades.

## Verificaciones de aceptación

- HTTPS y acceso correcto; `.env`, código y evidencias privadas inaccesibles por URL directa.
- Roles y jurisdicciones: pruebas negativas de acceso a recursos de otra persona/institución.
- Entrega, envío, revisión cualitativa, corrección y reapertura auditada.
- Descargas exactas y `php artisan aris:verificar-integridad` en la instalación autorizada.
- Restauración aislada del respaldo, documentando el resultado.

Para cambios de configuración, reconstruir caches. `config:cache` impide depender de `env()`
fuera de los archivos de configuración. Ante un rollback de aplicación, comprobar primero
compatibilidad del esquema; no deshacer migraciones con datos sin un procedimiento probado.

Referencia técnica: https://laravel.com/docs/13.x/deployment
