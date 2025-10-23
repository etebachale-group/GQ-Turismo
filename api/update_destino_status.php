<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$id_destino = $data['id_destino'] ?? null;
$estado = $data['estado'] ?? null;

if (!$id_destino || !$estado) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Verificar que el destino pertenece a un itinerario del usuario
$stmt = $conn->prepare("
    SELECT id.*, i.id_usuario 
    FROM itinerario_destinos id
    JOIN itinerarios i ON id.id_itinerario = i.id
    WHERE id.id = ?
");
$stmt->bind_param("i", $id_destino);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Destino no encontrado']);
    exit();
}

$destino = $result->fetch_assoc();
$stmt->close();

// Solo el turista dueño puede actualizar el estado
if ($user_type !== 'turista' || $destino['id_usuario'] != $user_id) {
    echo json_encode(['success' => false, 'message' => 'No tienes permiso para actualizar este destino']);
    exit();
}

// Actualizar el estado
$stmt = $conn->prepare("UPDATE itinerario_destinos SET estado = ? WHERE id = ?");
$stmt->bind_param("si", $estado, $id_destino);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Estado actualizado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado']);
}

$stmt->close();
$conn->close();
?>
