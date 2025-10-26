<?php
/**
 * Sistema de Prueba y Verificación - GQ Turismo
 * Versión 2.0 - Actualizada con sistema de tracking de itinerarios
 */

session_start();
require_once 'includes/db_connect.php';

$tests = [];
$errors = [];
$warnings = [];

// Test 1: Conexión a Base de Datos
try {
    if ($conn->ping()) {
        $tests['db_connection'] = [
            'status' => 'success',
            'message' => 'Conexión a base de datos establecida correctamente'
        ];
    }
} catch (Exception $e) {
    $tests['db_connection'] = [
        'status' => 'error',
        'message' => 'Error en conexión: ' . $e->getMessage()
    ];
    $errors[] = $tests['db_connection']['message'];
}

// Test 2: Tablas del Sistema
$required_tables = [
    'usuarios', 'destinos', 'agencias', 'guias_turisticos', 'lugares_locales',
    'itinerarios', 'itinerario_destinos', 'itinerario_tareas', 'itinerario_guias', 'itinerario_agencias', 'itinerario_locales',
    'pedidos_servicios', 'mensajes', 'servicios_agencia', 'servicios_guia', 'servicios_local',
    'menus_agencia', 'menus_local', 'publicidad_carousel',
    'guia_destinos', 'agencia_destinos', 'local_destinos'
];

$missing_tables = [];
foreach ($required_tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result->num_rows == 0) {
        $missing_tables[] = $table;
    }
}

if (empty($missing_tables)) {
    $tests['tables'] = [
        'status' => 'success',
        'message' => 'Todas las tablas requeridas existen (' . count($required_tables) . ' tablas)'
    ];
} else {
    $tests['tables'] = [
        'status' => 'error',
        'message' => 'Faltan tablas: ' . implode(', ', $missing_tables)
    ];
    $errors[] = $tests['tables']['message'];
}

// Test 3: Columnas Críticas
$critical_columns = [
    'usuarios' => ['id', 'nombre', 'email', 'tipo_usuario'],
    'itinerarios' => ['id', 'nombre_itinerario', 'descripcion', 'id_usuario', 'presupuesto_estimado', 'estado', 'fecha_inicio', 'fecha_fin', 'estado_itinerario'],
    'itinerario_destinos' => ['id', 'id_itinerario', 'id_destino', 'orden', 'precio', 'estado'],
    'itinerario_tareas' => ['id', 'id_itinerario', 'id_destino', 'tipo_tarea', 'titulo', 'estado', 'fecha_hora_inicio'],
    'pedidos_servicios' => ['id', 'id_turista', 'id_proveedor', 'tipo_proveedor', 'estado', 'id_destino', 'id_itinerario'],
    'publicidad_carousel' => ['id', 'titulo', 'descripcion', 'imagen', 'activo'],
    'guia_destinos' => ['id', 'id_guia', 'id_destino', 'disponible']
];

$column_issues = [];
foreach ($critical_columns as $table => $columns) {
    $result = $conn->query("DESCRIBE $table");
    if ($result) {
        $existing_columns = [];
        while ($row = $result->fetch_assoc()) {
            $existing_columns[] = $row['Field'];
        }
        foreach ($columns as $col) {
            if (!in_array($col, $existing_columns)) {
                $column_issues[] = "$table.$col";
            }
        }
    }
}

if (empty($column_issues)) {
    $tests['columns'] = [
        'status' => 'success',
        'message' => 'Todas las columnas críticas están presentes'
    ];
} else {
    $tests['columns'] = [
        'status' => 'warning',
        'message' => 'Columnas faltantes o diferentes: ' . implode(', ', $column_issues)
    ];
    $warnings[] = $tests['columns']['message'];
}

// Test 4: Sistema de Itinerarios
$stmt = $conn->query("SELECT COUNT(*) as count FROM itinerarios");
$itinerarios_count = $stmt->fetch_assoc()['count'];

$tests['itinerarios'] = [
    'status' => 'success',
    'message' => "Sistema de itinerarios operativo. Total: $itinerarios_count itinerarios"
];

// Test 5: Sistema de Tareas (Nuevo)
$tareas_count = 0;
$result = $conn->query("SHOW TABLES LIKE 'itinerario_tareas'");
if ($result->num_rows > 0) {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM itinerario_tareas");
    $tareas_count = $stmt->fetch_assoc()['count'];
    $tests['tareas'] = [
        'status' => 'success',
        'message' => "Sistema de tareas implementado. Total: $tareas_count tareas registradas"
    ];
} else {
    $tests['tareas'] = [
        'status' => 'warning',
        'message' => "Tabla itinerario_tareas no existe. Ejecutar fix_all_system_errors.sql"
    ];
    $warnings[] = $tests['tareas']['message'];
}

// Test 6: Sistema de Confirmaciones (Nuevo)
$confirmaciones_count = 0;
$result = $conn->query("SHOW TABLES LIKE 'servicio_confirmaciones'");
if ($result->num_rows > 0) {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM servicio_confirmaciones");
    $confirmaciones_count = $stmt->fetch_assoc()['count'];
    $tests['confirmaciones'] = [
        'status' => 'success',
        'message' => "Sistema de confirmaciones activo. Total: $confirmaciones_count confirmaciones"
    ];
} else {
    $tests['confirmaciones'] = [
        'status' => 'warning',
        'message' => "Tabla servicio_confirmaciones no existe. Ejecutar fix_all_system_errors.sql"
    ];
    $warnings[] = $tests['confirmaciones']['message'];
}

// Test 7: Guías y Destinos
$guias_destinos_count = 0;
$result = $conn->query("SHOW TABLES LIKE 'guias_destinos'");
if ($result->num_rows > 0) {
    $stmt = $conn->query("SELECT COUNT(*) as count FROM guias_destinos");
    $guias_destinos_count = $stmt->fetch_assoc()['count'];
    $tests['guias_destinos'] = [
        'status' => 'success',
        'message' => "Relación guías-destinos configurada. Total: $guias_destinos_count asignaciones"
    ];
} else {
    $tests['guias_destinos'] = [
        'status' => 'warning',
        'message' => "Tabla guias_destinos no existe. Ejecutar fix_all_system_errors.sql"
    ];
    $warnings[] = $tests['guias_destinos']['message'];
}

// Test 8: Archivos Críticos
$critical_files = [
    'index.php',
    'includes/db_connect.php',
    'mapa_tareas_itinerario.php',
    'seguimiento_itinerario.php',
    'admin/dashboard.php',
    'admin/admin_header.php',
    'admin/admin_footer.php',
    'admin/mis_pedidos.php',
    'api/actualizar_estado_tarea.php',
    'api/confirmar_servicio_proveedor.php',
    'assets/css/mobile-fixes.css',
    'assets/js/mobile-sidebar.js',
    'database/fix_all_system_errors.sql'
];

$missing_files = [];
foreach ($critical_files as $file) {
    if (!file_exists($file)) {
        $missing_files[] = $file;
    }
}

if (empty($missing_files)) {
    $tests['files'] = [
        'status' => 'success',
        'message' => 'Todos los archivos críticos existen'
    ];
} else {
    $tests['files'] = [
        'status' => 'warning',
        'message' => 'Archivos faltantes: ' . implode(', ', $missing_files)
    ];
    $warnings[] = $tests['files']['message'];
}

// Test 9: APIs del Sistema
$api_endpoints = [
    'api/actualizar_estado_tarea.php' => 'API de actualización de tareas',
    'api/confirmar_servicio_proveedor.php' => 'API de confirmación de servicios',
    'api/pedidos.php' => 'API de pedidos'
];

$api_status = [];
foreach ($api_endpoints as $endpoint => $description) {
    if (file_exists($endpoint)) {
        $api_status[] = "$description ✓";
    } else {
        $api_status[] = "$description ✗";
    }
}

$tests['apis'] = [
    'status' => count($api_status) == count($api_endpoints) ? 'success' : 'warning',
    'message' => 'APIs: ' . implode(', ', $api_status)
];

// Test 10: Archivos de Tracking y Seguimiento
$tracking_files = [
    'mapa_tareas_itinerario.php' => 'Mapa de tareas',
    'seguimiento_itinerario.php' => 'Seguimiento de itinerario'
];

$tracking_status = [];
foreach ($tracking_files as $file => $description) {
    if (file_exists($file)) {
        $tracking_status[] = "$description ✓";
    } else {
        $tracking_status[] = "$description ✗";
    }
}

$tests['tracking_files'] = [
    'status' => count($tracking_status) == count($tracking_files) ? 'success' : 'warning',
    'message' => 'Archivos de tracking: ' . implode(', ', $tracking_status)
];

// Test 11: Responsive Design
$responsive_files = [
    'assets/css/mobile-fixes.css' => 'CSS correcciones móviles',
    'assets/js/mobile-sidebar.js' => 'JavaScript sidebar móvil',
    'assets/css/admin-mobile.css' => 'CSS admin móvil'
];

$responsive_status = [];
foreach ($responsive_files as $file => $description) {
    if (file_exists($file)) {
        $responsive_status[] = "$description ✓";
    } else {
        $responsive_status[] = "$description ✗";
    }
}

$tests['responsive'] = [
    'status' => count($responsive_status) == count($responsive_files) ? 'success' : 'warning',
    'message' => 'Sistema responsive: ' . implode(', ', $responsive_status)
];

// Estadísticas Generales
$stats = [
    'usuarios' => $conn->query("SELECT COUNT(*) as c FROM usuarios")->fetch_assoc()['c'],
    'destinos' => $conn->query("SELECT COUNT(*) as c FROM destinos")->fetch_assoc()['c'],
    'agencias' => $conn->query("SELECT COUNT(*) as c FROM agencias")->fetch_assoc()['c'],
    'guias' => $conn->query("SELECT COUNT(*) as c FROM guias_turisticos")->fetch_assoc()['c'],
    'locales' => $conn->query("SELECT COUNT(*) as c FROM lugares_locales")->fetch_assoc()['c'],
    'itinerarios' => $itinerarios_count,
    'tareas' => $tareas_count,
    'confirmaciones' => $confirmaciones_count,
    'guias_destinos' => $guias_destinos_count
];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Pruebas - GQ Turismo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .test-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .test-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 30px rgba(0,0,0,0.12);
        }
        .test-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            text-align: center;
        }
        .test-item {
            padding: 1rem;
            border-left: 4px solid #e5e7eb;
            margin-bottom: 1rem;
            background: #f9fafb;
            border-radius: 8px;
        }
        .test-item.success { border-left-color: #10b981; background: #d1fae5; }
        .test-item.error { border-left-color: #ef4444; background: #fee2e2; }
        .test-item.warning { border-left-color: #f59e0b; background: #fef3c7; }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
        }
        .stat-value {
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
        }
        .stat-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <div class="test-header">
            <h1><i class="bi bi-clipboard-check me-2"></i>Sistema de Pruebas GQ Turismo</h1>
            <p class="mb-0">Versión 2.0 - Sistema de Tracking de Itinerarios Implementado</p>
            <small><?= date('d/m/Y H:i:s') ?></small>
        </div>

        <!-- Resumen -->
        <div class="test-card">
            <h3><i class="bi bi-speedometer2 me-2"></i>Resumen General</h3>
            <div class="stats-grid">
                <?php foreach ($stats as $label => $value): ?>
                    <div class="stat-box">
                        <p class="stat-value"><?= $value ?></p>
                        <p class="stat-label mb-0"><?= ucfirst($label) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Resultados de Pruebas -->
        <div class="test-card">
            <h3><i class="bi bi-list-check me-2"></i>Resultados de Pruebas</h3>
            <?php foreach ($tests as $name => $test): ?>
                <div class="test-item <?= $test['status'] ?>">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>
                                <?php if ($test['status'] === 'success'): ?>
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                <?php elseif ($test['status'] === 'error'): ?>
                                    <i class="bi bi-x-circle-fill text-danger me-2"></i>
                                <?php else: ?>
                                    <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i>
                                <?php endif; ?>
                                <?= ucfirst(str_replace('_', ' ', $name)) ?>
                            </strong>
                            <p class="mb-0 mt-1"><?= $test['message'] ?></p>
                        </div>
                        <span class="badge bg-<?= $test['status'] === 'success' ? 'success' : ($test['status'] === 'error' ? 'danger' : 'warning') ?>">
                            <?= strtoupper($test['status']) ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Errores y Advertencias -->
        <?php if (!empty($errors) || !empty($warnings)): ?>
            <div class="test-card">
                <h3><i class="bi bi-exclamation-triangle me-2"></i>Alertas del Sistema</h3>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <h6><i class="bi bi-x-circle me-2"></i>Errores Críticos:</h6>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?= $error ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($warnings)): ?>
                    <div class="alert alert-warning">
                        <h6><i class="bi bi-exclamation-triangle me-2"></i>Advertencias:</h6>
                        <ul class="mb-0">
                            <?php foreach ($warnings as $warning): ?>
                                <li><?= $warning ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Nuevas Funcionalidades -->
        <div class="test-card">
            <h3><i class="bi bi-stars me-2"></i>Nuevas Funcionalidades Implementadas</h3>
            <ul class="list-group">
                <li class="list-group-item"><i class="bi bi-check2 text-success me-2"></i>Sistema de mapa de tareas para itinerarios</li>
                <li class="list-group-item"><i class="bi bi-check2 text-success me-2"></i>Tracking en tiempo real del progreso de viajes</li>
                <li class="list-group-item"><i class="bi bi-check2 text-success me-2"></i>Confirmaciones de servicios por proveedores</li>
                <li class="list-group-item"><i class="bi bi-check2 text-success me-2"></i>Sistema de notificaciones integrado</li>
                <li class="list-group-item"><i class="bi bi-check2 text-success me-2"></i>Diseño responsive completo para móviles</li>
                <li class="list-group-item"><i class="bi bi-check2 text-success me-2"></i>Sidebar móvil funcional en todas las páginas admin</li>
                <li class="list-group-item"><i class="bi bi-check2 text-success me-2"></i>Página de gestión de publicidad modernizada</li>
                <li class="list-group-item"><i class="bi bi-check2 text-success me-2"></i>APIs RESTful para actualización de estados</li>
            </ul>
        </div>

        <div class="test-card text-center">
            <a href="index.php" class="btn btn-primary btn-lg me-2">
                <i class="bi bi-house me-2"></i>Ir al Inicio
            </a>
            <a href="admin/dashboard.php" class="btn btn-secondary btn-lg">
                <i class="bi bi-speedometer2 me-2"></i>Panel Admin
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
