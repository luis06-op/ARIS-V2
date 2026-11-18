# Instalación local

> Procedimiento para un entorno de desarrollo nuevo y autorizado. PHP, Composer, npm y la
> suite SQLite están verificados; B3 sigue pendiente y no se opera sobre MySQL sin autorización.
> El estado real y los comandos ejecutados se registran en `docs/ESTADO-ARIS-2.0.md`.

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

1. Crear la base única de la aplicación, exclusiva de ARIS: `aris_v2_db`.
2. PHPUnit usa SQLite en memoria para aislar pruebas automatizadas; no requiere una segunda
   base persistente.
3. Nunca reutilizar una base existente «por comodidad».
4. Credenciales en `.env` local, jamás en el repositorio.

```sql
CREATE DATABASE aris_v2_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
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

Estado real: dependencias y build están verificados. La aplicación local usa SQLite
(`database/database.sqlite`, ignorada por Git); PHPUnit usa `:memory:`. Las cinco migraciones
añadidas el 18-11-2026 se verificaron solo en memoria y **no se aplicaron a la base local**.
La validación y cualquier operación MySQL siguen pendientes por B3.

### 3.1 Autoridad inicial (PI-002)

En una instalación autorizada, con el esquema ya migrado y la conexión verificada por la
persona responsable, ejecutar desde la raíz del proyecto:

```bash
php8.4 setup_admin.php
# Equivalente:
php8.4 artisan aris:inicializar-administracion
```

El instalador pide nombre y código oficiales de la institución, nombre y correo de la autoridad
designada y contraseña con confirmación (entrada oculta). No ofrece datos de demostración ni
credenciales predeterminadas. No migra ni cambia la conexión. Se rechaza la reutilización si ya
se configuró la administración, aunque después se haya retirado su rol. El archivo solo funciona
por CLI y está fuera de `public/`.

La contraseña la entrega el operador al responsable institucional por el canal acordado; no
se imprime ni se envía correo. En esta sesión el comando se probó únicamente en SQLite de
pruebas; **no se ejecutó sobre la base local ni se creó un administrador real**.

### 3.2 Recorrido privado disponible

1. El administrador entra en «Control de accesos»: registra especialidades y fichas oficiales,
   vincula cuentas previamente registradas y asigna roles y jurisdicciones.
2. Habilita explícitamente al instructor para crear proyectos en una especialidad, cuando lo
   solicita Coordinación. Crear no designa tutor automáticamente: lo hace el administrador.
3. El instructor tutor vincula aprendices de la institución, crea una guía y publica/asigna
   una versión concreta al proyecto.
4. El aprendiz prepara un borrador contra esa asignación, carga evidencias y consulta versiones
   exactas. Una versión nueva conserva la anterior y empieza sin archivos.
5. La excepción PI-001 requiere actor autorizado, aprendiz vigente y justificación; queda auditada.

Las transiciones de envío/revisión/calificación y la publicación pública continúan pendientes
de PI-003, PI-004 y PI-006. Estos pasos no autorizan un piloto con datos de estudiantes.

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
# Paquete verificable de fuentes (F9 parcial)

Desde un checkout limpio con dependencias PHP instaladas, usar destinos nuevos en un
directorio existente fuera del repositorio:

```bash
php8.4 artisan aris:empaquetar-fuentes /ruta/temporal/aris-fuentes.zip
php8.4 artisan aris:verificar-paquete /ruta/temporal/aris-fuentes.zip
```

Ambos comandos devuelven 0 al completar; errores de validación devuelven 1. El segundo
no extrae archivos ni consulta la base. El primero lee fuentes de HEAD, excluye datos,
dependencias y compilados y muestra el SHA-256 completo del ZIP. Para comprobar
reproducibilidad, generar otro destino desde el mismo commit y comparar con `sha256sum`.
El manifiesto verifica coherencia interna, no firma ni procedencia. Conservar el hash
por un canal confiable. El ZIP requiere instalar dependencias y compilar assets antes
de evaluar una instalación; no autoriza migraciones ni despliegues.
