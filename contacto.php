<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="py-5" style="background: linear-gradient(135deg, #667eea 0%, #17a2b8 100%);" data-aos="fade-in">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-3 fw-bold mb-3 text-white">Contáctanos</h1>
                <p class="lead text-white">¿Tienes preguntas? Estamos aquí para ayudarte a planificar tu aventura perfecta en Guinea Ecuatorial.</p>
            </div>
            <div class="col-lg-6 text-center">
                <i class="bi bi-chat-dots-fill text-white" style="font-size: 8rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
</section>

<div class="container py-5">
    <div class="row g-5">
        <!-- Formulario de Contacto -->
        <div class="col-lg-7" data-aos="fade-right">
            <div class="card border-0 shadow-lg">
                <div class="card-body p-4 p-md-5">
                    <h3 class="fw-bold mb-4">Envíanos un Mensaje</h3>
                    <form id="contact-form">
                        <div class="mb-4">
                            <label for="name" class="form-label fw-semibold">
                                <i class="bi bi-person-fill text-primary me-2"></i>Nombre Completo
                            </label>
                            <input type="text" class="form-control form-control-lg" id="name" name="name" placeholder="Ej: Juan Pérez" required>
                        </div>
                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold">
                                <i class="bi bi-envelope-fill text-primary me-2"></i>Correo Electrónico
                            </label>
                            <input type="email" class="form-control form-control-lg" id="email" name="email" placeholder="tu@email.com" required>
                        </div>
                        <div class="mb-4">
                            <label for="subject" class="form-label fw-semibold">
                                <i class="bi bi-tag-fill text-primary me-2"></i>Asunto
                            </label>
                            <select class="form-select form-select-lg" id="subject" name="subject">
                                <option selected>Consulta General</option>
                                <option>Información sobre Destinos</option>
                                <option>Reservas y Pagos</option>
                                <option>Problemas Técnicos</option>
                                <option>Sugerencias</option>
                                <option>Otro</option>
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="message" class="form-label fw-semibold">
                                <i class="bi bi-chat-left-text-fill text-primary me-2"></i>Mensaje
                            </label>
                            <textarea class="form-control form-control-lg" id="message" name="message" rows="6" placeholder="Escribe tu mensaje aquí..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-send-fill me-2"></i>Enviar Mensaje
                        </button>
                    </form>
                    <div id="form-message" class="mt-3"></div>
                </div>
            </div>
        </div>

        <!-- Información de Contacto -->
        <div class="col-lg-5" data-aos="fade-left">
            <div class="sticky-lg-top" style="top: 2rem;">
                <h3 class="fw-bold mb-4">Información de Contacto</h3>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-start mb-4">
                            <div class="flex-shrink-0">
                                <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                                    <i class="bi bi-geo-alt-fill text-primary fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="fw-bold mb-2">Dirección</h5>
                                <p class="text-muted mb-0">Malabo, Guinea Ecuatorial<br>Calle Principal, Edificio GQ-Turismo</p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start mb-4">
                            <div class="flex-shrink-0">
                                <div class="bg-success bg-opacity-10 rounded-circle p-3">
                                    <i class="bi bi-telephone-fill text-success fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="fw-bold mb-2">Teléfono</h5>
                                <p class="text-muted mb-0">+233 20 887 0387<br>Lun - Vie: 9:00 - 18:00</p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start mb-4">
                            <div class="flex-shrink-0">
                                <div class="bg-info bg-opacity-10 rounded-circle p-3">
                                    <i class="bi bi-envelope-fill text-info fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="fw-bold mb-2">Email</h5>
                                <p class="text-muted mb-0">
                                    <a href="mailto:gqturismoguineaecuatorial@gmail.com" class="text-decoration-none">gqturismoguineaecuatorial@gmail.com</a><br>
                                    <a href="mailto:etebachalegroup@gmail.com" class="text-decoration-none">etebachalegroup@gmail.com</a>
                                </p>
                            </div>
                        </div>

                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0">
                                <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                                    <i class="bi bi-clock-fill text-warning fs-4"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="fw-bold mb-2">Horario</h5>
                                <p class="text-muted mb-0">
                                    Lunes - Viernes: 9:00 - 18:00<br>
                                    Sábado: 10:00 - 14:00<br>
                                    Domingo: Cerrado
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm bg-primary text-white">
                    <div class="card-body p-4 text-center">
                        <h5 class="fw-bold mb-3">Síguenos en Redes Sociales</h5>
                        <div class="d-flex gap-3 justify-content-center">
                            <a href="#" class="btn btn-light btn-sm rounded-circle" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-facebook fs-5"></i>
                            </a>
                            <a href="#" class="btn btn-light btn-sm rounded-circle" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-instagram fs-5"></i>
                            </a>
                            <a href="#" class="btn btn-light btn-sm rounded-circle" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-twitter fs-5"></i>
                            </a>
                            <a href="#" class="btn btn-light btn-sm rounded-circle" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-linkedin fs-5"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mt-4 border-0 shadow-sm" role="alert">
                    <div class="d-flex align-items-start">
                        <i class="bi bi-info-circle-fill me-3 fs-4"></i>
                        <div>
                            <h6 class="alert-heading fw-bold mb-2">Tiempo de Respuesta</h6>
                            <p class="mb-0 small">Normalmente respondemos en menos de 24 horas durante días laborables.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FAQ Section -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="text-center mb-5" data-aos="fade-up">
            <h2 class="display-5 fw-bold">Preguntas Frecuentes</h2>
            <p class="lead text-muted">Quizás encuentres tu respuesta aquí</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                ¿Cómo puedo reservar un itinerario?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Regístrate como turista, explora nuestros destinos y crea tu itinerario personalizado. Luego podrás convertirlo en una reserva y contratar servicios adicionales.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                ¿Puedo cancelar mi reserva?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Sí, puedes cancelar tu reserva desde tu panel de usuario. Las políticas de cancelación varían según el proveedor del servicio.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item border-0 shadow-sm mb-3">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                ¿Los guías están verificados?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Sí, todos nuestros guías turísticos y proveedores están verificados y cuentan con las licencias necesarias para operar.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
