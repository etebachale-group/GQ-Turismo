<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$task_id = $data['task_id'] ?? null;
$new_status = $data['status'] ?? null;
$user_id = $_SESSION['user_id'];

if (!$task_id || !$new_status) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit();
}

// Validar estados permitidos
$allowed_statuses = ['pendiente', 'en_progreso', 'completado', 'cancelado'];
if (!in_array($new_status, $allowed_statuses)) {
    echo json_encode(['success' => false, 'message' => 'Estado inválido']);
    exit();
}

// Verificar permisos (turista o proveedor asignado)
$stmt = $conn->prepare("SELECT id_itinerario, id_proveedor, creado_por FROM itinerario_tareas WHERE id = ?");
$stmt->bind_param("i", $task_id);
$stmt->execute();
$result = $stmt->get_result();
$task = $result->fetch_assoc();
$stmt->close();

if (!$task) {
    echo json_encode(['success' => false, 'message' => 'Tarea no encontrada']);
    exit();
}

// Verificar que el usuario tiene permiso para actualizar
if ($task['creado_por'] != $user_id && $task['id_proveedor'] != $user_id) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos para actualizar esta tarea']);
    exit();
}

// Actualizar el estado
$completado_por = ($new_status === 'completado') ? $user_id : null;
$fecha_completado = ($new_status === 'completado') ? date('Y-m-d H:i:s') : null;

$stmt = $conn->prepare("
    UPDATE itinerario_tareas 
    SET estado = ?, 
        completado_por = ?, 
        fecha_completado = ?
    WHERE id = ?
");
$stmt->bind_param("sisi", $new_status, $completado_por, $fecha_completado, $task_id);

if ($stmt->execute()) {
    // Crear notificación
    $mensaje = "Tarea actualizada a: " . ucfirst(str_replace('_', ' ', $new_status));
    $stmt_notif = $conn->prepare("
        INSERT INTO notificaciones (id_usuario, tipo, titulo, mensaje, url)
        VALUES (?, 'itinerario', 'Actualización de Tarea', ?, ?)
    ");
    $url = "mapa_itinerario.php?id=" . $task['id_itinerario'];
    $stmt_notif->bind_param("iss", $task['creado_por'], $mensaje, $url);
    $stmt_notif->execute();
    $stmt_notif->close();
    
    echo json_encode(['success' => true, 'message' => 'Tarea actualizada correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
