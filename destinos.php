<?php 
require_once 'includes/header.php';
require_once 'includes/db_connect.php';

// Obtener destinos directamente de la base de datos para evitar duplicados
$categoria_filtro = $_GET['categoria'] ?? 'all';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 9;
$offset = ($page - 1) * $items_per_page;

// Obtener categorías únicas
$categorias = [];
$sql_categorias = "SELECT DISTINCT categoria FROM destinos WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria ASC";
$result_categorias = $conn->query($sql_categorias);
if ($result_categorias) {
    while ($row = $result_categorias->fetch_assoc()) {
        $categorias[] = $row['categoria'];
    }
}

// Construir query para destinos
$where_clause = '';
$params = [];
$types = '';

if ($categoria_filtro !== 'all') {
    $where_clause = " WHERE categoria = ?";
    $params[] = $categoria_filtro;
    $types = 's';
}

// Contar total de destinos
$sql_count = "SELECT COUNT(DISTINCT id) as total FROM destinos" . $where_clause;
$stmt_count = $conn->prepare($sql_count);
if (!empty($params)) {
    $stmt_count->bind_param($types, ...$params);
}
$stmt_count->execute();
$total_destinos = $stmt_count->get_result()->fetch_assoc()['total'];
$stmt_count->close();
$total_pages = ceil($total_destinos / $items_per_page);

// Obtener destinos
$sql = "SELECT id, nombre, descripcion, imagen, categoria, precio, ciudad FROM destinos" . $where_clause . " ORDER BY nombre ASC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$params[] = $items_per_page;
$params[] = $offset;
$types .= 'ii';

if ($categoria_filtro !== 'all') {
    $stmt->bind_param($types, ...$params);
} else {
    $stmt->bind_param('ii', $items_per_page, $offset);
}

$stmt->execute();
$result = $stmt->get_result();
$destinos = [];
while ($row = $result->fetch_assoc()) {
    $destinos[] = $row;
}
$stmt->close();
$conn->close();
?>

<style>
.hero-destinos {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 4rem 0;
    margin-bottom: 3rem;
    position: relative;
    overflow: hidden;
}

.hero-destinos::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,112C672,96,768,96,864,112C960,128,1056,160,1152,160C1248,160,1344,128,1392,112L1440,96L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>');
    background-size: cover;
    background-position: bottom;
}

.destino-card {
    transition: all 0.3s ease;
    border: none;
    border-radius: 15px;
    overflow: hidden;
    height: 100%;
}

.destino-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.15) !important;
}

.destino-card img {
    height: 250px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.destino-card:hover img {
    transform: scale(1.1);
}

.categoria-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    z-index: 2;
    padding: 0.5rem 1rem;
    border-radius: 50px;
    font-weight: 600;
    font-size: 0.875rem;
}

.filter-btn {
    padding: 0.75rem 1.5rem;
    border-radius: 50px;
    margin: 0.25rem;
    transition: all 0.3s ease;
    border: 2px solid #667eea;
    background: white;
    color: #667eea;
}

.filter-btn:hover, .filter-btn.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
}

.precio-tag {
    font-size: 1.5rem;
    font-weight: 700;
    color: #667eea;
}
</style>

<!-- Hero Section -->
<section class="hero-destinos text-white">
    <div class="container position-relative">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-3 fw-bold mb-3" data-aos="fade-down">
                    <i class="bi bi-geo-alt-fill me-3"></i>Explora Nuestros Destinos
                </h1>
                <p class="lead mb-4" data-aos="fade-up" data-aos-delay="100">
                    Descubre la belleza y diversidad de Guinea Ecuatorial
                </p>
                <div class="d-flex justify-content-center gap-3" data-aos="fade-up" data-aos-delay="200">
                    <div class="text-center">
                        <h3 class="mb-0"><?php echo $total_destinos; ?></h3>
                        <small>Destinos</small>
                    </div>
                    <div class="text-center ms-4">
                        <h3 class="mb-0"><?php echo count($categorias); ?></h3>
                        <small>Categorías</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container py-5">
    <!-- Controles de Filtro -->
    <div class="row mb-5">
        <div class="col-12 text-center" data-aos="fade-up">
            <h5 class="mb-3">Filtrar por Categoría</h5>
            <div class="d-flex flex-wrap justify-content-center">
                <a href="destinos.php?categoria=all" class="filter-btn <?php echo $categoria_filtro === 'all' ? 'active' : ''; ?>">
                    <i class="bi bi-grid-fill me-2"></i>Todos
                </a>
                <?php foreach ($categorias as $cat): ?>
                    <a href="destinos.php?categoria=<?php echo urlencode($cat); ?>" 
                       class="filter-btn <?php echo $categoria_filtro === $cat ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($cat); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Grid de Destinos -->
    <div class="row g-4">
        <?php if (count($destinos) > 0): ?>
            <?php foreach ($destinos as $index => $destino): ?>
                <div class="col-md-6 col-lg-4" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>">
                    <div class="card destino-card shadow-sm">
                        <div class="position-relative overflow-hidden">
                            <?php 
                            $imagen = !empty($destino['imagen']) ? 'assets/img/destinos/' . $destino['imagen'] : 'assets/img/destinos/default.jpg';
                            ?>
                            <img src="<?php echo htmlspecialchars($imagen); ?>" 
                                 class="card-img-top" 
                                 alt="<?php echo htmlspecialchars($destino['nombre']); ?>"
                                 onerror="this.src='assets/img/destinos/default.jpg'">
                            <span class="categoria-badge bg-primary text-white">
                                <?php echo htmlspecialchars($destino['categoria']); ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title fw-bold mb-3">
                                <?php echo htmlspecialchars($destino['nombre']); ?>
                            </h5>
                            <p class="card-text text-muted">
                                <?php echo htmlspecialchars(substr($destino['descripcion'], 0, 120)) . '...'; ?>
                            </p>
                            
                            <?php if (!empty($destino['ciudad'])): ?>
                            <p class="text-muted small mb-3">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                <?php echo htmlspecialchars($destino['ciudad']); ?>
                            </p>
                            <?php endif; ?>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="precio-tag">
                                    <?php echo number_format($destino['precio'], 2); ?> €
                                </span>
                                <a href="detalle_destino.php?id=<?php echo $destino['id']; ?>" 
                                   class="btn btn-primary">
                                    <i class="bi bi-eye me-2"></i>Ver Detalles
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-info text-center py-5">
                    <i class="bi bi-info-circle fs-1 mb-3 d-block"></i>
                    <h4>No hay destinos disponibles</h4>
                    <p class="mb-0">No se encontraron destinos para la categoría seleccionada.</p>
                    <a href="destinos.php" class="btn btn-primary mt-3">Ver Todos los Destinos</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Paginación -->
    <?php if ($total_pages > 1): ?>
    <div class="row mt-5">
        <div class="col-12">
            <nav aria-label="Navegación de destinos">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?categoria=<?php echo urlencode($categoria_filtro); ?>&page=<?php echo $page - 1; ?>">
                                <i class="bi bi-chevron-left"></i> Anterior
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                <a class="page-link" href="?categoria=<?php echo urlencode($categoria_filtro); ?>&page=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?categoria=<?php echo urlencode($categoria_filtro); ?>&page=<?php echo $page + 1; ?>">
                                Siguiente <i class="bi bi-chevron-right"></i>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
