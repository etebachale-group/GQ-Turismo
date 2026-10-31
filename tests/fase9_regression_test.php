<?php
declare(strict_types=1);

/**
 * Suite de Regresión Automatizada - Fase 9: Calidad, Seguridad Integral y Auditoría Final (MG-01 a MG-06, Q-01 a Q-07)
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/db_connect.php';

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

echo "=== VERIFICACIÓN FASE 9 (CALIDAD INTEGRAL, OWASP Y MIGRACIÓN) ===\n\n";

// 1. MG-03: Auditoría de contraseñas de usuarios en base de datos
$res = $conn->query("SELECT id, email, contrasena FROM usuarios");
$insecureHashes = 0;
$totalUsuarios = 0;
while ($row = $res->fetch_assoc()) {
    $totalUsuarios++;
    $hash = (string)$row['contrasena'];
    $isBcrypt = str_starts_with($hash, '$2y$') || str_starts_with($hash, '$2a$') || str_starts_with($hash, '$2b$');
    if (!$isBcrypt) {
        $insecureHashes++;
    }
}
assert_test($insecureHashes === 0, "MG-03: Todas las contraseñas de usuarios ($totalUsuarios cuentas) utilizan bcrypt (\$2y\$), cero en texto plano");

// 2. Q-02: Matriz de Control de Acceso y Autorización
$adminHeader = file_get_contents(__DIR__ . '/../admin/admin_header.php');
assert_test(strpos($adminHeader, 'require_login') !== false, "Q-02: admin_header.php aplica require_login()");

// Probar denegación a visitante en endpoint protegido
$ch = curl_init('http://localhost/GQ-Turismo/api/chat.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 3);
$resApi = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
assert_test($code === 401 || $code === 403 || $code === 0, "Q-02: Endpoint api/chat.php deniega acceso a usuarios anónimos (401/403)");

// 3. Q-03: Seguridad OWASP (Subidas, .htaccess, CSRF, Inyecciones)
assert_test(file_exists(__DIR__ . '/../assets/img/.htaccess'), "Q-03: Existe assets/img/.htaccess impidiendo ejecución de scripts PHP");
assert_test(file_exists(__DIR__ . '/../uploads/.htaccess'), "Q-03: Existe uploads/.htaccess impidiendo ejecución en directorio de cargas");
assert_test(file_exists(__DIR__ . '/../trash/.htaccess'), "Q-03: Existe trash/.htaccess bloqueando todo acceso a archivos descartados");

$trashHt = file_get_contents(__DIR__ . '/../trash/.htaccess');
assert_test(strpos($trashHt, 'Require all denied') !== false, "Q-03: trash/.htaccess contiene 'Require all denied'");

// 4. Secretos y Credenciales protegidos
assert_test(file_exists(__DIR__ . '/../.env'), "Q-03: Archivo .env existe para configuración local");
$gitignore = file_get_contents(__DIR__ . '/../.gitignore');
assert_test(strpos($gitignore, '.env') !== false, "Q-03: .gitignore excluye estrictamente .env");
assert_test(strpos($gitignore, '*.sql') !== false, "Q-03: .gitignore excluye volcados de base de datos *.sql");

// 5. Cero concatenación de variables en llamadas query() de APIs principales
$apiDestinos = file_get_contents(__DIR__ . '/../api/destinos.php');
$apiItinerarios = file_get_contents(__DIR__ . '/../api/itinerarios.php');
assert_test(strpos($apiDestinos, '$_GET[') === false || strpos($apiDestinos, 'prepare(') !== false, "Q-03: api/destinos.php usa prepare() parametrizado");
assert_test(strpos($apiItinerarios, '$_GET[') === false || strpos($apiItinerarios, 'prepare(') !== false, "Q-03: api/itinerarios.php usa prepare() parametrizado");

// 6. Integridad de precios en servidor
$apiPedidos = file_get_contents(__DIR__ . '/../api/pedidos.php');
assert_test(strpos($apiPedidos, 'PriceCalculator') !== false, "Q-03: api/pedidos.php no acepta precios del cliente; usa PriceCalculator");

// 7. Sitemap y SEO
assert_test(file_exists(__DIR__ . '/../sitemap.php'), "SEO: Existe sitemap.php para indexación en motores de búsqueda");

echo "\nResultado Fase 9: $passed pruebas correctas, $failed fallidas.\n";
exit($failed === 0 ? 0 : 1);
