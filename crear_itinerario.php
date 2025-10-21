<?php 
require_once 'includes/header.php';

// Redirigir si el usuario no está autenticado o no es un turista
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'turista') {
    header('Location: index.php'); // O a una página de acceso denegado
    exit;
}

// Obtener todos los destinos para que el usuario elija
$destinos = [];
$sql = "SELECT id, nombre, descripcion, imagen FROM destinos ORDER BY nombre ASC";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $destinos[] = $row;
    }
}
$conn->close();
?>

<div class="container py-5">
    <div class="text-center mb-5">
        <h1 class="display-4 fw-bold">Crear Nuevo Itinerario</h1>
        <p class="lead text-muted">Selecciona hasta 5 destinos para tu próxima aventura.</p>
    </div>

    <form id="itinerary-form">
        <div class="row">
            <!-- Columna de selección de destinos -->
            <div class="col-lg-8">
                <h3 class="mb-4">Paso 1: Elige tus destinos</h3>
                <div class="row row-cols-1 row-cols-md-2 g-4">
                    <?php if (count($destinos) > 0): ?>
                        <?php foreach ($destinos as $destino): ?>
                            <div class="col">
                                <div class="card h-100 destination-card" data-id="<?php echo $destino['id']; ?>">
                                    <img src="assets/img/destinos/<?php echo htmlspecialchars($destino['imagen']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($destino['nombre']); ?>">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($destino['nombre']); ?></h5>
                                        <p class="card-text small"><?php echo substr(htmlspecialchars($destino['descripcion']), 0, 100); ?>...</p>
                                    </div>
                                    <div class="card-footer bg-transparent border-top-0">
                                        <button type="button" class="btn btn-sm btn-outline-primary w-100 add-to-itinerary">Agregar</button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No hay destinos disponibles en este momento.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Columna del itinerario actual -->
            <div class="col-lg-4">
                <div class="sticky-top" style="top: 80px;">
                    <h3 class="mb-4">Paso 2: Revisa y guarda</h3>
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Destinos Seleccionados</h5>
                            <ul class="list-group list-group-flush" id="selected-destinations">
                                <!-- Los destinos seleccionados se añadirán aquí con JS -->
                            </ul>
                        </div>
                    </div>
                    <div class="card mt-4">
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="itinerary-name" class="form-label">Nombre del Itinerario</label>
                                <input type="text" class="form-control" id="itinerary-name" placeholder="Ej: Aventura en la selva" required>
                            </div>
                            <div id="itinerary-feedback" class="mb-3"></div>
                            <button type="submit" class="btn btn-primary w-100">Guardar Itinerario</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>