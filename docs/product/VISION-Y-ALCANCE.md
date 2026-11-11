# Visión y alcance — ARIS 2.0

## 1. Definición

**ARIS 2.0** es un sistema de gestión documental y repositorio digital para la **trazabilidad
pedagógica**. Conserva, relaciona y hace comprensible el trabajo académico de una comunidad
educativa: desde la idea de un proyecto hasta sus evidencias, versiones, revisiones,
decisiones y publicación institucional.

No es una carpeta compartida con botones ni un clon de un LMS. Su especialización es la
memoria académica trazable: **qué se entregó, cuándo, por quién, contra qué guía y qué
versión exacta se revisó**.

## 2. Problema

En una institución educativa las evidencias se pierden cuando cambian equipos, se gradúan
estudiantes, rotan instructores, cierra un semestre o se reemplaza un archivo. Los materiales
se dispersan entre chats, dispositivos y correos. Consecuencia: no se puede responder con
fiabilidad qué se entregó, en qué versión, con qué observaciones ni qué cambió.

ARIS corrige esto con **relaciones explícitas, versiones inmutables y auditoría**, no con
métricas inventadas ni promesas de inteligencia artificial.

## 3. Propuesta de valor

> «ARIS permite que el conocimiento construido por estudiantes y docentes no desaparezca al
> finalizar una entrega: conserva su historia, sus revisiones y su contexto para que pueda
> seguir aportando a la institución».

## 4. Valores no negociables

1. **Educación como bien social** — pensado para colegios públicos, centros de formación y
   entidades sin ánimo de lucro, con bajo costo de adopción.
2. **Memoria antes que apariencia** — versiones, fechas, autoría y vínculos se protegen.
3. **Transparencia y verificabilidad** — cada resultado se explica por eventos y datos.
4. **Privacidad desde el diseño** — minimización de datos, especialmente de menores.
5. **Accesibilidad como requisito** — móviles modestos, teclado, conexiones lentas.
6. **Simplicidad operacional** — una institución con poco personal técnico debe poder
   desplegar, respaldar y recuperar la plataforma.
7. **Control humano** — evaluación, publicación, retención y permisos no se delegan por
   defecto a algoritmos.
8. **Independencia y portabilidad** — tecnologías mantenidas, datos exportables, formatos
   abiertos, sin servicios comerciales obligatorios.
9. **Progreso continuo** — un flujo robusto de punta a punta vale más que treinta pantallas
   sin backend.
10. **Sostenibilidad** — sin costos innecesarios para la institución.

## 5. Alcance del MVP institucional

Una instalación por institución, con:

- Autenticación segura y recuperación de cuenta por token.
- Roles institucionales y responsabilidades por proyecto.
- Proyectos con membresías y estados.
- Guías/actividades con versiones publicadas e inmutables.
- Asignaciones explícitas de actividad a proyecto, grupo o usuario.
- Entregas con versiones inmutables y estados explícitos.
- Documentos privados con checksum, MIME validado y descarga autorizada.
- Revisiones sobre una versión exacta, con comentarios conservados.
- Repositorio interno y publicación **aprobada** de una versión concreta.
- Auditoría de acciones relevantes.
- Búsqueda esencial por metadatos autorizados.
- Respaldo y restauración verificados.

## 6. Fuera del MVP

- Videoconferencias, chat permanente, red social, streaming o transcodificación de video.
- Renderizado de documentos complejos en servidor compartido.
- Calificación automática por IA, perfilado, biometría, vigilancia o predicción individual.
- Multitenencia SaaS completa, microservicios, Kubernetes, Redis o websockets obligatorios.
- Aplicación móvil nativa.
- Migración automática de bases de proyectos anteriores.
- Contenidos, cifras, reseñas o usuarios ficticios en la interfaz de producción.

## 7. Horizonte de evolución

| Etapa | Contenido |
|---|---|
| MVP institucional | El alcance de §5, en una sola institución |
| Piloto | Políticas cerradas, permisos definitivos, publicación controlada, métricas reales, QA de seguridad, móvil y operación supervisada |
| Producto maduro | Portabilidad institucional, archivado y exportación, perfiles de conservación, flujos y rúbricas configurables, notificaciones, gobernanza |
| Futuro opcional (no comprometido) | Búsqueda semántica, asistencia IA para clasificar o resumir, detección de duplicados, interoperabilidad con LMS, API pública |

La IA **no** es requisito para leer, subir, revisar o exportar documentos. Cualquier
incorporación futura requiere requisito aprobado, evaluación de privacidad y prueba.

## 8. Cómo se mide el éxito (sin inventar cifras)

Indicadores verificables a partir de datos reales del sistema:

- Porcentaje de entregas con versión, autor y fecha resueltos.
- Revisiones asociadas a una versión exacta (no a «la última»).
- Descargas autorizadas vs. intentos denegados (señal de control, no de uso).
- Restauraciones de respaldo exitosas en laboratorio.
- Consultas del repositorio público resueltas sin exponer contenido no publicado.

No se prometen latencias, adopción ni reducción de riesgo sin medición en un entorno real.

## 9. Restricciones de cumplimiento

El tratamiento de datos personales de la comunidad educativa, y en particular de menores,
exige política institucional y revisión jurídica. Se toma como referencia la normativa
colombiana de protección de datos personales (Ley 1581 de 2012 y sus desarrollos), **sin
afirmar certificación ni sustituir asesoría legal**. Ver
`docs/product/DECISIONES-INSTITUCIONALES.md`.
