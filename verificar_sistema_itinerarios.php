<?php
require_once 'includes/db_connect.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación del Sistema de Itinerarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        .check-ok { color: #28a745; }
        .check-error { color: #dc3545; }
        .check-warning { color: #ffc107; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="mb-4"><i class="bi bi-shield-check me-2"></i>Verificación del Sistema de Itinerarios</h1>
        
        <?php
        $checks = [];
        
        // 1. Verificar tabla itinerarios
        echo "<div class='card mb-3'><div class='card-body'>";
        echo "<h5><i class='bi bi-database me-2'></i>1. Verificando Tabla: itinerarios</h5>";
        $result = $conn->query("SHOW TABLES LIKE 'itinerarios'");
        if ($result && $result->num_rows > 0) {
            echo "<p class='check-ok'><i class='bi bi-check-circle me-2'></i>Tabla 'itinerarios' existe</p>";
            
            // Verificar columnas
            $result = $conn->query("DESCRIBE itinerarios");
            $columns = [];
            while ($row = $result->fetch_assoc()) {
                $columns[] = $row['Field'];
            }
            
            $required_columns = ['id', 'id_usuario', 'nombre_itinerario', 'estado', 'fecha_inicio', 
                                'fecha_fin', 'presupuesto_estimado', 'ciudad', 'notas', 'precio_total'];
            foreach ($required_columns as $col) {
                if (in_array($col, $columns)) {
                    echo "<small class='check-ok'>✓ Columna '{$col}' OK</small><br>";
                } else {
                    echo "<small class='check-error'>✗ Columna '{$col}' FALTA</small><br>";
                }
            }
        } else {
            echo "<p class='check-error'><i class='bi bi-x-circle me-2'></i>Tabla 'itinerarios' NO existe</p>";
        }
        echo "</div></div>";
        
        // 2. Verificar tabla itinerario_destinos
        echo "<div class='card mb-3'><div class='card-body'>";
        echo "<h5><i class='bi bi-database me-2'></i>2. Verificando Tabla: itinerario_destinos</h5>";
        $result = $conn->query("SHOW TABLES LIKE 'itinerario_destinos'");
        if ($result && $result->num_rows > 0) {
            echo "<p class='check-ok'><i class='bi bi-check-circle me-2'></i>Tabla 'itinerario_destinos' existe</p>";
            
            $result = $conn->query("DESCRIBE itinerario_destinos");
            $columns = [];
            while ($row = $result->fetch_assoc()) {
                $columns[] = $row['Field'];
            }
            
            if (in_array('orden', $columns)) {
                echo "<small class='check-ok'>✓ Columna 'orden' OK</small><br>";
            } else {
                echo "<small class='check-warning'>⚠ Columna 'orden' FALTA (opcional pero recomendada)</small><br>";
            }
        } else {
            echo "<p class='check-error'><i class='bi bi-x-circle me-2'></i>Tabla 'itinerario_destinos' NO existe</p>";
        }
        echo "</div></div>";
        
        // 3. Verificar tablas de servicios
        echo "<div class='card mb-3'><div class='card-body'>";
        echo "<h5><i class='bi bi-database me-2'></i>3. Verificando Tablas de Servicios</h5>";
        
        $service_tables = ['itinerario_guias', 'itinerario_agencias', 'itinerario_locales'];
        foreach ($service_tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->num_rows > 0) {
                echo "<p class='check-ok'><i class='bi bi-check-circle me-2'></i>Tabla '{$table}' existe</p>";
            } else {
                echo "<p class='check-error'><i class='bi bi-x-circle me-2'></i>Tabla '{$table}' NO existe</p>";
                echo "<small class='text-muted'>Ejecuta: fix_itinerarios_servicios.sql</small><br>";
            }
        }
        echo "</div></div>";
        
        // 4. Verificar tabla reservas actualizada
        echo "<div class='card mb-3'><div class='card-body'>";
        echo "<h5><i class='bi bi-database me-2'></i>4. Verificando Tabla: reservas</h5>";
        $result = $conn->query("SHOW TABLES LIKE 'reservas'");
        if ($result && $result->num_rows > 0) {
            echo "<p class='check-ok'><i class='bi bi-check-circle me-2'></i>Tabla 'reservas' existe</p>";
            
            $result = $conn->query("DESCRIBE reservas");
            $columns = [];
            while ($row = $result->fetch_assoc()) {
                $columns[] = $row['Field'];
            }
            
            $new_columns = ['id_itinerario', 'fecha_inicio', 'fecha_fin', 'num_personas', 
                           'telefono_contacto', 'comentarios', 'monto_total'];
            foreach ($new_columns as $col) {
                if (in_array($col, $columns)) {
                    echo "<small class='check-ok'>✓ Columna '{$col}' OK</small><br>";
                } else {
                    echo "<small class='check-error'>✗ Columna '{$col}' FALTA</small><br>";
                }
            }
            
            if (!in_array('id_itinerario', $columns)) {
                echo "<small class='text-muted'>Ejecuta: fix_reservas_itinerarios.sql</small><br>";
            }
        } else {
            echo "<p class='check-error'><i class='bi bi-x-circle me-2'></i>Tabla 'reservas' NO existe</p>";
        }
        echo "</div></div>";
        
        // 5. Verificar archivos PHP
        echo "<div class='card mb-3'><div class='card-body'>";
        echo "<h5><i class='bi bi-file-code me-2'></i>5. Verificando Archivos PHP</h5>";
        
        $files = [
            'crear_itinerario.php' => 'Página de creación/edición',
            'itinerario.php' => 'Página de lista',
            'reservas.php' => 'Página de reservas',
            'api/itinerarios.php' => 'API de itinerarios',
            'api/reservas.php' => 'API de reservas'
        ];
        
        foreach ($files as $file => $desc) {
            if (file_exists($file)) {
                $size = filesize($file);
                echo "<p class='check-ok'><i class='bi bi-check-circle me-2'></i>{$desc}: {$file} (" . number_format($size) . " bytes)</p>";
            } else {
                echo "<p class='check-error'><i class='bi bi-x-circle me-2'></i>{$desc}: {$file} NO EXISTE</p>";
            }
        }
        echo "</div></div>";
        
        // 6. Verificar datos de prueba
        echo "<div class='card mb-3'><div class='card-body'>";
        echo "<h5><i class='bi bi-database-check me-2'></i>6. Verificando Datos Disponibles</h5>";
        
        // Contar destinos
        $result = $conn->query("SELECT COUNT(*) as total FROM destinos");
        if ($result) {
            $row = $result->fetch_assoc();
            $total = $row['total'];
            if ($total > 0) {
                echo "<p class='check-ok'><i class='bi bi-geo-alt me-2'></i>{$total} destinos disponibles</p>";
            } else {
                echo "<p class='check-warning'><i class='bi bi-exclamation-triangle me-2'></i>No hay destinos disponibles</p>";
            }
        }
        
        // Contar guías
        $result = $conn->query("SELECT COUNT(*) as total FROM guias_turisticos");
        if ($result) {
            $row = $result->fetch_assoc();
            $total = $row['total'];
            if ($total > 0) {
                echo "<p class='check-ok'><i class='bi bi-person-badge me-2'></i>{$total} guías disponibles</p>";
            } else {
                echo "<p class='check-warning'><i class='bi bi-exclamation-triangle me-2'></i>No hay guías disponibles</p>";
            }
        }
        
        // Contar agencias
        $result = $conn->query("SELECT COUNT(*) as total FROM agencias");
        if ($result) {
            $row = $result->fetch_assoc();
            $total = $row['total'];
            if ($total > 0) {
                echo "<p class='check-ok'><i class='bi bi-building me-2'></i>{$total} agencias disponibles</p>";
            } else {
                echo "<p class='check-warning'><i class='bi bi-exclamation-triangle me-2'></i>No hay agencias disponibles</p>";
            }
        }
        
        // Contar locales
        $result = $conn->query("SELECT COUNT(*) as total FROM lugares_locales");
        if ($result) {
            $row = $result->fetch_assoc();
            $total = $row['total'];
            if ($total > 0) {
                echo "<p class='check-ok'><i class='bi bi-shop me-2'></i>{$total} locales disponibles</p>";
            } else {
                echo "<p class='check-warning'><i class='bi bi-exclamation-triangle me-2'></i>No hay locales disponibles</p>";
            }
        }
        
        // Contar turistas
        $result = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE user_type = 'turista'");
        if ($result) {
            $row = $result->fetch_assoc();
            $total = $row['total'];
            if ($total > 0) {
                echo "<p class='check-ok'><i class='bi bi-people me-2'></i>{$total} turistas registrados</p>";
            } else {
                echo "<p class='check-warning'><i class='bi bi-exclamation-triangle me-2'></i>No hay turistas registrados</p>";
            }
        }
        
        echo "</div></div>";
        
        // 7. Prueba de API
        echo "<div class='card mb-3'><div class='card-body'>";
        echo "<h5><i class='bi bi-plug me-2'></i>7. Información de APIs</h5>";
        echo "<p><strong>API Itinerarios:</strong> <code>api/itinerarios.php</code></p>";
        echo "<ul>";
        echo "<li>POST - Crear/Actualizar itinerario</li>";
        echo "<li>DELETE ?action=delete&id=X - Eliminar itinerario</li>";
        echo "<li>GET ?action=list - Listar itinerarios</li>";
        echo "<li>GET ?action=get_one&id=X - Obtener un itinerario</li>";
        echo "</ul>";
        
        echo "<p><strong>API Reservas:</strong> <code>api/reservas.php</code></p>";
        echo "<ul>";
        echo "<li>POST - Crear reserva desde itinerario</li>";
        echo "<li>Notifica automáticamente a todos los proveedores</li>";
        echo "</ul>";
        echo "</div></div>";
        
        // Resumen final
        echo "<div class='card bg-primary text-white'><div class='card-body'>";
        echo "<h4><i class='bi bi-info-circle me-2'></i>Instrucciones Finales</h4>";
        echo "<ol>";
        echo "<li>Si alguna tabla falta, ejecuta los scripts SQL correspondientes</li>";
        echo "<li>Asegúrate de tener datos de prueba (destinos, guías, agencias, locales)</li>";
        echo "<li>Crea un usuario turista para probar el sistema</li>";
        echo "<li>Accede a <code>crear_itinerario.php</code> para crear un itinerario</li>";
        echo "<li>Accede a <code>itinerario.php</code> para ver la lista</li>";
        echo "<li>Usa el botón 'Reservar' para probar el sistema de notificaciones</li>";
        echo "</ol>";
        echo "</div></div>";
        
        $conn->close();
        ?>
        
        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-primary btn-lg">Ir al Inicio</a>
            <a href="crear_itinerario.php" class="btn btn-success btn-lg">Crear Itinerario</a>
            <a href="itinerario.php" class="btn btn-info btn-lg">Ver Itinerarios</a>
        </div>
    </div>
</body>
</html>
