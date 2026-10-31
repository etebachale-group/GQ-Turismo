<?php
/**
 * Test Suite de Regresión Completo - Fase 4 (Design System y Componentes)
 * Verifica DS-01 a DS-04, CMP-01 a CMP-06 y styleguide.php.
 */

$total_tests = 0;
$passed_tests = 0;

function assert_test($description, $condition) {
    global $total_tests, $passed_tests;
    $total_tests++;
    if ($condition) {
        $passed_tests++;
        echo "  [PASS] $description\n";
    } else {
        echo "  [FAIL] $description\n";
    }
}

echo "\n=======================================================\n";
echo "EJECUTANDO BATERÍA DE PRUEBAS DE REGRESIÓN - FASE 4\n";
echo "=======================================================\n\n";

$tokensCss = file_get_contents(__DIR__ . '/../assets/css/gq-design-tokens.css');

// -------------------------------------------------------------
// DS-01: Paleta de Colores
// -------------------------------------------------------------
echo "[DS-01] Paleta de Colores Canónicos:\n";
assert_test("DS-01: Contiene variables Verde Selva (--c-selva-*)", strpos($tokensCss, '--c-selva-600') !== false);
assert_test("DS-01: Contiene variables Azul Atlántico (--c-atlantico-*)", strpos($tokensCss, '--c-atlantico-600') !== false);
assert_test("DS-01: Contiene variables Terracota (--c-terracota-*)", strpos($tokensCss, '--c-terracota-500') !== false);
assert_test("DS-01: Contiene variables Arena (--c-arena-*)", strpos($tokensCss, '--c-arena-100') !== false);
assert_test("DS-01: Contiene variables Ébano y Niebla", strpos($tokensCss, '--c-ebano-900') !== false && strpos($tokensCss, '--c-niebla-100') !== false);

// -------------------------------------------------------------
// DS-02: Tipografía
// -------------------------------------------------------------
echo "\n[DS-02] Tipografía Fluida:\n";
assert_test("DS-02: Define Fraunces como --font-display", strpos($tokensCss, "--font-display: 'Fraunces'") !== false);
assert_test("DS-02: Define Inter como --font-sans", strpos($tokensCss, "--font-sans: 'Inter'") !== false);
assert_test("DS-02: Usa clamp para escala tipográfica fluida", strpos($tokensCss, 'clamp(') !== false);

// -------------------------------------------------------------
// DS-03: Espaciado y Elevación
// -------------------------------------------------------------
echo "\n[DS-03] Espaciado y Elevación:\n";
assert_test("DS-03: Contiene escala de espaciado (--space-xs a --space-2xl)", strpos($tokensCss, '--space-md: 1rem') !== false);
assert_test("DS-03: Contiene sombras canónicas (--shadow-sm a --shadow-xl)", strpos($tokensCss, '--shadow-md:') !== false);

// -------------------------------------------------------------
// DS-04: Reset & Accesibilidad
// -------------------------------------------------------------
echo "\n[DS-04] Reset Moderno y Accesibilidad:\n";
assert_test("DS-04: Contiene box-sizing reset", strpos($tokensCss, 'box-sizing: border-box') !== false);
assert_test("DS-04: Contiene regla :focus-visible accesible", strpos($tokensCss, ':focus-visible') !== false);
assert_test("DS-04: Contiene media query prefers-reduced-motion", strpos($tokensCss, 'prefers-reduced-motion') !== false);

// -------------------------------------------------------------
// CMP-01 a CMP-06: Componentes Canónicos
// -------------------------------------------------------------
echo "\n[CMP-01 a CMP-06] Componentes:\n";
assert_test("CMP-01: Botón e-Visa (.btn--evisa) definido", strpos($tokensCss, '.btn--evisa') !== false);
assert_test("CMP-02: Tarjeta destino (.card-destino) definida", strpos($tokensCss, '.card-destino') !== false);
assert_test("CMP-02: Tarjeta proveedor (.card-proveedor) definida", strpos($tokensCss, '.card-proveedor') !== false);
assert_test("CMP-02: Tarjeta itinerario (.card-itinerario) definida", strpos($tokensCss, '.card-itinerario') !== false);
assert_test("CMP-03: Botón primario y outline (.btn--primary, .btn--outline)", strpos($tokensCss, '.btn--primary') !== false && strpos($tokensCss, '.btn--outline') !== false);
assert_test("CMP-04: Badges de estado semánticos definidos", 
    strpos($tokensCss, '.badge--pendiente') !== false &&
    strpos($tokensCss, '.badge--confirmado') !== false &&
    strpos($tokensCss, '.badge--cancelado') !== false &&
    strpos($tokensCss, '.badge--completado') !== false
);
assert_test("CMP-06: Bottom navigation móvil (.bottom-nav) definida", strpos($tokensCss, '.bottom-nav') !== false);

// -------------------------------------------------------------
// Styleguide local
// -------------------------------------------------------------
echo "\n[Styleguide Local]:\n";
assert_test("Styleguide: styleguide.php existe y es accesible", file_exists(__DIR__ . '/../styleguide.php'));
$sgContent = file_get_contents(__DIR__ . '/../styleguide.php');
assert_test("Styleguide: incluye paleta de colores", strpos($sgContent, 'Paleta de Colores') !== false);
assert_test("Styleguide: incluye render de boton_evisa()", strpos($sgContent, 'boton_evisa') !== false);

echo "\n-------------------------------------------------------\n";
printf("RESULTADO FINAL FASE 4: %d de %d pruebas superadas.\n", $passed_tests, $total_tests);
echo "-------------------------------------------------------\n\n";

if ($passed_tests === $total_tests) {
    exit(0);
} else {
    exit(1);
}
