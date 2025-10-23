<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'message' => 'Invalid request'];

// Verificar autenticación
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    $response['message'] = 'Acceso denegado. Debes iniciar sesión.';
    echo json_encode($response);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if ($input && isset($input['action'])) {
    switch ($input['action']) {
        case 'create_order':
            // Solo turistas pueden crear pedidos
            if ($_SESSION['user_type'] !== 'turista') {
                $response['message'] = 'Solo los turistas pueden crear pedidos.';
                echo json_encode($response);
                exit;
            }
            
            $id_turista = $_SESSION['user_id'];
            $tipo_proveedor = $input['tipo_proveedor'] ?? '';
            $id_proveedor = $input['id_proveedor'] ?? 0;
            $id_servicio_o_menu = $input['id_servicio_o_menu'] ?? 0; // Este será el id_guia si tipo_item es guia_destino
            $tipo_item = $input['tipo_item'] ?? '';
            $id_itinerario = $input['id_itinerario'] ?? null;
            $fecha_servicio = $input['fecha_servicio'] ?? '';
            $cantidad_personas = $input['cantidad_personas'] ?? 1;
            $precio_unitario = $input['precio_unitario'] ?? 0.00;
            $precio_total = $input['precio_total'] ?? 0.00;
            $id_destino = null; // Inicializar id_destino

            // Si el tipo_item es guia_destino, necesitamos el id_destino
            if ($tipo_item === 'guia_destino') {
                // El id_servicio_o_menu es en realidad el id_guia
                $id_guia = $id_servicio_o_menu;
                // El id_destino debe venir de algún lugar, asumiremos que viene en un nuevo campo 'id_destino_asociado'
                // o que se puede inferir del contexto. Por ahora, lo pasaremos como un nuevo campo.
                // Para simplificar, asumiremos que el id_destino se pasa como 'id_destino_asociado' en el input.
                $id_destino = $input['id_destino_asociado'] ?? null;

                // Validar que el id_destino_asociado no sea nulo
                if (empty($id_destino)) {
                    $response['message'] = 'ID de destino asociado es requerido para reservas de guía-destino.';
                    echo json_encode($response);
                    exit;
                }
            }

            // Validación básica
            if (empty($tipo_proveedor) || empty($id_proveedor) || empty($id_servicio_o_menu) || empty($tipo_item) || empty($fecha_servicio) || empty($precio_total)) {
                $response['message'] = 'Datos incompletos para realizar el pedido.';
                echo json_encode($response);
                exit;
            }

            // Asegurarse de que el tipo de proveedor y tipo de item sean válidos
            $allowed_proveedores = ['agencia', 'guia', 'local'];
            $allowed_items = ['servicio', 'menu', 'guia_destino']; // Añadir guia_destino
            if (!in_array($tipo_proveedor, $allowed_proveedores) || !in_array($tipo_item, $allowed_items)) {
                $response['message'] = 'Tipo de proveedor o tipo de item no válido.';
                echo json_encode($response);
                exit;
            }

            // Insertar en la base de datos
            $stmt = $conn->prepare("INSERT INTO pedidos_servicios (id_turista, tipo_proveedor, id_proveedor, id_servicio_o_menu, tipo_item, id_itinerario, fecha_servicio, cantidad_personas, precio_unitario, precio_total, id_destino) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isiisissddd", $id_turista, $tipo_proveedor, $id_proveedor, $id_servicio_o_menu, $tipo_item, $id_itinerario, $fecha_servicio, $cantidad_personas, $precio_unitario, $precio_total, $id_destino);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Pedido realizado con éxito. Espera la confirmación del proveedor.';
            } else {
                $response['message'] = 'Error al realizar el pedido: ' . $stmt->error;
            }
            $stmt->close();
            break;

        case 'update_status':
            // Solo proveedores pueden actualizar estado
            if (!in_array($_SESSION['user_type'], ['agencia', 'guia', 'local', 'super_admin'])) {
                $response['message'] = 'Solo los proveedores pueden actualizar pedidos.';
                echo json_encode($response);
                exit;
            }
            
            $pedido_id = $input['pedido_id'] ?? 0;
            $nuevo_estado = $input['estado'] ?? '';
            $user_id = $_SESSION['user_id'];
            $user_type = $_SESSION['user_type'];
            
            if (empty($pedido_id) || empty($nuevo_estado)) {
                $response['message'] = 'Datos incompletos.';
                echo json_encode($response);
                exit;
            }
            
            // Validar estados permitidos
            $estados_permitidos = ['pendiente', 'confirmado', 'cancelado', 'completado'];
            if (!in_array($nuevo_estado, $estados_permitidos)) {
                $response['message'] = 'Estado no válido.';
                echo json_encode($response);
                exit;
            }
            
            // Verificar que el pedido pertenece al proveedor
            $stmt_check = $conn->prepare("SELECT id FROM pedidos_servicios WHERE id = ? AND id_proveedor = ? AND tipo_proveedor = ?");
            $stmt_check->bind_param("iis", $pedido_id, $user_id, $user_type);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            
            if ($result_check->num_rows === 0) {
                $response['message'] = 'No tienes permiso para actualizar este pedido.';
                $stmt_check->close();
                echo json_encode($response);
                exit;
            }
            $stmt_check->close();
            
            // Actualizar estado
            $stmt = $conn->prepare("UPDATE pedidos_servicios SET estado = ? WHERE id = ?");
            $stmt->bind_param("si", $nuevo_estado, $pedido_id);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Estado actualizado correctamente.';
            } else {
                $response['message'] = 'Error al actualizar: ' . $stmt->error;
            }
            $stmt->close();
            break;

        case 'get_provider_orders':
            // Solo proveedores pueden ver sus pedidos
            if (!in_array($_SESSION['user_type'], ['agencia', 'guia', 'local'])) {
                $response['message'] = 'Solo los proveedores pueden ver pedidos.';
                echo json_encode($response);
                exit;
            }
            
            $user_id = $_SESSION['user_id'];
            $user_type = $_SESSION['user_type'];
            
            $stmt = $conn->prepare("
                SELECT ps.*, 
                       u.nombre as turista_nombre, 
                       u.email as turista_email
                FROM pedidos_servicios ps
                LEFT JOIN usuarios u ON ps.id_turista = u.id
                WHERE ps.id_proveedor = ? AND ps.tipo_proveedor = ?
                ORDER BY ps.fecha_solicitud DESC
            ");
            $stmt->bind_param("is", $user_id, $user_type);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $pedidos = [];
            while ($row = $result->fetch_assoc()) {
                $pedidos[] = $row;
            }
            $stmt->close();
            
            $response['success'] = true;
            $response['pedidos'] = $pedidos;
            break;

        // Otros casos como 'update_order_status' o 'get_orders' podrían ir aquí

        default:
            $response['message'] = 'Acción no reconocida.';
            break;
    }
}

$conn->close();

echo json_encode($response);
?>
