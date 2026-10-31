<?php
/**
 * Script de migración y preparación para base de datos de pruebas (F-02)
 * Uso: php bin/migrate_test.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\Support\Env;

// Forzar entorno testing
putenv('APP_ENV=testing');
$_ENV['APP_ENV'] = 'testing';
Env::reset();
Env::load(__DIR__ . '/../.env.testing');

$testDb = Env::get('DB_NAME');
if (!is_string($testDb) || !str_ends_with($testDb, '_test')) {
    fwrite(STDERR, "ERROR FATAL: La base de datos de pruebas DEBE terminar en '_test'. Actual: $testDb\n");
    exit(1);
}

$host = Env::get('DB_HOST', 'localhost');
$user = Env::get('DB_USER', 'root');
$pass = Env::get('DB_PASS', '');
$port = (int)Env::get('DB_PORT', 3306);
$sourceDb = 'gq_turismo';

echo "Conectando a MySQL en $host:$port...\n";
$mysqli = new mysqli($host, $user, $pass, '', $port);
if ($mysqli->connect_error) {
    fwrite(STDERR, "Error conectando a MySQL: " . $mysqli->connect_error . "\n");
    exit(1);
}

// 1. Recrear base de datos de test
echo "Recreando base de datos '$testDb'...\n";
$mysqli->query("DROP DATABASE IF EXISTS `$testDb`");
$mysqli->query("CREATE DATABASE `$testDb` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$mysqli->select_db($testDb);

// 2. Obtener lista de tablas de la base de datos fuente
echo "Copiando esquema desde '$sourceDb' hacia '$testDb'...\n";
$srcResult = $mysqli->query("SHOW TABLES FROM `$sourceDb`");
if (!$srcResult) {
    fwrite(STDERR, "Error al listar tablas de '$sourceDb': " . $mysqli->error . "\n");
    exit(1);
}

$mysqli->query("SET FOREIGN_KEY_CHECKS = 0");

$tablesCount = 0;
while ($row = $srcResult->fetch_row()) {
    $tableName = $row[0];
    $createRes = $mysqli->query("SHOW CREATE TABLE `$sourceDb`.`$tableName`");
    if ($createRes) {
        $createRow = $createRes->fetch_row();
        $createSql = $createRow[1];
        if ($mysqli->query($createSql)) {
            $tablesCount++;
        } else {
            echo "Aviso: no se pudo crear tabla $tableName: " . $mysqli->error . "\n";
        }
    }
}

$mysqli->query("SET FOREIGN_KEY_CHECKS = 1");

echo "Migración completada con éxito. $tablesCount tablas creadas en '$testDb'.\n";
