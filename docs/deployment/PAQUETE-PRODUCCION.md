# Paquete de producción

> Objetivo: un artefacto reproducible que se pueda instalar, verificar y retirar sin arrastrar
> código de desarrollo, fixtures, secretos ni documentación interna innecesaria.
> **Ningún paquete se ha generado todavía.**

## 1. Contenido del paquete

| Incluye | Excluye |
|---|---|
| `app/`, `bootstrap/`, `config/`, `database/`, `public/`, `resources/`, `routes/`, `vendor/` | `.env` real, `storage/app/private/*` |
| `composer.json`, `composer.lock`, `artisan` | `node_modules/`, `public/hot` |
| `public/build/` compilado | `tests/`, salvo que se decida incluirlos |
| `.env.example` | `.git/`, `.github/`, `.opencode/` |
| `MANIFIESTO.txt` + `SHA256SUMS` | logs, respaldos, bases de datos |
| Documentación de operación mínima | Documentación de agentes e informes internos |

## 2. Construcción reproducible

```bash
# 1. Árbol limpio desde la etiqueta/commit aprobado
git archive --format=tar.gz --prefix=aris/ <commit> -o /tmp/aris-src.tar.gz

# 2. Dependencias de producción
composer install --no-dev --optimize-autoloader --no-scripts
npm ci
npm run build

# 3. Caches de Laravel (se regeneran en destino con las rutas reales)
#    No incluir config cache construida en otra ruta.

# 4. Empaquetado
tar --exclude='node_modules' --exclude='.env' --exclude='storage/app/private/*' \
    -czf aris-<version>.tar.gz <árbol>

# 5. Manifiesto
find <árbol> -type f -print0 | sort -z | xargs -0 sha256sum > SHA256SUMS
```

## 3. Verificación del paquete

- [ ] `tar -tzf` lista todos los archivos esperados y ninguno prohibido.
- [ ] `sha256sum -c SHA256SUMS` pasa sin errores.
- [ ] No contiene `.env`, claves, volcados SQL ni archivos académicos.
- [ ] `public/` no incluye `storage` enlazado.
- [ ] El paquete se extrae en un directorio limpio y arranca con una base vacía.
- [ ] La versión y el commit quedan registrados en `MANIFIESTO.txt`.

## 4. Instalación en destino

1. Descomprimir fuera de `public_html`.
2. Crear `.env` desde `.env.example` con valores reales (no versionados).
3. `php artisan key:generate`.
4. `php artisan migrate --force`.
5. `php artisan config:cache`, `route:cache`, `view:cache` según corresponda.
6. Ajustar permisos de `storage/` y `bootstrap/cache/`.
7. Verificar HTTPS, `/login`, 404 en `.env` y descarga autorizada de una evidencia.

## 5. Reglas

- El paquete **no** mezcla documentación interna extensa ni logs de agentes.
- No se publica un paquete sin manifiesto y checksum.
- No se sobrescribe un paquete validado sin copia recuperable y autorización.
- No se declara «listo para producción» sin F8 cerrada, QA visual ejecutado y políticas
  institucionales aprobadas.
