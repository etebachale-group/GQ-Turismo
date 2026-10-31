<?php require_once 'includes/header.php'; ?>

<!-- Hero Editorial Contacto -->
<header class="catalog-hero" style="background: radial-gradient(circle at 80% 20%, rgba(200, 149, 79, 0.25) 0%, transparent 50%), radial-gradient(circle at 10% 80%, rgba(23, 97, 58, 0.95) 0%, rgba(13, 56, 33, 0.98) 100%); color: #fff; padding: 4.5rem 0 3.5rem 0; border-bottom: 1px solid rgba(255,255,255,0.08);">
    <div class="container text-center">
        <div class="d-inline-flex align-items-center gap-2 px-3 py-1 mb-3 rounded-pill" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); backdrop-filter: blur(8px);">
            <i class="bi bi-headset" style="color: var(--c-arena-400, #E0B470);"></i>
            <span style="font-size: 0.85rem; letter-spacing: 0.05em; text-transform: uppercase; font-weight: 600;">Atención al Viajero 24/7</span>
        </div>
        <h1 style="font-family: var(--font-display); font-size: clamp(2.4rem, 4.5vw, 3.6rem); font-weight: 700; line-height: 1.15; margin-bottom: 1rem;">
            Estamos Aquí para Asesorarte
        </h1>
        <p class="lead mx-auto" style="color: rgba(255,255,255,0.85); max-width: 680px;">
            ¿Preguntas sobre requisitos de visado, traslados entre islas o recomendaciones personalizadas para tu expedición? Escríbenos y un experto local te responderá.
        </p>
    </div>
</header>

<main class="container py-5">
    <div class="row g-5">
        <!-- Formulario de Contacto -->
        <div class="col-lg-7" data-aos="fade-right">
            <div class="card border-0 shadow-sm p-4 p-md-5" style="border-radius: 20px; background: #fff; border: 1px solid rgba(0,0,0,0.06) !important;">
                <h2 class="h4 fw-bold mb-3" style="font-family: var(--font-display); color: var(--c-selva-900);">
                    Envíanos tu Consulta
                </h2>
                <p class="text-muted small mb-4">Te responderemos por correo o teléfono en menos de 24 horas laborables.</p>

                <form id="contact-form">
                    <div class="mb-3">
                        <label for="name" class="form-label small fw-bold text-dark">
                            <i class="bi bi-person me-1 text-success"></i> Nombre Completo *
                        </label>
                        <input type="text" class="form-control rounded-pill bg-light py-2" id="name" name="name" placeholder="Ej: Santiago Ondo" required>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label small fw-bold text-dark">
                            <i class="bi bi-envelope me-1 text-primary"></i> Correo Electrónico *
                        </label>
                        <input type="email" class="form-control rounded-pill bg-light py-2" id="email" name="email" placeholder="viajero@ejemplo.com" required>
                    </div>

                    <div class="mb-3">
                        <label for="subject" class="form-label small fw-bold text-dark">
                            <i class="bi bi-tag me-1 text-warning"></i> Motivo de la Consulta
                        </label>
                        <select class="form-select rounded-pill bg-light py-2" id="subject" name="subject">
                            <option selected>Información General de Viaje</option>
                            <option>Dudas sobre Visado Electrónico (e-Visa)</option>
                            <option>Reserva de Guías y Destinos</option>
                            <option>Transporte Bioko &harr; Río Muni</option>
                            <option>Registro de Proveedores o Agencias</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="message" class="form-label small fw-bold text-dark">
                            <i class="bi bi-chat-dots me-1 text-success"></i> Mensaje Detallado *
                        </label>
                        <textarea class="form-control rounded-4 bg-light p-3" id="message" name="message" rows="5" placeholder="Cuéntanos fechas estimadas, número de personas o dudas específicas..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg rounded-pill w-100 fw-bold shadow-sm py-3">
                        <i class="bi bi-send-fill me-2"></i>Enviar Mensaje
                    </button>
                </form>
                
                <div id="form-message" class="mt-3"></div>
            </div>
        </div>

        <!-- Sidebar Información Oficial y WhatsApp -->
        <div class="col-lg-5" data-aos="fade-left">
            <div class="sticky-lg-top" style="top: 2rem;">
                <!-- Tarjeta WhatsApp Inmediato -->
                <div class="card border-0 shadow-sm p-4 mb-4 text-white" style="border-radius: 20px; background: linear-gradient(135deg, #128C7E 0%, #075E54 100%);">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center bg-white text-success" style="width: 48px; height: 48px;">
                            <i class="bi bi-whatsapp fs-3"></i>
                        </div>
                        <div>
                            <h3 class="h5 fw-bold mb-0">Atención WhatsApp Directa</h3>
                            <small class="text-white-50">Respuesta rápida en minutos</small>
                        </div>
                    </div>
                    <p class="small text-white-50 mb-3">¿Necesitas asistencia en tiempo real durante tu viaje o para resolver una duda urgente sobre traslados?</p>
                    <a href="https://wa.me/233208870387?text=Hola,%20deseo%20información%20sobre%20tours%20en%20GQ-Turismo" 
                       target="_blank" rel="noopener noreferrer" class="btn btn-light rounded-pill w-100 fw-bold text-success py-2">
                        <i class="bi bi-whatsapp me-2"></i>Chatear por WhatsApp
                    </a>
                </div>

                <!-- Tarjeta Canales Oficiales -->
                <div class="card border-0 shadow-sm p-4" style="border-radius: 20px; background: #fff; border: 1px solid rgba(0,0,0,0.06) !important;">
                    <h3 class="h5 fw-bold mb-4" style="font-family: var(--font-display); color: var(--c-selva-900);">
                        Puntos de Contacto
                    </h3>

                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-success" style="width: 42px; height: 42px; background: var(--c-selva-50);">
                            <i class="bi bi-geo-alt-fill fs-5"></i>
                        </div>
                        <div>
                            <span class="small text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Sede Principal</span>
                            <span class="fw-bold" style="color: var(--c-selva-900);">Malabo, Isla de Bioko</span>
                            <small class="text-muted d-block">Avenida de la Independencia &bull; Guinea Ecuatorial</small>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-primary" style="width: 42px; height: 42px; background: rgba(30, 95, 140, 0.1);">
                            <i class="bi bi-geo-alt-fill fs-5"></i>
                        </div>
                        <div>
                            <span class="small text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Oficina Regional</span>
                            <span class="fw-bold" style="color: var(--c-selva-900);">Bata, Litoral</span>
                            <small class="text-muted d-block">Paseo Marítimo &bull; Región Continental</small>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-warning" style="width: 42px; height: 42px; background: rgba(200, 149, 79, 0.15);">
                            <i class="bi bi-envelope-fill fs-5"></i>
                        </div>
                        <div>
                            <span class="small text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Correo Institucional</span>
                            <a href="mailto:gqturismoguineaecuatorial@gmail.com" class="text-decoration-none fw-semibold" style="color: var(--c-selva-700);">
                                gqturismoguineaecuatorial@gmail.com
                            </a>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-info" style="width: 42px; height: 42px; background: rgba(13, 202, 240, 0.1);">
                            <i class="bi bi-clock-fill fs-5"></i>
                        </div>
                        <div>
                            <span class="small text-muted d-block text-uppercase fw-bold" style="font-size: 0.7rem;">Horario de Asistencia</span>
                            <span class="fw-bold" style="color: var(--c-selva-900);">Lunes a Viernes: 08:30 - 18:00</span>
                            <small class="text-muted d-block">Sábados: 09:00 - 13:00 (GMT+1)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('contact-form');
    const msg = document.getElementById('form-message');

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            msg.innerHTML = '<div class="alert alert-info py-2 rounded-pill text-center">Enviando mensaje...</div>';

            const formData = new FormData(form);
            fetch('api/contact.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                msg.innerHTML = '<div class="alert alert-success py-2 rounded-pill text-center"><i class="bi bi-check-circle me-1"></i> ' + (data.message || 'Mensaje enviado correctamente') + '</div>';
                form.reset();
            })
            .catch(() => {
                msg.innerHTML = '<div class="alert alert-danger py-2 rounded-pill text-center">Error al enviar. Inténtalo por WhatsApp o correo.</div>';
            });
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
