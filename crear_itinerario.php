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
        <p class="lead text-muted">Organiza tu aventura paso a paso.</p>
    </div>

    <div class="row">
        <div class="col-md-12">
            <ul class="nav nav-pills nav-fill mb-3" id="itinerary-tabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="step1-tab" data-bs-toggle="tab" data-bs-target="#step1" type="button" role="tab" aria-controls="step1" aria-selected="true">1. Ciudad y Fechas</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="step2-tab" data-bs-toggle="tab" data-bs-target="#step2" type="button" role="tab" aria-controls="step2" aria-selected="false">2. Lugares a Visitar</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="step3-tab" data-bs-toggle="tab" data-bs-target="#step3" type="button" role="tab" aria-controls="step3" aria-selected="false">3. Alojamiento</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="step4-tab" data-bs-toggle="tab" data-bs-target="#step4" type="button" role="tab" aria-controls="step4" aria-selected="false">4. Guía Turístico</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="step5-tab" data-bs-toggle="tab" data-bs-target="#step5" type="button" role="tab" aria-controls="step5" aria-selected="false">5. Resumen y Guardar</button>
                </li>
            </ul>

            <div class="tab-content" id="itinerary-tabContent">
                <!-- Step 1: City and Dates -->
                <div class="tab-pane fade show active" id="step1" role="tabpanel" aria-labelledby="step1-tab">
                    <h3 class="mb-4">Paso 1: Elige tu Ciudad Principal y Fechas</h3>
                    <form id="form-step1">
                        <div class="mb-3">
                            <label for="itinerary-city" class="form-label">Ciudad Principal</label>
                            <input type="text" class="form-control" id="itinerary-city" placeholder="Ej: Malabo" required>
                        </div>
                        <div class="mb-3">
                            <label for="itinerary-start-date" class="form-label">Fecha de Inicio</label>
                            <input type="date" class="form-control" id="itinerary-start-date" required>
                        </div>
                        <div class="mb-3">
                            <label for="itinerary-end-date" class="form-label">Fecha de Fin</label>
                            <input type="date" class="form-control" id="itinerary-end-date" required>
                        </div>
                        <button type="button" class="btn btn-primary next-step">Siguiente</button>
                    </form>
                </div>

                <!-- Step 2: Places to Visit -->
                <div class="tab-pane fade" id="step2" role="tabpanel" aria-labelledby="step2-tab">
                    <h3 class="mb-4">Paso 2: Selecciona Lugares a Visitar</h3>
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
                                            <button type="button" class="btn btn-sm btn-outline-primary w-100 add-to-itinerary-item" data-item-type="destino" data-item-id="<?php echo $destino['id']; ?>" data-item-name="<?php echo htmlspecialchars($destino['nombre']); ?>">Agregar</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No hay destinos disponibles en este momento.</p>
                        <?php endif; ?>
                    </div>
                    <button type="button" class="btn btn-secondary prev-step mt-3">Anterior</button>
                    <button type="button" class="btn btn-primary next-step mt-3">Siguiente</button>
                </div>

                <!-- Step 3: Accommodation -->
                <div class="tab-pane fade" id="step3" role="tabpanel" aria-labelledby="step3-tab">
                    <h3 class="mb-4">Paso 3: Elige tu Alojamiento</h3>
                    <div class="row row-cols-1 row-cols-md-2 g-4">
                        <!-- Locales (Hoteles) se cargarán aquí dinámicamente con JS -->
                        <div class="col-12 text-center" id="loading-locales-spinner">
                            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2 text-muted">Cargando alojamientos...</p>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary prev-step mt-3">Anterior</button>
                    <button type="button" class="btn btn-primary next-step mt-3">Siguiente</button>
                </div>

                <!-- Step 4: Tourist Guide -->
                <div class="tab-pane fade" id="step4" role="tabpanel" aria-labelledby="step4-tab">
                    <h3 class="mb-4">Paso 4: Contrata un Guía Turístico</h3>
                    <div class="row row-cols-1 row-cols-md-2 g-4">
                        <!-- Guías se cargarán aquí dinámicamente con JS -->
                        <div class="col-12 text-center" id="loading-guias-spinner">
                            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2 text-muted">Cargando guías...</p>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary prev-step mt-3">Anterior</button>
                    <button type="button" class="btn btn-primary next-step mt-3">Siguiente</button>
                </div>

                <!-- Step 5: Review and Save -->
                <div class="tab-pane fade" id="step5" role="tabpanel" aria-labelledby="step5-tab">
                    <h3 class="mb-4">Paso 5: Revisa tu Itinerario y Guarda</h3>
                    <form id="final-itinerary-form">
                        <div class="mb-3">
                            <label for="final-itinerary-name" class="form-label">Nombre del Itinerario</label>
                            <input type="text" class="form-control" id="final-itinerary-name" placeholder="Ej: Aventura en la selva" required>
                        </div>
                        <div class="mb-3">
                            <label for="final-itinerary-city" class="form-label">Ciudad Principal</label>
                            <input type="text" class="form-control" id="final-itinerary-city" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="final-itinerary-dates" class="form-label">Fechas</label>
                            <input type="text" class="form-control" id="final-itinerary-dates" readonly>
                        </div>
                        <h4>Lugares a Visitar</h4>
                        <ul class="list-group mb-3" id="review-destinos">
                            <!-- Destinos seleccionados se mostrarán aquí -->
                        </ul>
                        <h4>Alojamiento</h4>
                        <ul class="list-group mb-3" id="review-alojamiento">
                            <!-- Alojamiento seleccionado se mostrará aquí -->
                        </ul>
                        <h4>Guía Turístico</h4>
                        <ul class="list-group mb-3" id="review-guia">
                            <!-- Guía seleccionado se mostrará aquí -->
                        </ul>
                        <div id="final-itinerary-feedback" class="mb-3"></div>
                        <button type="submit" class="btn btn-success w-100">Guardar Itinerario Final</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>