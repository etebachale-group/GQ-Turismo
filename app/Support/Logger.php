<?php
declare(strict_types=1);

namespace App\Support;

class Logger
{
    private static ?string $logFile = null;

    public static function getLogFile(): string
    {
        if (self::$logFile === null) {
            $dir = dirname(__DIR__, 2) . '/logs';
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            self::$logFile = $dir . '/app.log';
        }
        return self::$logFile;
    }

    public static function setLogFile(string $path): void
    {
        self::$logFile = $path;
    }

    public static function log(string $level, string $message, array $context = []): void
    {
        $logFile = self::getLogFile();
        $timestamp = date('Y-m-d H:i:s');
        $levelUpper = strtoupper($level);

        $user = Auth::user();
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
        $uri = $_SERVER['REQUEST_URI'] ?? 'CLI';

        $baseContext = [
            'ip' => $ip,
            'uri' => $uri,
            'user_id' => $user['id'] ?? null,
            'user_type' => $user['tipo_usuario'] ?? null,
        ];

        $mergedContext = array_merge($baseContext, $context);
        $contextJson = !empty($mergedContext) ? json_encode($mergedContext, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '{}';

        $entry = sprintf("[%s] [%s] %s %s\n", $timestamp, $levelUpper, $message, $contextJson);
        @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
    }

    public static function info(string $message, array $context = []): void
    {
        self::log('INFO', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::log('WARNING', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::log('ERROR', $message, $context);
    }

    public static function debug(string $message, array $context = []): void
    {
        if (Env::get('APP_DEBUG', false)) {
            self::log('DEBUG', $message, $context);
        }
    }
}
