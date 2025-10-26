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
$notes = $data['notes'] ?? '';
$user_id = $_SESSION['user_id'];

if (!$task_id) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit();
}

// Verificar permisos
$stmt = $conn->prepare("SELECT creado_por, id_proveedor FROM itinerario_tareas WHERE id = ?");
$stmt->bind_param("i", $task_id);
$stmt->execute();
$result = $stmt->get_result();
$task = $result->fetch_assoc();
$stmt->close();

if (!$task) {
    echo json_encode(['success' => false, 'message' => 'Tarea no encontrada']);
    exit();
}

if ($task['creado_por'] != $user_id && $task['id_proveedor'] != $user_id) {
    echo json_encode(['success' => false, 'message' => 'Sin permisos']);
    exit();
}

// Actualizar notas
$stmt = $conn->prepare("UPDATE itinerario_tareas SET notas = ? WHERE id = ?");
$stmt->bind_param("si", $notes, $task_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Notas guardadas correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al guardar: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
