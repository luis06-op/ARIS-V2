# Instalación local

> Procedimiento para un entorno de desarrollo nuevo. **Nada de esto se ha ejecutado todavía**
> en este repositorio: F1 está bloqueada por la ausencia de Composer y de un servidor MySQL
> activo (ver `docs/ESTADO-ARIS-2.0.md`). Este documento es el objetivo verificable, no un
> informe de éxito.

## 1. Requisitos

| Componente | Mínimo | Comprobación |
|---|---|---|
| PHP | 8.3 (Laravel 13) · 8.2 (Laravel 12) | `php -v` |
| Extensiones | `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `intl`, `zip`, `curl` | `php -m` |
| Composer | 2.x | `composer --version` |
| Node / npm | 20 LTS+ | `node -v`, `npm -v` |
| MySQL / MariaDB | 8.0 / 10.6+, `utf8mb4` | `mysql --version` |
| Git | 2.30+ | `git --version` |

En el equipo de desarrollo verificado: PHP 8.2.12, Node 26.13.0, npm 11.20.0, MariaDB de XAMPP
instalado pero **no iniciado**, Composer **ausente**.

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

## 3. Pasos previstos

```bash
# 1. Dependencias
composer install
cp .env.example .env
php artisan key:generate

# 2. Base sintética ya creada y configurada en .env
php artisan migrate

# 3. Frontend
npm install
npm run build        # o npm run dev durante el desarrollo

# 4. Servidor local
php artisan serve --host=127.0.0.1 --port=8099
```

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
| Migraciones desde cero | `php artisan migrate` | Todas aplicadas sin error |
| Re-ejecución | `php artisan migrate` | «Nothing to migrate» |
| Pruebas | `php artisan test` | Suite en verde |
| Documento privado inaccesible | `curl -I http://127.0.0.1:8099/storage/...` | 404 |
| `.env` fuera del control de versiones | `git check-ignore -v .env` | Coincide con `.gitignore` |

## 6. Problemas conocidos del entorno

| Síntoma | Causa probable | Acción |
|---|---|---|
| `php` carga `php.ini` de XAMPP | El binario del PATH es un enlace a `/opt/lampp/bin/php` | Verificar extensiones antes de instalar |
| `composer: command not found` | Composer no instalado | Instalación local en `~/.local/bin` (requiere autorización) |
| `SQLSTATE[HY000] [2002]` | MySQL no iniciado | Iniciar el servicio **con autorización** |
| `npm ETIMEDOUT` | Registro npm inaccesible desde la red actual | Reintentar; no bloquea el backend |
| Laravel 13 no instala | PHP 8.2 < 8.3 | Decidir según `ADR-002` |

## 7. Reglas

- **No** usar `sudo` ni modificar servicios globales sin autorización.
- **No** apuntar a bases de otros proyectos.
- **No** ejecutar `migrate:fresh` sobre una base que no sea inequívocamente sintética.
- **No** versionar `.env`, `vendor/`, `node_modules/` ni archivos académicos.
