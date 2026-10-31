<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/session_security.php';
require_once __DIR__ . '/../includes/db_connect.php';

use App\Domain\Messaging\ChatService;

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$chatService = new ChatService($conn);
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_or_create':
            $otroUsuarioId = (int)($_POST['otro_usuario_id'] ?? $_GET['otro_usuario_id'] ?? 0);
            $pedidoId = isset($_POST['pedido_id']) ? (int)$_POST['pedido_id'] : (isset($_GET['pedido_id']) ? (int)$_GET['pedido_id'] : null);
            
            if ($otroUsuarioId <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'ID de usuario destinatario no válido.']);
                exit;
            }

            $convId = $chatService->obtenerOCrearConversacion($userId, $otroUsuarioId, $pedidoId);
            echo json_encode(['success' => true, 'conversacion_id' => $convId]);
            break;

        case 'send':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Método no permitido']);
                exit;
            }

            $convId = (int)($_POST['conversacion_id'] ?? 0);
            $mensaje = trim((string)($_POST['mensaje'] ?? ''));

            if ($convId <= 0 || $mensaje === '') {
                http_response_code(400);
                echo json_encode(['error' => 'Parámetros inválidos para enviar mensaje.']);
                exit;
            }

            $resultado = $chatService->enviarMensaje($convId, $userId, $mensaje);
            echo json_encode(['success' => true, 'mensaje' => $resultado]);
            break;

        case 'messages':
            $convId = (int)($_GET['conversacion_id'] ?? 0);
            if ($convId <= 0) {
                http_response_code(400);
                echo json_encode(['error' => 'conversacion_id es requerido']);
                exit;
            }

            $mensajes = $chatService->obtenerMensajes($convId, $userId);
            echo json_encode(['success' => true, 'mensajes' => $mensajes]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no reconocida.']);
            break;
    }
} catch (\Throwable $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
