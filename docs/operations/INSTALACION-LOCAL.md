# Instalación local

> Procedimiento para un entorno de desarrollo nuevo. **Nada de esto se ha ejecutado todavía**
> en este repositorio: F1 está bloqueada por la ausencia de Composer y de un servidor MySQL
> activo (ver `docs/ESTADO-ARIS-2.0.md`). Este documento es el objetivo verificable, no un
> informe de éxito.

## 1. Requisitos

| Componente | Mínimo | Comprobación |
|---|---|---|
| PHP | 8.3+ (Laravel 13) | `php8.4 -v` |
| Extensiones | `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `intl`, `zip`, `curl`, `gd` | `php8.4 -m` |
| Composer | 2.x | `php8.4 ~/.local/bin/composer --version` |
| Node / npm | 20 LTS+ | `node -v`, `npm -v` |
| MySQL / MariaDB | 8.0 / 10.6+, `utf8mb4` | `mysql --version` |
| Git | 2.30+ | `git --version` |

En el equipo de desarrollo: **PHP 8.4.23** estático en `~/.local/php/8.4.23/` (comando
`php8.4`), **Composer 2.11.2** en `~/.local/bin/composer`, Node 26.13.0, npm 11.20.0.
El `php` del PATH sigue siendo el 8.2.12 de XAMPP y **no se modifica**.

### 1.1 Por qué existe `php8.4`

Laravel 13 exige PHP ≥ 8.3 y el equipo solo tenía 8.2.12. Para no alterar el sistema ni otros
proyectos se instaló una compilación estática local de `static-php-cli` (build *bulk*), con su
propio `php.ini` cargado mediante `PHPRC` (ver ADR-002). Reproducir en otro equipo:

1. Descargar `php-8.4.23-cli-linux-x86_64.tar.gz` desde
   `https://dl.static-php.dev/static-php-cli/bulk/` (fijar la versión exacta).
2. Extraer el binario en `~/.local/php/8.4.23/bin/php` y verificar `php -m`: deben aparecer
   `intl`, `pdo_mysql`, `zip`, `gd`, `fileinfo`, `openssl`, `mbstring`.
3. Crear `~/.local/php/8.4.23/php.ini` con `memory_limit=512M`, `upload_max_filesize=12M`,
   `post_max_size=16M`, `date.timezone=UTC`.
4. Envoltorio `~/.local/bin/php8.4`:

   ```sh
   #!/usr/bin/env sh
   export PHPRC=/home/<usuario>/.local/php/8.4.23
   exec /home/<usuario>/.local/php/8.4.23/bin/php "$@"
   ```

No se publica un checksum porque el proveedor no lo ofrece: se mitiga fijando la versión y
verificando las extensiones. En Windows o macOS el procedimiento cambia; usar el instalador
oficial de PHP de la plataforma y ajustar los comandos.

## 2. Aislamiento de la base

1. Crear una instancia **exclusiva** del proyecto: `aris2_dev`.
2. Crear una base separada para pruebas: `aris2_test` (nunca compartida con otros proyectos).
3. Nunca reutilizar una base existente «por comodidad».
4. Credenciales en `.env` local, jamás en el repositorio.

```sql
CREATE DATABASE aris2_dev  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE aris2_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

`CREATE DATABASE` **no** pertenece a las migraciones: lo ejecuta la persona que aprovisiona.

## 3. Pasos

```bash
# 1. Dependencias (en este equipo, con el PHP local)
php8.4 ~/.local/bin/composer install
cp .env.example .env
php8.4 artisan key:generate

# 2. Base sintética ya creada y configurada en .env
php8.4 artisan migrate

# 3. Frontend
npm install
npm run build        # o npm run dev durante el desarrollo

# 4. Servidor local
php8.4 artisan serve --host=127.0.0.1 --port=8099
```

Estado real: los pasos 1, 3 (sin `npm install`) y 4 se ejecutaron al instalar el esqueleto;
el paso 2 está **pendiente** porque no hay servidor MySQL activo. La base local actual es
SQLite (`database/database.sqlite`, ignorada por Git) y **no** valida el esquema de ARIS.

## 4. Permisos de almacenamiento

```bash
mkdir -p storage/app/private/{documents,quarantine,exports}
chmod -R u+rwX,go-rwx storage/app/private
```

El usuario del servidor web debe poder escribir en `storage/` y `bootstrap/cache/`. **No** se
crea `public/storage` ni se enlazan evidencias al DocumentRoot.

## 5. Verificación mínima

| Comprobación | Comando | Resultado esperado |
|---|---|---|
| Migraciones desde cero | `php8.4 artisan migrate` | Todas aplicadas sin error |
| Re-ejecución | `php8.4 artisan migrate` | «Nothing to migrate» |
| Pruebas | `php8.4 artisan test` | Suite en verde |
| Documento privado inaccesible | `curl -I http://127.0.0.1:8099/storage/...` | 404 |
| `.env` fuera del control de versiones | `git check-ignore -v .env` | Coincide con `.gitignore` |

## 6. Problemas conocidos del entorno

| Síntoma | Causa probable | Acción |
|---|---|---|
| `php` carga el `php.ini` de XAMPP | El binario del PATH es un enlace a `/opt/lampp/bin/php` | Usar `php8.4` para este proyecto |
| `composer: command not found` | Composer no está en el PATH | Usar `php8.4 ~/.local/bin/composer` |
| `SQLSTATE[HY000] [2002]` | MySQL no iniciado | Iniciar el servicio **con autorización** |
| `npm ETIMEDOUT` | Registro npm inaccesible desde la red actual | Reintentar; no bloquea el backend |
| `php8.4: command not found` | Falta el envoltorio local | Repetir §1.1 |

## 7. Reglas

- **No** usar `sudo` ni modificar servicios globales sin autorización.
- **No** apuntar a bases de otros proyectos.
- **No** ejecutar `migrate:fresh` sobre una base que no sea inequívocamente sintética.
- **No** versionar `.env`, `vendor/`, `node_modules/` ni archivos académicos.
