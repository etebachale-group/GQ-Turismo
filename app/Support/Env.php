<?php
declare(strict_types=1);

namespace App\Support;

class Env
{
    private static bool $loaded = false;
    private static array $variables = [];

    public static function load(?string $filePath = null): void
    {
        if ($filePath === null) {
            $baseDir = dirname(__DIR__, 2);
            $appEnv = getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? 'local');
            if ($appEnv === 'testing' && file_exists($baseDir . '/.env.testing')) {
                $filePath = $baseDir . '/.env.testing';
            } else {
                $filePath = $baseDir . '/.env';
            }
        }

        if (!file_exists($filePath)) {
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (str_contains($line, '=')) {
                [$key, $val] = explode('=', $line, 2);
                $key = trim($key);
                $val = trim($val);
                if ((str_starts_with($val, '"') && str_ends_with($val, '"')) ||
                    (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
                    $val = substr($val, 1, -1);
                }
                self::$variables[$key] = $val;
                if (!isset($_ENV[$key])) {
                    $_ENV[$key] = $val;
                }
                if (getenv($key) === false) {
                    putenv("$key=$val");
                }
            }
        }

        self::$loaded = true;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        if (!self::$loaded) {
            self::load();
        }

        if (array_key_exists($key, self::$variables)) {
            $val = self::$variables[$key];
        } elseif (isset($_ENV[$key])) {
            $val = $_ENV[$key];
        } elseif (getenv($key) !== false) {
            $val = getenv($key);
        } else {
            return $default;
        }

        $lower = strtolower((string)$val);
        if ($lower === 'true' || $lower === '(true)') {
            return true;
        }
        if ($lower === 'false' || $lower === '(false)') {
            return false;
        }
        if ($lower === 'null' || $lower === '(null)') {
            return null;
        }
        if ($lower === 'empty' || $lower === '(empty)') {
            return '';
        }

        return $val;
    }

    public static function isTesting(): bool
    {
        return self::get('APP_ENV') === 'testing';
    }

    public static function reset(): void
    {
        self::$loaded = false;
        self::$variables = [];
    }
}
