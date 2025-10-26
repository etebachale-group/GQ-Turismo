<?php
/**
 * Script de actualización completa del sistema
 * Corrige todos los errores y actualiza funcionalidades
 */

echo "=== ACTUALIZACIÓN COMPLETA DEL SISTEMA GQ TURISMO ===\n\n";

require_once 'includes/db_connect.php';

// 1. Verificar y crear columnas faltantes
echo "1. Verificando estructura de base de datos...\n";

$updates = [
    // Itinerarios
    "ALTER TABLE `itinerarios` ADD COLUMN IF NOT EXISTS `fecha_inicio` DATE NULL",
    "ALTER TABLE `itinerarios` ADD COLUMN IF NOT EXISTS `fecha_fin` DATE NULL",
    "ALTER TABLE `itinerarios` ADD COLUMN IF NOT EXISTS `descripcion` TEXT NULL",
    
    // Itinerario destinos
    "ALTER TABLE `itinerario_destinos` ADD COLUMN IF NOT EXISTS `precio` DECIMAL(10,2) DEFAULT 0.00",
    "ALTER TABLE `itinerario_destinos` ADD COLUMN IF NOT EXISTS `fecha_inicio` DATE NULL",
    "ALTER TABLE `itinerario_destinos` ADD COLUMN IF NOT EXISTS `fecha_fin` DATE NULL",
    "ALTER TABLE `itinerario_destinos` ADD COLUMN IF NOT EXISTS `descripcion` TEXT NULL",
    
    // Mensajes
    "ALTER TABLE `mensajes` ADD COLUMN IF NOT EXISTS `id_emisor` INT(11) NULL",
    "ALTER TABLE `mensajes` ADD COLUMN IF NOT EXISTS `id_receptor` INT(11) NULL",
    "ALTER TABLE `mensajes` ADD COLUMN IF NOT EXISTS `leido` TINYINT(1) DEFAULT 0",
    "ALTER TABLE `mensajes` ADD COLUMN IF NOT EXISTS `fecha_lectura` DATETIME NULL"
];

foreach ($updates as $sql) {
    if ($conn->query($sql)) {
        echo "✓ ";
    } else {
        if (strpos($conn->error, 'Duplicate column') === false && strpos($conn->error, 'check that it exists') === false) {
            echo "! Error: " . $conn->error . "\n";
        }
    }
}

echo "\n2. Verificando estructura de mensajes...\n";
$result = $conn->query("SHOW COLUMNS FROM mensajes LIKE 'id_usuario'");
if ($result->num_rows > 0) {
    $conn->query("UPDATE `mensajes` SET `id_emisor` = `id_usuario` WHERE `id_emisor` IS NULL AND `id_usuario` IS NOT NULL");
    echo "✓ Datos migrados\n";
} else {
    echo "✓ No requiere migración\n";
}

echo "\n3. Creando índices para rendimiento...\n";
$indices = [
    "ALTER TABLE `mensajes` ADD INDEX IF NOT EXISTS `idx_emisor` (`id_emisor`)",
    "ALTER TABLE `mensajes` ADD INDEX IF NOT EXISTS `idx_receptor` (`id_receptor`)",
    "ALTER TABLE `mensajes` ADD INDEX IF NOT EXISTS `idx_leido` (`leido`)"
];

foreach ($indices as $sql) {
    if ($conn->query($sql)) {
        echo "✓ Índice creado\n";
    }
}

echo "\n=== ACTUALIZACIÓN COMPLETADA ===\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n";
echo "\nPróximos pasos:\n";
echo "1. Verificar que no haya errores en PHP\n";
echo "2. Probar el sistema de mensajes\n";
echo "3. Probar el tracking de itinerarios\n";
echo "4. Revisar diseño responsive en móviles\n";
?>
