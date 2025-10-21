<?php 
require_once 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'turista') {
    header('Location: index.php'); // O a una página de acceso denegado
    exit;
}

$id_usuario = $_SESSION['user_id'];
$itinerarios = [];
$destinos_en_itinerarios = [];
$categorias_destinos = [];
$guias_recomendados = [];
$locales_recomendados = [];

// Obtener itinerarios del usuario
$sql_itinerarios = "SELECT i.id, i.nombre_itinerario, GROUP_CONCAT(d.id) as ids_destinos, GROUP_CONCAT(d.nombre ORDER BY id.orden SEPARATOR ', ') as nombres_destinos
        FROM itinerarios i
        JOIN itinerario_destinos id ON i.id = id.id_itinerario
        JOIN destinos d ON id.id_destino = d.id
        WHERE i.id_usuario = ?
        GROUP BY i.id
        ORDER BY i.fecha_creacion DESC";

$stmt_itinerarios = $conn->prepare($sql_itinerarios);
$stmt_itinerarios->bind_param("i", $id_usuario);
$stmt_itinerarios->execute();
$result_itinerarios = $stmt_itinerarios->get_result();

while ($row = $result_itinerarios->fetch_assoc()) {
    $itinerarios[] = $row;
    if (!empty($row['ids_destinos'])) {
        $destinos_en_itinerarios = array_merge($destinos_en_itinerarios, explode(',', $row['ids_destinos']));
    }
}
$stmt_itinerarios->close();

// Obtener categorías únicas de los destinos en los itinerarios
if (!empty($destinos_en_itinerarios)) {
    $placeholders = implode(',', array_fill(0, count($destinos_en_itinerarios), '?'));
    $sql_categorias = "SELECT DISTINCT categoria FROM destinos WHERE id IN ($placeholders)";
    $stmt_categorias = $conn->prepare($sql_categorias);
    $types = str_repeat('i', count($destinos_en_itinerarios));
    $stmt_categorias->bind_param($types, ...$destinos_en_itinerarios);
    $stmt_categorias->execute();
    $result_categorias = $stmt_categorias->get_result();
    while ($row = $result_categorias->fetch_assoc()) {
        $categorias_destinos[] = $row['categoria'];
    }
    $stmt_categorias->close();
}

// Buscar guías recomendados basados en especialidades que coincidan con las categorías de destinos
if (!empty($categorias_destinos)) {
    $placeholders = implode(',', array_fill(0, count($categorias_destinos), '?'));
    $search_terms = [];
    foreach ($categorias_destinos as $cat) {
        $search_terms[] = "especialidades LIKE '%" . $conn->real_escape_string($cat) . "%'";
    }
    $sql_guias = "SELECT id, nombre_guia, descripcion, especialidades, precio_hora, contacto_email FROM guias_turisticos WHERE " . implode(' OR ', $search_terms) . " LIMIT 3";
    $result_guias = $conn->query($sql_guias);
    if ($result_guias) {
        while ($row = $result_guias->fetch_assoc()) {
            $guias_recomendados[] = $row;
        }
    }
}

// Buscar locales recomendados basados en el tipo de local (simplificado por ahora)
// Podríamos buscar locales que ofrezcan servicios relacionados con las categorías de destinos
if (!empty($categorias_destinos)) {
    $search_terms = [];
    foreach ($categorias_destinos as $cat) {
        // Esto es una simplificación, idealmente se buscaría por tipo_local o servicios_local
        $search_terms[] = "tipo_local LIKE '%" . $conn->real_escape_string($cat) . "%'";
    }
    // Si no hay coincidencias directas, buscar tipos de locales generales
    if (empty($search_terms)) {
        $search_terms[] = "tipo_local IN ('Restaurante', 'Hotel', 'Atracción')";
    }
    $sql_locales = "SELECT id, nombre_local, descripcion, tipo_local, direccion, contacto_email FROM lugares_locales WHERE " . implode(' OR ', $search_terms) . " LIMIT 3";
    $result_locales = $conn->query($sql_locales);
    if ($result_locales) {
        while ($row = $result_locales->fetch_assoc()) {
            $locales_recomendados[] = $row;
        }
    }
}

$conn->close();
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h1 class="display-4 fw-bold">Mis Itinerarios</h1>
        <a href="crear_itinerario.php" class="btn btn-primary">Crear Nuevo Itinerario</a>
    </div>

    <div id="itineraries-list">
        <?php if (count($itinerarios) > 0): ?>
            <div class="list-group">
                <?php foreach ($itinerarios as $itinerario): ?>
                    <div class="list-group-item list-group-item-action flex-column align-items-start mb-3">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1"><?php echo htmlspecialchars($itinerario['nombre_itinerario']); ?></h5>
                            <button class="btn btn-sm btn-outline-danger delete-itinerary" data-id="<?php echo $itinerario['id']; ?>">Eliminar</button>
                        </div>
                        <p class="mb-1 text-muted">Destinos: <?php echo htmlspecialchars($itinerario['nombres_destinos']); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center">
                <p class="lead">Aún no has creado ningún itinerario.</p>
                <a href="crear_itinerario.php" class="btn btn-lg btn-primary">¡Crea tu primer itinerario!</a>
            </div>
        <?php endif; ?>
    </div>
    <div id="itinerary-delete-feedback" class="mt-3"></div>

    <?php if (count($itinerarios) > 0): // Mostrar recomendaciones solo si hay itinerarios ?>
        <h2 class="display-5 fw-bold mt-5 mb-4">Recomendaciones para tu Viaje</h2>

        <?php if (!empty($guias_recomendados)): ?>
            <h3 class="mt-4">Guías Turísticos Recomendados</h3>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($guias_recomendados as $guia): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($guia['nombre_guia']) ?></h5>
                                <p class="card-text text-muted">Especialidades: <?= htmlspecialchars($guia['especialidades']) ?></p>
                                <p class="card-text">Precio/Hora: <?= number_format($guia['precio_hora'], 2) ?> €</p>
                                <div class="mt-auto pt-3">
                                    <a href="detalle_guia.php?id=<?= htmlspecialchars($guia['id']) ?>" class="btn btn-sm btn-outline-primary">Ver Perfil</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted">No encontramos guías turísticos recomendados para tus destinos actuales.</p>
        <?php endif; ?>

        <?php if (!empty($locales_recomendados)): ?>
            <h3 class="mt-4">Lugares y Locales Recomendados</h3>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($locales_recomendados as $local): ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($local['nombre_local']) ?></h5>
                                <p class="card-text text-muted">Tipo: <?= htmlspecialchars($local['tipo_local']) ?></p>
                                <p class="card-text">Dirección: <?= htmlspecialchars($local['direccion']) ?></p>
                                <div class="mt-auto pt-3">
                                    <a href="detalle_local.php?id=<?= htmlspecialchars($local['id']) ?>" class="btn btn-sm btn-outline-primary">Ver Detalles</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted">No encontramos lugares o locales recomendados para tus destinos actuales.</p>
        <?php endif; ?>

    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>