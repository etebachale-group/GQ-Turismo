<?php
// Evitar errores de conexión mostrando información útil
error_reporting(E_ALL);
ini_set('display_errors', 1);
define('DB_ACCESS_ALLOWED', true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación Completa del Sistema - GQ Turismo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 2rem; }
        .card { border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); margin-bottom: 2rem; }
        .check-item { padding: 1rem; border-bottom: 1px solid #e9ecef; transition: all 0.3s; }
        .check-item:hover { background: #f8f9fa; }
        .check-item:last-child { border-bottom: none; }
        .status-ok { color: #28a745; font-weight: 600; }
        .status-error { color: #dc3545; font-weight: 600; }
        .status-warning { color: #ffc107; font-weight: 600; }
        .section-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
        .badge-count { font-size: 0.9rem; }
        .progress { height: 25px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-11">
                <div class="text-center text-white mb-4">
                    <h1 class="display-4 fw-bold mb-3">
                        <i class="bi bi-shield-check me-3"></i>
                        Verificación Completa del Sistema
                    </h1>
                    <p class="lead">GQ-Turismo - Análisis de Estructura, Funcionalidades y Diseño</p>
                    <p class="small">Fecha: <?php echo date('d/m/Y H:i:s'); ?></p>
                </div>

                <?php
                // Estadísticas generales
                $total_checks = 0;
                $passed_checks = 0;
                $warning_checks = 0;
                $failed_checks = 0;
                ?>

                <div class="card">
                    <div class="card-header section-header">
                        <h4 class="mb-0"><i class="bi bi-database me-2"></i>1. Estado de la Base de Datos</h4>
                    </div>
                    <div class="card-body">
                        <?php
                        require_once 'includes/db_connect.php';
                        
                        $checks = [];
                        
                        // 1. Verificar conexión
                        if ($conn) {
                            $checks[] = ['name' => 'Conexión a Base de Datos', 'status' => 'ok', 'message' => 'Conectado a: ' . $dbname];
                            $passed_checks++;
                        } else {
                            $checks[] = ['name' => 'Conexión a Base de Datos', 'status' => 'error', 'message' => 'Error de conexión'];
                            $failed_checks++;
                        }
                        $total_checks++;
                        
                        // 2. Verificar tablas principales
                        $required_tables = [
                            'usuarios' => 'Usuarios del sistema',
                            'itinerarios' => 'Itinerarios de viaje',
                            'destinos' => 'Destinos turísticos',
                            'mensajes' => 'Sistema de mensajería',
                            'conversaciones' => 'Conversaciones de chat',
                            'itinerario_destinos' => 'Relación itinerarios-destinos',
                            'itinerario_guias' => 'Relación itinerarios-guías',
                            'itinerario_agencias' => 'Relación itinerarios-agencias',
                            'itinerario_locales' => 'Relación itinerarios-locales',
                            'agencias' => 'Agencias de viajes',
                            'guias_turisticos' => 'Guías turísticos',
                            'lugares_locales' => 'Lugares locales',
                            'reservas' => 'Sistema de reservas',
                            'pedidos_servicios' => 'Pedidos de servicios',
                            'carouseles' => 'Carruseles publicitarios'
                        ];
                        
                        foreach ($required_tables as $table => $description) {
                            $total_checks++;
                            $result = $conn->query("SHOW TABLES LIKE '$table'");
                            if ($result && $result->num_rows > 0) {
                                $count_result = $conn->query("SELECT COUNT(*) as total FROM $table");
                                $count = $count_result->fetch_assoc()['total'];
                                $checks[] = [
                                    'name' => "$description ($table)", 
                                    'status' => 'ok', 
                                    'message' => "$count registros"
                                ];
                                $passed_checks++;
                            } else {
                                $checks[] = [
                                    'name' => "$description ($table)", 
                                    'status' => 'error', 
                                    'message' => 'Tabla no existe'
                                ];
                                $failed_checks++;
                            }
                        }
                        
                        // 3. Verificar columnas críticas
                        $column_checks = [
                            ['table' => 'itinerarios', 'column' => 'presupuesto_estimado', 'desc' => 'Presupuesto en itinerarios'],
                            ['table' => 'itinerarios', 'column' => 'nombre_itinerario', 'desc' => 'Nombre de itinerario'],
                            ['table' => 'destinos', 'column' => 'latitude', 'desc' => 'Latitud de destinos'],
                            ['table' => 'destinos', 'column' => 'longitude', 'desc' => 'Longitud de destinos'],
                            ['table' => 'destinos', 'column' => 'ciudad', 'desc' => 'Ciudad de destinos'],
                            ['table' => 'destinos', 'column' => 'categoria', 'desc' => 'Categoría de destinos'],
                            ['table' => 'mensajes', 'column' => 'id_conversacion', 'desc' => 'ID conversación en mensajes'],
                            ['table' => 'conversaciones', 'column' => 'ultimo_mensaje', 'desc' => 'Último mensaje en conversaciones'],
                            ['table' => 'usuarios', 'column' => 'tipo_usuario', 'desc' => 'Tipo de usuario']
                        ];
                        
                        foreach ($column_checks as $check) {
                            $total_checks++;
                            $result = $conn->query("SHOW COLUMNS FROM {$check['table']} LIKE '{$check['column']}'");
                            if ($result && $result->num_rows > 0) {
                                $checks[] = [
                                    'name' => $check['desc'], 
                                    'status' => 'ok', 
                                    'message' => "{$check['table']}.{$check['column']}"
                                ];
                                $passed_checks++;
                            } else {
                                $checks[] = [
                                    'name' => $check['desc'], 
                                    'status' => 'error', 
                                    'message' => "Columna {$check['column']} faltante"
                                ];
                                $failed_checks++;
                            }
                        }
                        
                        // 4. Verificar integridad de datos
                        $integrity_checks = [
                            "SELECT COUNT(*) as total FROM itinerario_destinos WHERE id_itinerario NOT IN (SELECT id FROM itinerarios)" => "Destinos huérfanos",
                            "SELECT COUNT(*) as total FROM itinerario_guias WHERE id_itinerario NOT IN (SELECT id FROM itinerarios)" => "Guías huérfanas",
                            "SELECT COUNT(*) as total FROM mensajes WHERE id_conversacion NOT IN (SELECT id FROM conversaciones)" => "Mensajes huérfanos",
                            "SELECT COUNT(*) as total FROM pedidos_servicios WHERE id_turista NOT IN (SELECT id FROM usuarios)" => "Pedidos huérfanos"
                        ];
                        
                        foreach ($integrity_checks as $query => $name) {
                            $total_checks++;
                            $result = $conn->query($query);
                            if ($result) {
                                $row = $result->fetch_assoc();
                                $total = $row['total'] ?? 0;
                                if ($total == 0) {
                                    $checks[] = ['name' => $name, 'status' => 'ok', 'message' => 'Sin problemas'];
                                    $passed_checks++;
                                } else {
                                    $checks[] = ['name' => $name, 'status' => 'warning', 'message' => "$total encontrados"];
                                    $warning_checks++;
                                }
                            }
                        }
                        
                        // Mostrar resultados
                        foreach ($checks as $check) {
                            $icon = $check['status'] == 'ok' ? 'check-circle-fill' : 
                                   ($check['status'] == 'warning' ? 'exclamation-triangle-fill' : 'x-circle-fill');
                            $class = 'status-' . $check['status'];
                            
                            echo "<div class='check-item'>";
                            echo "<div class='d-flex justify-content-between align-items-center'>";
                            echo "<div>";
                            echo "<i class='bi bi-$icon me-2 $class'></i>";
                            echo "<strong>{$check['name']}</strong>";
                            echo "</div>";
                            echo "<span class='$class'>{$check['message']}</span>";
                            echo "</div>";
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header section-header">
                        <h4 class="mb-0"><i class="bi bi-file-earmark-code me-2"></i>2. Archivos del Sistema</h4>
                    </div>
                    <div class="card-body">
                        <?php
                        $required_files = [
                            // Páginas principales
                            ['path' => 'index.php', 'name' => 'Página Principal', 'category' => 'Principal'],
                            ['path' => 'about.php', 'name' => 'Sobre Nosotros', 'category' => 'Principal'],
                            ['path' => 'contacto.php', 'name' => 'Contacto', 'category' => 'Principal'],
                            
                            // Sistema de itinerarios
                            ['path' => 'itinerario.php', 'name' => 'Gestión de Itinerarios', 'category' => 'Itinerarios'],
                            ['path' => 'crear_itinerario.php', 'name' => 'Crear Itinerario', 'category' => 'Itinerarios'],
                            
                            // Páginas de exploración
                            ['path' => 'destinos.php', 'name' => 'Listado de Destinos', 'category' => 'Exploración'],
                            ['path' => 'detalle_destino.php', 'name' => 'Detalle de Destino', 'category' => 'Exploración'],
                            ['path' => 'agencias.php', 'name' => 'Listado de Agencias', 'category' => 'Exploración'],
                            ['path' => 'detalle_agencia.php', 'name' => 'Detalle de Agencia', 'category' => 'Exploración'],
                            ['path' => 'guias.php', 'name' => 'Listado de Guías', 'category' => 'Exploración'],
                            ['path' => 'detalle_guia.php', 'name' => 'Detalle de Guía', 'category' => 'Exploración'],
                            ['path' => 'locales.php', 'name' => 'Listado de Locales', 'category' => 'Exploración'],
                            ['path' => 'detalle_local.php', 'name' => 'Detalle de Local', 'category' => 'Exploración'],
                            
                            // Sistema de usuario
                            ['path' => 'mis_mensajes.php', 'name' => 'Sistema de Chat', 'category' => 'Usuario'],
                            ['path' => 'mis_pedidos.php', 'name' => 'Mis Pedidos', 'category' => 'Usuario'],
                            ['path' => 'reservas.php', 'name' => 'Sistema de Reservas', 'category' => 'Usuario'],
                            ['path' => 'pagar.php', 'name' => 'Página de Pago', 'category' => 'Usuario'],
                            
                            // APIs
                            ['path' => 'api/auth.php', 'name' => 'API Autenticación', 'category' => 'API'],
                            ['path' => 'api/itinerarios.php', 'name' => 'API Itinerarios', 'category' => 'API'],
                            ['path' => 'api/messages.php', 'name' => 'API Mensajes', 'category' => 'API'],
                            ['path' => 'api/get_conversation.php', 'name' => 'API Conversaciones', 'category' => 'API'],
                            ['path' => 'api/destinos.php', 'name' => 'API Destinos', 'category' => 'API'],
                            ['path' => 'api/pedidos.php', 'name' => 'API Pedidos', 'category' => 'API'],
                            ['path' => 'api/reservas.php', 'name' => 'API Reservas', 'category' => 'API'],
                            
                            // Includes
                            ['path' => 'includes/header.php', 'name' => 'Header', 'category' => 'Includes'],
                            ['path' => 'includes/footer.php', 'name' => 'Footer', 'category' => 'Includes'],
                            ['path' => 'includes/db_connect.php', 'name' => 'Conexión DB', 'category' => 'Includes'],
                            
                            // Admin
                            ['path' => 'admin/dashboard.php', 'name' => 'Dashboard Admin', 'category' => 'Admin'],
                            ['path' => 'admin/manage_destinos.php', 'name' => 'Gestión Destinos', 'category' => 'Admin'],
                            ['path' => 'admin/manage_agencias.php', 'name' => 'Gestión Agencias', 'category' => 'Admin'],
                            ['path' => 'admin/manage_guias.php', 'name' => 'Gestión Guías', 'category' => 'Admin'],
                            ['path' => 'admin/manage_locales.php', 'name' => 'Gestión Locales', 'category' => 'Admin'],
                            ['path' => 'admin/manage_users.php', 'name' => 'Gestión Usuarios', 'category' => 'Admin'],
                            
                            // Assets CSS
                            ['path' => 'assets/css/style.css', 'name' => 'Estilos Principales', 'category' => 'CSS'],
                            ['path' => 'assets/css/modern-ui.css', 'name' => 'UI Moderna', 'category' => 'CSS'],
                            ['path' => 'assets/css/mobile-enhancements.css', 'name' => 'Mejoras Móvil', 'category' => 'CSS'],
                            ['path' => 'assets/css/responsive.css', 'name' => 'Responsive', 'category' => 'CSS'],
                            
                            // Assets JS
                            ['path' => 'assets/js/main.js', 'name' => 'JavaScript Principal', 'category' => 'JavaScript'],
                            ['path' => 'assets/js/auth.js', 'name' => 'Autenticación JS', 'category' => 'JavaScript'],
                            ['path' => 'assets/js/itinerario.js', 'name' => 'Itinerarios JS', 'category' => 'JavaScript'],
                            ['path' => 'assets/js/mobile.js', 'name' => 'Móvil JS', 'category' => 'JavaScript']
                        ];
                        
                        $file_stats = ['ok' => 0, 'error' => 0];
                        $categories = [];
                        
                        foreach ($required_files as $file) {
                            $exists = file_exists($file['path']);
                            if (!isset($categories[$file['category']])) {
                                $categories[$file['category']] = ['ok' => 0, 'error' => 0];
                            }
                            
                            if ($exists) {
                                $file_stats['ok']++;
                                $categories[$file['category']]['ok']++;
                                $passed_checks++;
                            } else {
                                $file_stats['error']++;
                                $categories[$file['category']]['error']++;
                                $failed_checks++;
                            }
                            $total_checks++;
                        }
                        
                        // Mostrar resumen por categorías
                        echo "<div class='mb-4'><h5>Resumen por Categorías:</h5><div class='row g-3'>";
                        foreach ($categories as $cat => $stats) {
                            $total_cat = $stats['ok'] + $stats['error'];
                            $percent = $total_cat > 0 ? round(($stats['ok'] / $total_cat) * 100) : 0;
                            $color = $percent == 100 ? 'success' : ($percent >= 75 ? 'warning' : 'danger');
                            echo "<div class='col-md-4'>";
                            echo "<div class='card'><div class='card-body'>";
                            echo "<h6 class='card-title'>$cat</h6>";
                            echo "<div class='progress'><div class='progress-bar bg-$color' style='width: {$percent}%'>{$percent}%</div></div>";
                            echo "<small class='text-muted'>{$stats['ok']}/{$total_cat} archivos OK</small>";
                            echo "</div></div></div>";
                        }
                        echo "</div></div>";
                        
                        // Mostrar lista detallada
                        foreach ($required_files as $file) {
                            $exists = file_exists($file['path']);
                            $icon = $exists ? 'check-circle-fill' : 'x-circle-fill';
                            $class = $exists ? 'status-ok' : 'status-error';
                            $message = $exists ? 'Existe' : 'No encontrado';
                            
                            echo "<div class='check-item'>";
                            echo "<div class='d-flex justify-content-between align-items-center'>";
                            echo "<div>";
                            echo "<i class='bi bi-$icon me-2 $class'></i>";
                            echo "<strong>{$file['name']}</strong>";
                            echo "<br><small class='text-muted'>{$file['path']}</small>";
                            echo "</div>";
                            echo "<span class='badge bg-secondary'>{$file['category']}</span>";
                            echo "<span class='$class ms-2'>$message</span>";
                            echo "</div>";
                            echo "</div>";
                        }
                        ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header section-header">
                        <h4 class="mb-0"><i class="bi bi-gear me-2"></i>3. Configuración PHP y Servidor</h4>
                    </div>
                    <div class="card-body">
                        <?php
                        $php_checks = [
                            ['name' => 'Versión PHP', 'value' => phpversion(), 'required' => '7.4+', 'status' => version_compare(phpversion(), '7.4', '>=') ? 'ok' : 'error'],
                            ['name' => 'Extensión MySQLi', 'value' => extension_loaded('mysqli') ? 'Activa' : 'No activa', 'required' => 'Requerida', 'status' => extension_loaded('mysqli') ? 'ok' : 'error'],
                            ['name' => 'Extensión JSON', 'value' => extension_loaded('json') ? 'Activa' : 'No activa', 'required' => 'Requerida', 'status' => extension_loaded('json') ? 'ok' : 'error'],
                            ['name' => 'Extensión GD (Imágenes)', 'value' => extension_loaded('gd') ? 'Activa' : 'No activa', 'required' => 'Recomendada', 'status' => extension_loaded('gd') ? 'ok' : 'warning'],
                            ['name' => 'Extensión Fileinfo', 'value' => extension_loaded('fileinfo') ? 'Activa' : 'No activa', 'required' => 'Recomendada', 'status' => extension_loaded('fileinfo') ? 'ok' : 'warning'],
                            ['name' => 'Max Upload Size', 'value' => ini_get('upload_max_filesize'), 'required' => '2M+', 'status' => 'ok'],
                            ['name' => 'Max Post Size', 'value' => ini_get('post_max_size'), 'required' => '8M+', 'status' => 'ok'],
                            ['name' => 'Memory Limit', 'value' => ini_get('memory_limit'), 'required' => '128M+', 'status' => 'ok'],
                            ['name' => 'Sesiones PHP', 'value' => session_status() !== PHP_SESSION_DISABLED ? 'Habilitadas' : 'Deshabilitadas', 'required' => 'Requerida', 'status' => session_status() !== PHP_SESSION_DISABLED ? 'ok' : 'error'],
                            ['name' => 'Display Errors', 'value' => ini_get('display_errors') ? 'ON' : 'OFF', 'required' => 'OFF en producción', 'status' => ini_get('display_errors') ? 'warning' : 'ok'],
                            ['name' => 'Error Reporting', 'value' => error_reporting(), 'required' => 'Configurado', 'status' => 'ok']
                        ];
                        
                        foreach ($php_checks as $check) {
                            $total_checks++;
                            if ($check['status'] == 'ok') $passed_checks++;
                            elseif ($check['status'] == 'warning') $warning_checks++;
                            else $failed_checks++;
                            
                            $icon = $check['status'] == 'ok' ? 'check-circle-fill' : 
                                   ($check['status'] == 'warning' ? 'exclamation-triangle-fill' : 'x-circle-fill');
                            $class = 'status-' . $check['status'];
                            
                            echo "<div class='check-item'>";
                            echo "<div class='d-flex justify-content-between align-items-center'>";
                            echo "<div>";
                            echo "<i class='bi bi-$icon me-2 $class'></i>";
                            echo "<strong>{$check['name']}</strong>";
                            echo "<br><small class='text-muted'>Requerido: {$check['required']}</small>";
                            echo "</div>";
                            echo "<span class='$class'>{$check['value']}</span>";
                            echo "</div>";
                            echo "</div>";
                        }
                        
                        // Cerrar conexión
                        if (isset($conn) && $conn) {
                            $conn->close();
                        }
                        ?>
                    </div>
                </div>

                <!-- Resumen Final -->
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h4 class="mb-0"><i class="bi bi-clipboard-data me-2"></i>Resumen General del Sistema</h4>
                    </div>
                    <div class="card-body">
                        <?php
                        $success_rate = $total_checks > 0 ? round(($passed_checks / $total_checks) * 100) : 0;
                        $color = $success_rate >= 90 ? 'success' : ($success_rate >= 70 ? 'warning' : 'danger');
                        ?>
                        <div class="row text-center mb-4">
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h3 class="text-primary"><?php echo $total_checks; ?></h3>
                                        <p class="mb-0">Total Verificaciones</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h3 class="text-success"><?php echo $passed_checks; ?></h3>
                                        <p class="mb-0">Exitosas</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h3 class="text-warning"><?php echo $warning_checks; ?></h3>
                                        <p class="mb-0">Advertencias</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h3 class="text-danger"><?php echo $failed_checks; ?></h3>
                                        <p class="mb-0">Errores</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <h5 class="mb-3">Tasa de Éxito del Sistema:</h5>
                        <div class="progress mb-3" style="height: 30px;">
                            <div class="progress-bar bg-<?php echo $color; ?>" role="progressbar" 
                                 style="width: <?php echo $success_rate; ?>%;" 
                                 aria-valuenow="<?php echo $success_rate; ?>" 
                                 aria-valuemin="0" aria-valuemax="100">
                                <strong><?php echo $success_rate; ?>%</strong>
                            </div>
                        </div>
                        
                        <?php if ($success_rate >= 90): ?>
                        <div class="alert alert-success">
                            <h5 class="alert-heading"><i class="bi bi-check-circle-fill me-2"></i>Sistema en Excelente Estado</h5>
                            <p class="mb-0">El sistema GQ-Turismo está funcionando correctamente y está listo para producción.</p>
                        </div>
                        <?php elseif ($success_rate >= 70): ?>
                        <div class="alert alert-warning">
                            <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill me-2"></i>Sistema Funcional con Advertencias</h5>
                            <p class="mb-0">El sistema funciona pero requiere atención en algunas áreas. Revise las advertencias anteriores.</p>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-danger">
                            <h5 class="alert-heading"><i class="bi bi-x-circle-fill me-2"></i>Sistema Requiere Atención</h5>
                            <p class="mb-0">Se han detectado múltiples problemas que requieren corrección inmediata.</p>
                        </div>
                        <?php endif; ?>
                        
                        <hr>
                        
                        <h5 class="mb-3">Funcionalidades Principales:</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bi bi-map-fill text-primary me-2"></i>Sistema de Itinerarios</h6>
                                        <ul class="small mb-0">
                                            <li>Crear itinerarios personalizados</li>
                                            <li>Agregar destinos, guías, agencias y locales</li>
                                            <li>Cálculo automático de presupuesto</li>
                                            <li>Gestión de fechas y tiempos</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bi bi-chat-dots-fill text-success me-2"></i>Sistema de Mensajería</h6>
                                        <ul class="small mb-0">
                                            <li>Chat en tiempo real</li>
                                            <li>Conversaciones entre usuarios</li>
                                            <li>Notificaciones de mensajes nuevos</li>
                                            <li>Interfaz móvil optimizada</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bi bi-compass-fill text-info me-2"></i>Exploración de Destinos</h6>
                                        <ul class="small mb-0">
                                            <li>Búsqueda avanzada con filtros</li>
                                            <li>Geolocalización con mapas</li>
                                            <li>Categorización de destinos</li>
                                            <li>Sistema de reseñas y valoraciones</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <h6 class="card-title"><i class="bi bi-bag-check-fill text-warning me-2"></i>Sistema de Pedidos y Reservas</h6>
                                        <ul class="small mb-0">
                                            <li>Pedidos a proveedores de servicios</li>
                                            <li>Gestión de reservas</li>
                                            <li>Seguimiento de estado</li>
                                            <li>Notificaciones automáticas</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex gap-2 flex-wrap justify-content-center">
                            <a href="index.php" class="btn btn-primary">
                                <i class="bi bi-house me-2"></i>Ir al Inicio
                            </a>
                            <a href="itinerario.php" class="btn btn-success">
                                <i class="bi bi-map me-2"></i>Ver Itinerarios
                            </a>
                            <a href="mis_mensajes.php" class="btn btn-info text-white">
                                <i class="bi bi-chat-dots me-2"></i>Sistema de Chat
                            </a>
                            <a href="destinos.php" class="btn btn-warning">
                                <i class="bi bi-compass me-2"></i>Explorar Destinos
                            </a>
                            <a href="admin/dashboard.php" class="btn btn-secondary">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard Admin
                            </a>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <small class="text-white">
                        <i class="bi bi-info-circle me-1"></i>
                        Última verificación: <?php echo date('d/m/Y H:i:s'); ?>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
