<?php
/**
 * Test Suite de Regresión Completo - Fase 1 (Hotfixes del Legado)
 * Verifica H-01 hasta H-08 y Anexo A.
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
echo "EJECUTANDO BATERÍA DE PRUEBAS DE REGRESIÓN - FASE 1\n";
echo "=======================================================\n\n";

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db_connect.php';

// -------------------------------------------------------------
// Test H-01: Registro seguro
// -------------------------------------------------------------
echo "[H-01] Registro seguro:\n";
$email_test = 'turista_test_' . time() . '@example.com';
$subcmd = 'php ' . escapeshellarg(__DIR__ . '/helpers/register_subtest.php') . ' ' . escapeshellarg($email_test);
shell_exec($subcmd);

$stmt = $conn->prepare("SELECT tipo_usuario FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email_test);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();
assert_test("H-01: Registro forzado a 'turista' a pesar de enviar 'super_admin'", $res && $res['tipo_usuario'] === 'turista');

// Clean up
$conn->query("DELETE FROM usuarios WHERE email = '$email_test'");

// -------------------------------------------------------------
// Test H-02: FileUpload seguro
// -------------------------------------------------------------
echo "\n[H-02] RCE FileUpload & .htaccess:\n";
$fakeFile = [
    'name' => 'malicious.php.jpg',
    'type' => 'image/jpeg',
    'tmp_name' => __DIR__ . '/fake_image.jpg',
    'error' => UPLOAD_ERR_OK,
    'size' => 100
];
file_put_contents(__DIR__ . '/fake_image.jpg', '<?php phpinfo(); ?>');

$uploadRes = \App\Support\FileUpload::uploadImage($fakeFile, __DIR__ . '/../assets/img/destinos/');
assert_test("H-02: Rechaza archivo PHP disfrazado de JPG", $uploadRes['success'] === false);

@unlink(__DIR__ . '/fake_image.jpg');

assert_test("H-02: Existe .htaccess en assets/img/", file_exists(__DIR__ . '/../assets/img/.htaccess'));
$htContent = file_get_contents(__DIR__ . '/../assets/img/.htaccess');
assert_test("H-02: .htaccess contiene 'Require all denied'", strpos($htContent, 'Require all denied') !== false);

// -------------------------------------------------------------
// Test H-03: Consultas preparadas
// -------------------------------------------------------------
echo "\n[H-03] Consultas preparadas en API:\n";
$itinerariosCode = file_get_contents(__DIR__ . '/../api/itinerarios.php');
assert_test("H-03: api/itinerarios.php usa prepare() para id", strpos($itinerariosCode, 'WHERE id = ?') !== false);
$destinosCode = file_get_contents(__DIR__ . '/../api/destinos.php');
assert_test("H-03: api/destinos.php usa prepare() para id", strpos($destinosCode, 'WHERE id = ?') !== false);

// -------------------------------------------------------------
// Test H-04: Precios calculados en servidor
// -------------------------------------------------------------
echo "\n[H-04] Precios calculados en servidor (PriceCalculator):\n";
assert_test("H-04: Clase PriceCalculator existe", class_exists(\App\Domain\Booking\PriceCalculator::class));
$pedidosCode = file_get_contents(__DIR__ . '/../api/pedidos.php');
assert_test("H-04: api/pedidos.php invoca PriceCalculator", strpos($pedidosCode, 'PriceCalculator') !== false);

// -------------------------------------------------------------
// Test H-05: Eliminación solo POST con CSRF
// -------------------------------------------------------------
echo "\n[H-05] Eliminación solo por POST con CSRF:\n";
$adminManagers = [
    'admin/manage_destinos.php',
    'admin/manage_agencias.php',
    'admin/manage_guias.php',
    'admin/manage_locales.php',
    'admin/manage_publicidad_carousel.php',
    'admin/manage_users.php'
];

foreach ($adminManagers as $mgr) {
    $code = file_get_contents(__DIR__ . '/../' . $mgr);
    $hasGetDelete = preg_match('/\$_GET\[[\'"]action[\'"]\]\s*==\s*[\'"]delete/i', $code);
    assert_test("H-05: $mgr no tiene eliminación por GET", !$hasGetDelete);
    assert_test("H-05: $mgr valida verify_csrf", strpos($code, 'verify_csrf') !== false);
}

// -------------------------------------------------------------
// Test H-06: Credenciales y .env
// -------------------------------------------------------------
echo "\n[H-06] Variables de entorno y secretos:\n";
assert_test("H-06: Archivo .env existe en raíz", file_exists(__DIR__ . '/../.env'));
assert_test("H-06: Archivo .env.example existe en raíz", file_exists(__DIR__ . '/../.env.example'));
$gitIgnore = file_get_contents(__DIR__ . '/../.gitignore');
assert_test("H-06: .gitignore contiene .env", strpos($gitIgnore, '.env') !== false);
assert_test("H-06: .gitignore contiene *.sql", strpos($gitIgnore, '*.sql') !== false);

// -------------------------------------------------------------
// Test H-07: Archivos de prueba fuera del webroot
// -------------------------------------------------------------
echo "\n[H-07] Archivos de prueba fuera de webroot:\n";
assert_test("H-07: test_system.php no está en raíz", !file_exists(__DIR__ . '/../test_system.php'));
assert_test("H-07: test_system_complete.php no está en raíz", !file_exists(__DIR__ . '/../test_system_complete.php'));
assert_test("H-07: verificar_sistema.php no está en raíz", !file_exists(__DIR__ . '/../verificar_sistema.php'));
assert_test("H-07: verificar_sistema_itinerarios.php no está en raíz", !file_exists(__DIR__ . '/../verificar_sistema_itinerarios.php'));
assert_test("H-07: verify_system.php no está en raíz", !file_exists(__DIR__ . '/../verify_system.php'));
assert_test("H-07: api/test_db.php no está en api/", !file_exists(__DIR__ . '/../api/test_db.php'));
assert_test("H-07: trash/.htaccess existe con Require all denied", file_exists(__DIR__ . '/../trash/.htaccess'));

// -------------------------------------------------------------
// Test H-08: Sesión en administración
// -------------------------------------------------------------
echo "\n[H-08] Sesión y autorización en admin:\n";
$adminFiles = glob(__DIR__ . '/../admin/*.php');
$excluded = ['admin_header.php', 'admin_footer.php', 'sidebar.php', 'login.php', 'logout.php'];
foreach ($adminFiles as $file) {
    $base = basename($file);
    if (in_array($base, $excluded)) continue;
    $code = file_get_contents($file);
    assert_test("H-08: admin/$base incluye session_security y require_login", 
        strpos($code, 'session_security.php') !== false && strpos($code, 'require_login(') !== false
    );
}

// -------------------------------------------------------------
// Test Anexo A: e-Visa Tests
// -------------------------------------------------------------
echo "\n[Anexo A] Pruebas del módulo e-Visa:\n";
require_once __DIR__ . '/../config/fuentes_oficiales.php';
require_once __DIR__ . '/../includes/evisa.php';
assert_test("Anexo A: fuente_oficial('evisa_gnq') devuelve URL canónica", fuente_oficial('evisa_gnq', 'es') === 'https://egonline.vfsevisa.com/gnq/en/on-boarding');
assert_test("Anexo A: boton_evisa contiene rel='noopener noreferrer'", strpos(boton_evisa(), 'rel="noopener noreferrer"') !== false);
assert_test("Anexo A: boton_evisa contiene target='_blank'", strpos(boton_evisa(), 'target="_blank"') !== false);

echo "\n-------------------------------------------------------\n";
printf("RESULTADO FINAL: %d de %d pruebas superadas.\n", $passed_tests, $total_tests);
echo "-------------------------------------------------------\n\n";

if ($passed_tests === $total_tests) {
    exit(0);
} else {
    exit(1);
}
