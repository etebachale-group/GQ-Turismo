# AGENTS.md · GQ-Turismo

Plataforma de turismo para Guinea Ecuatorial: destinos, itinerarios, reservas, pagos en efectivo o transferencia, chat, publicidad.
Fuente de verdad: `GQ-TURISMO_PLAN_MAESTRO_ANTIGRAVITY.md`. Si hay contradicción, manda ese documento.

## Reglas no negociables
1. Ningún secreto en el código. Usar `.env` (nunca versionado).
2. Ningún precio viene del cliente. El monto sale de `servicio_tarifas` o `pedido_items`.
3. Ninguna consulta SQL concatena entrada. Usar consultas preparadas.
4. Toda acción que modifica datos: POST, CSRF y verificación de permisos.
5. Ningún endpoint público acepta `rol` ni `tipo_usuario`.
6. Toda subida pasa por `FileUpload`.
7. Identidad: sesión y chat usan `usuarios.id`. `proveedores.id` se relaciona por `user_id`. Nunca comparar ambos.
8. Colores, tamaños y espacios solo desde `gq-design-tokens.css`.
9. Textos en `lang/`. Ningún texto fijo en vistas.
10. URLs oficiales solo desde `config/fuentes_oficiales.php` por ID.
11. No tocar el legado salvo tickets `H-*`.
12. No borrar datos sin respaldo y registro en `docs/`.

## Comandos
- `php tests/evisa_test.php` → pruebas del botón de e-Visa
- `php bin/test_runner.php` → suite (ver limitaciones en el plan, sección 5.5)
- `git checkout -b <tipo>/<ticket>-<descripcion>` → rama de ticket

## Cuando no estés seguro
Detente. Deja el ticket en Bloqueado y escribe la pregunta concreta en el reporte. No inventes reglas de negocio.
