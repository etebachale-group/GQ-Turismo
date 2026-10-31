<?php
/**
 * Test Runner Automatizado — GQ-Turismo
 * Ejecuta la suite de verificación integral en entorno local XAMPP.
 */

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

require_once __DIR__ . '/../vendor/autoload.php';

function get_db(): mysqli {
    $c = @new mysqli("localhost", "root", "", "gq_turismo", 3306);
    $c->set_charset("utf8mb4");
    return $c;
}

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

function run_test(string $name, callable $testFunc) {
    global $totalTests, $passedTests, $failedTests;
    $totalTests++;
    echo "[TEST] $name: ";
    try {
        $result = $testFunc();
        if ($result === true) {
            echo "✅ PASÓ\n";
            $passedTests++;
        } else {
            echo "❌ FALLÓ (Resultado inesperado)\n";
            $failedTests++;
        }
    } catch (\Throwable $e) {
        echo "❌ FALLÓ (" . $e->getMessage() . ")\n";
        $failedTests++;
    }
}

echo "========================================================\n";
echo "SUITE DE PRUEBAS DE VERIFICACIÓN LOCAL — GQ-TURISMO\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "========================================================\n\n";

// Test 1: Conexión MySQL
run_test("Conexión activa a base de datos MySQL local", function() {
    $c = get_db();
    return $c->ping() === true;
});

// Test 2: Bloqueo de elevación de privilegios en registro
run_test("El registro público ignora 'super_admin' y asigna 'turista'", function() {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST['action'] = 'register';
    $_POST['nombre'] = 'QA Test User';
    $email = 'qa_' . uniqid() . '@test.local';
    $_POST['email'] = $email;
    $_POST['contrasena'] = 'Secret123!';
    $_POST['tipo_usuario'] = 'super_admin'; // Intento de exploit

    // Ejecutar en subproceso CLI aislado para no contaminar contexto de conexión
    $cmd = 'php -r "$_SERVER[\'REQUEST_METHOD\']=\'POST\'; $_POST=[\'action\'=>\'register\',\'nombre\'=>\'QA Test\',\'email\'=>\'' . $email . '\',\'contrasena\'=>\'Secret123!\',\'tipo_usuario\'=>\'super_admin\']; ob_start(); chdir(\'api\'); require \'auth.php\'; ob_get_clean();"';
    shell_exec($cmd);

    $c = get_db();
    $stmt = $c->prepare("SELECT tipo_usuario FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $c->query("DELETE FROM usuarios WHERE email = '$email'");
    return ($row['tipo_usuario'] ?? '') === 'turista';
});

// Test 3: Verificación de Precios en el Servidor
run_test("PriceCalculator ignora precios del cliente y consulta la BD", function() {
    $c = get_db();
    $price = \App\Domain\Booking\PriceCalculator::getVerifiedPrice($c, 'agencia', 'servicio', 999999, 999999);
    return is_float($price) && $price === 0.00;
});

// Test 4: FileUpload bloquea archivos maliciosos
run_test("FileUpload rechaza archivos ejecutables (.php)", function() {
    $tempFile = tempnam(sys_get_temp_dir(), 'test_php');
    file_put_contents($tempFile, '<?php phpinfo(); ?>');

    $fakeUpload = [
        'name' => 'malicious.php',
        'type' => 'application/x-php',
        'tmp_name' => $tempFile,
        'error' => UPLOAD_ERR_OK,
        'size' => filesize($tempFile)
    ];

    $res = \App\Support\FileUpload::uploadImage($fakeUpload, sys_get_temp_dir());
    @unlink($tempFile);
    return $res['success'] === false && strpos($res['error'], 'Tipo de archivo no permitido') !== false;
});

// Test 5: Tokens CSRF
run_test("Mecanismo CSRF genera y valida tokens criptográficos", function() {
    $token = csrf_token();
    $valid = verify_csrf($token);
    $invalid = verify_csrf('token_falso_malicioso');
    return $valid === true && $invalid === false;
});

// Test 6: Pasarela de Pagos (PaymentGateway)
run_test("ManualPaymentGateway implementa PaymentGatewayInterface", function() {
    $c = get_db();
    $gateway = new \App\Domain\Payments\ManualPaymentGateway($c);
    return $gateway instanceof \App\Domain\Payments\PaymentGatewayInterface;
});

// Test 7: HTTP Status 200 en Home
run_test("Página de inicio (index.php) responde HTTP 200", function() {
    $ctx = stream_context_create(['http' => ['timeout' => 5]]);
    $content = @file_get_contents('http://localhost/GQ-Turismo/index.php', false, $ctx);
    return $content !== false && strlen($content) > 1000;
});

echo "\n--------------------------------------------------------\n";
echo "RESULTADOS: $passedTests/$totalTests superadas con éxito.\n";
if ($failedTests === 0) {
    echo "ESTADO FINAL: ✅ TODAS LAS PRUEBAS PASARON SATISFACTORIAMENTE.\n";
} else {
    echo "ESTADO FINAL: ⚠️ $failedTests PRUEBAS FALLARON.\n";
}
echo "========================================================\n";
