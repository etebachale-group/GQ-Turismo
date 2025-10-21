<?php
session_start();
require_once '../includes/db_connect.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Acción no válida.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if (!$conn) {
        $response['message'] = 'Error de conexión a la base de datos.';
        echo json_encode($response);
        exit();
    }

    switch ($action) {
        case 'register':
            $nombre = trim($_POST['nombre'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $contrasena = $_POST['contrasena'] ?? '';
            $tipo_usuario = $_POST['tipo_usuario'] ?? 'turista'; // Default to 'turista'

            if (empty($nombre) || empty($email) || empty($contrasena)) {
                $response['message'] = 'Todos los campos son obligatorios.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $response['message'] = 'El formato del email no es válido.';
            } elseif (!in_array($tipo_usuario, ['turista', 'agencia', 'guia', 'local', 'super_admin'])) {
                $response['message'] = 'Tipo de usuario no válido.';
            } else {
                // Verificar si el email ya existe
                $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->store_result();

                if ($stmt->num_rows > 0) {
                    $response['message'] = 'Este email ya está registrado.';
                } else {
                    // Hashear contraseña y registrar usuario
                    $hash = password_hash($contrasena, PASSWORD_DEFAULT);
                    $insert_stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, contrasena, tipo_usuario) VALUES (?, ?, ?, ?)");
                    $insert_stmt->bind_param("ssss", $nombre, $email, $hash, $tipo_usuario);

                    if ($insert_stmt->execute()) {
                        $response['success'] = true;
                        $response['message'] = 'Registro completado con éxito. Ahora puedes iniciar sesión.';
                        if ($tipo_usuario === 'guia') {
                            $response['redirect'] = 'admin/manage_guias.php'; // Redirigir al guía a su panel
                        }
                    } else {
                        $response['message'] = 'Error durante el registro. Inténtalo de nuevo.';
                    }
                    $insert_stmt->close();
                }
                $stmt->close();
            }
            break;

        case 'login':
            $email = trim($_POST['email'] ?? '');
            $contrasena = $_POST['contrasena'] ?? '';

            if (empty($email) || empty($contrasena)) {
                $response['message'] = 'Email y contraseña son obligatorios.';
            } else {
                $stmt = $conn->prepare("SELECT id, nombre, contrasena, tipo_usuario FROM usuarios WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($user = $result->fetch_assoc()) {
                    if (password_verify($contrasena, $user['contrasena'])) {
                        // Iniciar sesión
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_nombre'] = $user['nombre'];
                        $_SESSION['user_type'] = $user['tipo_usuario']; // Store user type
                        $response['success'] = true;
                        $response['message'] = 'Inicio de sesión correcto. Redirigiendo...';
                    } else {
                        $response['message'] = 'La contraseña es incorrecta.';
                    }
                } else {
                    $response['message'] = 'No se encontró ningún usuario con ese email.';
                }
                $stmt->close();
            }
            break;
    }

    $conn->close();
}

echo json_encode($response);
?>
