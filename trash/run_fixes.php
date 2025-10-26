<?php
require_once 'includes/db_connect.php';

$sql_file = 'database/fix_all_system_issues.sql';
$sql = file_get_contents($sql_file);

// Ejecutar queries una por una
$queries = explode(';', $sql);
$success = 0;
$errors = 0;

foreach($queries as $query) {
    $query = trim($query);
    // Saltar comentarios y líneas vacías
    if(empty($query) || strpos($query, '--') === 0 || strpos($query, '/*') === 0) {
        continue;
    }
    
    if($conn->query($query)) {
        $success++;
        echo "✓ OK: " . substr($query, 0, 60) . "...\n";
    } else {
        $errors++;
        echo "✗ ERROR: " . $conn->error . "\n";
        echo "  Query: " . substr($query, 0, 100) . "...\n";
    }
}

echo "\n=== RESUMEN ===\n";
echo "Éxitos: $success\n";
echo "Errores: $errors\n";
?>
