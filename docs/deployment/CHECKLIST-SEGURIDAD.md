# Checklist de seguridad

> Se ejecuta antes de cada despliegue y al cerrar F8. Cada casilla debe tener evidencia
> reproducible; marcar sin comprobar invalida el checklist. **Ninguna casilla está verificada
> todavía** porque no existe aplicación desplegada.

## 1. Configuración

- [ ] `APP_ENV=production` y `APP_DEBUG=false`.
- [ ] `APP_KEY` única por instalación, fuera del repositorio.
- [ ] `.env` con permisos `600` y respuesta 404 por HTTP.
- [ ] Cookies `Secure`, `HttpOnly`, `SameSite=Lax`.
- [ ] HTTPS forzado y HSTS evaluado.
- [ ] `APP_URL` correcto (afecta enlaces y verificación de origen).
- [ ] Errores no muestran trazas al usuario.

## 2. Acceso

- [ ] Registro público deshabilitado (`PI-002`).
- [ ] Sesión regenerada al autenticar; logout invalida la sesión.
- [ ] Rate limit en login y recuperación.
- [ ] Tokens de recuperación de un solo uso, hasheados y con expiración.
- [ ] Respuestas de recuperación no revelan si el correo existe.
- [ ] Roles por defecto sin privilegios.
- [ ] Membresías vencidas no autorizan.

## 3. Autorización

- [ ] Toda ruta privada tiene policy verificada.
- [ ] Pruebas negativas de IDOR en proyectos, entregas, documentos y revisiones.
- [ ] Descargas autorizan documento **y** versión.
- [ ] Acciones masivas autorizan cada elemento.
- [ ] 403/404 no filtran existencia de recursos privados.

## 4. Archivos

- [ ] Evidencias fuera de `public/`; sin `public/storage`.
- [ ] Whitelist de extensión y MIME real.
- [ ] Nombre físico opaco; sin rutas del usuario.
- [ ] Límite de tamaño aplicado en PHP y en Laravel.
- [ ] Checksum verificado tras la escritura.
- [ ] Reconciliación de huérfanos ejecutada.
- [ ] Video deshabilitado salvo aprobación.

## 5. Entrada y salida

- [ ] Validación en servidor de todos los formularios.
- [ ] Consultas parametrizadas (Eloquent/query builder).
- [ ] Escape de salida; sin HTML enriquecido sin sanitizador.
- [ ] CSRF activo en todos los formularios y llamadas Inertia.
- [ ] Sin `eval`, sin inclusión dinámica de archivos, sin deserialización insegura.

## 6. Operación

- [ ] Logs sin contraseñas, tokens ni contenido de documentos.
- [ ] `audit_events` con actor, acción, recurso, resultado e instante.
- [ ] Respaldo de base + blobs y **restauración probada**.
- [ ] Espacio en disco monitoreado.
- [ ] Dependencias revisadas (`composer audit`) y procedencia verificada.
- [ ] Sin servicios de terceros obligatorios ni telemetría no aprobada.

## 7. DocumentRoot y exposición

- [ ] Raíz web apunta solo a `public/`.
- [ ] `/.env`, `/composer.json`, `/storage`, `/database`, `/config` responden 404.
- [ ] `phpinfo()` eliminado.
- [ ] Listado de directorios deshabilitado.
- [ ] `public/build` servido con caché, sin mapas de depuración en producción.

## 8. Evidencia

Registrar en `docs/ESTADO-ARIS-2.0.md`: fecha, ejecutor, comando o captura por casilla y
resultado. Las casillas no ejecutadas se dejan sin marcar y se declaran como pendientes.
