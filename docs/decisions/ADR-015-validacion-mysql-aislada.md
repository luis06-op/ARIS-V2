# ADR-015 — Validación MySQL aislada

- Fecha: 2026-11-18.
- Estado: aceptada.

Se valida el esquema con MySQL Community 8.4.13 en servidor local separado, socket privado
y red desactivada. La autorización B3 de esta sesión se aplica a datos sintéticos nuevos.

Dos índices automáticos excedían 64 caracteres. Se corrigen sus nombres en las migraciones
de creación para permitir instalar desde cero; no cambian restricciones ni datos. Una
migración posterior no podía resolver un error que impedía completar las iniciales.

Las pruebas de rollback de esquema usan `DatabaseMigrations`, evitando DDL dentro de una
transacción que MySQL confirma implícitamente. La simulación de auditoría fallida es portable.

Se añade un trigger para impedir cambios del hash/tamaño del blob, preservando cambios
operativos de estado y ruta. La integridad de bytes sigue verificándose al descargar.
Los controles no protegen frente a administradores SQL capaces de suprimir los triggers.

Suite aprobada en ambos motores: 123 pruebas y 723 aserciones. No se certifican aquí carga,
disponibilidad, concurrencia ampliada ni una instalación Hostinger.
