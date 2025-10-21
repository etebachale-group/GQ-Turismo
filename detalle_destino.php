    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />

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
}

// Fetch gallery images for the destination
$gallery_images = [];
if ($destino) {
    $stmt_gallery = $conn->prepare("SELECT ruta_imagen, descripcion FROM imagenes_destino WHERE id_destino = ? ORDER BY fecha_subida DESC");
    $stmt_gallery->bind_param("i", $id_destino);
    $stmt_gallery->execute();
    $result_gallery = $stmt_gallery->get_result();
    while ($row = $result_gallery->fetch_assoc()) {
        $gallery_images[] = $row;
    }
    $stmt_gallery->close();
}

// Fetch destination's location
$destino_location = null;
if ($destino) {
    $stmt_location = $conn->prepare("SELECT latitude, longitude FROM destinos WHERE id = ?");
    $stmt_location->bind_param("i", $id_destino);
    $stmt_location->execute();
    $result_location = $stmt_location->get_result();
    if ($result_location->num_rows > 0) {
        $destino_location = $result_location->fetch_assoc();
    }
    $stmt_location->close();
}

$conn->close(); // Close connection at the very end of the script
?>

<div class="container py-5">
    <?php if ($destino): ?>
        <div class="row g-5">
            <div class="col-lg-7">
                <?php if (count($gallery_images) > 0): ?>
                    <div id="destinationGallery" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <?php foreach ($gallery_images as $index => $image): ?>
                                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                    <img src="assets/img/destinos/<?= htmlspecialchars($image['ruta_imagen']) ?>" class="d-block w-100 img-fluid rounded shadow-lg" style="max-height: 500px; object-fit: cover;" alt="<?= htmlspecialchars($image['descripcion']) ?>">
                                    <?php if (!empty($image['descripcion'])): ?>
                                        <div class="carousel-caption d-none d-md-block">
                                            <p><?= htmlspecialchars($image['descripcion']) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#destinationGallery" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#destinationGallery" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                <?php else: ?>
                    <img src="assets/img/destinos/<?php echo htmlspecialchars($destino['imagen']); ?>" class="img-fluid rounded shadow-lg w-100" style="max-height: 500px; object-fit: cover;" alt="<?php echo htmlspecialchars($destino['nombre']); ?>">
                <?php endif; ?>
            </div>
            <div class="col-lg-5 d-flex flex-column">
                <div>
                    <span class="badge bg-primary mb-2 fs-6"><?php echo htmlspecialchars($destino['categoria']); ?></span>
                    <h1 class="display-5 fw-bold"><?php echo htmlspecialchars($destino['nombre']); ?></h1>
                    <hr>
                    <p class="lead text-muted"><?php echo nl2br(htmlspecialchars($destino['descripcion'])); ?></p>
                </div>
                
                <div class="mt-auto">
                    <?php if ($destino_location && $destino_location['latitude'] && $destino_location['longitude']): ?>
                        <h3 class="mt-5 mb-3">Ubicación del Destino</h3>
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <div id="map" style="height: 300px; width: 100%;"></div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info text-center">Ubicación del destino no disponible.</div>
                    <?php endif; ?>

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

<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Lógica para el mapa de ubicación del destino
    const mapElement = document.getElementById('map');
    <?php if ($destino_location && $destino_location['latitude'] && $destino_location['longitude']): ?>
        if (mapElement) {
            const latitude = <?= $destino_location['latitude'] ?>;
            const longitude = <?= $destino_location['longitude'] ?>;
            const map = L.map('map').setView([latitude, longitude], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            L.marker([latitude, longitude]).addTo(map)
                .bindPopup('<b>Ubicación del Destino</b>')
                .openPopup();
        }
    <?php endif; ?>
});
</script>

<?php
require_once 'includes/footer.php';
?>
