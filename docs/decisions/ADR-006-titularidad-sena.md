# ADR-006 — Titularidad del código y condiciones de publicación

- **Estado:** aceptada (titularidad) · **publicación pública pendiente de confirmación**
- **Fecha:** 2026-11-11
- **Contexto de fase:** F0

## Contexto

ARIS 2.0 se construye en el marco de una institución pública de formación. El responsable del
proyecto indicó en esta sesión que **los derechos pertenecen al SENA**. El repositorio ya está
publicado como **público** en GitHub, decisión tomada por el usuario en esta misma sesión.

Publicar código institucional sin un aviso explícito de titularidad deja ambigüedad legal y
puede interpretarse como cesión implícita a terceros.

## Decisión

1. La obra es **titularidad del SENA**, año 2026, según instrucción del responsable.
2. Se añade `AVISO-DE-DERECHOS.md` en la raíz con el alcance y las restricciones de uso.
3. **No se aplica ninguna licencia permisiva** (MIT, Apache, GPL) mientras el SENA no la
   autorice por escrito. Sin licencia, rige el derecho de autor por defecto: todos los derechos
   reservados.
4. El `README.md` declara la titularidad y remite al aviso.
5. La publicación pública queda registrada como decisión del usuario, con la confirmación
   formal del SENA pendiente (`PI-011`).

## Alternativas consideradas

| Alternativa | Motivo del descarte |
|---|---|
| MIT / Apache-2.0 | Cedería derechos que no corresponden al agente ni al usuario |
| GPL-3.0 | Misma objeción, además de imponer copyleft sin mandato institucional |
| Sin aviso de derechos | Ambigüedad legal en un repositorio público |
| Repositorio privado | El usuario eligió público; se mantiene, con el aviso y la confirmación pendiente |

## Consecuencias

- (+) La titularidad queda declarada y es coherente con el marco institucional.
- (+) Un tercero no puede alegar buena fe para reutilizar el código.
- (−) Un repositorio público sin licencia no permite contribuciones externas legítimas: no se
  aceptan PR de terceros hasta que exista autorización.
- (−) Si el SENA exige otra figura (licencia institucional, transferencia, contrato), habrá que
  actualizar este ADR, el aviso y el README.

## Pendiente

1. Confirmación escrita del SENA sobre la publicación pública del repositorio.
2. Decisión sobre licencia institucional, si la hubiera.
3. Definir si se aceptan contribuciones y bajo qué términos.
