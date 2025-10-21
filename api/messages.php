<?php
header('Content-Type: application/json');
session_start();
include '../includes/db_connect.php';

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    $response['message'] = 'Usuario no autenticado.';
    echo json_encode($response);
    exit();
}

$current_user_id = $_SESSION['user_id'];
$current_user_type = $_SESSION['user_type'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $receiver_id = $input['receiver_id'] ?? null;
    $receiver_type = $input['receiver_type'] ?? null;
    $message_content = $input['message'] ?? null;

    if (!$receiver_id || !$receiver_type || !$message_content) {
        $response['message'] = 'Datos incompletos para enviar el mensaje.';
        echo json_encode($response);
        exit();
    }

    // Validar que el receiver_id existe y tiene el receiver_type correcto
    $valid_receiver = false;
    $receiver_table = '';
    if ($receiver_type === 'turista') {
        $receiver_table = 'usuarios'; // Turistas son usuarios directamente
    } else if ($receiver_type === 'agencia') {
        $receiver_table = 'agencias';
    } else if ($receiver_type === 'guia') {
        $receiver_table = 'guias_turisticos';
    } else if ($receiver_type === 'local') {
        $receiver_table = 'lugares_locales';
    }

    if ($receiver_table) {
        $stmt_check_receiver = $conn->prepare("SELECT id FROM " . $receiver_table . " WHERE id = ?");
        $stmt_check_receiver->bind_param("i", $receiver_id);
        $stmt_check_receiver->execute();
        $stmt_check_receiver->store_result();
        if ($stmt_check_receiver->num_rows > 0) {
            $valid_receiver = true;
        }
        $stmt_check_receiver->close();
    }

    if (!$valid_receiver) {
        $response['message'] = 'Destinatario no válido o no encontrado.';
        echo json_encode($response);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO mensajes (sender_id, sender_type, receiver_id, receiver_type, message) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isiss", $current_user_id, $current_user_type, $receiver_id, $receiver_type, $message_content);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Mensaje enviado con éxito.';
    } else {
        $response['message'] = 'Error al enviar el mensaje: ' . $stmt->error;
    }
    $stmt->close();

} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Obtener mensajes enviados y recibidos por el usuario actual
    $messages = [];

    $stmt = $conn->prepare("SELECT m.*, s.nombre as sender_name, r.nombre as receiver_name FROM mensajes m LEFT JOIN usuarios s ON m.sender_id = s.id LEFT JOIN usuarios r ON m.receiver_id = r.id WHERE (m.sender_id = ? AND m.sender_type = ?) OR (m.receiver_id = ? AND m.receiver_type = ?) ORDER BY m.timestamp DESC");
    $stmt->bind_param("isis", $current_user_id, $current_user_type, $current_user_id, $current_user_type);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    $stmt->close();

    // Marcar mensajes recibidos como leídos
    $stmt_mark_read = $conn->prepare("UPDATE mensajes SET is_read = TRUE WHERE receiver_id = ? AND receiver_type = ? AND is_read = FALSE");
    $stmt_mark_read->bind_param("is", $current_user_id, $current_user_type);
    $stmt_mark_read->execute();
    $stmt_mark_read->close();

    $response['success'] = true;
    $response['messages'] = $messages;

} else {
    $response['message'] = 'Método no permitido.';
}

$conn->close();
echo json_encode($response);
?>