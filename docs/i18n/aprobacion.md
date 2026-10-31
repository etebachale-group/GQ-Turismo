# Aprobación y Revisión Lingüística - GQ-Turismo (I18N-03)

## Idiomas Soportados
1. **Español (`es`)**: Idioma oficial principal y de trabajo en Guinea Ecuatorial.
2. **Francés (`fr`)**: Idioma oficial de la CEMAC y de los países fronterizos (Camerún, Gabón).
3. **Portugués (`pt`)**: Idioma co-oficial de Guinea Ecuatorial y miembro de la CPLP.
4. **Inglés (`en`)**: Idioma vehicular para el turismo internacional y solicitudes de e-Visa.

## Formatos Locales Canónicos (I18N-04)
- **Moneda**: Franco CFA de África Central (`XAF` / `FCFA`). Separador de miles con espacio y sin decimales: ej. `25 000 FCFA`.
- **Equivalencia de referencia**: 1 EUR = 655,957 FCFA (paridad fija CEMAC).
- **Fechas**: Formato local oficial `DD/MM/AAAA`.
- **Teléfono**: Prefijo nacional oficial `+240` (Guinea Ecuatorial).

## Verificación de Expansión de Texto (+30%) (I18N-02)
- Se ha verificado que las etiquetas y botones traducidos al francés y portugués (que presentan una expansión natural del 20% al 30% respecto al español) no desbordan los contenedores táctiles ni rompen las tarjetas de catálogo ni la barra inferior móvil.
- El diseño utiliza `flex-wrap: wrap`, `word-break: normal` y alturas mínimas táctiles de `44px` (`--touch-min`).

## Estado
- [x] Extracción a `lang/es.php`, `lang/en.php`, `lang/fr.php`, `lang/pt.php`.
- [x] Helper `__()` y clase `App\Support\I18n`.
- [x] Selector y persistencia de idioma en sesión y cookie.
- [x] Formatos de moneda `format_currency()` en FCFA sin decimales.
- [x] Aprobación lingüística completada.
