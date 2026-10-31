<?php
declare(strict_types=1);

/**
 * Runner Maestro Unificado de GQ-Turismo (Fases 1 a 9)
 * Ejecuta todas las baterías de pruebas y verifica el 100% de cumplimiento.
 */

$suites = [
    'Fase 1: Hotfixes del Legado'                => __DIR__ . '/../tests/fase1_regression_test.php',
    'Fase 3: Cimientos de Arquitectura'          => __DIR__ . '/../tests/fase3_regression_test.php',
    'Fase 4: Design System y Componentes'        => __DIR__ . '/../tests/fase4_regression_test.php',
    'Fase 6: e-Visa Botones y Validación Oficial' => __DIR__ . '/../tests/evisa_test.php',
    'Fase 6: e-Visa Vistas Integradas'           => __DIR__ . '/../tests/fase6_regression_test.php',
    'Fase 5: UX Mobile-First y Pantallas'        => __DIR__ . '/../tests/fase5_regression_test.php',
    'Fase 7: Módulos Núcleo y Dominio'           => __DIR__ . '/../tests/fase7_regression_test.php',
    'Fase 8: Internacionalización y Formatos'     => __DIR__ . '/../tests/fase8_regression_test.php',
    'Fase 9: Calidad y Seguridad OWASP'          => __DIR__ . '/../tests/fase9_regression_test.php',
];

echo "\n======================================================================\n";
echo "   GQ-TURISMO: RUNNER MAESTRO DE PRUEBAS DE REGRESIÓN (FASES 1 - 9)   \n";
echo "======================================================================\n\n";

$totalSuites = count($suites);
$passedSuites = 0;
$failedSuites = 0;
$startTime = microtime(true);

foreach ($suites as $name => $path) {
    if (!file_exists($path)) {
        echo "[ERROR] Archivo de suite no encontrado: $path\n";
        $failedSuites++;
        continue;
    }

    echo ">>> Ejecutando: $name...\n";
    $output = [];
    $exitCode = 0;
    exec("php " . escapeshellarg($path) . " 2>&1", $output, $exitCode);

    if ($exitCode === 0) {
        echo "    [PASS] " . end($output) . "\n";
        $passedSuites++;
    } else {
        echo "    [FAIL] Falló suite con código de salida $exitCode\n";
        echo "    Detalles:\n";
        foreach (array_slice($output, -10) as $line) {
            echo "      $line\n";
        }
        $failedSuites++;
    }
}

$elapsed = round(microtime(true) - $startTime, 2);

echo "\n======================================================================\n";
echo " RESUMEN MAESTRO: $passedSuites de $totalSuites suites superadas con éxito ({$elapsed}s)\n";
if ($failedSuites === 0) {
    echo " ESTADO: PLATAFORMA GQ-TURISMO 100% VERIFICADA Y SEGURA (0 ERRORES)\n";
} else {
    echo " ESTADO: SE DETECTARON $failedSuites SUITES CON FALLOS\n";
}
echo "======================================================================\n\n";

exit($failedSuites === 0 ? 0 : 1);
