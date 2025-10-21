<?php
require_once 'includes/header.php';

// 1. Validar el ID que llega por GET
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    echo "<div class='container py-5'><div class='alert alert-danger text-center'>ID de destino no válido o no proporcionado.</div></div>";
    require_once 'includes/footer.php';
    exit();
}

$id_destino = (int)$_GET['id'];

// 2. Preparar y ejecutar la consulta de forma segura
$destino = null;
if ($conn) {
    $sql = "SELECT nombre, descripcion, imagen, categoria, precio FROM destinos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if($stmt) {
        $stmt->bind_param("i", $id_destino);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $destino = $result->fetch_assoc();
        }
        $stmt->close();
    }
    $conn->close();
}

?>

<div class="container py-5">
    <?php if ($destino): ?>
        <div class="row g-5">
            <div class="col-lg-7">
                <img src="assets/img/destinos/<?php echo htmlspecialchars($destino['imagen']); ?>" class="img-fluid rounded shadow-lg w-100" style="max-height: 500px; object-fit: cover;" alt="<?php echo htmlspecialchars($destino['nombre']); ?>">
            </div>
            <div class="col-lg-5 d-flex flex-column">
                <div>
                    <span class="badge bg-primary mb-2 fs-6"><?php echo htmlspecialchars($destino['categoria']); ?></span>
                    <h1 class="display-5 fw-bold"><?php echo htmlspecialchars($destino['nombre']); ?></h1>
                    <hr>
                    <p class="lead text-muted"><?php echo nl2br(htmlspecialchars($destino['descripcion'])); ?></p>
                </div>
                
                <div class="mt-auto">
                    <div class="bg-light p-4 rounded mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h3 fw-bold text-primary"><?php echo number_format($destino['precio'], 2); ?> €</span>
                            <span class="text-muted">/ por persona</span>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-primary btn-lg"><i class="bi bi-calendar-check me-2"></i>Reservar Ahora</button>
                        <button class="btn btn-outline-secondary"><i class="bi bi-plus-lg me-2"></i>Añadir al Itinerario</button>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <h2 class="fw-bold">Destino no encontrado</h2>
            <p class="text-muted">El destino que buscas no existe o no está disponible en este momento.</p>
            <a href="destinos.php" class="btn btn-primary mt-3">Volver a la lista de Destinos</a>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'includes/footer.php';
?>
