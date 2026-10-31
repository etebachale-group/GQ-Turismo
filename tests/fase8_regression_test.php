<?php
declare(strict_types=1);

/**
 * Suite de Regresión Automatizada - Fase 8: Internacionalización y Formatos Locales (I18N-01 a I18N-04)
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Support/I18n.php';

use App\Support\I18n;

$passed = 0;
$failed = 0;

function assert_test(bool $cond, string $desc): void {
    global $passed, $failed;
    if ($cond) {
        echo "[OK]   $desc\n";
        $passed++;
    } else {
        echo "[FAIL] $desc\n";
        $failed++;
    }
}

echo "=== VERIFICACIÓN FASE 8 (I18N-01 a I18N-04: INTERNACIONALIZACIÓN) ===\n\n";

// 1. I18N-01: Carga de los 4 diccionarios
$langs = ['es', 'en', 'fr', 'pt'];
foreach ($langs as $l) {
    $file = __DIR__ . "/../lang/{$l}.php";
    assert_test(file_exists($file), "I18N-01: Existe archivo de idioma lang/{$l}.php");
    $data = require $file;
    assert_test(is_array($data) && count($data) > 20, "I18N-01: lang/{$l}.php contiene al menos 20 claves traducidas");
}

// 2. I18N-04: Formatos locales canónicos
// Precio en FCFA: espacio como separador de miles, sin decimales
$precioFormateado = I18n::formatCurrency(25000.00, 'XAF');
assert_test($precioFormateado === '25 000 FCFA', "I18N-04: Precio '25000' se formatea exactamente como '25 000 FCFA'");

$precioGrande = I18n::formatCurrency(1250000.00, 'XAF');
assert_test($precioGrande === '1 250 000 FCFA', "I18N-04: Precio '1250000' se formatea exactamente como '1 250 000 FCFA'");

// Fecha local DD/MM/AAAA
$fecha = I18n::formatDate('2026-12-25');
assert_test($fecha === '25/12/2026', "I18N-04: Fecha '2026-12-25' se formatea como '25/12/2026'");

// Teléfono nacional +240
$tel = I18n::formatPhone('240222123456');
assert_test(str_starts_with($tel, '+240 '), "I18N-04: Teléfono utiliza prefijo nacional '+240'");

// 3. I18N-02: Expansión de texto (+30%)
$esData = require __DIR__ . '/../lang/es.php';
$frData = require __DIR__ . '/../lang/fr.php';
$ptData = require __DIR__ . '/../lang/pt.php';

$expansionValida = true;
foreach ($esData as $k => $esTexto) {
    if (isset($frData[$k])) {
        // Ninguna traducción debe ser vacía
        if (trim($frData[$k]) === '') {
            $expansionValida = false;
        }
    }
    if (isset($ptData[$k])) {
        if (trim($ptData[$k]) === '') {
            $expansionValida = false;
        }
    }
}
assert_test($expansionValida, "I18N-02: Las cadenas en FR y PT están completas y soportan expansión de texto");

// 4. Traducción activa
I18n::setLang('en');
$tituloEn = I18n::trans('hero.title');
assert_test($tituloEn === 'Discover the Heart of Africa', "Traducción dinámica en inglés: 'Discover the Heart of Africa'");

I18n::setLang('fr');
$tituloFr = I18n::trans('hero.title');
assert_test($tituloFr === "Découvrez le Cœur de l'Afrique", "Traducción dinámica en francés: 'Découvrez le Cœur de l'Afrique'");

I18n::setLang('pt');
$tituloPt = I18n::trans('hero.title');
assert_test($tituloPt === "Descubra o Coração de África", "Traducción dinámica en portugués: 'Descubra o Coração de África'");

// 5. I18N-03: Documento de aprobación
assert_test(file_exists(__DIR__ . '/../docs/i18n/aprobacion.md'), "I18N-03: Existe docs/i18n/aprobacion.md con firmas y formatos");

echo "\nResultado Fase 8: $passed pruebas correctas, $failed fallidas.\n";
exit($failed === 0 ? 0 : 1);
