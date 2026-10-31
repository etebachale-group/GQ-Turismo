<?php
declare(strict_types=1);

namespace App\Support;

use Throwable;

class ErrorHandler
{
    private static bool $registered = false;

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }

        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        register_shutdown_function([self::class, 'handleShutdown']);

        self::$registered = true;
    }

    public static function handleError(int $level, string $message, string $file = '', int $line = 0): bool
    {
        if (!(error_reporting() & $level)) {
            return false;
        }

        Logger::warning("PHP Error ($level): $message", [
            'file' => $file,
            'line' => $line,
        ]);

        return true;
    }

    public static function handleException(Throwable $e): void
    {
        Logger::error("Excepción no capturada: " . $e->getMessage(), [
            'exception' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ]);

        $debug = (bool)Env::get('APP_DEBUG', false);

        if (Auth::isApiRequest()) {
            http_response_code(500);
            header('Content-Type: application/json; charset=utf-8');
            $response = [
                'success' => false,
                'message' => $debug ? $e->getMessage() : 'Error interno del servidor.',
            ];
            if ($debug) {
                $response['file'] = $e->getFile();
                $response['line'] = $e->getLine();
            }
            echo json_encode($response);
            exit();
        }

        if ($debug) {
            echo "<h1>Error 500 - Excepción</h1>";
            echo "<p><strong>" . htmlspecialchars($e->getMessage()) . "</strong></p>";
            echo "<p>en " . htmlspecialchars($e->getFile()) . ":" . $e->getLine() . "</p>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
            exit();
        }

        http_response_code(500);
        $errorPage = dirname(__DIR__, 2) . '/errors/500.php';
        if (file_exists($errorPage)) {
            include $errorPage;
        } else {
            echo "Error 500: Error interno del servidor.";
        }
        exit();
    }

    public static function handleShutdown(): void
    {
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
            Logger::error("PHP Fatal Error: {$error['message']}", [
                'file' => $error['file'],
                'line' => $error['line'],
            ]);
            if (!headers_sent()) {
                http_response_code(500);
            }
            if (Auth::isApiRequest()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'message' => 'Error crítico en el servidor.'
                ]);
            } else {
                $errorPage = dirname(__DIR__, 2) . '/errors/500.php';
                if (file_exists($errorPage)) {
                    include $errorPage;
                }
            }
        }
    }
}
