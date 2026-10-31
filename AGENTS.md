# Reglas para agentes en GQ-Turismo

## Prohibido
- Commitear secretos, contraseñas, dumps SQL o archivos `.env`.
- Tocar la base de datos de producción. Solo staging o local con datos anonimizados.
- Ejecutar `migrate:fresh`, `db:wipe` o cualquier comando destructivo fuera de local.
- Hacer push directo a `main`. Todo cambio va por PR o rama de feature.
- Desactivar, saltar o borrar tests para que el CI pase.
- Aceptar precios, roles o IDs de propietario enviados por el cliente.
- Eliminar datos por GET.
- Usar `DB::statement` o `DB::select` con variables sin bindings.

## Obligatorio
- Una tarea = una rama `feat/ID-descripcion`.
- Antes de codear: leer la tarea completa, los ADR relacionados y `docs/inventario/matriz.md`.
- Escribir primero la verificación/test y luego el código.
- Si una decisión no está en un ADR, detenerse y preguntar.
- Si una tarea exige tocar algo fuera de su alcance, anotarlo y no hacerlo.

## Entregable de cada tarea
1. Resumen de cambios (qué y por qué).
2. Lista de archivos modificados.
3. Evidencia de verificación (pruebas locales exitosas).
4. Riesgos y pendientes.
5. Pasos para verificar manualmente.
