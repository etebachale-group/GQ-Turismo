<?php
header('Content-Type: application/json');
require_once '../includes/db_connect.php';
session_start();

$response = ['success' => false, 'message' => 'Invalid request'];

// Verificar si el usuario está logueado y es un turista
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'turista') {
    $response['message'] = 'Acceso denegado. Solo los turistas pueden realizar pedidos.';
    echo json_encode($response);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if ($input && isset($input['action'])) {
    switch ($input['action']) {
        case 'create_order':
            $id_turista = $_SESSION['user_id'];
            $tipo_proveedor = $input['tipo_proveedor'] ?? '';
            $id_proveedor = $input['id_proveedor'] ?? 0;
            $id_servicio_o_menu = $input['id_servicio_o_menu'] ?? 0;
            $tipo_item = $input['tipo_item'] ?? '';
            $id_itinerario = $input['id_itinerario'] ?? null; // Nuevo campo
            $fecha_servicio = $input['fecha_servicio'] ?? '';
            $cantidad_personas = $input['cantidad_personas'] ?? 1;
            $precio_unitario = $input['precio_unitario'] ?? 0.00;
            $precio_total = $input['precio_total'] ?? 0.00;

            // Validación básica
            if (empty($tipo_proveedor) || empty($id_proveedor) || empty($id_servicio_o_menu) || empty($tipo_item) || empty($fecha_servicio) || empty($precio_total)) {
                $response['message'] = 'Datos incompletos para realizar el pedido.';
                echo json_encode($response);
                exit;
            }

            // Asegurarse de que el tipo de proveedor y tipo de item sean válidos
            $allowed_proveedores = ['agencia', 'guia', 'local'];
            $allowed_items = ['servicio', 'menu'];
            if (!in_array($tipo_proveedor, $allowed_proveedores) || !in_array($tipo_item, $allowed_items)) {
                $response['message'] = 'Tipo de proveedor o tipo de item no válido.';
                echo json_encode($response);
                exit;
            }

            // Insertar en la base de datos
            $stmt = $conn->prepare("INSERT INTO pedidos_servicios (id_turista, tipo_proveedor, id_proveedor, id_servicio_o_menu, tipo_item, id_itinerario, fecha_servicio, cantidad_personas, precio_unitario, precio_total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isiisissdd", $id_turista, $tipo_proveedor, $id_proveedor, $id_servicio_o_menu, $tipo_item, $id_itinerario, $fecha_servicio, $cantidad_personas, $precio_unitario, $precio_total);

            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Pedido realizado con éxito. Espera la confirmación del proveedor.';
            } else {
                $response['message'] = 'Error al realizar el pedido: ' . $stmt->error;
            }
            $stmt->close();
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
