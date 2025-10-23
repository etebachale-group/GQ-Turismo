<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);
$tipo_servicio = $data['tipo_servicio'] ?? null; // 'guia', 'agencia', 'local'
$id_relacion = $data['id_relacion'] ?? null;
$nuevo_estado = $data['nuevo_estado'] ?? null;
$notas = $data['notas'] ?? '';

if (!$tipo_servicio || !$id_relacion || !$nuevo_estado) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Validar que el tipo de servicio coincide con el tipo de usuario
$valid_types = [
    'guia' => 'guia',
    'agencia' => 'agencia',
    'local' => 'local'
];

if (!isset($valid_types[$tipo_servicio]) || $valid_types[$tipo_servicio] !== $user_type) {
    echo json_encode(['success' => false, 'message' => 'Tipo de servicio no válido para tu usuario']);
    exit();
}

// Determinar la tabla correcta
$tablas = [
    'guia' => 'itinerario_guias',
    'agencia' => 'itinerario_agencias',
    'local' => 'itinerario_locales'
];

$tabla_proveedor = [
    'guia' => 'guias_turisticos',
    'agencia' => 'agencias',
    'local' => 'lugares_locales'
];

$tabla = $tablas[$tipo_servicio];
$tabla_prov = $tabla_proveedor[$tipo_servicio];
$id_campo = 'id_' . $tipo_servicio;

// Verificar que el servicio pertenece al proveedor actual
$stmt = $conn->prepare("
    SELECT ir.* FROM $tabla ir
    JOIN $tabla_prov p ON ir.$id_campo = p.id
    WHERE ir.id = ? AND p.id_usuario = ?
");
$stmt->bind_param("ii", $id_relacion, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Servicio no encontrado o no tienes permisos']);
    exit();
}

$stmt->close();

// Actualizar el estado
$fecha_campo = '';
if ($nuevo_estado === 'confirmado') {
    $fecha_campo = ', fecha_confirmacion = NOW()';
} elseif ($nuevo_estado === 'completado') {
    $fecha_campo = ', fecha_completado = NOW()';
}

$sql = "UPDATE $tabla SET estado = ?, notas = ? $fecha_campo WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $nuevo_estado, $notas, $id_relacion);

if ($stmt->execute()) {
    // Notificar al turista (opcional: agregar sistema de notificaciones)
    echo json_encode([
        'success' => true, 
        'message' => 'Estado del servicio actualizado correctamente',
        'nuevo_estado' => $nuevo_estado
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado']);
}

$stmt->close();
$conn->close();
?>
