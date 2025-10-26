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

$data = json_decode(file_get_contents('php://input'), true);
$tarea_id = $data['tarea_id'] ?? null;
$nuevo_estado = $data['estado'] ?? null;

if (!$tarea_id || !$nuevo_estado) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

// Validar estados permitidos
$estados_validos = ['pendiente', 'en_progreso', 'completado', 'cancelado'];
if (!in_array($nuevo_estado, $estados_validos)) {
    echo json_encode(['success' => false, 'message' => 'Estado no válido']);
    exit();
}

// Verificar que el usuario tiene acceso a esta tarea
$stmt = $conn->prepare("
    SELECT t.*, i.id_turista 
    FROM itinerario_tareas t
    INNER JOIN itinerarios i ON t.id_itinerario = i.id
    WHERE t.id = ?
");
$stmt->bind_param("i", $tarea_id);
$stmt->execute();
$tarea = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$tarea) {
    echo json_encode(['success' => false, 'message' => 'Tarea no encontrada']);
    exit();
}

// Verificar permisos
$tiene_permiso = false;
if ($user_type === 'turista' && $tarea['id_turista'] == $user_id) {
    $tiene_permiso = true;
} elseif ($user_type === 'guia') {
    // Verificar que el guía está asignado
    $stmt = $conn->prepare("
        SELECT 1 FROM itinerario_guias ig
        INNER JOIN guias_turisticos gt ON ig.id_guia = gt.id
        WHERE ig.id_itinerario = ? AND gt.id_usuario = ?
    ");
    $stmt->bind_param("ii", $tarea['id_itinerario'], $user_id);
    $stmt->execute();
    $tiene_permiso = $stmt->get_result()->num_rows > 0;
    $stmt->close();
}

if (!$tiene_permiso) {
    echo json_encode(['success' => false, 'message' => 'No tiene permiso para modificar esta tarea']);
    exit();
}

// Actualizar estado
$fecha_completado = null;
$completado_por = null;

if ($nuevo_estado === 'completado') {
    $fecha_completado = date('Y-m-d H:i:s');
    $completado_por = $user_id;
}

$stmt = $conn->prepare("
    UPDATE itinerario_tareas 
    SET estado = ?, 
        fecha_completado = ?, 
        completado_por = ?,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = ?
");
$stmt->bind_param("ssii", $nuevo_estado, $fecha_completado, $completado_por, $tarea_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar estado']);
}

$stmt->close();
$conn->close();
?>
