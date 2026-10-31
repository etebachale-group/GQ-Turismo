<?php
declare(strict_types=1);

namespace App\Support;

class Auth
{
    /**
     * Valida que el usuario tenga uno de los roles permitidos.
     * Si la petición es API/JSON, responde con código 401 o 403 en JSON.
     * Si es petición web, redirige adecuadamente.
     */
    public static function requireRole(array|string $roles, ?string $redirect = null): void
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }

        $rolesArray = is_array($roles) ? $roles : [$roles];
        $isApi = self::isApiRequest();

        // Verificar autenticación
        if (empty($_SESSION['user_id'])) {
            if ($isApi) {
                http_response_code(401);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'error' => 'unauthenticated',
                    'message' => 'Debe iniciar sesión para realizar esta acción.'
                ]);
                exit();
            }

            $loginUrl = $redirect ?? self::getLoginUrl();
            $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/';
            header("Location: $loginUrl");
            exit();
        }

        $userType = $_SESSION['user_type'] ?? '';

        // Verificar rol
        if (!in_array($userType, $rolesArray, true)) {
            if ($isApi) {
                http_response_code(403);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'error' => 'unauthorized',
                    'message' => 'No tiene permisos para acceder a este recurso.'
                ]);
                exit();
            }

            header("Location: /GQ-Turismo/index.php?error=unauthorized");
            exit();
        }
    }

    public static function user(): ?array
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }

        if (empty($_SESSION['user_id'])) {
            return null;
        }

        return [
            'id' => $_SESSION['user_id'],
            'nombre' => $_SESSION['user_nombre'] ?? '',
            'tipo_usuario' => $_SESSION['user_type'] ?? '',
        ];
    }

    public static function id(): ?int
    {
        $u = self::user();
        return $u ? (int)$u['id'] : null;
    }

    public static function isApiRequest(): bool
    {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $xReq = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        $uri = $_SERVER['REQUEST_URI'] ?? '';

        return str_contains($accept, 'application/json')
            || strtolower($xReq) === 'xmlhttprequest'
            || str_contains($uri, '/api/');
    }

    private static function getLoginUrl(): string
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (str_contains($uri, '/admin/')) {
            return '/GQ-Turismo/admin/login.php';
        }
        return '/GQ-Turismo/index.php';
    }
}
