<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

// Verificar si el usuario está logueado y es un 'turista'
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'turista') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado. Solo los turistas pueden hacer reservas.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_destino = $_POST['id_destino'] ?? '';
    $fecha = $_POST['fecha'] ?? '';
    $personas = $_POST['personas'] ?? '';
    $id_usuario = $_SESSION['user_id'];

    // Validación simple
    if (empty($id_destino) || empty($fecha) || empty($personas)) {
        echo json_encode(['success' => false, 'message' => 'Por favor, completa todos los campos.']);
        exit;
    }

    // Insertar en la base de datos
    $query = "INSERT INTO reservas (id_destino, id_usuario, fecha, personas) VALUES (?, ?, ?, ?)";
    
    $stmt = $conn->prepare($query);
    if ($stmt === false) {
        echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("iisi", $id_destino, $id_usuario, $fecha, $personas);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => '¡Reserva realizada con éxito! Nos pondremos en contacto contigo para confirmar los detalles.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al realizar la reserva: ' . $stmt->error]);
    }

    $stmt->close();
    $conn->close();

} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
?>