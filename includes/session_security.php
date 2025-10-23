<?php
/**
 * Seguridad y validación de sesiones
 * Incluir este archivo en todas las páginas protegidas
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    // Configuración segura de sesiones
    ini_set('session.cookie_httponly', 1); // Prevenir acceso JavaScript a cookies de sesión
    ini_set('session.use_only_cookies', 1); // Solo usar cookies, no URL
    ini_set('session.cookie_secure', 0);    // Cambiar a 1 en producción con HTTPS
    ini_set('session.cookie_samesite', 'Strict'); // Protección CSRF
    
    session_start();
}

/**
 * Validar que el usuario esté autenticado
 * @param array $allowed_types Tipos de usuario permitidos (opcional)
 * @return void
 */
function require_login($allowed_types = []) {
    if (!isset($_SESSION['user_id'])) {
        // Usuario no autenticado
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header("Location: " . get_login_url());
        exit();
    }
    
    // Validar timeout de sesión (30 minutos de inactividad)
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
        session_unset();
        session_destroy();
        header("Location: " . get_login_url() . "?timeout=1");
        exit();
    }
    $_SESSION['LAST_ACTIVITY'] = time();
    
    // Regenerar ID de sesión cada 30 minutos
    if (!isset($_SESSION['CREATED'])) {
        $_SESSION['CREATED'] = time();
    } else if (time() - $_SESSION['CREATED'] > 1800) {
        session_regenerate_id(true);
        $_SESSION['CREATED'] = time();
    }
    
    // Validar tipo de usuario si se especificó
    if (!empty($allowed_types) && isset($_SESSION['user_type'])) {
        if (!in_array($_SESSION['user_type'], $allowed_types)) {
            header("Location: " . get_home_url() . "?error=unauthorized");
            exit();
        }
    }
    
    // Validar IP del usuario (opcional, puede causar problemas con proxies)
    if (!isset($_SESSION['USER_IP'])) {
        $_SESSION['USER_IP'] = get_user_ip();
    } else if ($_SESSION['USER_IP'] !== get_user_ip()) {
        // IP cambió, posible secuestro de sesión
        session_unset();
        session_destroy();
        header("Location: " . get_login_url() . "?error=security");
        exit();
    }
}

/**
 * Obtener IP del usuario
 * @return string
 */
function get_user_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

/**
 * Obtener URL de login según el contexto
 * @return string
 */
function get_login_url() {
    // Detectar si estamos en carpeta admin
    if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
        return '/GQ-Turismo/admin/login.php';
    }
    return '/GQ-Turismo/index.php';
}

/**
 * Obtener URL de inicio según el tipo de usuario
 * @return string
 */
function get_home_url() {
    if (isset($_SESSION['user_type'])) {
        if (in_array($_SESSION['user_type'], ['agencia', 'guia', 'local', 'super_admin'])) {
            return '/GQ-Turismo/admin/dashboard.php';
        }
    }
    return '/GQ-Turismo/index.php';
}

/**
 * Cerrar sesión de forma segura
 * @return void
 */
function logout_user() {
    $_SESSION = array();
    
    // Eliminar cookie de sesión
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Validar token CSRF
 * @param string $token Token recibido del formulario
 * @return bool
 */
function validate_csrf_token($token) {
    if (!isset($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Generar token CSRF
 * @return string
 */
function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Obtener campo hidden con token CSRF
 * @return string HTML
 */
function csrf_token_field() {
    $token = generate_csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
}

/**
 * Sanitizar entrada de usuario
 * @param string $data Datos a sanitizar
 * @return string
 */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Registrar intento de acceso fallido
 * @param string $email Email del intento
 * @param string $ip IP del intento
 * @return void
 */
function log_failed_login($email, $ip) {
    $log_file = __DIR__ . '/../logs/failed_logins.log';
    $log_dir = dirname($log_file);
    
    // Crear directorio de logs si no existe
    if (!is_dir($log_dir)) {
        mkdir($log_dir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] Failed login attempt for '$email' from IP: $ip\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}

/**
 * Verificar si IP está bloqueada por intentos fallidos
 * @param string $ip IP a verificar
 * @return bool
 */
function is_ip_blocked($ip) {
    $log_file = __DIR__ . '/../logs/failed_logins.log';
    
    if (!file_exists($log_file)) {
        return false;
    }
    
    // Contar intentos en los últimos 15 minutos
    $content = file_get_contents($log_file);
    $lines = explode("\n", $content);
    $recent_attempts = 0;
    $time_threshold = time() - 900; // 15 minutos
    
    foreach ($lines as $line) {
        if (strpos($line, $ip) !== false) {
            preg_match('/\[([\d\-: ]+)\]/', $line, $matches);
            if (!empty($matches[1])) {
                $log_time = strtotime($matches[1]);
                if ($log_time > $time_threshold) {
                    $recent_attempts++;
                }
            }
        }
    }
    
    // Bloquear si hay más de 5 intentos fallidos en 15 minutos
    return $recent_attempts >= 5;
}

/**
 * Validar fuerza de contraseña
 * @param string $password Contraseña a validar
 * @return array ['valid' => bool, 'errors' => array]
 */
function validate_password_strength($password) {
    $errors = [];
    
    if (strlen($password) < 8) {
        $errors[] = "La contraseña debe tener al menos 8 caracteres";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "La contraseña debe contener al menos una letra mayúscula";
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "La contraseña debe contener al menos una letra minúscula";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "La contraseña debe contener al menos un número";
    }
    
    if (!preg_match('/[^A-Za-z0-9]/', $password)) {
        $errors[] = "La contraseña debe contener al menos un carácter especial";
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}
?>
