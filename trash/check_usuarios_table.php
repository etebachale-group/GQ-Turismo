<?php
require_once 'includes/db_connect.php';

$result = $conn->query("DESCRIBE usuarios");
echo "Columnas en tabla usuarios:\n";
while($row = $result->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
?>
