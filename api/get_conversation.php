<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../includes/db_connect.php';

$response = ['success' => false, 'messages' => []];

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    $response['message'] = 'Usuario no autenticado.';
    echo json_encode($response);
    exit();
}

$current_user_id = $_SESSION['user_id'];
$current_user_type = $_SESSION['user_type'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $contact_id = $_GET['contact_id'] ?? null;
    $contact_type = $_GET['contact_type'] ?? null;

    if (!$contact_id || !$contact_type) {
        $response['message'] = 'Faltan parámetros.';
        echo json_encode($response);
        exit();
    }

    // Get all messages between current user and contact
    $stmt = $conn->prepare("
        SELECT m.*, 
               CASE 
                   WHEN m.sender_type = 'turista' THEN (SELECT nombre FROM usuarios WHERE id = m.sender_id)
                   WHEN m.sender_type = 'agencia' THEN (SELECT nombre_agencia FROM agencias WHERE id = m.sender_id)
                   WHEN m.sender_type = 'guia' THEN (SELECT nombre_guia FROM guias_turisticos WHERE id = m.sender_id)
                   WHEN m.sender_type = 'local' THEN (SELECT nombre_local FROM lugares_locales WHERE id = m.sender_id)
               END as sender_name,
               CASE 
                   WHEN m.receiver_type = 'turista' THEN (SELECT nombre FROM usuarios WHERE id = m.receiver_id)
                   WHEN m.receiver_type = 'agencia' THEN (SELECT nombre_agencia FROM agencias WHERE id = m.receiver_id)
                   WHEN m.receiver_type = 'guia' THEN (SELECT nombre_guia FROM guias_turisticos WHERE id = m.receiver_id)
                   WHEN m.receiver_type = 'local' THEN (SELECT nombre_local FROM lugares_locales WHERE id = m.receiver_id)
               END as receiver_name
        FROM mensajes m
        WHERE ((m.sender_id = ? AND m.sender_type = ? AND m.receiver_id = ? AND m.receiver_type = ?)
           OR (m.receiver_id = ? AND m.receiver_type = ? AND m.sender_id = ? AND m.sender_type = ?))
        ORDER BY m.timestamp ASC
    ");
    
    $stmt->bind_param("isisiiss", 
        $current_user_id, $current_user_type, $contact_id, $contact_type,
        $current_user_id, $current_user_type, $contact_id, $contact_type
    );
    
    $stmt->execute();
    $result = $stmt->get_result();

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    $stmt->close();

    // Mark messages from this contact as read
    $stmt_mark_read = $conn->prepare("
        UPDATE mensajes 
        SET is_read = TRUE 
        WHERE sender_id = ? 
        AND sender_type = ? 
        AND receiver_id = ? 
        AND receiver_type = ? 
        AND is_read = FALSE
    ");
    $stmt_mark_read->bind_param("isis", $contact_id, $contact_type, $current_user_id, $current_user_type);
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
