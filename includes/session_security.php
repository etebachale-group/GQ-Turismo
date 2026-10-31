<?php
/**
 * Seguridad y Gestión de Sesiones GQ-Turismo
 */

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// Cargar autoloader de Composer si existe
$autoloader = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Exige inicio de sesión y valida roles permitidos
 */
function require_auth(array $allowed_roles = []): void {
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? 'index.php';
        header("Location: " . (strpos($_SERVER['REQUEST_URI'] ?? '', '/admin/') !== false ? 'login.php' : 'admin/login.php'));
        exit();
    }

    if (!empty($allowed_roles)) {
        $user_role = $_SESSION['user_type'] ?? '';
        if (!in_array($user_role, $allowed_roles, true)) {
            http_response_code(403);
            die("Acceso Denegado: Permisos insuficientes.");
        }
    }
}

/**
 * Valida token CSRF en peticiones POST
 */
function require_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            die("Error de seguridad: Token CSRF inválido o expirado.");
        }
    }
}
