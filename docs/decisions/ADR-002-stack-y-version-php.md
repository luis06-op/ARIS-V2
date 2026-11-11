# ADR-002 — Stack y versión de PHP

- **Estado:** aceptada · **decidida por:** responsable del proyecto (11-11-2026)
- **Fecha:** 2026-11-11 · **Actualizado:** 2026-11-11
- **Contexto de fase:** F0 → F1

## Contexto verificado

- El documento rector fija como objetivo **Laravel 13 + PHP ≥ 8.3**.
- El sistema solo tenía **PHP 8.2.12** (`/opt/lampp/bin/php`, vía `/home/luis/.local/bin/php`) y
  **no había Composer**.
- Laravel 13 exige `php: ^8.3` (verificado en Packagist: `laravel/framework v13.42.0`).

## Decisión

1. Instalar **PHP 8.4.23 local** (compilación estática de `static-php-cli`, build *bulk*) en
   `~/.local/php/8.4.23/`, con envoltorio `~/.local/bin/php8.4` que carga
   `~/.local/php/8.4.23/php.ini` mediante `PHPRC`.
2. Instalar **Composer 2.11.2** en `~/.local/bin/composer`, verificando la firma SHA-384
   publicada por getcomposer.org.
3. Crear el proyecto con **Laravel 13.42.0** y PHP 8.4.23.
4. **No** modificar el PHP del sistema ni XAMPP: el `php` del PATH sigue siendo 8.2.12 para los
   demás proyectos del equipo. ARIS usa `php8.4` explícitamente.

## Justificación

- Cumple el objetivo del documento rector sin `sudo`, sin paquetes del sistema y de forma
  reversible (basta borrar `~/.local/php/8.4.23`).
- Evita romper otros proyectos que dependen del PHP 8.2 de XAMPP.
- El build *bulk* incluye las extensiones que el producto necesita: `intl`, `zip`, `gd`,
  `pdo_mysql`, `fileinfo`, `openssl`, `mbstring`, `sodium`, `curl`.

## Alternativas descartadas

| Alternativa | Motivo |
|---|---|
| Laravel 12 con PHP 8.2 | Retroceso respecto al objetivo; obligaría a actualizar antes de F9 |
| Instalar PHP 8.4 del sistema (pacman) | Requiere `sudo` y altera el entorno de otros proyectos |
| Contenedor Docker | Añade una dependencia operativa no justificada en esta fase |

## Consecuencias

- (+) Stack alineado con el documento rector y con un hosting que exija ≥ 8.3.
- (+) Aislamiento: cada proyecto conserva su versión de PHP.
- (−) `php8.4` es un binario estático mantenido fuera del repositorio: se documenta su origen y
  se debe reinstalar en otro equipo (procedimiento reproducible en `docs/operations/INSTALACION-LOCAL.md`).
- (−) El binario no verifica checksum publicado (el proveedor no lo ofrece); se mitiga fijando
  la versión exacta y verificando sus extensiones al instalar.
- (−) `php.ini` se carga por `PHPRC`; sin el envoltorio, los límites vuelven a los valores por
  defecto de la compilación.

## Verificación

| Comprobación | Resultado |
|---|---|
| `php8.4 -v` | PHP 8.4.23 (cli) |
| `php8.4 -m` | `intl`, `pdo_mysql`, `zip`, `gd`, `fileinfo`, `openssl`, `mbstring`, `curl` presentes |
| `php8.4 ~/.local/bin/composer --version` | Composer 2.11.2 con PHP 8.4.23 |
| `php8.4 artisan --version` | Laravel Framework 13.42.0 |
| `php8.4 artisan test` | 2 pruebas aprobadas |
