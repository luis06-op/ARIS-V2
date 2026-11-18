# ADR-013 — Paquete reproducible de fuentes

- **Fecha:** 2026-11-18
- **Estado:** aceptada
- **Fase:** F9 iniciada.

## Decisión

`aris:empaquetar-fuentes` exige un árbol Git limpio y lee los blobs del commit HEAD,
con lista permitida de rutas. Produce un ZIP sin compresión y `MANIFIESTO.json`
con commit, inventario, tamaños y SHA-256. Orden, fecha del commit y permisos se fijan
para obtener bytes reproducibles con el mismo entorno PHP/libzip. El destino debe
ser nuevo; un error elimina únicamente el paquete que se estaba construyendo.

Se excluyen entorno real, bases, evidencias, fixtures, dependencias y assets compilados.
Se incluye `.env.example` y los archivos de conservación de directorios de Laravel.
Los límites técnicos son 10.000 archivos, 64 MiB por archivo, 256 MiB en total y
1 MiB de manifiesto. No representan cuotas académicas aprobadas.

`aris:verificar-paquete` comprueba el inventario exacto, archivos obligatorios, rutas,
tamaños y hashes sin extraer entradas. Rechaza enlaces y duplicados. El manifiesto
no es una firma: demuestra coherencia interna, no autenticidad del autor ni que
los archivos correspondan al commit declarado por un tercero. La procedencia exige
comparar el hash completo con una referencia confiable.

## Alcance

Es una entrega de fuentes, todavía requiere dependencias y compilación. No es una
candidata desplegable certificada. F9 no se cierra: faltan instalación limpia integral,
QA visual y las puertas operativas e institucionales del plan.

Las pruebas usan repositorios temporales sintéticos y ZIP manipulados: reproducibilidad,
exclusiones, árbol sucio, destino existente, enlace versionado, corrupción, ausencias,
entradas adicionales, enlaces ZIP, JSON inválido, traversal y límites del manifiesto.
