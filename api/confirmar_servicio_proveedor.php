<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Validar que es un proveedor
if (!in_array($user_type, ['agencia', 'guia', 'local'])) {
    echo json_encode(['success' => false, 'message' => 'Solo proveedores pueden confirmar servicios']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$pedido_id = $data['pedido_id'] ?? null;
$nuevo_estado = $data['estado'] ?? null;
$notas = $data['notas'] ?? '';

if (!$pedido_id || !$nuevo_estado) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

// Validar estados permitidos
$estados_validos = ['confirmado', 'rechazado', 'en_progreso', 'completado'];
if (!in_array($nuevo_estado, $estados_validos)) {
    echo json_encode(['success' => false, 'message' => 'Estado no válido']);
    exit();
}

// Verificar que el pedido pertenece al proveedor
$stmt = $conn->prepare("
    SELECT * FROM pedidos_servicios 
    WHERE id = ? AND id_proveedor = ? AND tipo_proveedor = ?
");
$stmt->bind_param("iis", $pedido_id, $user_id, $user_type);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pedido) {
    echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
    exit();
}

// Actualizar estado del pedido
$stmt = $conn->prepare("UPDATE pedidos_servicios SET estado = ? WHERE id = ?");
$stmt->bind_param("si", $nuevo_estado, $pedido_id);
$stmt->execute();
$stmt->close();

// Registrar confirmación
$fecha_confirmacion = null;
$fecha_completado = null;

if (in_array($nuevo_estado, ['confirmado', 'rechazado'])) {
    $fecha_confirmacion = date('Y-m-d H:i:s');
}

if ($nuevo_estado === 'completado') {
    $fecha_completado = date('Y-m-d H:i:s');
}

// Verificar si ya existe una confirmación en la nueva tabla
$stmt = $conn->prepare("
    SELECT id FROM confirmacion_servicios 
    WHERE id_pedido_servicio = ? AND id_proveedor = ?
");
$stmt->bind_param("ii", $pedido_id, $user_id);
$stmt->execute();
$confirmacion_existe = $stmt->get_result()->num_rows > 0;
$stmt->close();

if ($confirmacion_existe) {
    // Actualizar
    $update_sql = "UPDATE confirmacion_servicios SET estado = ?";
    $params = [$nuevo_estado];
    $types = "s";
    
    if ($fecha_confirmacion) {
        $update_sql .= ", fecha_confirmacion = ?";
        $params[] = $fecha_confirmacion;
        $types .= "s";
    }
    
    if ($fecha_completado) {
        $update_sql .= ", fecha_completado = ?";
        $params[] = $fecha_completado;
        $types .= "s";
    }
    
    if ($notas) {
        $update_sql .= ", notas = CONCAT(COALESCE(notas, ''), ?)";
        $params[] = "\n[" . date('Y-m-d H:i:s') . "]: $notas";
        $types .= "s";
    }
    
    $update_sql .= " WHERE id_pedido_servicio = ? AND id_proveedor = ?";
    $params[] = $pedido_id;
    $params[] = $user_id;
    $types .= "ii";
    
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param($types, ...$params);
} else {
    // Insertar
    $stmt = $conn->prepare("
        INSERT INTO confirmacion_servicios 
        (id_pedido_servicio, id_proveedor, tipo_proveedor, estado, fecha_confirmacion, fecha_completado, notas)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $note_with_timestamp = $notas ? "[" . date('Y-m-d H:i:s') . "]: $notas" : null;
    $stmt->bind_param("iisssss", $pedido_id, $user_id, $user_type, $nuevo_estado, $fecha_confirmacion, $fecha_completado, $note_with_timestamp);
}

if ($stmt->execute()) {
    // Notificar al turista (aquí podrías agregar una notificación)
    echo json_encode([
        'success' => true, 
        'message' => 'Servicio ' . $nuevo_estado . ' correctamente'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar confirmación']);
}

$stmt->close();
$conn->close();
?>
