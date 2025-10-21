<?php
require_once 'includes/header.php';
require_once 'includes/db_connect.php';

$search_query = $_GET['query'] ?? '';
$search_type = $_GET['type'] ?? 'all';
$results = [];
$title = "Resultados de Búsqueda";

if ($conn) {
    $query_parts = [];
    $params = [];
    $param_types = '';

    // Search in Destinos
    if ($search_type === 'all' || $search_type === 'destinos') {
        $query_parts[] = "(SELECT id, nombre_destino as name, 'destino' as type, descripcion, NULL as especialidades, NULL as tipo_local, NULL as precio_hora, NULL as contacto_email, NULL as contacto_telefono, ruta_imagen FROM destinos WHERE nombre_destino LIKE ? OR descripcion LIKE ?)";
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
        $param_types .= 'ss';
    }

    // Search in Agencias
    if ($search_type === 'all' || $search_type === 'agencias') {
        $query_parts[] = "(SELECT id, nombre_agencia as name, 'agencia' as type, descripcion, NULL as especialidades, NULL as tipo_local, NULL as precio_hora, contacto_email, contacto_telefono, NULL as ruta_imagen FROM agencias WHERE nombre_agencia LIKE ? OR descripcion LIKE ?)";
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
        $param_types .= 'ss';
    }

    // Search in Guías
    if ($search_type === 'all' || $search_type === 'guias') {
        $query_parts[] = "(SELECT id, nombre_guia as name, 'guia' as type, descripcion, especialidades, NULL as tipo_local, precio_hora, contacto_email, contacto_telefono, NULL as ruta_imagen FROM guias_turisticos WHERE nombre_guia LIKE ? OR descripcion LIKE ? OR especialidades LIKE ?)";
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
        $param_types .= 'sss';
    }

    // Search in Locales
    if ($search_type === 'all' || $search_type === 'locales') {
        $query_parts[] = "(SELECT id, nombre_local as name, 'local' as type, descripcion, NULL as especialidades, tipo_local, NULL as precio_hora, contacto_email, contacto_telefono, NULL as ruta_imagen FROM lugares_locales WHERE nombre_local LIKE ? OR descripcion LIKE ? OR tipo_local LIKE ?)";
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
        $params[] = '%' . $search_query . '%';
        $param_types .= 'sss';
    }

    if (!empty($query_parts)) {
        $full_query = implode(' UNION ALL ', $query_parts) . " ORDER BY name ASC";
        $stmt = $conn->prepare($full_query);
        
        // Dynamically bind parameters
        if (!empty($params)) {
            $stmt->bind_param($param_types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
        $stmt->close();
    }
    $conn->close();
}

?>

<div class="container py-5">
    <h1 class="mb-4"><?= htmlspecialchars($title) ?></h1>

    <?php if (!empty($search_query)): ?>
        <p class="lead">Mostrando resultados para "<strong><?= htmlspecialchars($search_query) ?></strong>" en la categoría "<strong><?= htmlspecialchars(ucfirst($search_type)) ?></strong>":</p>
    <?php else: ?>
        <p class="lead">Por favor, introduce un término de búsqueda.</p>
    <?php endif; ?>

    <?php if (empty($results) && !empty($search_query)): ?>
        <div class="alert alert-warning" role="alert">
            No se encontraron resultados para tu búsqueda.
        </div>
    <?php elseif (!empty($results)): ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($results as $item): ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <?php if ($item['type'] === 'destino' && !empty($item['ruta_imagen'])): ?>
                            <img src="assets/img/destinos/<?= htmlspecialchars($item['ruta_imagen']) ?>" class="card-img-top" alt="<?= htmlspecialchars($item['name']) ?>" style="height: 200px; object-fit: cover;">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($item['name']) ?></h5>
                            <h6 class="card-subtitle mb-2 text-muted"><?= htmlspecialchars(ucfirst($item['type'])) ?></h6>
                            <p class="card-text"><?= nl2br(htmlspecialchars(mb_strimwidth($item['descripcion'], 0, 150, "..."))) ?></p>
                            <?php if ($item['type'] === 'guia' && !empty($item['especialidades'])): ?>
                                <p class="card-text"><small class="text-muted">Especialidades: <?= htmlspecialchars($item['especialidades']) ?></small></p>
                            <?php endif; ?>
                            <?php if ($item['type'] === 'local' && !empty($item['tipo_local'])): ?>
                                <p class="card-text"><small class="text-muted">Tipo de Local: <?= htmlspecialchars($item['tipo_local']) ?></small></p>
                            <?php endif; ?>
                            <?php if (!empty($item['contacto_email'])): ?>
                                <p class="card-text"><small class="text-muted">Email: <?= htmlspecialchars($item['contacto_email']) ?></small></p>
                            <?php endif; ?>
                            <?php if (!empty($item['contacto_telefono'])): ?>
                                <p class="card-text"><small class="text-muted">Teléfono: <?= htmlspecialchars($item['contacto_telefono']) ?></small></p>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer">
                            <?php 
                                $detail_page = '#';
                                if ($item['type'] === 'destino') $detail_page = 'detalle_destino.php?id=' . $item['id'];
                                else if ($item['type'] === 'agencia') $detail_page = 'detalle_agencia.php?id=' . $item['id'];
                                else if ($item['type'] === 'guia') $detail_page = 'detalle_guia.php?id=' . $item['id'];
                                else if ($item['type'] === 'local') $detail_page = 'detalle_local.php?id=' . $item['id'];
                            ?>
                            <a href="<?= $detail_page ?>" class="btn btn-primary btn-sm">Ver Detalles</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>