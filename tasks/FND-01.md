# FND-01: Estructura del Repositorio, Plantillas y Configuración Base

**Fase:** F2 (Fundamentos) · **Prioridad:** Alta · **Depende de:** G1 (Aprobación de Fase 1)

## Contexto
Establecer la estructura de carpetas estándar del proyecto, reglas rectoras para agentes en `AGENTS.md`, plantilla de PRs y control estricto de secretos en `.gitignore`.

## Alcance
- Incluye:
  - Verificar que `.env` y backups estén ignorados en `.gitignore`.
  - Crear `.github/pull_request_template.md`.
  - Estructurar carpetas de documentación técnica (`docs/adr/`, `docs/inventario/`, `docs/operacion/`, `docs/usuario/`).
- No incluye: Código de la aplicación.

## Criterios de Aceptación
- [ ] `.gitignore` previene el commit de archivos `.env`, `.sql` y credenciales.
- [ ] Plantilla de Pull Request configurada con los 5 puntos requeridos por `AGENTS.md`.

## Verificación
- `git status --ignored` verifica que `.env` está ignorado.
