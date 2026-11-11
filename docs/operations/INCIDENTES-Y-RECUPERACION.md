# Incidentes y recuperación

> Guía de respuesta ante fallos técnicos o de seguridad. No sustituye el procedimiento legal de
> la institución ni la notificación a autoridades cuando corresponda.

## 1. Clasificación

| Nivel | Ejemplo | Respuesta |
|---|---|---|
| S1 — Crítico | Acceso indebido a evidencias, pérdida de datos, credenciales expuestas | Contención inmediata, preservar evidencia, avisar al responsable |
| S2 — Alto | Error que impide entregar o revisar, corrupción parcial | Mitigación en horas, revisión de causa |
| S3 — Medio | Fallo de una pantalla, error de validación confuso | Corrección planificada |
| S4 — Bajo | Detalle visual, texto incorrecto | Backlog |

## 2. Contención (S1)

1. **Aislar el flujo afectado**, no desactivar controles globales: revocar la publicación, la
   sesión o el enlace comprometido.
2. **Preservar evidencia no sensible**: registros de auditoría, marcas de tiempo, versión del
   código.
3. **No borrar** archivos ni filas para «limpiar» antes de entender el alcance.
4. Rotar credenciales afectadas si hay sospecha de exposición.
5. Informar al responsable institucional y seguir su criterio sobre notificación a titulares.

## 3. Diagnóstico mínimo

```bash
# Estado de la aplicación y versión desplegada
php artisan about

# Integridad de almacenamiento (previsto F8)
php artisan aris:verificar-storage

# Integridad académica (previsto F8)
php artisan aris:verificar-integridad
```

- Revisar `storage/logs/` **sin** copiar datos personales a canales externos.
- Comparar `audit_events` del periodo con la versión del código desplegada.
- Confirmar si el fallo es de datos, de aplicación o de infraestructura.

## 4. Recuperación

| Escenario | Acción |
|---|---|
| Error de aplicación tras despliegue | Rollback de código (ver `docs/deployment/ROLLBACK.md`) |
| Migración fallida | Detener, restaurar base desde respaldo verificado, corregir migración |
| Blob perdido o corrupto | Restaurar desde respaldo de archivos; marcar el documento como no disponible |
| Base perdida | Restaurar base + blobs del mismo punto de consistencia |
| Acceso indebido confirmado | Revocar sesiones, rotar secretos, revisar permisos, documentar |

Nunca se «arregla» una inconsistencia borrando evidencia histórica.

## 5. Cierre del incidente

1. Causa raíz identificada (o hipótesis explícita y pendiente).
2. Acción correctiva con prueba que demuestre que no se repite.
3. Registro en `docs/ESTADO-ARIS-2.0.md` si afecta al desarrollo.
4. Si el incidente revela una decisión institucional no definida, se abre o actualiza el `PI`
   correspondiente.

## 6. Contactos y responsabilidades

| Rol | Responsabilidad | Persona |
|---|---|---|
| Responsable de datos | Decide sobre notificación y tratamiento | **Por definir (`PI-009`)** |
| Soporte técnico | Ejecuta contención y restauración | **Por definir** |
| Coordinación académica | Decide sobre el impacto pedagógico | **Por definir** |

Sin estos nombres, ARIS no puede operar un piloto real.
