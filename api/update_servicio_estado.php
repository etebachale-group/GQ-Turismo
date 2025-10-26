<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$pedido_id = $data['pedido_id'] ?? null;
$nuevo_estado = $data['estado'] ?? null;
$notas = $data['notas'] ?? '';
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

if (!$pedido_id || !$nuevo_estado) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit();
}

// Validar estados permitidos
$allowed_statuses = ['pendiente', 'confirmado', 'rechazado', 'completado'];
if (!in_array($nuevo_estado, $allowed_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Estado inválido']);
    exit();
}

// Verificar que el usuario es el proveedor del servicio
$stmt = $conn->prepare("SELECT * FROM pedidos_servicios WHERE id = ? AND id_proveedor = ? AND tipo_proveedor = ?");
$stmt->bind_param("iis", $pedido_id, $user_id, $user_type);
$stmt->execute();
$result = $stmt->get_result();
$pedido = $result->fetch_assoc();
$stmt->close();

if (!$pedido) {
    echo json_encode(['success' => false, 'message' => 'Pedido no encontrado o sin permisos']);
    exit();
}

// Actualizar estado del pedido
$stmt = $conn->prepare("UPDATE pedidos_servicios SET estado = ? WHERE id = ?");
$stmt->bind_param("si", $nuevo_estado, $pedido_id);

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $stmt->error]);
    exit();
}
$stmt->close();

// Registrar confirmación
$stmt = $conn->prepare("
    INSERT INTO confirmaciones_servicios 
    (id_pedido_servicio, id_proveedor, tipo_proveedor, estado_confirmacion, fecha_confirmacion, notas_proveedor)
    VALUES (?, ?, ?, ?, NOW(), ?)
    ON DUPLICATE KEY UPDATE 
    estado_confirmacion = VALUES(estado_confirmacion),
    fecha_confirmacion = VALUES(fecha_confirmacion),
    notas_proveedor = VALUES(notas_proveedor)
");
$stmt->bind_param("iisss", $pedido_id, $user_id, $user_type, $nuevo_estado, $notas);
$stmt->execute();
$stmt->close();

// Si se confirma, crear tarea automáticamente
if ($nuevo_estado === 'confirmado' && $pedido['id_itinerario']) {
    // Obtener nombre del servicio
    $nombre_servicio = 'Servicio confirmado';
    $tipo_tarea = 'actividad';
    
    switch ($user_type) {
        case 'guia':
            $tipo_tarea = 'guia';
            $stmt = $conn->prepare("SELECT nombre_servicio FROM servicios_guia WHERE id = ?");
            break;
        case 'local':
            $tipo_tarea = 'comida';
            if ($pedido['tipo_item'] === 'servicio') {
                $stmt = $conn->prepare("SELECT nombre_servicio as nombre FROM servicios_local WHERE id = ?");
            } else {
                $stmt = $conn->prepare("SELECT nombre_menu as nombre FROM menus_local WHERE id = ?");
            }
            break;
        case 'agencia':
            if ($pedido['tipo_item'] === 'servicio') {
                $stmt = $conn->prepare("SELECT nombre_servicio as nombre FROM servicios_agencia WHERE id = ?");
            } else {
                $stmt = $conn->prepare("SELECT nombre_menu as nombre FROM menus_agencia WHERE id = ?");
            }
            break;
    }
    
    if (isset($stmt)) {
        $stmt->bind_param("i", $pedido['id_servicio_o_menu']);
        $stmt->execute();
        $service_result = $stmt->get_result();
        if ($row = $service_result->fetch_assoc()) {
            $nombre_servicio = $row['nombre_servicio'] ?? $row['nombre'];
        }
        $stmt->close();
    }
    
    // Crear tarea
    $stmt = $conn->prepare("
        INSERT INTO itinerario_tareas 
        (id_itinerario, id_destino, titulo, descripcion, tipo_tarea, id_proveedor, tipo_proveedor, creado_por, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')
    ");
    $titulo = "Servicio: " . $nombre_servicio;
    $descripcion = "Servicio confirmado por " . $user_type;
    $stmt->bind_param("iisssiis", 
        $pedido['id_itinerario'], 
        $pedido['id_destino'], 
        $titulo, 
        $descripcion, 
        $tipo_tarea, 
        $user_id, 
        $user_type, 
        $pedido['id_turista']
    );
    $stmt->execute();
    $stmt->close();
    
    // Crear notificación
    $stmt = $conn->prepare("
        INSERT INTO notificaciones (id_usuario, tipo, titulo, mensaje, url)
        VALUES (?, 'confirmacion', 'Servicio Confirmado', ?, ?)
    ");
    $mensaje = "Tu servicio '$nombre_servicio' ha sido confirmado";
    $url = "mapa_itinerario.php?id=" . $pedido['id_itinerario'];
    $stmt->bind_param("iss", $pedido['id_turista'], $mensaje, $url);
    $stmt->execute();
    $stmt->close();
}

echo json_encode(['success' => true, 'message' => 'Servicio actualizado correctamente']);
$conn->close();
?>
