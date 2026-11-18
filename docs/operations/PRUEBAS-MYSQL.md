# Pruebas MySQL 8.x sobre base desechable

La suite utiliza `migrate:fresh` y rollback: el destino debe ser una base sintética exclusiva.
El ejecutor comprueba versión, socket y nombre antes de iniciar. Nunca usar bases existentes
de la aplicación ni de otros proyectos.

```bash
ARIS_MYSQL_DISPOSABLE=1 DB_SOCKET=/ruta/privada/mysql.sock \
DB_DATABASE=aris_test_certificacion DB_USERNAME=usuario_de_pruebas DB_PASSWORD='' \
php8.4 scripts/probar-mysql.php
```

El servidor y la cuenta se preparan previamente con permisos exclusivamente sobre la base
de pruebas. En esta sesión se inició un MySQL Community 8.4.13 separado, sin servicios del
sistema, con `--skip-networking`, `--mysqlx=OFF`, socket y datadir en un directorio temporal
privado. No se reutilizó el MariaDB de XAMPP. El socket sin TCP está protegido por el directorio
0700; las credenciales locales sintéticas no sirven para una instalación real.

Se encontraron y corrigieron dos nombres de índices automáticos superiores a 64 caracteres.
Los nombres cortos se fijan en las migraciones de creación porque MySQL no podía llegar a
una migración posterior. No cambian columnas ni reglas de unicidad; instalaciones SQLite
anteriores conservan nombres anteriores con las mismas restricciones.

Las pruebas de DDL utilizan `DatabaseMigrations`: MySQL hace commits implícitos con DDL y
no debe envolverse un rollback de esquema dentro de `RefreshDatabase`. La simulación de
fallo de auditoría utiliza un listener transaccional portable en lugar de un trigger temporal
SQLite dentro de la transacción.

La migración `2026_11_18_000008` impide alterar SHA-256 y tamaño de `file_blobs`; permite
cambiar ruta/estado para almacenamiento y cuarentena. El control de integridad detecta
cambios en los bytes físicos. Los triggers no protegen frente a una autoridad capaz de
eliminarlos o reemplazar tablas. Mantener separados los privilegios de migración y operación.

Resultado inicial aprobado: **123 pruebas / 723 aserciones**, tanto en MySQL 8.4.13 como en
SQLite. Incluye auditoría append-only, rechazo de hash modificado, claves RESTRICT, índices
críticos, integridad física y recorrido académico. No equivale a medir rendimiento con una
carga institucional ni a certificar el hosting.
