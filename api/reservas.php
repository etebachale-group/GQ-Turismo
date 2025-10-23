<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

// Verificar autenticación
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'turista') {
    $response['message'] = 'Acceso denegado. Solo los turistas pueden hacer reservas.';
    echo json_encode($response);
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itinerario_id = $_POST['itinerario_id'] ?? null;
    $fecha_reserva = date('Y-m-d'); // Fecha actual de la reserva
    $fecha_inicio = $_POST['fecha_reserva_inicio'] ?? null;
    $fecha_fin = $_POST['fecha_reserva_fin'] ?? null;
    $num_personas = $_POST['num_personas'] ?? 1;
    $metodo_pago = $_POST['metodo_pago'] ?? 'efectivo';
    $telefono_contacto = $_POST['telefono_contacto'] ?? '';
    $comentarios = $_POST['comentarios'] ?? '';

    // Validación
    if (empty($itinerario_id)) {
        $response['message'] = 'Itinerario no especificado.';
        echo json_encode($response);
        exit;
    }

    // Verificar que el itinerario existe y pertenece al usuario
    $stmt = $conn->prepare("SELECT * FROM itinerarios WHERE id = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $itinerario_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $itinerario = $result->fetch_assoc();
    $stmt->close();

    if (!$itinerario) {
        $response['message'] = 'Itinerario no encontrado o no tienes permiso.';
        echo json_encode($response);
        exit;
    }

    $conn->begin_transaction();

    try {
        // Calcular precio total
        $total_precio = $itinerario['precio_total'] * $num_personas;
        $estado = 'pendiente';
        
        // Crear la reserva principal
        $stmt = $conn->prepare("
            INSERT INTO reservas (id_itinerario, id_usuario, fecha_reserva, personas, metodo_pago, total_precio, estado) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iisisds", $itinerario_id, $user_id, $fecha_reserva, $num_personas, $metodo_pago, $total_precio, $estado);
        $stmt->execute();
        $reserva_id = $conn->insert_id;
        $stmt->close();

        // Obtener información del turista
        $stmt = $conn->prepare("SELECT nombre FROM usuarios WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $turista = $result->fetch_assoc();
        $turista_nombre = $turista['nombre'] ?? 'Usuario';
        $stmt->close();

        // Obtener destinos del itinerario
        $stmt = $conn->prepare("
            SELECT d.id, d.nombre, d.precio
            FROM itinerario_destinos id
            JOIN destinos d ON id.id_destino = d.id
            WHERE id.id_itinerario = ?
            ORDER BY id.orden ASC
        ");
        $stmt->bind_param("i", $itinerario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Insertar destinos en reserva_servicios
        $stmt_serv = $conn->prepare("INSERT INTO reserva_servicios (id_reserva, tipo_servicio, id_servicio, precio, estado) VALUES (?, 'destino', ?, ?, 'pendiente')");
        while ($row = $result->fetch_assoc()) {
            $precio_dest = $row['precio'] * $num_personas;
            $stmt_serv->bind_param("iid", $reserva_id, $row['id'], $precio_dest);
            $stmt_serv->execute();
        }
        $stmt->close();
        $stmt_serv->close();

        // Obtener servicios del itinerario (guías, agencias, locales)
        $stmt = $conn->prepare("
            SELECT tipo_servicio, id_servicio
            FROM itinerario_servicios
            WHERE id_itinerario = ?
        ");
        $stmt->bind_param("i", $itinerario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $servicios_info = [];
        while ($row = $result->fetch_assoc()) {
            $servicios_info[] = $row;
        }
        $stmt->close();

        // Insertar servicios y notificar proveedores
        foreach ($servicios_info as $servicio) {
            $tipo = $servicio['tipo_servicio'];
            $id_serv = $servicio['id_servicio'];
            
            // Insertar en reserva_servicios
            $stmt_serv = $conn->prepare("INSERT INTO reserva_servicios (id_reserva, tipo_servicio, id_servicio, estado) VALUES (?, ?, ?, 'pendiente')");
            $stmt_serv->bind_param("isi", $reserva_id, $tipo, $id_serv);
            $stmt_serv->execute();
            $stmt_serv->close();
            
            // Enviar notificación al proveedor
            $proveedor_id = $id_serv;
            $tipo_proveedor = $tipo;
            
            $mensaje = "Nueva reserva para el itinerario '{$itinerario['nombre_itinerario']}'\n\n";
            $mensaje .= "Turista: {$turista_nombre}\n";
            $mensaje .= "Fecha reserva: {$fecha_reserva}\n";
            $mensaje .= "Personas: {$num_personas}\n";
            if ($comentarios) $mensaje .= "Comentarios: {$comentarios}\n";
            $mensaje .= "\nPor favor, contacta al turista para confirmar el servicio.";
            
            // Insertar mensaje
            $stmt_msg = $conn->prepare("
                INSERT INTO mensajes (sender_id, sender_type, receiver_id, receiver_type, message) 
                VALUES (?, 'turista', ?, ?, ?)
            ");
            $stmt_msg->bind_param("iiss", $user_id, $proveedor_id, $tipo_proveedor, $mensaje);
            $stmt_msg->execute();
            $stmt_msg->close();
        }

        $conn->commit();
        
        $response['success'] = true;
        $response['message'] = 'Reserva creada exitosamente. Los proveedores han sido notificados.';
        $response['data'] = [
            'reserva_id' => $reserva_id,
            'total_precio' => $total_precio
        ];

    } catch (Exception $e) {
        $conn->rollback();
        $response['message'] = 'Error al procesar la reserva: ' . $e->getMessage();
    }
}
// OBTENER RESERVAS DEL USUARIO
else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? 'list';
    
    if ($action === 'list') {
        $sql = "SELECT r.*, i.nombre_itinerario, i.ciudad
                FROM reservas r
                JOIN itinerarios i ON r.id_itinerario = i.id
                WHERE r.id_usuario = ?
                ORDER BY r.fecha_reserva DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $reservas = [];
        while ($row = $result->fetch_assoc()) {
            // Obtener servicios de la reserva
            $stmt_serv = $conn->prepare("SELECT * FROM reserva_servicios WHERE id_reserva = ?");
            $stmt_serv->bind_param("i", $row['id']);
            $stmt_serv->execute();
            $result_serv = $stmt_serv->get_result();
            $servicios = [];
            while ($serv = $result_serv->fetch_assoc()) {
                $servicios[] = $serv;
            }
            $stmt_serv->close();
            
            $row['servicios'] = $servicios;
            $reservas[] = $row;
        }
        $stmt->close();
        
        $response['success'] = true;
        $response['data'] = $reservas;
    }
    else if ($action === 'get_one') {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $stmt = $conn->prepare("
                SELECT r.*, i.nombre_itinerario, i.ciudad, i.notas
                FROM reservas r
                JOIN itinerarios i ON r.id_itinerario = i.id
                WHERE r.id = ? AND r.id_usuario = ?
            ");
            $stmt->bind_param("ii", $id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                // Obtener servicios
                $stmt_serv = $conn->prepare("SELECT * FROM reserva_servicios WHERE id_reserva = ?");
                $stmt_serv->bind_param("i", $id);
                $stmt_serv->execute();
                $result_serv = $stmt_serv->get_result();
                $servicios = [];
                while ($serv = $result_serv->fetch_assoc()) {
                    $servicios[] = $serv;
                }
                $stmt_serv->close();
                
                $row['servicios'] = $servicios;
                $response['success'] = true;
                $response['data'] = $row;
            } else {
                $response['message'] = 'Reserva no encontrada';
            }
            $stmt->close();
        }
    }
}

else {
    $response['message'] = 'Método no permitido';
}

echo json_encode($response);
$conn->close();
?>
