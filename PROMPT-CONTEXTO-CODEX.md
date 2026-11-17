# Contexto para retomar ARIS 2.0 en una nueva sesión de Codex

> **Nota histórica (17-11-2026):** este documento fue el relevo para F1. Las migraciones del
> núcleo ya están implementadas y verificadas en SQLite; el estado vigente está en
> `docs/ESTADO-ARIS-2.0.md`. Se conserva como registro del traspaso, no como tarea pendiente.

Continúa el desarrollo del producto **ARIS 2.0** en este repositorio. Antes de cambiar
código, lee `AGENTS.md`, `docs/ESTADO-ARIS-2.0.md` y `docs/PLAN-DE-FASES.md`. Estos archivos
rigen el alcance, las restricciones y la tarea siguiente.

## Situación al iniciar

- Repositorio: `ARIS-V2`, rama `main`.
- F0 está cerrada y F1 está parcial: esqueleto Laravel 13.42.0 instalado con PHP 8.4 local.
- El modelo de dominio y sus decisiones institucionales están documentados.
- La siguiente tarea concreta registrada es preparar las migraciones del núcleo: `institutions`,
  `users`, `roles`, membresías y `projects`, además de sus modelos y pruebas de integridad.
- El registro de estado menciona que falta un servidor MySQL/MariaDB para ejecutar migraciones
  de ARIS. Comprueba el entorno actual antes de asumir que ese bloqueo sigue vigente.

## Forma de trabajar

1. Trabaja únicamente dentro de la raíz actual de `ARIS-V2`. No leas ni modifiques otros
   proyectos, bases de datos, respaldos o credenciales.
2. No sobrescribas ni restaures archivos locales. Comprueba `git status` antes de empezar.
3. Sigue `AGENTS.md`: no hagas `git push`, cambios en remotos, despliegues ni commits salvo que
   el usuario lo autorice expresamente. No inventes políticas institucionales ni datos.
4. La única fuente editable del esquema son las migraciones Laravel. No crees un `schema.sql`
   paralelo. Respeta las reglas de privacidad, autorización, historial y borrado de `AGENTS.md`.
5. No apuntes a una base de datos preexistente. Si hace falta ejecutar migraciones, verifica que
   la instancia sea sintética, nueva y dedicada a ARIS (`aris2_dev` o `aris2_test`). Si no puedes
   verificarlo, no ejecutes operaciones sobre esa base; documenta el bloqueo y avanza en tareas
   seguras que no dependan de ella.
6. Implementa solo decisiones ya aprobadas. Si una migración depende de una política pendiente,
   conserva el bloqueo y regístralo; no asignes valores por defecto arbitrarios.
7. No declares pruebas ejecutadas si no las ejecutaste. No agregues pruebas fuera del trabajo
   autorizado por el usuario y las puertas de calidad del proyecto.
8. Al cerrar la sesión, actualiza `docs/ESTADO-ARIS-2.0.md` con cambios, comandos y resultados
   reales, bloqueos y un siguiente objetivo verificable. No crees otro informe paralelo.

## Primeros pasos

```bash
git status --short --branch
git log -5 --oneline --decorate
```

Después de leer los documentos rectores, confirma si MySQL/MariaDB está disponible y cuál es la
base activa sin revelar secretos. Continúa la siguiente tarea verificable de F1 y deja el
repositorio en un estado revisable. No publiques cambios.
