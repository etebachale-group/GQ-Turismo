<?php
/**
 * Test de regresión para Fase 6: Integración del Botón e-Visa
 */
require_once __DIR__ . '/../includes/evisa.php';

$passed = 0;
$failed = 0;

function assert_true($cond, $desc) {
    global $passed, $failed;
    if ($cond) {
        echo "[OK] $desc\n";
        $passed++;
    } else {
        echo "[FAIL] $desc\n";
        $failed++;
    }
}

// 1. VI-01: Header link
$headerContent = file_get_contents(__DIR__ . '/../includes/header.php');
assert_true(strpos($headerContent, 'boton_evisa') !== false || strpos($headerContent, 'Solicitar e-Visa') !== false, "VI-01: Header incluye enlace o botón e-Visa");

// 2. VI-02: Home page
$indexContent = file_get_contents(__DIR__ . '/../index.php');
assert_true(strpos($indexContent, 'boton_evisa') !== false, "VI-02: index.php contiene llamada a boton_evisa()");
assert_true(strpos($indexContent, '¿Viajas a Guinea Ecuatorial desde el exterior?') !== false, "VI-02: index.php contiene sección informativa de visado oficial");

// 3. VI-03: Detalle de destino
$detalleContent = file_get_contents(__DIR__ . '/../detalle_destino.php');
assert_true(strpos($detalleContent, 'boton_evisa') !== false, "VI-03: detalle_destino.php contiene llamada a boton_evisa()");
assert_true(strpos($detalleContent, 'Aviso e-Visa') !== false, "VI-03: detalle_destino.php contiene bloque de aviso de visado");

// 4. VI-05: Mis pedidos
$pedidosContent = file_get_contents(__DIR__ . '/../mis_pedidos.php');
assert_true(strpos($pedidosContent, 'boton_evisa') !== false, "VI-05: mis_pedidos.php contiene llamada a boton_evisa()");
assert_true(strpos($pedidosContent, 'Banner Informativo e-Visa') !== false, "VI-05: mis_pedidos.php contiene banner informativo de e-Visa");

// 5. VI-04: Documentación
assert_true(file_exists(__DIR__ . '/../docs/evisa/verificacion_manual.md'), "VI-04: Existe docs/evisa/verificacion_manual.md");

// 6. Atributos de seguridad del botón HTML
$htmlBtn = boton_evisa('es');
assert_true(strpos($htmlBtn, 'target="_blank"') !== false, "Botón abre en pestaña nueva (target=_blank)");
assert_true(strpos($htmlBtn, 'rel="noopener noreferrer"') !== false, "Botón tiene rel noopener noreferrer");
assert_true(strpos($htmlBtn, 'egonline.vfsevisa.com') !== false, "Botón apunta a dominio oficial seguro https://egonline.vfsevisa.com");

echo "\nResultado Fase 6: $passed pruebas correctas, $failed fallidas.\n";
exit($failed === 0 ? 0 : 1);
