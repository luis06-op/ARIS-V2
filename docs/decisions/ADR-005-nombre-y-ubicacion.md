# ADR-005 — Nombre, ubicación y publicación del repositorio

- **Estado:** aceptada (publicación remota pendiente de autorización)
- **Fecha:** 2026-11-11
- **Contexto de fase:** F0

## Contexto

El documento rector denomina al producto **ARIS 2.0** y propone la carpeta `ARIS-2.0/`. La
instrucción del usuario pide un repositorio llamado **ARIS-V2**.

## Decisión

- **Producto y documentación:** «ARIS 2.0».
- **Repositorio y directorio:** `ARIS-V2`.
- **Ubicación:** `/home/luis/Proyectos/github/ARIS-V2`, junto a los demás proyectos del
  espacio de trabajo, en carpeta nueva y aislada.
- **Rama principal:** `main`.
- **Remoto:** ninguno. No hay push, ni repositorio público, ni licencia elegida.

`PROJECT_ROOT` se descubre siempre desde el entorno; no se codifica la ruta absoluta en código,
pruebas, configuración ni scripts.

## Motivo del nombre

«ARIS-V2» es un identificador de repositorio corto y legible; «ARIS 2.0» es el nombre de
producto. Mantener ambos evita renombrar rutas más adelante y no altera ninguna decisión
técnica.

## Consecuencias

- La documentación usa «ARIS 2.0» para el producto y `ARIS-V2` para el repositorio.
- Crear un remoto, publicar o cambiar la licencia exige autorización explícita del usuario.
- Si en el futuro se adopta otro nombre comercial, solo cambian `README.md` y la marca, no el
  modelo de datos.
