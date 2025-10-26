<?php
session_start();
header('Content-Type: application/json');
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Obtener datos del POST
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

// Actualizar estado de tarea
if ($action === 'update_task_status') {
    $task_id = $data['task_id'] ?? 0;
    $new_status = $data['status'] ?? '';
    $id_itinerario = $data['itinerario_id'] ?? 0;
    
    if (!in_array($new_status, ['pendiente', 'en_progreso', 'completado', 'cancelado'])) {
        echo json_encode(['success' => false, 'message' => 'Estado inválido']);
        exit();
    }
    
    // Verificar permisos
    $check = null;
    if ($user_type === 'turista') {
        $check = $conn->prepare("SELECT t.* FROM itinerario_tareas t 
                                 JOIN itinerarios i ON t.id_itinerario = i.id 
                                 WHERE t.id = ? AND i.id_usuario = ?");
        $check->bind_param("ii", $task_id, $user_id);
    } else if (in_array($user_type, ['guia', 'agencia', 'local'])) {
        $check = $conn->prepare("SELECT t.* FROM itinerario_tareas t WHERE t.id = ? AND t.id_proveedor = ?");
        $check->bind_param("ii", $task_id, $user_id);
    }
    
    if ($check) {
        $check->execute();
        if ($check->get_result()->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'No tienes permiso']);
            exit();
        }
        $check->close();
    }
    
    // Actualizar estado
    $stmt = $conn->prepare("UPDATE itinerario_tareas SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $task_id);
    
    if ($stmt->execute()) {
        // Calcular progreso del itinerario
        $prog = $conn->prepare("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN estado = 'completado' THEN 1 ELSE 0 END) as completadas
            FROM itinerario_tareas 
            WHERE id_itinerario = ?
        ");
        $prog->bind_param("i", $id_itinerario);
        $prog->execute();
        $result = $prog->get_result()->fetch_assoc();
        $progreso = $result['total'] > 0 ? round(($result['completadas'] / $result['total']) * 100) : 0;
        
        // Actualizar estado del itinerario si todas las tareas están completadas
        if ($progreso == 100) {
            $conn->query("UPDATE itinerarios SET estado_itinerario = 'completado' WHERE id = $id_itinerario");
        } else if ($progreso > 0 && $progreso < 100) {
            $conn->query("UPDATE itinerarios SET estado_itinerario = 'en_curso' WHERE id = $id_itinerario");
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Estado actualizado',
            'progreso' => $progreso
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $stmt->error]);
    }
    $stmt->close();
}

// Agregar nota a tarea
else if ($action === 'add_note') {
    $task_id = $data['task_id'] ?? 0;
    $note = $data['note'] ?? '';
    
    $stmt = $conn->prepare("UPDATE itinerario_tareas SET notas = CONCAT(COALESCE(notas, ''), ?, '\n---\n') WHERE id = ?");
    $note_with_time = date('[Y-m-d H:i] ') . $note;
    $stmt->bind_param("si", $note_with_time, $task_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Nota agregada']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al agregar nota']);
    }
    $stmt->close();
}

// Iniciar itinerario
else if ($action === 'start_itinerary') {
    $itinerario_id = $data['itinerario_id'] ?? 0;
    
    // Verificar que es el turista dueño
    $check = $conn->prepare("SELECT id FROM itinerarios WHERE id = ? AND id_usuario = ?");
    $check->bind_param("ii", $itinerario_id, $user_id);
    $check->execute();
    
    if ($check->get_result()->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit();
    }
    $check->close();
    
    // Verificar que todos los servicios están confirmados
    $pending = $conn->query("
        SELECT COUNT(*) as count FROM pedidos_servicios 
        WHERE id_itinerario = $itinerario_id AND estado != 'confirmado'
    ")->fetch_assoc()['count'];
    
    if ($pending > 0) {
        echo json_encode([
            'success' => false, 
            'message' => 'Aún hay servicios pendientes de confirmación'
        ]);
        exit();
    }
    
    // Iniciar itinerario
    $stmt = $conn->prepare("UPDATE itinerarios SET estado_itinerario = 'iniciado' WHERE id = ?");
    $stmt->bind_param("i", $itinerario_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Itinerario iniciado']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al iniciar']);
    }
    $stmt->close();
}

// Confirmar servicio (para proveedores)
else if ($action === 'confirm_service') {
    $service_id = $data['service_id'] ?? 0;
    $status = $data['status'] ?? 'confirmado';
    
    if (!in_array($user_type, ['guia', 'agencia', 'local'])) {
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit();
    }
    
    // Obtener ID del proveedor según tipo
    $provider_table = [
        'guia' => 'guias_turisticos',
        'agencia' => 'agencias',
        'local' => 'lugares_locales'
    ];
    
    $table = $provider_table[$user_type] ?? null;
    if (!$table) {
        echo json_encode(['success' => false, 'message' => 'Tipo de usuario inválido']);
        exit();
    }
    
    $provider = $conn->query("SELECT id FROM $table WHERE id_usuario = $user_id")->fetch_assoc();
    $provider_id = $provider['id'] ?? 0;
    
    if (!$provider_id) {
        echo json_encode(['success' => false, 'message' => 'Proveedor no encontrado']);
        exit();
    }
    
    // Actualizar estado del servicio
    $stmt = $conn->prepare("
        UPDATE pedidos_servicios 
        SET estado = ? 
        WHERE id = ? AND id_proveedor = ? AND tipo_proveedor = ?
    ");
    $stmt->bind_param("siis", $status, $service_id, $provider_id, $user_type);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Servicio actualizado']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
    }
    $stmt->close();
}

else {
    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}

$conn->close();
?>
