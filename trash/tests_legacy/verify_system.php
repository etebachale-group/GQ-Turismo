<?php
// Script de verificación de conexiones y funcionalidad del sistema
header('Content-Type: application/json');

$results = [
    'database' => [],
    'apis' => [],
    'permissions' => [],
    'tables' => [],
    'overall_status' => 'unknown'
];

// 1. Verificar conexión a base de datos
try {
    require_once 'includes/db_connect.php';
    
    if ($conn) {
        $results['database']['connection'] = 'OK';
        $results['database']['host'] = 'localhost';
        $results['database']['database'] = 'gq_turismo';
        
        // Verificar tablas principales
        $tables_to_check = [
            'usuarios', 'destinos', 'agencias', 'guias_turisticos', 
            'lugares_locales', 'itinerarios', 'mensajes', 
            'servicios_agencia', 'servicios_guia', 'servicios_local'
        ];
        
        foreach ($tables_to_check as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->num_rows > 0) {
                $count_result = $conn->query("SELECT COUNT(*) as total FROM $table");
                $count = $count_result->fetch_assoc()['total'];
                $results['tables'][$table] = [
                    'exists' => true,
                    'records' => $count
                ];
            } else {
                $results['tables'][$table] = [
                    'exists' => false,
                    'records' => 0
                ];
            }
        }
        
        $conn->close();
    } else {
        $results['database']['connection'] = 'FAILED';
        $results['database']['error'] = 'No se pudo establecer conexión';
    }
} catch (Exception $e) {
    $results['database']['connection'] = 'ERROR';
    $results['database']['error'] = $e->getMessage();
}

// 2. Verificar APIs
$api_endpoints = [
    'destinos' => 'api/destinos.php?action=get_categories',
    'agencias' => 'api/agencias.php?action=list',
    'guias' => 'api/guias.php?action=list',
    'locales' => 'api/locales.php?action=list',
    'messages' => 'api/messages.php',
    'itinerarios' => 'api/itinerarios.php?action=list'
];

foreach ($api_endpoints as $name => $endpoint) {
    $file_path = __DIR__ . '/' . explode('?', $endpoint)[0];
    if (file_exists($file_path)) {
        $results['apis'][$name] = [
            'exists' => true,
            'path' => $file_path,
            'readable' => is_readable($file_path)
        ];
    } else {
        $results['apis'][$name] = [
            'exists' => false,
            'path' => $file_path
        ];
    }
}

// 3. Verificar permisos de directorios
$directories_to_check = [
    'assets/img/destinos',
    'assets/img/agencias',
    'assets/img/guias',
    'assets/img/locales',
    'api',
    'admin'
];

foreach ($directories_to_check as $dir) {
    $dir_path = __DIR__ . '/' . $dir;
    if (is_dir($dir_path)) {
        $results['permissions'][$dir] = [
            'exists' => true,
            'writable' => is_writable($dir_path),
            'readable' => is_readable($dir_path)
        ];
    } else {
        $results['permissions'][$dir] = [
            'exists' => false
        ];
    }
}

// 4. Determinar estado general
$all_ok = true;

// Verificar base de datos
if ($results['database']['connection'] !== 'OK') {
    $all_ok = false;
}

// Verificar tablas críticas
$critical_tables = ['usuarios', 'destinos', 'mensajes'];
foreach ($critical_tables as $table) {
    if (!isset($results['tables'][$table]) || !$results['tables'][$table]['exists']) {
        $all_ok = false;
        break;
    }
}

// Verificar APIs críticas
$critical_apis = ['destinos', 'messages'];
foreach ($critical_apis as $api) {
    if (!isset($results['apis'][$api]) || !$results['apis'][$api]['exists']) {
        $all_ok = false;
        break;
    }
}

$results['overall_status'] = $all_ok ? 'OK' : 'NEEDS_ATTENTION';
$results['timestamp'] = date('Y-m-d H:i:s');

echo json_encode($results, JSON_PRETTY_PRINT);
?>
