<?php
require_once 'includes/db_connect.php';

// Validar ID
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    header("Location: destinos.php");
    exit();
}

$id_destino = (int)$_GET['id'];

// Obtener información del destino
$destino = null;
$sql = "SELECT * FROM destinos WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_destino);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $destino = $result->fetch_assoc();
} else {
    $stmt->close();
    $conn->close();
    header("Location: destinos.php");
    exit();
}
$stmt->close();

// Obtener galería de imágenes
$gallery_images = [];
$stmt_gallery = $conn->prepare("SELECT ruta_imagen, descripcion FROM imagenes_destino WHERE id_destino = ? ORDER BY fecha_subida DESC");
$stmt_gallery->bind_param("i", $id_destino);
$stmt_gallery->execute();
$result_gallery = $stmt_gallery->get_result();
while ($row = $result_gallery->fetch_assoc()) {
    $gallery_images[] = $row;
}
$stmt_gallery->close();

// Obtener guías recomendados
$guias_recomendados = [];
if (!empty($destino['ciudad'])) {
    $stmt_guias = $conn->prepare("
        SELECT id, nombre_guia, especialidades, precio_hora, descripcion, imagen_perfil
        FROM guias_turisticos 
        WHERE ciudad_operacion = ? OR especialidades LIKE ?
        LIMIT 3
    ");
    $search_especialidad = '%' . $destino['categoria'] . '%';
    $stmt_guias->bind_param("ss", $destino['ciudad'], $search_especialidad);
    $stmt_guias->execute();
    $result_guias = $stmt_guias->get_result();
    while ($row = $result_guias->fetch_assoc()) {
        $guias_recomendados[] = $row;
    }
    $stmt_guias->close();
}

// Obtener locales cercanos
$locales_cercanos = [];
if (!empty($destino['ciudad'])) {
    $stmt_locales = $conn->prepare("
        SELECT id, nombre_local, tipo_local, direccion, descripcion, imagen_perfil
        FROM lugares_locales 
        WHERE direccion LIKE ?
        LIMIT 3
    ");
    $search_ciudad = '%' . $destino['ciudad'] . '%';
    $stmt_locales->bind_param("s", $search_ciudad);
    $stmt_locales->execute();
    $result_locales = $stmt_locales->get_result();
    while ($row = $result_locales->fetch_assoc()) {
        $locales_cercanos[] = $row;
    }
    $stmt_locales->close();
}

// Obtener destinos similares
$destinos_similares = [];
$stmt_similares = $conn->prepare("
    SELECT id, nombre, imagen, categoria, precio 
    FROM destinos 
    WHERE categoria = ? AND id != ?
    ORDER BY RAND()
    LIMIT 3
");
$stmt_similares->bind_param("si", $destino['categoria'], $id_destino);
$stmt_similares->execute();
$result_similares = $stmt_similares->get_result();
while ($row = $result_similares->fetch_assoc()) {
    $destinos_similares[] = $row;
}
$stmt_similares->close();

$conn->close();

require_once 'includes/header.php';
?>

<style>
.hero-destino {
    position: relative;
    height: 500px;
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

.hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,0.3), rgba(0,0,0,0.7));
}

.hero-content {
    position: relative;
    z-index: 2;
    padding-top: 150px;
}

.info-card {
    border-radius: 15px;
    border: none;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.gallery-item {
    cursor: pointer;
    transition: transform 0.3s ease;
    border-radius: 10px;
    overflow: hidden;
}

.gallery-item:hover {
    transform: scale(1.05);
}

.recommendation-card {
    transition: all 0.3s ease;
    border-radius: 12px;
    overflow: hidden;
}

.recommendation-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.feature-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}
</style>

<!-- Hero Section -->
<div class="hero-destino" style="background-image: url('<?php echo !empty($destino['imagen']) ? 'assets/img/destinos/' . htmlspecialchars($destino['imagen']) : 'assets/img/destinos/default.jpg'; ?>');">
    <div class="hero-overlay"></div>
    <div class="container hero-content text-white">
        <div class="row">
            <div class="col-lg-8">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php" class="text-white">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="destinos.php" class="text-white">Destinos</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">
                            <?php echo htmlspecialchars($destino['nombre']); ?>
                        </li>
                    </ol>
                </nav>
                <h1 class="display-3 fw-bold mb-3" data-aos="fade-up">
                    <?php echo htmlspecialchars($destino['nombre']); ?>
                </h1>
                <div class="d-flex gap-3 flex-wrap" data-aos="fade-up" data-aos-delay="100">
                    <span class="badge bg-primary px-3 py-2">
                        <i class="bi bi-tag-fill me-2"></i><?php echo htmlspecialchars($destino['categoria']); ?>
                    </span>
                    <?php if (!empty($destino['ciudad'])): ?>
                    <span class="badge bg-light text-dark px-3 py-2">
                        <i class="bi bi-geo-alt-fill me-2"></i><?php echo htmlspecialchars($destino['ciudad']); ?>
                    </span>
                    <?php endif; ?>
                    <span class="badge bg-success px-3 py-2">
                        <i class="bi bi-cash me-2"></i><?php echo number_format($destino['precio'], 2); ?> €
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4">
        <!-- Contenido Principal -->
        <div class="col-lg-8">
            <!-- Descripción -->
            <div class="card info-card mb-4" data-aos="fade-up">
                <div class="card-body p-4">
                    <h2 class="h3 fw-bold mb-4">
                        <i class="bi bi-info-circle-fill text-primary me-2"></i>
                        Descripción
                    </h2>
                    <p class="lead text-muted" style="white-space: pre-line;">
                        <?php echo htmlspecialchars($destino['descripcion']); ?>
                    </p>
                </div>
            </div>

            <!-- Características -->
            <div class="card info-card mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card-body p-4">
                    <h2 class="h3 fw-bold mb-4">
                        <i class="bi bi-star-fill text-warning me-2"></i>
                        Características del Destino
                    </h2>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="feature-icon bg-primary bg-opacity-10 text-primary me-3">
                                    <i class="bi bi-geo-alt-fill"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Ubicación</h6>
                                    <p class="text-muted mb-0">
                                        <?php echo htmlspecialchars($destino['ciudad'] ?? 'Guinea Ecuatorial'); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="feature-icon bg-success bg-opacity-10 text-success me-3">
                                    <i class="bi bi-cash-coin"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Precio</h6>
                                    <p class="text-muted mb-0">
                                        <?php echo number_format($destino['precio'], 2); ?> € / persona
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="feature-icon bg-info bg-opacity-10 text-info me-3">
                                    <i class="bi bi-tag-fill"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Categoría</h6>
                                    <p class="text-muted mb-0">
                                        <?php echo htmlspecialchars($destino['categoria']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="feature-icon bg-warning bg-opacity-10 text-warning me-3">
                                    <i class="bi bi-star"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0">Experiencia</h6>
                                    <p class="text-muted mb-0">Inolvidable</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Galería de Imágenes -->
            <?php if (count($gallery_images) > 0): ?>
            <div class="card info-card mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="card-body p-4">
                    <h2 class="h3 fw-bold mb-4">
                        <i class="bi bi-images text-primary me-2"></i>
                        Galería de Imágenes
                    </h2>
                    <div class="row g-3">
                        <?php foreach ($gallery_images as $image): ?>
                        <div class="col-md-4">
                            <div class="gallery-item">
                                <img src="assets/img/destinos/<?php echo htmlspecialchars($image['ruta_imagen']); ?>" 
                                     class="img-fluid w-100" 
                                     style="height: 200px; object-fit: cover;"
                                     alt="<?php echo htmlspecialchars($image['descripcion'] ?? 'Imagen'); ?>"
                                     data-bs-toggle="modal"
                                     data-bs-target="#imageModal"
                                     onclick="showImage('assets/img/destinos/<?php echo htmlspecialchars($image['ruta_imagen']); ?>')">
                            </div>
                            <?php if (!empty($image['descripcion'])): ?>
                            <p class="text-muted small mt-2">
                                <?php echo htmlspecialchars($image['descripcion']); ?>
                            </p>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Mapa (si hay coordenadas) -->
            <?php if (!empty($destino['latitude']) && !empty($destino['longitude'])): ?>
            <div class="card info-card mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="card-body p-4">
                    <h2 class="h3 fw-bold mb-4">
                        <i class="bi bi-map text-danger me-2"></i>
                        Ubicación en el Mapa
                    </h2>
                    <div id="map" style="height: 400px; border-radius: 10px;"></div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Card de Reserva -->
            <div class="card info-card sticky-top mb-4" style="top: 20px;" data-aos="fade-left">
                <div class="card-body p-4">
                    <h3 class="h4 fw-bold mb-3">Reserva este Destino</h3>
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted">Precio por persona:</span>
                            <span class="h3 text-primary fw-bold mb-0">
                                <?php echo number_format($destino['precio'], 2); ?> €
                            </span>
                        </div>
                    </div>
                    
                    <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'turista'): ?>
                    <div class="d-grid gap-2">
                        <a href="crear_itinerario.php?destino=<?php echo $destino['id']; ?>" 
                           class="btn btn-primary btn-lg">
                            <i class="bi bi-calendar-plus me-2"></i>
                            Agregar a Itinerario
                        </a>
                        <a href="reservas.php?destino=<?php echo $destino['id']; ?>" 
                           class="btn btn-success btn-lg">
                            <i class="bi bi-check-circle me-2"></i>
                            Reservar Ahora
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        <a href="index.php#login">Inicia sesión</a> como turista para reservar
                    </div>
                    <?php endif; ?>
                    
                    <hr class="my-4">
                    
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-shield-check text-success fs-4 me-3"></i>
                        <div>
                            <h6 class="mb-0">Garantía de Calidad</h6>
                            <small class="text-muted">Destinos verificados</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <i class="bi bi-headset text-primary fs-4 me-3"></i>
                        <div>
                            <h6 class="mb-0">Soporte 24/7</h6>
                            <small class="text-muted">Estamos aquí para ayudarte</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="bi bi-hand-thumbs-up text-warning fs-4 me-3"></i>
                        <div>
                            <h6 class="mb-0">Mejor Precio</h6>
                            <small class="text-muted">Garantizado</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Guías Recomendados -->
    <?php if (count($guias_recomendados) > 0): ?>
    <div class="mt-5" data-aos="fade-up">
        <h2 class="h3 fw-bold mb-4">
            <i class="bi bi-person-badge text-primary me-2"></i>
            Guías Recomendados
        </h2>
        <div class="row g-4">
            <?php foreach ($guias_recomendados as $guia): ?>
            <div class="col-md-4">
                <div class="card recommendation-card h-100 shadow-sm">
                    <?php if (!empty($guia['imagen_perfil'])): ?>
                    <img src="assets/img/guias/<?php echo htmlspecialchars($guia['imagen_perfil']); ?>" 
                         class="card-img-top" style="height: 200px; object-fit: cover;" 
                         alt="<?php echo htmlspecialchars($guia['nombre_guia']); ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="card-title fw-bold">
                            <?php echo htmlspecialchars($guia['nombre_guia']); ?>
                        </h5>
                        <p class="text-muted small mb-2">
                            <i class="bi bi-star-fill text-warning me-1"></i>
                            <?php echo htmlspecialchars($guia['especialidades']); ?>
                        </p>
                        <p class="card-text">
                            <?php echo htmlspecialchars(substr($guia['descripcion'] ?? 'Guía profesional', 0, 100)) . '...'; ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h5 text-primary mb-0">
                                <?php echo number_format($guia['precio_hora'], 2); ?> €/h
                            </span>
                            <a href="detalle_guia.php?id=<?php echo $guia['id']; ?>" 
                               class="btn btn-sm btn-outline-primary">
                                Ver Perfil
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Locales Cercanos -->
    <?php if (count($locales_cercanos) > 0): ?>
    <div class="mt-5" data-aos="fade-up">
        <h2 class="h3 fw-bold mb-4">
            <i class="bi bi-shop text-success me-2"></i>
            Locales Cercanos
        </h2>
        <div class="row g-4">
            <?php foreach ($locales_cercanos as $local): ?>
            <div class="col-md-4">
                <div class="card recommendation-card h-100 shadow-sm">
                    <?php if (!empty($local['imagen_perfil'])): ?>
                    <img src="assets/img/locales/<?php echo htmlspecialchars($local['imagen_perfil']); ?>" 
                         class="card-img-top" style="height: 200px; object-fit: cover;" 
                         alt="<?php echo htmlspecialchars($local['nombre_local']); ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <span class="badge bg-success mb-2">
                            <?php echo htmlspecialchars($local['tipo_local']); ?>
                        </span>
                        <h5 class="card-title fw-bold">
                            <?php echo htmlspecialchars($local['nombre_local']); ?>
                        </h5>
                        <p class="text-muted small mb-2">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                            <?php echo htmlspecialchars($local['direccion']); ?>
                        </p>
                        <p class="card-text">
                            <?php echo htmlspecialchars(substr($local['descripcion'] ?? 'Local recomendado', 0, 100)) . '...'; ?>
                        </p>
                        <a href="detalle_local.php?id=<?php echo $local['id']; ?>" 
                           class="btn btn-sm btn-outline-success w-100">
                            Ver Detalles
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Destinos Similares -->
    <?php if (count($destinos_similares) > 0): ?>
    <div class="mt-5" data-aos="fade-up">
        <h2 class="h3 fw-bold mb-4">
            <i class="bi bi-geo-fill text-info me-2"></i>
            Destinos Similares
        </h2>
        <div class="row g-4">
            <?php foreach ($destinos_similares as $similar): ?>
            <div class="col-md-4">
                <div class="card recommendation-card h-100 shadow-sm">
                    <img src="<?php echo !empty($similar['imagen']) ? 'assets/img/destinos/' . htmlspecialchars($similar['imagen']) : 'assets/img/destinos/default.jpg'; ?>" 
                         class="card-img-top" style="height: 200px; object-fit: cover;" 
                         alt="<?php echo htmlspecialchars($similar['nombre']); ?>">
                    <div class="card-body">
                        <span class="badge bg-primary mb-2">
                            <?php echo htmlspecialchars($similar['categoria']); ?>
                        </span>
                        <h5 class="card-title fw-bold">
                            <?php echo htmlspecialchars($similar['nombre']); ?>
                        </h5>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <span class="h5 text-primary mb-0">
                                <?php echo number_format($similar['precio'], 2); ?> €
                            </span>
                            <a href="detalle_destino.php?id=<?php echo $similar['id']; ?>" 
                               class="btn btn-sm btn-primary">
                                Ver Destino
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Modal para ver imágenes -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="modalImage" src="" class="img-fluid w-100" alt="Imagen ampliada">
            </div>
        </div>
    </div>
</div>

<script>
function showImage(src) {
    document.getElementById('modalImage').src = src;
}

// Inicializar mapa si hay coordenadas
<?php if (!empty($destino['latitude']) && !empty($destino['longitude'])): ?>
// Aquí podrías inicializar un mapa con Leaflet o Google Maps
// Por ahora, mostraremos un placeholder
document.addEventListener('DOMContentLoaded', function() {
    const mapDiv = document.getElementById('map');
    if (mapDiv) {
        mapDiv.innerHTML = `
            <div class="d-flex align-items-center justify-content-center h-100 bg-light rounded">
                <div class="text-center p-4">
                    <i class="bi bi-geo-alt-fill text-primary" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Coordenadas del Destino</h5>
                    <p class="text-muted mb-0">
                        Lat: <?php echo htmlspecialchars($destino['latitude']); ?><br>
                        Lng: <?php echo htmlspecialchars($destino['longitude']); ?>
                    </p>
                    <a href="https://www.google.com/maps?q=<?php echo htmlspecialchars($destino['latitude']); ?>,<?php echo htmlspecialchars($destino['longitude']); ?>" 
                       target="_blank" class="btn btn-primary mt-3">
                        <i class="bi bi-map me-2"></i>Ver en Google Maps
                    </a>
                </div>
            </div>
        `;
    }
});
<?php endif; ?>
</script>

<?php require_once 'includes/footer.php'; ?>
