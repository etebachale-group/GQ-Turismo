<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Completo del Sistema - GQ-Turismo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }
        
        .test-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 2rem;
            margin-bottom: 1.5rem;
        }
        
        .success { color: #10b981; }
        .error { color: #ef4444; }
        .warning { color: #f59e0b; }
        .info { color: #3b82f6; }
        
        .test-result {
            padding: 1rem;
            margin: 0.5rem 0;
            border-radius: 8px;
            border-left: 4px solid;
        }
        
        .test-result.success {
            background: #d1fae5;
            border-color: #10b981;
        }
        
        .test-result.error {
            background: #fee2e2;
            border-color: #ef4444;
        }
        
        .test-result.warning {
            background: #fef3c7;
            border-color: #f59e0b;
        }
        
        .progress-bar {
            height: 30px;
            font-size: 1rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="test-card">
            <h1 class="text-center mb-4">
                <i class="bi bi-gear-fill me-2"></i>
                Test Completo del Sistema GQ-Turismo
            </h1>
            <p class="text-center text-muted mb-4">Verificación de todas las funcionalidades y estructura del sistema</p>
            
            <div class="progress mb-4">
                <div class="progress-bar progress-bar-striped progress-bar-animated" id="progressBar" style="width: 0%">0%</div>
            </div>
        </div>

        <?php
        require_once 'includes/db_connect.php';
        
        $total_tests = 0;
        $passed_tests = 0;
        $failed_tests = 0;
        $warnings = 0;
        
        function showResult($test_name, $status, $message, &$total, &$passed, &$failed, &$warnings) {
            $total++;
            $icon = '';
            $class = '';
            
            switch($status) {
                case 'success':
                    $passed++;
                    $icon = 'bi-check-circle-fill';
                    $class = 'success';
                    break;
                case 'error':
                    $failed++;
                    $icon = 'bi-x-circle-fill';
                    $class = 'error';
                    break;
                case 'warning':
                    $warnings++;
                    $icon = 'bi-exclamation-triangle-fill';
                    $class = 'warning';
                    break;
            }
            
            echo "<div class='test-result $class'>";
            echo "<i class='bi $icon me-2'></i>";
            echo "<strong>$test_name:</strong> $message";
            echo "</div>";
        }
        
        // TEST 1: Conexión a la base de datos
        echo "<div class='test-card'>";
        echo "<h3><i class='bi bi-database me-2'></i>1. Tests de Base de Datos</h3>";
        
        if ($conn) {
            showResult("Conexión DB", "success", "Conexión establecida correctamente", $total_tests, $passed_tests, $failed_tests, $warnings);
        } else {
            showResult("Conexión DB", "error", "Error de conexión: " . mysqli_connect_error(), $total_tests, $passed_tests, $failed_tests, $warnings);
        }
        
        // Verificar tablas principales
        $tables = [
            'usuarios', 'destinos', 'itinerarios', 'itinerario_destinos', 'itinerario_tareas',
            'pedidos_servicios', 'servicios_agencia', 'servicios_guia', 'servicios_local',
            'guias_turisticos', 'agencias', 'locales_turisticos', 'publicidad_carousel',
            'publicidades', 'guias_destinos', 'messages'
        ];
        
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->num_rows > 0) {
                showResult("Tabla $table", "success", "Existe en la base de datos", $total_tests, $passed_tests, $failed_tests, $warnings);
            } else {
                showResult("Tabla $table", "error", "No existe en la base de datos", $total_tests, $passed_tests, $failed_tests, $warnings);
            }
        }
        
        echo "</div>";
        
        // TEST 2: Verificar columnas críticas
        echo "<div class='test-card'>";
        echo "<h3><i class='bi bi-table me-2'></i>2. Tests de Estructura</h3>";
        
        $critical_columns = [
            ['table' => 'usuarios', 'column' => 'telefono'],
            ['table' => 'itinerario_destinos', 'column' => 'precio'],
            ['table' => 'itinerario_destinos', 'column' => 'fecha_inicio'],
            ['table' => 'itinerario_destinos', 'column' => 'descripcion'],
            ['table' => 'itinerario_tareas', 'column' => 'estado'],
            ['table' => 'guias_destinos', 'column' => 'id_guia'],
            ['table' => 'publicidad_carousel', 'column' => 'titulo']
        ];
        
        foreach ($critical_columns as $col) {
            $result = $conn->query("SHOW COLUMNS FROM {$col['table']} LIKE '{$col['column']}'");
            if ($result && $result->num_rows > 0) {
                showResult("Columna {$col['table']}.{$col['column']}", "success", "Existe correctamente", $total_tests, $passed_tests, $failed_tests, $warnings);
            } else {
                showResult("Columna {$col['table']}.{$col['column']}", "error", "No existe", $total_tests, $passed_tests, $failed_tests, $warnings);
            }
        }
        
        echo "</div>";
        
        // TEST 3: Verificar archivos PHP críticos
        echo "<div class='test-card'>";
        echo "<h3><i class='bi bi-file-code me-2'></i>3. Tests de Archivos PHP</h3>";
        
        $php_files = [
            'index.php', 'destinos.php', 'guias.php', 'locales.php', 'agencias.php',
            'crear_itinerario.php', 'itinerario.php', 'seguimiento_itinerario.php',
            'mapa_itinerario.php', 'tracking_itinerario.php', 'admin/dashboard.php',
            'admin/manage_destinos.php', 'admin/manage_guias.php', 'admin/manage_agencias.php',
            'admin/manage_locales.php', 'admin/mis_pedidos.php', 'admin/manage_publicidad_carousel.php'
        ];
        
        foreach ($php_files as $file) {
            if (file_exists($file)) {
                // Verificar errores de sintaxis
                $output = [];
                exec("php -l $file 2>&1", $output, $return_var);
                $output_str = implode("\n", $output);
                
                if ($return_var === 0 && strpos($output_str, 'No syntax errors') !== false) {
                    showResult("Archivo $file", "success", "Existe y sin errores de sintaxis", $total_tests, $passed_tests, $failed_tests, $warnings);
                } else {
                    showResult("Archivo $file", "warning", "Existe pero puede tener errores", $total_tests, $passed_tests, $failed_tests, $warnings);
                }
            } else {
                showResult("Archivo $file", "error", "No existe", $total_tests, $passed_tests, $failed_tests, $warnings);
            }
        }
        
        echo "</div>";
        
        // TEST 4: Verificar directorios
        echo "<div class='test-card'>";
        echo "<h3><i class='bi bi-folder me-2'></i>4. Tests de Directorios</h3>";
        
        $directories = [
            'admin/', 'api/', 'assets/', 'assets/css/', 'assets/js/', 'assets/img/',
            'assets/img/destinos/', 'assets/img/guias/', 'assets/img/locales/',
            'assets/img/agencias/', 'assets/img/carouseles/', 'assets/img/publicidad/',
            'includes/', 'database/', 'informe/', 'trash/'
        ];
        
        foreach ($directories as $dir) {
            if (is_dir($dir)) {
                $writable = is_writable($dir) ? "y escribible" : "pero NO escribible";
                $status = is_writable($dir) ? "success" : "warning";
                showResult("Directorio $dir", $status, "Existe $writable", $total_tests, $passed_tests, $failed_tests, $warnings);
            } else {
                showResult("Directorio $dir", "error", "No existe", $total_tests, $passed_tests, $failed_tests, $warnings);
            }
        }
        
        echo "</div>";
        
        // TEST 5: Verificar funcionalidades del sistema
        echo "<div class='test-card'>";
        echo "<h3><i class='bi bi-check2-square me-2'></i>5. Tests de Funcionalidades</h3>";
        
        // Verificar si hay usuarios en el sistema
        $result = $conn->query("SELECT COUNT(*) as count FROM usuarios");
        $count = $result->fetch_assoc()['count'];
        if ($count > 0) {
            showResult("Usuarios en sistema", "success", "$count usuarios registrados", $total_tests, $passed_tests, $failed_tests, $warnings);
        } else {
            showResult("Usuarios en sistema", "warning", "No hay usuarios registrados", $total_tests, $passed_tests, $failed_tests, $warnings);
        }
        
        // Verificar destinos
        $result = $conn->query("SELECT COUNT(*) as count FROM destinos");
        $count = $result->fetch_assoc()['count'];
        if ($count > 0) {
            showResult("Destinos", "success", "$count destinos registrados", $total_tests, $passed_tests, $failed_tests, $warnings);
        } else {
            showResult("Destinos", "warning", "No hay destinos registrados", $total_tests, $passed_tests, $failed_tests, $warnings);
        }
        
        // Verificar itinerarios
        $result = $conn->query("SELECT COUNT(*) as count FROM itinerarios");
        $count = $result->fetch_assoc()['count'];
        if ($count > 0) {
            showResult("Itinerarios", "success", "$count itinerarios creados", $total_tests, $passed_tests, $failed_tests, $warnings);
        } else {
            showResult("Itinerarios", "warning", "No hay itinerarios creados", $total_tests, $passed_tests, $failed_tests, $warnings);
        }
        
        // Verificar guías
        $result = $conn->query("SELECT COUNT(*) as count FROM guias_turisticos");
        $count = $result->fetch_assoc()['count'];
        if ($count > 0) {
            showResult("Guías turísticos", "success", "$count guías registrados", $total_tests, $passed_tests, $failed_tests, $warnings);
        } else {
            showResult("Guías turísticos", "warning", "No hay guías registrados", $total_tests, $passed_tests, $failed_tests, $warnings);
        }
        
        // Verificar agencias
        $result = $conn->query("SELECT COUNT(*) as count FROM agencias");
        $count = $result->fetch_assoc()['count'];
        if ($count > 0) {
            showResult("Agencias", "success", "$count agencias registradas", $total_tests, $passed_tests, $failed_tests, $warnings);
        } else {
            showResult("Agencias", "warning", "No hay agencias registradas", $total_tests, $passed_tests, $failed_tests, $warnings);
        }
        
        // Verificar locales
        $result = $conn->query("SELECT COUNT(*) as count FROM locales_turisticos");
        $count = $result->fetch_assoc()['count'];
        if ($count > 0) {
            showResult("Locales turísticos", "success", "$count locales registrados", $total_tests, $passed_tests, $failed_tests, $warnings);
        } else {
            showResult("Locales turísticos", "warning", "No hay locales registrados", $total_tests, $passed_tests, $failed_tests, $warnings);
        }
        
        echo "</div>";
        
        // TEST 6: Verificar configuración
        echo "<div class='test-card'>";
        echo "<h3><i class='bi bi-sliders me-2'></i>6. Tests de Configuración</h3>";
        
        // Verificar PHP version
        $php_version = phpversion();
        if (version_compare($php_version, '7.4.0', '>=')) {
            showResult("Versión PHP", "success", "PHP $php_version (recomendado >= 7.4)", $total_tests, $passed_tests, $failed_tests, $warnings);
        } else {
            showResult("Versión PHP", "warning", "PHP $php_version (recomendado >= 7.4)", $total_tests, $passed_tests, $failed_tests, $warnings);
        }
        
        // Verificar extensiones
        $required_extensions = ['mysqli', 'gd', 'fileinfo', 'session'];
        foreach ($required_extensions as $ext) {
            if (extension_loaded($ext)) {
                showResult("Extensión $ext", "success", "Cargada correctamente", $total_tests, $passed_tests, $failed_tests, $warnings);
            } else {
                showResult("Extensión $ext", "error", "No está cargada", $total_tests, $passed_tests, $failed_tests, $warnings);
            }
        }
        
        echo "</div>";
        
        // Resumen final
        echo "<div class='test-card'>";
        echo "<h2 class='text-center mb-4'><i class='bi bi-clipboard-check me-2'></i>Resumen de Tests</h2>";
        
        $success_percentage = $total_tests > 0 ? round(($passed_tests / $total_tests) * 100) : 0;
        
        echo "<div class='row text-center mb-4'>";
        echo "<div class='col-md-3'>";
        echo "<h3 class='text-primary'>$total_tests</h3>";
        echo "<p>Total Tests</p>";
        echo "</div>";
        echo "<div class='col-md-3'>";
        echo "<h3 class='success'>$passed_tests</h3>";
        echo "<p>Exitosos</p>";
        echo "</div>";
        echo "<div class='col-md-3'>";
        echo "<h3 class='error'>$failed_tests</h3>";
        echo "<p>Fallidos</p>";
        echo "</div>";
        echo "<div class='col-md-3'>";
        echo "<h3 class='warning'>$warnings</h3>";
        echo "<p>Advertencias</p>";
        echo "</div>";
        echo "</div>";
        
        echo "<div class='progress' style='height: 40px;'>";
        $bar_class = $success_percentage >= 80 ? 'bg-success' : ($success_percentage >= 60 ? 'bg-warning' : 'bg-danger');
        echo "<div class='progress-bar $bar_class' style='width: {$success_percentage}%'>";
        echo "<strong style='font-size: 1.2rem;'>{$success_percentage}% Éxito</strong>";
        echo "</div>";
        echo "</div>";
        
        if ($success_percentage >= 90) {
            echo "<div class='alert alert-success mt-4'>";
            echo "<h4><i class='bi bi-check-circle-fill me-2'></i>¡Excelente!</h4>";
            echo "<p>El sistema está funcionando correctamente. Todos los componentes críticos están operativos.</p>";
            echo "</div>";
        } elseif ($success_percentage >= 70) {
            echo "<div class='alert alert-warning mt-4'>";
            echo "<h4><i class='bi bi-exclamation-triangle-fill me-2'></i>Atención</h4>";
            echo "<p>El sistema está funcionando pero hay algunas advertencias. Revisa los tests fallidos.</p>";
            echo "</div>";
        } else {
            echo "<div class='alert alert-danger mt-4'>";
            echo "<h4><i class='bi bi-x-circle-fill me-2'></i>Problemas Detectados</h4>";
            echo "<p>Hay problemas críticos que necesitan ser resueltos. Revisa los errores y corrígelos.</p>";
            echo "</div>";
        }
        
        echo "</div>";
        
        $conn->close();
        ?>
        
        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-primary btn-lg">
                <i class="bi bi-house-fill me-2"></i>Volver al Inicio
            </a>
            <a href="admin/dashboard.php" class="btn btn-secondary btn-lg ms-2">
                <i class="bi bi-speedometer2 me-2"></i>Ir al Dashboard
            </a>
        </div>
    </div>
    
    <script>
        // Animar barra de progreso
        setTimeout(() => {
            const percentage = <?= $success_percentage ?>;
            const progressBar = document.getElementById('progressBar');
            progressBar.style.width = percentage + '%';
            progressBar.textContent = percentage + '%';
            
            if (percentage >= 80) {
                progressBar.classList.remove('progress-bar-striped', 'progress-bar-animated');
                progressBar.classList.add('bg-success');
            } else if (percentage >= 60) {
                progressBar.classList.add('bg-warning');
            } else {
                progressBar.classList.add('bg-danger');
            }
        }, 500);
    </script>
</body>
</html>
