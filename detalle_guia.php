<?php 
require_once 'includes/header.php';
?>
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />

<?php
// 1. Validar el ID que llega por GET
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    echo "<div class='container py-5'><div class='alert alert-danger text-center'>ID de guía no válido o no proporcionado.</div></div>";
    require_once 'includes/footer.php';
    exit();
}

$id_guia = (int)$_GET['id'];

// 2. Obtener datos del guía
$guia = null;
$imagenes = [];
$servicios = [];

if ($conn) {
    $stmt = $conn->prepare("SELECT id, nombre_guia, descripcion, especialidades, precio_hora, contacto_email, contacto_telefono FROM guias_turisticos WHERE id = ?");
    $stmt->bind_param("i", $id_guia);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $guia = $result->fetch_assoc();

        // Obtener imágenes del guía
        $stmt_images = $conn->prepare("SELECT id, ruta_imagen, descripcion FROM imagenes_guia WHERE id_guia = ? ORDER BY fecha_subida DESC");
        $stmt_images->bind_param("i", $id_guia);
        $stmt_images->execute();
        $result_images = $stmt_images->get_result();
        while ($row = $result_images->fetch_assoc()) {
            $imagenes[] = $row;
        }
        $stmt_images->close();

        // Obtener servicios del guía
        $stmt_services = $conn->prepare("SELECT id, nombre_servicio, descripcion, precio FROM servicios_guia WHERE id_guia = ? ORDER BY nombre_servicio ASC");
        $stmt_services->bind_param("i", $id_guia);
        $stmt_services->execute();
        $result_services = $stmt_services->get_result();
        while ($row = $result_services->fetch_assoc()) {
            $servicios[] = $row;
        }
        $stmt_services->close();

    }
    $stmt->close();
} // End of if ($conn) block

// Fetch guide's current location
$guia_location = null;
if ($guia) {
    $stmt_location = $conn->prepare("SELECT current_latitude, current_longitude, last_updated FROM guias_turisticos WHERE id = ?");
    $stmt_location->bind_param("i", $id_guia);
    $stmt_location->execute();
    $result_location = $stmt_location->get_result();
    if ($result_location->num_rows > 0) {
        $guia_location = $result_location->fetch_assoc();
    }
    $stmt_location->close();
}

// Fetch reviews and average rating for the guide
$average_rating = 0;
$total_reviews = 0;
$reviews = [];
$turista_itinerarios = [];

if ($guia) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'http://localhost/GQ-Turismo/api/reviews.php?provider_id=' . $guia['id'] . '&provider_type=guia');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $reviews_json = curl_exec($ch);
    curl_close($ch);

    $reviews_data = json_decode($reviews_json, true);

    if ($reviews_data && $reviews_data['success']) {
        $average_rating = $reviews_data['average_rating'];
        $total_reviews = $reviews_data['total_reviews'];
        $reviews = $reviews_data['reviews'];
    }
}

$conn->close(); // Close connection at the very end of the script
?>

<div class="container py-5">
    <?php if ($guia): ?>
        <div class="row g-5">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3"><?= htmlspecialchars($guia['nombre_guia']) ?></h1>
                <p class="lead text-muted"><?= nl2br(htmlspecialchars($guia['descripcion'])) ?></p>
                <hr>
                <p><i class="bi bi-star-fill me-2 text-warning"></i>Especialidades: <?= htmlspecialchars($guia['especialidades'] ?? 'N/A') ?></p>
                <p><i class="bi bi-cash me-2"></i>Precio por Hora: <?= number_format($guia['precio_hora'], 2) ?> €</p>
                <p><i class="bi bi-envelope me-2"></i>Email: <?= htmlspecialchars($guia['contacto_email']) ?></p>
                <p><i class="bi bi-phone me-2"></i>Teléfono: <?= htmlspecialchars($guia['contacto_telefono'] ?? 'N/A') ?></p>

                <h2 class="mt-5 mb-3">Servicios Ofrecidos</h2>
                <?php if (count($servicios) > 0): ?>
                    <div class="list-group mb-4">
                        <?php foreach ($servicios as $servicio): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1"><?= htmlspecialchars($servicio['nombre_servicio']) ?></h5>
                                    <p class="mb-1 text-muted"><?= htmlspecialchars($servicio['descripcion']) ?></p>
                                </div>
                                <span class="badge bg-primary rounded-pill fs-5"><?= number_format($servicio['precio'], 2) ?> €</span>
                                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'turista'): ?>
                                    <button class="btn btn-sm btn-outline-primary order-service-btn" data-bs-toggle="modal" data-bs-target="#orderServiceModal" data-id="<?= htmlspecialchars($servicio['id']) ?>" data-type="servicio" data-price="<?= htmlspecialchars($servicio['precio']) ?>" data-provider-type="guia" data-provider-id="<?= htmlspecialchars($guia['id']) ?>" data-name="<?= htmlspecialchars($servicio['nombre_servicio']) ?>">Contratar</button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-primary" disabled>Contratar</button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">Este guía no tiene servicios registrados.</p>
                <?php endif; ?>

                <h2 class="mt-5 mb-3">Galería de Imágenes</h2>
                <?php if (count($imagenes) > 0): ?>
                    <div class="row row-cols-1 row-cols-md-2 g-4">
                        <?php foreach ($imagenes as $imagen): ?>
                            <div class="col">
                                <div class="card h-100 shadow-sm">
                                    <img src="assets/img/guias/<?= htmlspecialchars($imagen['ruta_imagen']) ?>" class="card-img-top" alt="<?= htmlspecialchars($imagen['descripcion']) ?>" style="height: 200px; object-fit: cover;">
                                    <div class="card-body">
                                        <p class="card-text"><?= htmlspecialchars($imagen['descripcion']) ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-muted">Este guía no tiene imágenes en su galería.</p>
                <?php endif; ?>

                <h2 class="mt-5 mb-3">Ubicación Actual del Guía</h2>
                <?php if ($guia_location && $guia_location['current_latitude'] && $guia_location['current_longitude']): ?>
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            <div id="map" style="height: 400px; width: 100%;"></div>
                            <p class="card-text mt-3"><small class="text-muted">Última actualización: <?= htmlspecialchars($guia_location['last_updated']) ?></small></p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center">Ubicación del guía no disponible o no actualizada.</div>
                <?php endif; ?>

                <h2 class="mt-5 mb-3">Valoraciones y Reseñas</h2>
                <?php if ($guia): ?>
                    <div class="mb-4">
                        <?php if ($total_reviews > 0): ?>
                            <p class="lead">Puntuación Media: <strong><?= number_format($average_rating, 1) ?> / 5</strong> (basado en <?= $total_reviews ?> valoraciones)</p>
                            <div class="d-flex align-items-center mb-3">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi bi-star<?= ($i <= round($average_rating)) ? '-fill text-warning' : '' ?> me-1"></i>
                                <?php endfor; ?>
                            </div>
                        <?php else: ?>
                            <p class="lead">Este guía aún no tiene valoraciones.</p>
                        <?php endif; ?>
                    </div>

                    <?php if (count($reviews) > 0): ?>
                        <div class="list-group">
                            <?php foreach ($reviews as $review): ?>
                                <div class="list-group-item mb-3">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1"><?= htmlspecialchars($review['reviewer_name']) ?></h5>
                                        <small><?= date('d/m/Y', strtotime($review['timestamp'])) ?></small>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="bi bi-star<?= ($i <= $review['rating']) ? '-fill text-warning' : '' ?> me-1"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <p class="mb-1"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Contactar Guía</h5>
                        <p class="card-text">¿Interesado en los servicios de este guía? Contáctalo directamente.</p>
                        <a href="mailto:<?= htmlspecialchars($guia['contacto_email']) ?>" class="btn btn-primary w-100 mb-2">Enviar Email</a>
                        <?php if ($guia['contacto_telefono']): ?>
                            <a href="tel:<?= htmlspecialchars($guia['contacto_telefono']) ?>" class="btn btn-outline-secondary w-100 mb-2">Llamar</a>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'turista'): ?>
                            <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#sendMessageModal" data-receiver-id="<?= htmlspecialchars($guia['id']) ?>" data-receiver-type="guia" data-receiver-name="<?= htmlspecialchars($guia['nombre_guia']) ?>">Enviar Mensaje</button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <h2 class="fw-bold">Guía no encontrado</h2>
            <p class="text-muted">El guía que buscas no existe o no está disponible en este momento.</p>
            <a href="guias.php" class="btn btn-primary mt-3">Volver a la lista de Guías</a>
        </div>
    <?php endif; ?>
</div>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const orderServiceModal = document.getElementById('orderServiceModal');
    const modalItemName = document.getElementById('modalItemName');
    const orderItemId = document.getElementById('orderItemId');
    const orderItemType = document.getElementById('orderItemType');
    const orderItemPrice = document.getElementById('orderItemPrice');
    const orderProviderType = document.getElementById('orderProviderType');
    const orderProviderId = document.getElementById('orderProviderId');
    const orderDate = document.getElementById('orderDate');
    const orderPersons = document.getElementById('orderPersons');
    const orderTotalPrice = document.getElementById('orderTotalPrice');
    const orderItinerary = document.getElementById('orderItinerary'); // Nuevo
    const orderServiceForm = document.getElementById('orderServiceForm');
    const orderServiceMessage = document.getElementById('orderServiceMessage');

    let currentItemPrice = 0;

    orderServiceModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget; // Button that triggered the modal
        modalItemName.textContent = button.dataset.name;
        orderItemId.value = button.dataset.id;
        orderItemType.value = button.dataset.type;
        orderItemPrice.value = button.dataset.price;
        orderProviderType.value = button.dataset.providerType;
        orderProviderId.value = button.dataset.providerId;
        
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

        // Reset itinerary selection
        if (orderItinerary) {
            orderItinerary.value = "";
        }
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
        orderServiceMessage.innerHTML = '<div class="alert alert-info">Enviando solicitud...</div>';

        const formData = {
            action: 'create_order',
            tipo_proveedor: orderProviderType.value,
            id_proveedor: orderProviderId.value,
            id_servicio_o_menu: orderItemId.value,
            tipo_item: orderItemType.value,
            fecha_servicio: orderDate.value,
            cantidad_personas: orderPersons.value,
            precio_unitario: currentItemPrice,
            precio_total: orderTotalPrice.value
        };

        // Añadir id_itinerario si está seleccionado
        if (orderItinerary && orderItinerary.value) {
            formData.id_itinerario = orderItinerary.value;
        }

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
            } else {
                orderServiceMessage.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            orderServiceMessage.innerHTML = '<div class="alert alert-danger">Hubo un error de conexión. Por favor, inténtalo de nuevo más tarde.</div>';
        });
    });

    // Lógica para el modal de enviar mensaje
    const sendMessageModal = document.getElementById('sendMessageModal');
    const modalReceiverName = document.getElementById('modalReceiverName');
    const messageReceiverId = document.getElementById('messageReceiverId');
    const messageReceiverType = document.getElementById('messageReceiverType');
    const messageContent = document.getElementById('messageContent');
    const sendMessageForm = document.getElementById('sendMessageForm');
    const sendMessageResponse = document.getElementById('sendMessageResponse');

    if (sendMessageModal) {
        sendMessageModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            modalReceiverName.textContent = button.dataset.receiverName;
            messageReceiverId.value = button.dataset.receiverId;
            messageReceiverType.value = button.dataset.receiverType;
            messageContent.value = ''; // Clear previous message
            sendMessageResponse.innerHTML = ''; // Clear previous response
        });

        sendMessageForm.addEventListener('submit', function(e) {
            e.preventDefault();
            sendMessageResponse.innerHTML = '<div class="alert alert-info">Enviando mensaje...</div>';

            const formData = {
                receiver_id: messageReceiverId.value,
                receiver_type: messageReceiverType.value,
                message: messageContent.value
            };

            fetch('api/start_conversation.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    sendMessageResponse.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                    sendMessageForm.reset();
                    // Redirect to messages page after a short delay
                    setTimeout(() => {
                        window.location.href = data.redirect || 'mis_mensajes.php';
                    }, 2000);
                } else {
                    sendMessageResponse.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                sendMessageResponse.innerHTML = '<div class="alert alert-danger">Hubo un error de conexión. Por favor, inténtalo de nuevo más tarde.</div>';
            });
        });
    }
    // Lógica para el mapa de ubicación del guía
    const mapElement = document.getElementById('map');
    <?php if ($guia_location && $guia_location['current_latitude'] && $guia_location['current_longitude']): ?>
        if (mapElement) {
            const latitude = <?= $guia_location['current_latitude'] ?>;
            const longitude = <?= $guia_location['current_longitude'] ?>;
            const map = L.map('map').setView([latitude, longitude], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            L.marker([latitude, longitude]).addTo(map)
                .bindPopup('<b>Ubicación actual del guía</b><br>Última actualización: <?= htmlspecialchars($guia_location['last_updated']) ?>')
                .openPopup();
        }
    <?php endif; ?>
});
</script>

<?php require_once 'includes/footer.php'; ?>

<!-- Modal para Contratar Servicio -->
<div class="modal fade" id="orderServiceModal" tabindex="-1" aria-labelledby="orderServiceModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="orderServiceModalLabel">Contratar <span id="modalItemName"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="orderServiceForm">
          <input type="hidden" id="orderItemId" name="item_id">
          <input type="hidden" id="orderItemType" name="item_type">
          <input type="hidden" id="orderItemPrice" name="item_price">
          <input type="hidden" id="orderProviderType" name="provider_type">
          <input type="hidden" id="orderProviderId" name="provider_id">
          
          <div class="mb-3">
            <label for="orderDate" class="form-label">Fecha del Servicio</label>
            <input type="date" class="form-control" id="orderDate" name="order_date" required>
          </div>
          <div class="mb-3">
            <label for="orderPersons" class="form-label">Número de Horas (para guías)</label>
            <input type="number" class="form-control" id="orderPersons" name="order_persons" value="1" min="1" required>
          </div>
          <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'turista' && count($turista_itinerarios) > 0): ?>
          <div class="mb-3">
            <label for="orderItinerary" class="form-label">Asociar a Itinerario (Opcional)</label>
            <select class="form-select" id="orderItinerary" name="order_itinerary">
              <option value="">Ninguno</option>
              <?php foreach ($turista_itinerarios as $itinerario): ?>
                <option value="<?= htmlspecialchars($itinerario['id']) ?>"><?= htmlspecialchars($itinerario['nombre_itinerario']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php endif; ?>
          <div class="mb-3">
            <label for="orderTotalPrice" class="form-label">Precio Total</label>
            <input type="text" class="form-control" id="orderTotalPrice" name="order_total_price" readonly>
          </div>
          <div id="orderServiceMessage" class="mt-3"></div>
          <button type="submit" class="btn btn-primary w-100">Confirmar Contratación</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Modal para Enviar Mensaje -->
<div class="modal fade" id="sendMessageModal" tabindex="-1" aria-labelledby="sendMessageModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="sendMessageModalLabel">Enviar Mensaje a <span id="modalReceiverName"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="sendMessageForm">
          <input type="hidden" id="messageReceiverId" name="receiver_id">
          <input type="hidden" id="messageReceiverType" name="receiver_type">
          <div class="mb-3">
            <label for="messageContent" class="form-label">Tu Mensaje</label>
            <textarea class="form-control" id="messageContent" name="message_content" rows="5" required></textarea>
          </div>
          <div id="sendMessageResponse" class="mt-3"></div>
          <button type="submit" class="btn btn-primary w-100">Enviar</button>
        </form>
      </div>
    </div>
  </div>
</div>
