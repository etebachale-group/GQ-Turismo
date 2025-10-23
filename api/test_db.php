<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

$test = $_GET['test'] ?? '';
$response = ['success' => false, 'message' => '', 'details' => null];

switch ($test) {
    case 'itinerarios_table':
        $result = $conn->query("SHOW COLUMNS FROM itinerarios");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        $required = ['id', 'id_usuario', 'nombre_itinerario', 'estado', 'fecha_inicio', 'fecha_fin', 
                     'presupuesto_estimado', 'ciudad', 'notas', 'precio_total'];
        $missing = array_diff($required, $columns);
        
        if (empty($missing)) {
            $response['success'] = true;
            $response['message'] = 'Tabla itinerarios tiene todas las columnas necesarias';
            $response['details'] = $columns;
        } else {
            $response['message'] = 'Faltan columnas: ' . implode(', ', $missing);
            $response['details'] = ['found' => $columns, 'missing' => $missing];
        }
        break;

    case 'itinerario_destinos_table':
        $result = $conn->query("SHOW TABLES LIKE 'itinerario_destinos'");
        if ($result->num_rows > 0) {
            $columns_result = $conn->query("SHOW COLUMNS FROM itinerario_destinos");
            $columns = [];
            while ($row = $columns_result->fetch_assoc()) {
                $columns[] = $row['Field'];
            }
            $response['success'] = true;
            $response['message'] = 'Tabla itinerario_destinos existe';
            $response['details'] = $columns;
        } else {
            $response['message'] = 'Tabla itinerario_destinos NO existe';
        }
        break;

    case 'itinerario_servicios_table':
        $result = $conn->query("SHOW TABLES LIKE 'itinerario_servicios'");
        if ($result->num_rows > 0) {
            $columns_result = $conn->query("SHOW COLUMNS FROM itinerario_servicios");
            $columns = [];
            while ($row = $columns_result->fetch_assoc()) {
                $columns[] = $row['Field'];
            }
            $response['success'] = true;
            $response['message'] = 'Tabla itinerario_servicios existe';
            $response['details'] = $columns;
        } else {
            $response['message'] = 'Tabla itinerario_servicios NO existe';
        }
        break;

    case 'reserva_servicios_table':
        $result = $conn->query("SHOW TABLES LIKE 'reserva_servicios'");
        if ($result->num_rows > 0) {
            $columns_result = $conn->query("SHOW COLUMNS FROM reserva_servicios");
            $columns = [];
            while ($row = $columns_result->fetch_assoc()) {
                $columns[] = $row['Field'];
            }
            $response['success'] = true;
            $response['message'] = 'Tabla reserva_servicios existe';
            $response['details'] = $columns;
        } else {
            $response['message'] = 'Tabla reserva_servicios NO existe';
        }
        break;

    case 'check_duplicates':
        $result = $conn->query("
            SELECT nombre, categoria, COUNT(*) as count 
            FROM destinos 
            GROUP BY nombre, categoria 
            HAVING count > 1
        ");
        
        $duplicates = [];
        while ($row = $result->fetch_assoc()) {
            $duplicates[] = $row;
        }
        
        if (empty($duplicates)) {
            $response['success'] = true;
            $response['message'] = 'No hay destinos duplicados';
        } else {
            $response['message'] = 'Se encontraron ' . count($duplicates) . ' destinos duplicados';
            $response['details'] = $duplicates;
        }
        break;

    case 'check_indexes':
        $tables = ['itinerarios', 'itinerario_destinos', 'itinerario_servicios', 'mensajes', 'destinos'];
        $indexes = [];
        
        foreach ($tables as $table) {
            $result = $conn->query("SHOW INDEX FROM $table");
            $table_indexes = [];
            while ($row = $result->fetch_assoc()) {
                $table_indexes[] = $row['Key_name'];
            }
            $indexes[$table] = array_unique($table_indexes);
        }
        
        $response['success'] = true;
        $response['message'] = 'Índices verificados';
        $response['details'] = $indexes;
        break;

    default:
        $response['message'] = 'Prueba no reconocida';
}

echo json_encode($response);
$conn->close();
?>
