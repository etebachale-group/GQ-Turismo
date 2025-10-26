<?php
session_start();
require_once 'includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Obtener datos JSON
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

// Actualizar estado de tarea
if ($action === 'update_task_status') {
    $task_id = $data['task_id'] ?? 0;
    $status = $data['status'] ?? '';
    $itinerario_id = $data['itinerario_id'] ?? 0;
    
    // Verificar permisos
    $is_authorized = false;
    
    if ($user_type === 'turista') {
        // El turista puede marcar tareas de sus propios itinerarios
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM itinerarios WHERE id = ? AND id_usuario = ?");
        $stmt->bind_param("ii", $itinerario_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $is_authorized = $result['count'] > 0;
        $stmt->close();
    } elseif ($user_type === 'guia') {
        // El guía puede marcar tareas de itinerarios asignados
        $stmt = $conn->prepare("
            SELECT COUNT(*) as count 
            FROM itinerario_guias ig
            INNER JOIN guias_turisticos gt ON ig.id_guia = gt.id
            WHERE ig.id_itinerario = ? AND gt.id_usuario = ?
        ");
        $stmt->bind_param("ii", $itinerario_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $is_authorized = $result['count'] > 0;
        $stmt->close();
    }
    
    if (!$is_authorized) {
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit();
    }
    
    // Actualizar estado
    $stmt = $conn->prepare("
        UPDATE itinerario_tareas 
        SET estado = ?, 
            completado_por = ?,
            fecha_completado = IF(? = 'completado', NOW(), NULL)
        WHERE id = ? AND id_itinerario = ?
    ");
    $stmt->bind_param("sisii", $status, $user_id, $status, $task_id, $itinerario_id);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Estado actualizado correctamente',
            'new_status' => $status
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $stmt->error]);
    }
    
    $stmt->close();
}

// Confirmar servicio (para proveedores)
elseif ($action === 'confirm_service') {
    $pedido_id = $data['pedido_id'] ?? 0;
    $status = $data['status'] ?? 'confirmado';
    
    // Verificar que el proveedor es el correcto
    $tipo_map = [
        'agencia' => 'agencia',
        'guia' => 'guia',
        'local' => 'local'
    ];
    
    $tipo_proveedor = $tipo_map[$user_type] ?? '';
    
    if (!$tipo_proveedor) {
        echo json_encode(['success' => false, 'message' => 'Tipo de usuario no válido']);
        exit();
    }
    
    // Obtener ID del proveedor según tipo
    $proveedor_id = null;
    
    if ($user_type === 'agencia') {
        $stmt = $conn->prepare("SELECT id FROM agencias WHERE id_usuario = ?");
    } elseif ($user_type === 'guia') {
        $stmt = $conn->prepare("SELECT id FROM guias_turisticos WHERE id_usuario = ?");
    } elseif ($user_type === 'local') {
        $stmt = $conn->prepare("SELECT id FROM locales_turisticos WHERE id_usuario = ?");
    }
    
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $proveedor_id = $result['id'] ?? null;
    $stmt->close();
    
    if (!$proveedor_id) {
        echo json_encode(['success' => false, 'message' => 'Proveedor no encontrado']);
        exit();
    }
    
    // Actualizar estado del pedido
    $stmt = $conn->prepare("
        UPDATE pedidos_servicios 
        SET estado = ?
        WHERE id = ? AND id_proveedor = ? AND tipo_proveedor = ?
    ");
    $stmt->bind_param("siis", $status, $pedido_id, $proveedor_id, $tipo_proveedor);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Servicio confirmado correctamente',
            'new_status' => $status
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al confirmar: ' . $stmt->error]);
    }
    
    $stmt->close();
}

// Obtener estadísticas del itinerario
elseif ($action === 'get_itinerary_stats') {
    $itinerario_id = $data['itinerario_id'] ?? 0;
    
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN estado = 'completado' THEN 1 ELSE 0 END) as completadas,
            SUM(CASE WHEN estado = 'en_progreso' THEN 1 ELSE 0 END) as en_progreso,
            SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes
        FROM itinerario_tareas
        WHERE id_itinerario = ?
    ");
    $stmt->bind_param("i", $itinerario_id);
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    $progreso = $stats['total'] > 0 ? round(($stats['completadas'] / $stats['total']) * 100) : 0;
    
    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'progreso' => $progreso
    ]);
}

else {
    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}

$conn->close();
?>
