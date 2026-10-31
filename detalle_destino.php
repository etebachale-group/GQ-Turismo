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
    SELECT id, nombre, imagen, categoria, precio, ciudad
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

$hero_img = !empty($destino['imagen']) ? 'assets/img/destinos/' . htmlspecialchars($destino['imagen']) : 'assets/img/destinos/default.jpg';
$precio_num = (float)($destino['precio']);
$precio_eur = round($precio_num / 655.957, 1);
?>

<!-- Hero Editorial Destino -->
<section class="position-relative text-white" style="background: linear-gradient(180deg, rgba(13, 56, 33, 0.45) 0%, rgba(13, 56, 33, 0.88) 100%), url('<?= $hero_img ?>') center/cover no-repeat; min-height: 440px; padding: 4.5rem 0 3.5rem 0; display: flex; align-items: flex-end;">
    <div class="container position-relative" style="z-index: 2;">
        <!-- Breadcrumb Glass -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0" style="background: rgba(0,0,0,0.3); backdrop-filter: blur(8px); display: inline-flex; padding: 0.4rem 1rem; border-radius: 50px;">
                <li class="breadcrumb-item"><a href="index.php" class="text-white-50 text-decoration-none">Inicio</a></li>
                <li class="breadcrumb-item"><a href="destinos.php" class="text-white-50 text-decoration-none">Destinos</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page"><?= htmlspecialchars($destino['nombre']); ?></li>
            </ol>
        </nav>

        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge rounded-pill px-3 py-2" style="background: var(--c-selva-700); font-size: 0.85rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase;">
                <i class="bi bi-tag-fill me-1"></i><?= htmlspecialchars($destino['categoria']); ?>
            </span>
            <?php if (!empty($destino['ciudad'])): ?>
                <span class="badge rounded-pill px-3 py-2" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(8px); font-size: 0.85rem;">
                    <i class="bi bi-geo-alt-fill text-warning me-1"></i><?= htmlspecialchars($destino['ciudad']); ?>
                </span>
            <?php endif; ?>
            <span class="badge rounded-pill px-3 py-2" style="background: rgba(200, 149, 79, 0.9); font-size: 0.85rem; font-weight: 600;">
                <i class="bi bi-shield-check me-1"></i>Verificado Oficial
            </span>
        </div>

        <h1 class="display-4 fw-bold mb-3" style="font-family: var(--font-display); line-height: 1.15; max-width: 900px; text-shadow: 0 4px 12px rgba(0,0,0,0.4);">
            <?= htmlspecialchars($destino['nombre']); ?>
        </h1>

        <div class="d-flex align-items-center gap-3">
            <div style="font-size: 1.8rem; font-weight: 800; color: var(--c-arena-300, #F4D396); font-family: var(--font-display);">
                <?= format_currency($precio_num, 'XAF') ?>
            </div>
            <div class="text-white-50" style="font-size: 0.9rem; border-left: 1px solid rgba(255,255,255,0.25); padding-left: 0.75rem;">
                Por persona &bull; &asymp; <?= $precio_eur ?> &euro;
            </div>
        </div>
    </div>
</section>

<main class="container py-5">
    <div class="row g-4">
        <!-- Columna Principal -->
        <div class="col-lg-8">
            <!-- Tarjeta Descripción -->
            <article class="card border-0 shadow-sm p-4 p-md-5 mb-4" style="border-radius: 20px; background: #fff; border: 1px solid rgba(0,0,0,0.06) !important;">
                <h2 class="h4 fw-bold mb-3" style="font-family: var(--font-display); color: var(--c-selva-900);">
                    Sobre esta experiencia
                </h2>
                <div class="text-muted lead fs-6" style="line-height: 1.75; white-space: pre-line;">
                    <?= htmlspecialchars($destino['descripcion']); ?>
                </div>

                <!-- Aspectos Destacados -->
                <div class="row g-3 mt-4 pt-4 border-top">
                    <div class="col-sm-6 col-md-4">
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: var(--c-selva-50, #f0f7f2);">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: var(--c-selva-700); color: #fff;">
                                <i class="bi bi-geo-alt-fill fs-5"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem; font-weight: 700;">Localización</small>
                                <span class="fw-bold" style="color: var(--c-selva-900); font-size: 0.9rem;"><?= htmlspecialchars($destino['ciudad'] ?? 'Guinea Ecuatorial') ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: rgba(200, 149, 79, 0.1);">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: var(--c-arena-600, #A07233); color: #fff;">
                                <i class="bi bi-clock-history fs-5"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem; font-weight: 700;">Duración</small>
                                <span class="fw-bold" style="color: var(--c-selva-900); font-size: 0.9rem;">Día completo</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-4">
                        <div class="d-flex align-items-center gap-3 p-3 rounded-3" style="background: rgba(30, 95, 140, 0.1);">
                            <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #1E5F8C; color: #fff;">
                                <i class="bi bi-person-check-fill fs-5"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block text-uppercase" style="font-size: 0.7rem; font-weight: 700;">Guía</small>
                                <span class="fw-bold" style="color: var(--c-selva-900); font-size: 0.9rem;">Local Verificado</span>
                            </div>
                        </div>
                    </div>
                </div>
            </article>

            <!-- Galería de Imágenes -->
            <?php if (count($gallery_images) > 0): ?>
                <section class="card border-0 shadow-sm p-4 p-md-5 mb-4" style="border-radius: 20px; background: #fff; border: 1px solid rgba(0,0,0,0.06) !important;">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="h4 fw-bold mb-0" style="font-family: var(--font-display); color: var(--c-selva-900);">
                            Galería Fotográfica
                        </h2>
                        <span class="text-muted small"><?= count($gallery_images) ?> capturas</span>
                    </div>
                    <div class="row g-3">
                        <?php foreach ($gallery_images as $image): ?>
                            <div class="col-sm-6 col-md-4">
                                <div class="position-relative overflow-hidden rounded-3 shadow-sm" style="aspect-ratio: 4/3; cursor: pointer; transition: transform 0.25s ease;"
                                     onclick="showImage('assets/img/destinos/<?= htmlspecialchars($image['ruta_imagen']); ?>')"
                                     data-bs-toggle="modal" data-bs-target="#imageModal">
                                    <img src="assets/img/destinos/<?= htmlspecialchars($image['ruta_imagen']); ?>" 
                                         alt="<?= htmlspecialchars($image['descripcion'] ?? 'Fotografía del destino'); ?>" 
                                         class="w-100 h-100 object-fit-cover"
                                         style="transition: transform 0.3s ease;">
                                    <div class="position-absolute bottom-0 start-0 end-0 p-2 text-white text-center" style="background: linear-gradient(0deg, rgba(0,0,0,0.7) 0%, transparent 100%);">
                                        <small style="font-size: 0.75rem;"><i class="bi bi-zoom-in me-1"></i>Ver detalle</small>
                                    </div>
                                </div>
                                <?php if (!empty($image['descripcion'])): ?>
                                    <p class="text-muted small mt-1 mb-0"><?= htmlspecialchars($image['descripcion']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>

            <!-- Mapa si tiene coordenadas -->
            <?php if (!empty($destino['latitude']) && !empty($destino['longitude'])): ?>
                <section class="card border-0 shadow-sm p-4 p-md-5 mb-4" style="border-radius: 20px; background: #fff; border: 1px solid rgba(0,0,0,0.06) !important;">
                    <h2 class="h4 fw-bold mb-3" style="font-family: var(--font-display); color: var(--c-selva-900);">
                        Ubicación Geográfica
                    </h2>
                    <div id="map" class="rounded-3 p-4 text-center" style="background: var(--c-selva-50, #f0f7f2); min-height: 200px;">
                        <i class="bi bi-geo-alt-fill fs-1" style="color: var(--c-selva-700);"></i>
                        <h5 class="mt-2 fw-bold" style="color: var(--c-selva-900);"><?= htmlspecialchars($destino['nombre']); ?></h5>
                        <p class="text-muted small mb-3">Lat: <?= htmlspecialchars($destino['latitude']); ?> | Lng: <?= htmlspecialchars($destino['longitude']); ?></p>
                        <a href="https://www.google.com/maps?q=<?= htmlspecialchars($destino['latitude']); ?>,<?= htmlspecialchars($destino['longitude']); ?>" 
                           target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-success rounded-pill px-4">
                            <i class="bi bi-map me-1"></i>Abrir en Google Maps
                        </a>
                    </div>
                </section>
            <?php endif; ?>
        </div>

        <!-- Columna Lateral Sticky -->
        <aside class="col-lg-4">
            <!-- Aviso e-Visa para turistas extranjeros (Requisito VI-03) -->
            <div class="card border-0 shadow-sm p-4 mb-4 text-center" style="background: linear-gradient(135deg, var(--c-selva-50, #f0f7f2) 0%, #fff 100%); border-radius: 20px; border: 1px solid var(--c-selva-100, #e3f1e9) !important;" data-aos="fade-left">
                <span class="badge rounded-pill mx-auto mb-2 px-3 py-1" style="background: var(--c-selva-700); font-size: 0.75rem; letter-spacing: 0.05em; text-transform: uppercase;">
                    Visado Oficial
                </span>
                <h3 class="h5 fw-bold mb-2" style="font-family: var(--font-display); color: var(--c-selva-900);">
                    Aviso e-Visa Oficial
                </h3>
                <p class="text-muted small mb-3">
                    Si eres viajero internacional, tramita con antelación tu e-Visa oficial para ingresar a la República de Guinea Ecuatorial.
                </p>
                <?= boton_evisa('es', 'btn btn-primary rounded-pill w-100 py-2 fw-semibold') ?>
            </div>

            <!-- Card de Reserva Sticky -->
            <div class="card border-0 shadow-sm p-4 sticky-top mb-4" style="top: 24px; border-radius: 20px; background: #fff; border: 1px solid rgba(0,0,0,0.06) !important;">
                <div class="d-flex justify-content-between align-items-baseline mb-3">
                    <div>
                        <span class="text-muted text-uppercase small d-block fw-bold" style="letter-spacing: 0.05em; font-size: 0.7rem;">Tarifa desde</span>
                        <span class="fw-bold" style="font-size: 1.8rem; color: var(--c-selva-700); font-family: var(--font-display);">
                            <?= format_currency($precio_num, 'XAF') ?>
                        </span>
                    </div>
                    <span class="text-muted small">&asymp; <?= $precio_eur ?> &euro;</span>
                </div>

                <div class="p-3 rounded-3 mb-4" style="background: var(--c-arena-50, #FDF9F2); border: 1px dashed var(--c-arena-300, #F4D396);">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-shield-check text-success"></i>
                        <span class="small fw-bold" style="color: var(--c-selva-900);">Pago Seguro Local</span>
                    </div>
                    <small class="text-muted d-block" style="font-size: 0.75rem;">
                        Aceptamos efectivo al inicio de la actividad o transferencia directa local en FCFA (XAF).
                    </small>
                </div>

                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'turista'): ?>
                    <div class="d-grid gap-2">
                        <a href="reservas.php?destino=<?= $destino['id']; ?>" class="btn btn-primary rounded-pill py-3 fw-bold">
                            <i class="bi bi-calendar-check me-2"></i>Reservar Esta Experiencia
                        </a>
                        <a href="crear_itinerario.php?destino=<?= $destino['id']; ?>" class="btn btn-outline-secondary rounded-pill py-2 fw-semibold">
                            <i class="bi bi-journal-plus me-2"></i>Añadir a mi Itinerario
                        </a>
                    </div>
                <?php else: ?>
                    <div class="d-grid gap-2">
                        <a href="index.php#login" class="btn btn-primary rounded-pill py-3 fw-bold">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Inicia Sesión para Reservar
                        </a>
                        <a href="registro.php" class="btn btn-outline-secondary rounded-pill py-2">
                            Crear Cuenta de Viajero
                        </a>
                    </div>
                <?php endif; ?>

                <hr class="my-4">

                <div class="d-flex flex-column gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-success" style="width: 36px; height: 36px; background: rgba(23, 97, 58, 0.1);">
                            <i class="bi bi-patch-check-fill fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold small" style="color: var(--c-selva-900);">Operador Verificado</div>
                            <small class="text-muted" style="font-size: 0.75rem;">Supervisado por Turismo de Guinea Ecuatorial</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-primary" style="width: 36px; height: 36px; background: rgba(30, 95, 140, 0.1);">
                            <i class="bi bi-arrow-repeat fs-5"></i>
                        </div>
                        <div>
                            <div class="fw-bold small" style="color: var(--c-selva-900);">Cancelación Flexible</div>
                            <small class="text-muted" style="font-size: 0.75rem;">Hasta 48 horas antes sin penalización</small>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </div>

    <!-- Guías Recomendados -->
    <?php if (count($guias_recomendados) > 0): ?>
        <section class="mt-5 pt-4 border-top">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 fw-bold mb-1" style="font-family: var(--font-display); color: var(--c-selva-900);">
                        Guías Oficiales para este Destino
                    </h2>
                    <p class="text-muted small mb-0">Profesionales certificados con amplio conocimiento de la zona</p>
                </div>
            </div>
            <div class="row g-4">
                <?php foreach ($guias_recomendados as $guia): 
                    $avatar = !empty($guia['imagen_perfil']) ? 'assets/img/guias/' . htmlspecialchars($guia['imagen_perfil']) : 'assets/img/default-avatar.svg';
                ?>
                    <div class="col-md-4">
                        <article class="card h-100 border-0 shadow-sm p-4 text-center" style="border-radius: 16px; background: #fff; border: 1px solid rgba(0,0,0,0.06) !important;">
                            <img src="<?= $avatar ?>" alt="<?= htmlspecialchars($guia['nombre_guia']) ?>" 
                                 class="rounded-circle mx-auto mb-3 object-fit-cover shadow-sm" style="width: 80px; height: 80px;"
                                 onerror="this.src='assets/img/default-avatar.svg'">
                            <h3 class="h6 fw-bold mb-1" style="color: var(--c-selva-900);"><?= htmlspecialchars($guia['nombre_guia']); ?></h3>
                            <span class="badge rounded-pill mb-2 px-3 py-1" style="background: var(--c-selva-50); color: var(--c-selva-800); font-size: 0.75rem;">
                                <?= htmlspecialchars($guia['especialidades']); ?>
                            </span>
                            <p class="text-muted small mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <?= htmlspecialchars($guia['descripcion'] ?? 'Guía profesional local con experiencia'); ?>
                            </p>
                            <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-auto">
                                <span class="fw-bold" style="color: var(--c-selva-700);">
                                    <?= format_currency((float)$guia['precio_hora'], 'XAF'); ?>/h
                                </span>
                                <a href="detalle_guia.php?id=<?= $guia['id']; ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    Ver Perfil
                                </a>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <!-- Destinos Similares -->
    <?php if (count($destinos_similares) > 0): ?>
        <section class="mt-5 pt-4 border-top">
            <h2 class="h4 fw-bold mb-4" style="font-family: var(--font-display); color: var(--c-selva-900);">
                Otros Santuarios y Destinos que te pueden gustar
            </h2>
            <div class="row g-4">
                <?php foreach ($destinos_similares as $similar): 
                    $sim_img = !empty($similar['imagen']) ? 'assets/img/destinos/' . htmlspecialchars($similar['imagen']) : 'assets/img/destinos/default.jpg';
                ?>
                    <div class="col-md-4">
                        <article class="card-experience h-100 d-flex flex-column" style="background: #fff; border-radius: 16px; overflow: hidden; border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 4px 16px rgba(0,0,0,0.04);">
                            <div class="position-relative overflow-hidden" style="aspect-ratio: 16 / 10;">
                                <img src="<?= $sim_img ?>" alt="<?= htmlspecialchars($similar['nombre']) ?>" 
                                     class="w-100 h-100 object-fit-cover" loading="lazy"
                                     onerror="this.src='assets/img/destinos/default.jpg'">
                                <span class="position-absolute top-0 start-0 m-3 px-3 py-1 rounded-pill text-white" 
                                      style="background: rgba(23, 97, 58, 0.85); backdrop-filter: blur(8px); font-size: 0.75rem; font-weight: 600;">
                                    <?= htmlspecialchars($similar['categoria']); ?>
                                </span>
                            </div>
                            <div class="p-4 d-flex flex-column flex-grow-1 justify-content-between">
                                <h3 class="h6 fw-bold mb-2" style="font-family: var(--font-display); color: var(--c-selva-900);">
                                    <a href="detalle_destino.php?id=<?= $similar['id'] ?>" class="text-decoration-none text-reset">
                                        <?= htmlspecialchars($similar['nombre']); ?>
                                    </a>
                                </h3>
                                <div class="pt-3 border-top d-flex justify-content-between align-items-center mt-2">
                                    <span class="fw-bold" style="color: var(--c-selva-700);">
                                        <?= format_currency((float)$similar['precio'], 'XAF'); ?>
                                    </span>
                                    <a href="detalle_destino.php?id=<?= $similar['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        Detalles
                                    </a>
                                </div>
                            </div>
                        </article>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</main>

<!-- Modal para ver imágenes ampliadas -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0" style="background: transparent;">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body text-center p-2">
                <img id="modalImage" src="" class="img-fluid rounded-4 shadow-lg" alt="Imagen ampliada" style="max-height: 85vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

<script>
function showImage(src) {
    const el = document.getElementById('modalImage');
    if (el) el.src = src;
}
</script>

<?php require_once 'includes/footer.php'; ?>
