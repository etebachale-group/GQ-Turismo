<?php
declare(strict_types=1);

namespace App\Testing;

use App\Support\Env;
use mysqli;
use RuntimeException;

class TestCase
{
    protected static ?mysqli $conn = null;

    public static function assertValidTestDatabase(?string $dbName): void
    {
        if (!is_string($dbName) || !str_ends_with($dbName, '_test')) {
            throw new RuntimeException("ERROR DE SEGURIDAD: Los tests solo pueden ejecutarse contra una BD que termine en '_test'. Actual: " . var_export($dbName, true));
        }
    }

    public static function setUpTestEnvironment(): void
    {
        putenv('APP_ENV=testing');
        $_ENV['APP_ENV'] = 'testing';
        Env::reset();
        Env::load();

        $dbName = Env::get('DB_NAME');
        self::assertValidTestDatabase($dbName);
    }

    public static function getConnection(): mysqli
    {
        self::setUpTestEnvironment();
        if (self::$conn === null || !@self::$conn->ping()) {
            $host = Env::get('DB_HOST', 'localhost');
            $user = Env::get('DB_USER', 'root');
            $pass = Env::get('DB_PASS', '');
            $dbname = Env::get('DB_NAME');
            $port = (int)Env::get('DB_PORT', 3306);
            self::$conn = new mysqli($host, $user, $pass, $dbname, $port);
            self::$conn->set_charset('utf8mb4');
        }
        return self::$conn;
    }
}
