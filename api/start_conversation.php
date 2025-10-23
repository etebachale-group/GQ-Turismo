<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../includes/db_connect.php';

$response = ['success' => false, 'message' => ''];

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    $response['message'] = 'Usuario no autenticado.';
    echo json_encode($response);
    exit();
}

// Solo turistas pueden iniciar conversaciones
if ($_SESSION['user_type'] !== 'turista') {
    $response['message'] = 'Solo los turistas pueden iniciar conversaciones.';
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
        $response['message'] = 'Datos incompletos. Se requiere receiver_id, receiver_type y message.';
        echo json_encode($response);
        exit();
    }

    // Validar receiver_type
    $valid_types = ['agencia', 'guia', 'local'];
    if (!in_array($receiver_type, $valid_types)) {
        $response['message'] = 'Tipo de receptor no válido.';
        echo json_encode($response);
        exit();
    }

    // Validar que el receptor existe
    $valid_receiver = false;
    $receiver_name = '';
    
    switch ($receiver_type) {
        case 'agencia':
            $stmt_check = $conn->prepare("SELECT id, nombre_agencia as nombre FROM agencias WHERE id = ?");
            break;
        case 'guia':
            $stmt_check = $conn->prepare("SELECT id, nombre_guia as nombre FROM guias_turisticos WHERE id = ?");
            break;
        case 'local':
            $stmt_check = $conn->prepare("SELECT id, nombre_local as nombre FROM lugares_locales WHERE id = ?");
            break;
    }

    if ($stmt_check) {
        $stmt_check->bind_param("i", $receiver_id);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            $receiver_data = $result_check->fetch_assoc();
            $valid_receiver = true;
            $receiver_name = $receiver_data['nombre'];
        }
        $stmt_check->close();
    }

    if (!$valid_receiver) {
        $response['message'] = 'El destinatario no existe.';
        echo json_encode($response);
        exit();
    }

    // Insertar el mensaje
    $stmt = $conn->prepare("INSERT INTO mensajes (sender_id, sender_type, receiver_id, receiver_type, message) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isiss", $current_user_id, $current_user_type, $receiver_id, $receiver_type, $message_content);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Mensaje enviado con éxito a ' . htmlspecialchars($receiver_name) . '. Puedes ver la conversación en "Mis Mensajes".';
        $response['redirect'] = 'mis_mensajes.php';
    } else {
        $response['message'] = 'Error al enviar el mensaje: ' . $stmt->error;
    }
    $stmt->close();

} else {
    $response['message'] = 'Método no permitido.';
}

$conn->close();
echo json_encode($response);
?>
