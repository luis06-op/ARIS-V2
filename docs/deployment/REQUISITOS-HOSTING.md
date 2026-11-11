# Requisitos del hosting

> **Ningún plan de hosting ha sido contratado ni verificado.** Este documento es la lista de
> comprobaciones que deben hacerse sobre el plan real antes de cualquier despliegue. No se
> asume compatibilidad por publicidad del proveedor.

## 1. Capacidades imprescindibles

| Requisito | Mínimo | Cómo se comprueba | Estado |
|---|---|---|---|
| PHP | 8.3+ | `php -v` en el servidor o panel | NO VERIFICADO |
| Extensiones | `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `intl`, `zip`, `curl`, `gd` | `php -m` o página de diagnóstico temporal | NO VERIFICADO |
| Composer | 2.x con acceso a CLI o instalación por SSH | `composer --version` | NO VERIFICADO |
| Base de datos | MySQL 8 / MariaDB 10.6+, `utf8mb4`, InnoDB | Panel o cliente | NO VERIFICADO |
| DocumentRoot | Configurable a `public/` (o subdominio con raíz en `public/`) | Panel de hosting | NO VERIFICADO |
| Código fuera de `public_html` | Permitido | Panel / SSH | NO VERIFICADO |
| Almacenamiento escribible | `storage/` y `bootstrap/cache/` | Prueba de escritura | NO VERIFICADO |
| Espacio | Suficiente para blobs + respaldos + margen | Panel | NO VERIFICADO |
| Límite de carga | ≥ 10 MB por archivo, alineado con `upload_max_filesize` y `post_max_size` | `php -i` | NO VERIFICADO |
| Memoria PHP | ≥ 256 MB recomendado | `php -i` | NO VERIFICADO |
| HTTPS | Certificado válido y forzado | Navegador | NO VERIFICADO |
| Cron | Al menos una ejecución programada | Panel | NO VERIFICADO |
| Respaldos del proveedor | Existencia y frecuencia reales | Contrato | NO VERIFICADO |
| Node en producción | **No requerido** | — | — |

## 2. Estructura de despliegue

```text
/home/<cuenta>/aris/            # código, fuera del DocumentRoot
├── app/ bootstrap/ config/ database/ resources/ routes/ storage/ vendor/
└── public/                     # ÚNICO directorio expuesto
    ├── index.php
    ├── build/
    └── assets/
```

- El DocumentRoot apunta a `…/aris/public`.
- `storage/` **no** se enlaza a `public/`.
- `.env` vive junto al código, con permisos `600`.
- No se clona el repositorio completo dentro de `public_html`.

## 3. Criterios de descarte del proveedor

No contratar un plan si:

- no permite apuntar el DocumentRoot a `public/` ni usar un subdirectorio como raíz;
- solo ofrece PHP 8.1 o inferior;
- no permite archivos de 10 MB o tiene `post_max_size` inamovible por debajo;
- no permite escribir en `storage/` fuera del directorio público;
- no ofrece HTTPS o lo cobra como extra obligatorio;
- prohíbe cron o lo limita a tareas que no pueden ejecutarse;
- no permite exportar la base de datos.

## 4. Verificación antes de desplegar

1. Crear un `phpinfo()` temporal en un subdirectorio **protegido**, revisarlo y eliminarlo.
2. Confirmar versión de PHP y extensiones.
3. Confirmar la ruta real del DocumentRoot con un archivo marcador.
4. Probar escritura en `storage/`.
5. Probar conexión a una base **vacía** del proyecto.
6. Confirmar que `https://dominio/.env` responde 404.
7. Registrar los resultados en `docs/ESTADO-ARIS-2.0.md`.

Cualquier despliegue real requiere **autorización explícita del usuario**.
