<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include 'includes/db_connect.php';

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
    $sql = "SELECT nombre, descripcion, imagen, categoria, precio, ciudad FROM destinos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if($stmt) {
        $stmt->bind_param("i", $id_destino);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $destino = $result->fetch_assoc();
        }
        $stmt->close();
    } else {
        // Error al preparar la consulta
        echo "<div class='container py-5'><div class='alert alert-danger text-center'>Error al preparar la consulta de destino: " . $conn->error . "</div></div>";
        require_once 'includes/footer.php';
        exit();
    }
} else {
    // Error de conexión a la base de datos
    echo "<div class='container py-5'><div class='alert alert-danger text-center'>Error de conexión a la base de datos.</div></div>";
    require_once 'includes/footer.php';
    exit();
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

// Obtener guías disponibles para este destino
$available_guias = [];
if ($destino && isset($destino['ciudad'])) {
    $stmt_available_guias = $conn->prepare("
        SELECT gt.id, gt.nombre_guia, gt.especialidades, gt.precio_hora
        FROM guias_turisticos gt
        JOIN guias_destinos gd ON gt.id = gd.id_guia
        WHERE gd.id_destino = ? AND gt.ciudad_operacion = ?
        ORDER BY gt.nombre_guia ASC
    ");
    $stmt_available_guias->bind_param("is", $id_destino, $destino['ciudad']);
    $stmt_available_guias->execute();
    $result_available_guias = $stmt_available_guias->get_result();
    while ($row = $result_available_guias->fetch_assoc()) {
        $available_guias[] = $row;
    }
    $stmt_available_guias->close();
}

$conn->close(); // Close connection at the very end of the script
?>
<?php include 'includes/header.php'; ?>


    <?php if ($destino): ?>
        <div class="row g-5 mt-4">
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

                    <div class="d-grid gap-2 mt-4">
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'turista'): ?>
                            <?php if (count($available_guias) > 0): ?>
                                <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#selectGuiaModal"><i class="bi bi-calendar-check me-2"></i>Reservar con Guía</button>
                            <?php else: ?>
                                <button class="btn btn-primary btn-lg" disabled><i class="bi bi-calendar-check me-2"></i>No hay Guías Disponibles</button>
                            <?php endif; ?>
                            <button class="btn btn-outline-secondary"><i class="bi bi-plus-lg me-2"></i>Añadir al Itinerario</button>
                        <?php else: ?>
                            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#loginModal"><i class="bi bi-calendar-check me-2"></i>Reservar Ahora</button>
                            <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#loginModal"><i class="bi bi-plus-lg me-2"></i>Añadir al Itinerario</button>
                        <?php endif; ?>
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


<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const mapElement = document.getElementById('map');
    let latitude = null;
    let longitude = null;

    <?php if ($destino_location && $destino_location['latitude'] && $destino_location['longitude']): ?>
        latitude = <?= $destino_location['latitude'] ?>;
        longitude = <?= $destino_location['longitude'] ?>;
    <?php endif; ?>

    if (mapElement && latitude !== null && longitude !== null) {
        const map = L.map('map').setView([latitude, longitude], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        L.marker([latitude, longitude]).addTo(map)
            .bindPopup('<b>Ubicación del Destino</b>')
            .openPopup();
    }

    // Lógica para el modal de encargar servicio (orderServiceModal)
    const orderServiceModal = document.getElementById('orderServiceModal');
    const modalItemName = document.getElementById('modalItemName');
    const orderItemId = document.getElementById('orderItemId');
    const orderItemType = document.getElementById('orderItemType');
    const orderItemPrice = document.getElementById('orderItemPrice');
    const orderProviderType = document.getElementById('orderProviderType');
    const orderProviderId = document.getElementById('orderProviderId');
    const orderDestinationId = document.getElementById('orderDestinationId'); // Nuevo
    const orderDate = document.getElementById('orderDate');
    const orderPersons = document.getElementById('orderPersons');
    const orderTotalPrice = document.getElementById('orderTotalPrice');
    const orderServiceForm = document.getElementById('orderServiceForm');
    const orderServiceMessage = document.getElementById('orderServiceMessage');

    let currentItemPrice = 0;

    if (orderServiceModal) {
        orderServiceModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget; // Button that triggered the modal
            modalItemName.textContent = button.dataset.name;
            orderItemId.value = button.dataset.id;
            orderItemType.value = button.dataset.type;
            orderItemPrice.value = button.dataset.price;
            orderProviderType.value = button.dataset.providerType;
            orderProviderId.value = button.dataset.providerId;
            orderDestinationId.value = button.dataset.idDestinoAsociado || ''; // Leer el nuevo atributo
            
            currentItemPrice = parseFloat(button.dataset.price);
            orderPersons.value = 1;
            orderTotalPrice.value = (currentItemPrice * parseInt(orderPersons.value)).toFixed(2);

            // Set min date to today
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0'); // Months start at 0!
            const dd = String(today.getDate()).padStart(2, '0');
            orderDate.min = `${yyyy}-${mm}-${dd}`;
            orderDate.value = `${yyyy}-${mm}-${dd}`;
        });

        orderPersons.addEventListener('input', function() {
            const persons = parseInt(orderPersons.value);
            if (!isNaN(persons) && persons > 0) {
                orderTotalPrice.value = (currentItemPrice * persons).toFixed(2);
            } else {
                orderTotalPrice.value = currentItemPrice.toFixed(2);
            }
        });

        orderServiceForm.addEventListener('submit', function(e) {
            e.preventDefault();
            orderServiceMessage.innerHTML = '<div class="alert alert-info">Enviando pedido...</div>';

            const formData = {
                action: 'create_order',
                tipo_proveedor: orderProviderType.value,
                id_proveedor: orderProviderId.value,
                id_servicio_o_menu: orderItemId.value,
                tipo_item: orderItemType.value,
                id_itinerario: null, // No se usa en este contexto directamente
                fecha_servicio: orderDate.value,
                cantidad_personas: orderPersons.value,
                precio_unitario: currentItemPrice,
                precio_total: orderTotalPrice.value,
                id_destino_asociado: orderDestinationId.value // Enviar el id_destino
            };

            fetch('api/pedidos.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    orderServiceMessage.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                    orderServiceForm.reset();
                    // Optionally close modal or refresh page
                    setTimeout(() => {
                        const modal = bootstrap.Modal.getInstance(orderServiceModal);
                        modal.hide();
                        // location.reload(); // Recargar si es necesario
                    }, 2000);
                } else {
                    orderServiceMessage.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                orderServiceMessage.innerHTML = '<div class="alert alert-danger">Hubo un error de conexión. Por favor, inténtalo de nuevo más tarde.</div>';
            });
        });
    }
});



<div class="modal fade" id="selectGuiaModal" tabindex="-1" aria-labelledby="selectGuiaModalLabel" aria-hidden="true">

  <div class="modal-dialog modal-dialog-centered">

    <div class="modal-content">

      <div class="modal-header">

        <h5 class="modal-title" id="selectGuiaModalLabel">Seleccionar Guía para <?= htmlspecialchars($destino['nombre']) ?></h5>

        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

      </div>

      <div class="modal-body">

        <?php if (count($available_guias) > 0): ?>

            <p>Guías disponibles en <?= htmlspecialchars($destino['ciudad']) ?> que ofrecen este destino:</p>

            <div class="list-group">

                <?php foreach ($available_guias as $guia): ?>

                                        <button type="button" class="list-group-item list-group-item-action" data-bs-toggle="modal" data-bs-target="#orderServiceModal"

                                                data-id="<?= htmlspecialchars($guia['id']) ?>"

                                                data-type="guia_destino"

                                                data-price="<?= htmlspecialchars($guia['precio_hora']) ?>"

                                                data-provider-type="guia"

                                                data-provider-id="<?= htmlspecialchars($guia['id']) ?>"

                                                data-name="<?= htmlspecialchars($guia['nombre_guia']) ?> (<?= htmlspecialchars($destino['nombre']) ?>)"

                                                data-id-destino-asociado="<?= htmlspecialchars($id_destino) ?>">

                        <div class="d-flex w-100 justify-content-between">

                            <h5 class="mb-1"><?= htmlspecialchars($guia['nombre_guia']) ?></h5>

                            <small><?= htmlspecialchars(number_format($guia['precio_hora'], 2)) ?> €/hora</small>

                        </div>

                        <p class="mb-1"><small class="text-muted">Especialidades: <?= htmlspecialchars($guia['especialidades']) ?></small></p>

                    </button>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="alert alert-info text-center">No hay guías disponibles para este destino en esta ciudad.</div>

        <?php endif; ?>

      </div>

    </div>

  </div>

</div>



<?php require_once 'includes/footer.php'; ?>
