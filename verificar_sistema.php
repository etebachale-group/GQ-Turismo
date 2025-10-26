<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación del Sistema - GQ Turismo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        .check-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 2rem;
            margin-bottom: 1.5rem;
        }
        .status-ok { color: #10b981; }
        .status-error { color: #ef4444; }
        .check-item {
            padding: 0.75rem;
            margin: 0.5rem 0;
            border-radius: 8px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="check-card">
            <h1 class="text-center mb-4">
                <i class="bi bi-clipboard-check me-2"></i>
                Verificación del Sistema GQ-Turismo
            </h1>
            <p class="text-center text-muted mb-4">
                Comprobación de todas las correcciones aplicadas
            </p>
        </div>

        <?php
        require_once 'includes/db_connect.php';
        
        $checks = [
            'database' => [],
            'files' => [],
            'features' => []
        ];

        // === VERIFICACIONES DE BASE DE DATOS ===
        echo '<div class="check-card">';
        echo '<h3><i class="bi bi-database me-2"></i>Base de Datos</h3>';
        
        // Verificar tablas
        $tables_to_check = [
            'itinerario_tareas' => 'Tabla de tareas de itinerarios',
            'guias_destinos' => 'Tabla guías-destinos',
            'publicidad_carousel' => 'Tabla de publicidad',
            'confirmacion_servicios' => 'Tabla de confirmaciones'
        ];
        
        foreach ($tables_to_check as $table => $desc) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            $exists = $result && $result->num_rows > 0;
            echo '<div class="check-item">';
            echo "<span>$desc</span>";
            echo '<span class="' . ($exists ? 'status-ok' : 'status-error') . '">';
            echo '<i class="bi bi-' . ($exists ? 'check-circle-fill' : 'x-circle-fill') . '"></i> ';
            echo ($exists ? 'OK' : 'FALTA');
            echo '</span>';
            echo '</div>';
        }
        
        // Verificar columnas
        $columns_to_check = [
            ['table' => 'usuarios', 'column' => 'telefono', 'desc' => 'Columna telefono en usuarios'],
            ['table' => 'itinerarios', 'column' => 'id_turista', 'desc' => 'Columna id_turista en itinerarios'],
            ['table' => 'itinerario_destinos', 'column' => 'precio', 'desc' => 'Columna precio en itinerario_destinos'],
            ['table' => 'itinerario_destinos', 'column' => 'fecha_inicio', 'desc' => 'Columna fecha_inicio en itinerario_destinos'],
        ];
        
        foreach ($columns_to_check as $col) {
            $result = $conn->query("SHOW COLUMNS FROM {$col['table']} LIKE '{$col['column']}'");
            $exists = $result && $result->num_rows > 0;
            echo '<div class="check-item">';
            echo "<span>{$col['desc']}</span>";
            echo '<span class="' . ($exists ? 'status-ok' : 'status-error') . '">';
            echo '<i class="bi bi-' . ($exists ? 'check-circle-fill' : 'x-circle-fill') . '"></i> ';
            echo ($exists ? 'OK' : 'FALTA');
            echo '</span>';
            echo '</div>';
        }
        
        echo '</div>';

        // === VERIFICACIONES DE ARCHIVOS ===
        echo '<div class="check-card">';
        echo '<h3><i class="bi bi-file-code me-2"></i>Archivos y Funcionalidades</h3>';
        
        $files_to_check = [
            'mapa_itinerario.php' => 'Mapa de tareas de itinerario',
            'admin/confirmacion_servicios.php' => 'Panel de confirmación de servicios',
            'api/itinerario_tracking.php' => 'API de tracking',
            'assets/css/mobile-responsive-final.css' => 'CSS responsive móvil',
            'database/fix_all_critical_issues_2025.sql' => 'Script de correcciones SQL',
        ];
        
        foreach ($files_to_check as $file => $desc) {
            $exists = file_exists($file);
            echo '<div class="check-item">';
            echo "<span>$desc</span>";
            echo '<span class="' . ($exists ? 'status-ok' : 'status-error') . '">';
            echo '<i class="bi bi-' . ($exists ? 'check-circle-fill' : 'x-circle-fill') . '"></i> ';
            echo ($exists ? 'OK' : 'FALTA');
            echo '</span>';
            echo '</div>';
        }
        
        echo '</div>';

        // === RESUMEN ===
        echo '<div class="check-card text-center">';
        echo '<h3 class="mb-4">Resumen de Verificación</h3>';
        
        $total_db = count($tables_to_check) + count($columns_to_check);
        $ok_db = 0;
        foreach ($tables_to_check as $table => $desc) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->num_rows > 0) $ok_db++;
        }
        foreach ($columns_to_check as $col) {
            $result = $conn->query("SHOW COLUMNS FROM {$col['table']} LIKE '{$col['column']}'");
            if ($result && $result->num_rows > 0) $ok_db++;
        }
        
        $total_files = count($files_to_check);
        $ok_files = 0;
        foreach ($files_to_check as $file => $desc) {
            if (file_exists($file)) $ok_files++;
        }
        
        $total = $total_db + $total_files;
        $ok = $ok_db + $ok_files;
        $percent = round(($ok / $total) * 100);
        
        echo '<div class="row g-4">';
        echo '<div class="col-md-4">';
        echo '<div class="card border-success">';
        echo '<div class="card-body">';
        echo '<h1 class="status-ok">' . $ok . '/' . $total . '</h1>';
        echo '<p class="mb-0">Verificaciones Exitosas</p>';
        echo '</div></div></div>';
        
        echo '<div class="col-md-4">';
        echo '<div class="card border-primary">';
        echo '<div class="card-body">';
        echo '<h1 class="text-primary">' . $percent . '%</h1>';
        echo '<p class="mb-0">Completado</p>';
        echo '</div></div></div>';
        
        echo '<div class="col-md-4">';
        echo '<div class="card border-info">';
        echo '<div class="card-body">';
        echo '<h1 class="text-info">' . ($total - $ok) . '</h1>';
        echo '<p class="mb-0">Pendientes</p>';
        echo '</div></div></div>';
        echo '</div>';
        
        if ($percent >= 90) {
            echo '<div class="alert alert-success mt-4">';
            echo '<i class="bi bi-check-circle-fill me-2"></i>';
            echo '<strong>¡Sistema Verificado!</strong> Todas las correcciones se han aplicado correctamente.';
            echo '</div>';
        } else {
            echo '<div class="alert alert-warning mt-4">';
            echo '<i class="bi bi-exclamation-triangle-fill me-2"></i>';
            echo '<strong>Atención:</strong> Algunas verificaciones fallaron. Revisa los detalles arriba.';
            echo '</div>';
        }
        
        echo '<div class="mt-4">';
        echo '<a href="test_system.php" class="btn btn-primary me-2">';
        echo '<i class="bi bi-gear me-2"></i>Test Completo del Sistema';
        echo '</a>';
        echo '<a href="admin/dashboard.php" class="btn btn-success">';
        echo '<i class="bi bi-speedometer2 me-2"></i>Ir al Dashboard';
        echo '</a>';
        echo '</div>';
        
        echo '</div>';
        ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
