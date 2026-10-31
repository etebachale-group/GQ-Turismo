<?php
/**
 * Test Suite de Regresión Completo - Fase 3 (Cimientos de Arquitectura)
 * Verifica F-01 hasta F-07.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Support\Env;
use App\Support\Auth;
use App\Support\Logger;
use App\Support\ErrorHandler;
use App\Testing\TestCase;
use App\Domain\Supplier\SupplierService;

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
echo "EJECUTANDO BATERÍA DE PRUEBAS DE REGRESIÓN - FASE 3\n";
echo "=======================================================\n\n";

// -------------------------------------------------------------
// F-01: Env Loader
// -------------------------------------------------------------
echo "[F-01] Loader de Entorno Centralizado:\n";
Env::reset();
assert_test("F-01: Env::get('DB_NAME') devuelve valor no vacío", !empty(Env::get('DB_NAME')));
assert_test("F-01: Env::get con valor por defecto funciona", Env::get('NON_EXISTENT_KEY_123', 'fallback_val') === 'fallback_val');
assert_test("F-01: Env parsea booleanos correctamente", is_bool(Env::get('APP_DEBUG')));

// -------------------------------------------------------------
// F-02: Base de datos de pruebas aislada
// -------------------------------------------------------------
echo "\n[F-02] Base de datos de pruebas aislada:\n";
assert_test("F-02: Archivo .env.testing existe", file_exists(__DIR__ . '/../.env.testing'));
assert_test("F-02: Script bin/migrate_test.php existe", file_exists(__DIR__ . '/../bin/migrate_test.php'));

// Test enforcement: TestCase must abort if DB does not end with _test
$threwSecurityException = false;
try {
    TestCase::assertValidTestDatabase('production_db');
} catch (\RuntimeException $e) {
    $threwSecurityException = str_contains($e->getMessage(), '_test');
}
assert_test("F-02: TestCase aborta de inmediato si DB no termina en '_test'", $threwSecurityException);

// Conectar a BD test
$testConn = TestCase::getConnection();
assert_test("F-02: Conexión exitosa a la base de datos de pruebas terminada en '_test'", 
    $testConn->ping() && str_ends_with(Env::get('DB_NAME'), '_test')
);

// -------------------------------------------------------------
// F-03: Middleware requerir_rol()
// -------------------------------------------------------------
echo "\n[F-03] Middleware central de autorización:\n";
assert_test("F-03: Función requerir_rol existe", function_exists('requerir_rol'));
assert_test("F-03: Clase Auth existe", class_exists(Auth::class));

// Test API 401 unauthenticated
$cmdApi401 = 'php ' . escapeshellarg(__DIR__ . '/helpers/auth_subtest.php') . ' unauthenticated';
$outApi401 = shell_exec($cmdApi401);
assert_test("F-03: Auth responde 401 JSON a petición API no autenticada", str_contains((string)$outApi401, 'unauthenticated'));

// Test API 403 unauthorized
$cmdApi403 = 'php ' . escapeshellarg(__DIR__ . '/helpers/auth_subtest.php') . ' unauthorized';
$outApi403 = shell_exec($cmdApi403);
assert_test("F-03: Auth responde 403 JSON si rol no autorizado", str_contains((string)$outApi403, 'unauthorized'));

// -------------------------------------------------------------
// F-04: Contrato de servicios de proveedores
// -------------------------------------------------------------
echo "\n[F-04] Contrato de servicios de proveedores (SupplierService):\n";
assert_test("F-04: Interfaz SupplierServiceInterface existe", interface_exists(\App\Domain\Supplier\SupplierServiceInterface::class));
assert_test("F-04: Clase SupplierService existe", class_exists(SupplierService::class));

$supplierService = new SupplierService($testConn);

// Create a supplier fixture in test DB
$testConn->query("SET FOREIGN_KEY_CHECKS = 0");
$testConn->query("DELETE FROM agencias WHERE contacto_email = 'test_agency@gq.test'");
$testConn->query("INSERT INTO agencias (id_usuario, nombre_agencia, descripcion, contacto_email) VALUES (9999, 'Agencia Test Cimientos', 'Desc', 'test_agency@gq.test')");
$agencyId = (int)$testConn->insert_id;

$supplier = $supplierService->obtenerPorId($agencyId, 'agencia');
assert_test("F-04: obtenerPorId retorna instancia Supplier", $supplier !== null && $supplier->nombre === 'Agencia Test Cimientos');

// Add a test service
$testConn->query("INSERT INTO servicios_agencia (id_agencia, nombre_servicio, descripcion, precio) VALUES ($agencyId, 'Tour Malabo', 'Tour guiado', 15000.00)");
$svcId = (int)$testConn->insert_id;

$svcs = $supplierService->listarServicios($agencyId, 'agencia');
assert_test("F-04: listarServicios retorna servicios del proveedor", count($svcs) > 0 && $svcs[0]['nombre_servicio'] === 'Tour Malabo');

$calcPrice = $supplierService->calcularPrecio($svcId, 'agencia', ['cantidad' => 2]);
assert_test("F-04: calcularPrecio calcula precio unitario * cantidad", $calcPrice === 30000.00);

// Clean up
$testConn->query("DELETE FROM servicios_agencia WHERE id = $svcId");
$testConn->query("DELETE FROM agencias WHERE id = $agencyId");
$testConn->query("SET FOREIGN_KEY_CHECKS = 1");

// -------------------------------------------------------------
// F-05: Carga única de tokens de diseño
// -------------------------------------------------------------
echo "\n[F-05] Carga de Tokens de Diseño y Google Fonts:\n";
$publicHeader = file_get_contents(__DIR__ . '/../includes/header.php');
assert_test("F-05: includes/header.php incluye gq-design-tokens.css", strpos($publicHeader, 'gq-design-tokens.css') !== false);
assert_test("F-05: includes/header.php incluye Fraunces & Inter", strpos($publicHeader, 'family=Fraunces') !== false && strpos($publicHeader, 'family=Inter') !== false);

$adminHeader = file_get_contents(__DIR__ . '/../admin/admin_header.php');
assert_test("F-05: admin/admin_header.php incluye gq-design-tokens.css", strpos($adminHeader, 'gq-design-tokens.css') !== false);
assert_test("F-05: admin/admin_header.php no contiene hojas móviles redundantes purgadas", strpos($adminHeader, 'mobile-optimization.css') === false);

// -------------------------------------------------------------
// F-06: Helper monetario FCFA
// -------------------------------------------------------------
echo "\n[F-06] Helper Monetario (FCFA):\n";
assert_test("F-06: format_currency(25000) produce '25 000 FCFA'", format_currency(25000) === '25 000 FCFA');
assert_test("F-06: format_currency(1500.5) redondea a entero en FCFA", format_currency(1500) === '1 500 FCFA');

// -------------------------------------------------------------
// F-07: Error Handler y Logger estructurado
// -------------------------------------------------------------
echo "\n[F-07] Error Handler & Logger:\n";
assert_test("F-07: Clase Logger existe", class_exists(Logger::class));
assert_test("F-07: Clase ErrorHandler existe", class_exists(ErrorHandler::class));
assert_test("F-07: Vista errors/500.php existe", file_exists(__DIR__ . '/../errors/500.php'));
assert_test("F-07: Vista errors/404.php existe", file_exists(__DIR__ . '/../errors/404.php'));

// Test log writing
$testLogMsg = 'Test log message ' . time();
Logger::info($testLogMsg, ['test_key' => 'test_val']);
$logContent = file_get_contents(Logger::getLogFile());
assert_test("F-07: Logger escribe mensaje y contexto estructurado en logs/app.log", 
    strpos($logContent, $testLogMsg) !== false && strpos($logContent, 'test_val') !== false
);

echo "\n-------------------------------------------------------\n";
printf("RESULTADO FINAL FASE 3: %d de %d pruebas superadas.\n", $passed_tests, $total_tests);
echo "-------------------------------------------------------\n\n";

if ($passed_tests === $total_tests) {
    exit(0);
} else {
    exit(1);
}
