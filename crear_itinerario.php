<?php
require_once 'includes/header.php';
require_once 'includes/db_connect.php';

// Verificar que sea turista
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'turista') {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$edit_mode = false;
$itinerario_data = null;
$destinos_seleccionados = [];

// Modo edición
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_mode = true;
    $itinerario_id = (int)$_GET['edit'];
    
    $stmt = $conn->prepare("SELECT * FROM itinerarios WHERE id = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $itinerario_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $itinerario_data = $result->fetch_assoc();
        
        $stmt_destinos = $conn->prepare("SELECT id_destino FROM itinerario_destinos WHERE id_itinerario = ? ORDER BY orden ASC");
        $stmt_destinos->bind_param("i", $itinerario_id);
        $stmt_destinos->execute();
        $result_destinos = $stmt_destinos->get_result();
        
        while ($row = $result_destinos->fetch_assoc()) {
            $destinos_seleccionados[] = $row['id_destino'];
        }
        $stmt_destinos->close();
    }
    $stmt->close();
}

// Obtener destinos
$destinos = [];
$sql = "SELECT id, nombre, descripcion, imagen, categoria, precio, ciudad FROM destinos ORDER BY nombre ASC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $destinos[] = $row;
}

// Obtener categorías
$categorias = [];
$sql_cat = "SELECT DISTINCT categoria FROM destinos WHERE categoria IS NOT NULL ORDER BY categoria ASC";
$result_cat = $conn->query($sql_cat);
while ($row = $result_cat->fetch_assoc()) {
    $categorias[] = $row['categoria'];
}

// Obtener servicios
$guias = [];
$sql_guias = "SELECT id, nombre_guia, especialidades, precio_hora, imagen_perfil FROM guias_turisticos ORDER BY nombre_guia ASC";
$result_guias = $conn->query($sql_guias);
while ($row = $result_guias->fetch_assoc()) {
    $guias[] = $row;
}

$agencias = [];
$sql_agencias = "SELECT id, nombre_agencia, descripcion, imagen_perfil FROM agencias ORDER BY nombre_agencia ASC";
$result_agencias = $conn->query($sql_agencias);
while ($row = $result_agencias->fetch_assoc()) {
    $agencias[] = $row;
}

$locales = [];
$sql_locales = "SELECT id, nombre_local, tipo_local, direccion, imagen_perfil FROM lugares_locales ORDER BY nombre_local ASC";
$result_locales = $conn->query($sql_locales);
while ($row = $result_locales->fetch_assoc()) {
    $locales[] = $row;
}

$conn->close();
?>

<style>
.wizard-container { max-width: 1200px; margin: 0 auto; }
.wizard-steps { display: flex; justify-content: space-between; margin-bottom: 3rem; position: relative; }
.wizard-steps::before { content: ''; position: absolute; top: 25px; left: 0; right: 0; height: 2px; background: #e9ecef; z-index: 0; }
.wizard-step { flex: 1; text-align: center; position: relative; z-index: 1; }
.wizard-step-circle { width: 50px; height: 50px; border-radius: 50%; background: white; border: 3px solid #e9ecef; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-weight: bold; font-size: 1.2rem; color: #6c757d; transition: all 0.3s ease; }
.wizard-step.active .wizard-step-circle { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-color: #667eea; color: white; }
.wizard-step.completed .wizard-step-circle { background: #28a745; border-color: #28a745; color: white; }
.wizard-step-title { font-size: 0.875rem; font-weight: 600; color: #6c757d; }
.wizard-step.active .wizard-step-title { color: #667eea; }
.wizard-content { background: white; border-radius: 15px; padding: 2rem; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
.step-content { display: none; }
.step-content.active { display: block; animation: fadeIn 0.3s ease; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.destino-card, .service-card { border: 2px solid transparent; transition: all 0.3s ease; cursor: pointer; border-radius: 12px; overflow: hidden; }
.destino-card:hover, .service-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
.destino-card.selected, .service-card.selected { border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2); }
.destino-order-badge { position: absolute; top: 10px; left: 10px; width: 35px; height: 35px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 50%; display: none; align-items: center; justify-content: center; font-weight: bold; z-index: 2; }
.selected-items-preview { background: #f8f9fa; border-radius: 10px; padding: 1rem; margin-top: 1rem; }
.filter-chip { padding: 0.5rem 1rem; border: 2px solid #e9ecef; border-radius: 50px; cursor: pointer; transition: all 0.3s ease; background: white; margin: 0.25rem; }
.filter-chip:hover { border-color: #667eea; background: #f8f9fa; }
.filter-chip.active { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-color: #667eea; color: white; }
.summary-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px; padding: 2rem; }
</style>

<div class="container py-5">
    <div class="wizard-container">
        <div class="text-center mb-5" data-aos="fade-down">
            <h1 class="display-4 fw-bold">
                <i class="bi bi-map-fill me-3"></i>
                <?php echo $edit_mode ? 'Editar Itinerario' : 'Crear Nuevo Itinerario'; ?>
            </h1>
            <p class="lead text-muted">Planifica tu aventura perfecta paso a paso</p>
        </div>

        <div class="wizard-steps" data-aos="fade-up">
            <div class="wizard-step active" data-step="1">
                <div class="wizard-step-circle">1</div>
                <div class="wizard-step-title">Información</div>
            </div>
            <div class="wizard-step" data-step="2">
                <div class="wizard-step-circle">2</div>
                <div class="wizard-step-title">Destinos</div>
            </div>
            <div class="wizard-step" data-step="3">
                <div class="wizard-step-circle">3</div>
                <div class="wizard-step-title">Servicios</div>
            </div>
            <div class="wizard-step" data-step="4">
                <div class="wizard-step-circle">4</div>
                <div class="wizard-step-title">Confirmar</div>
            </div>
        </div>

        <div class="wizard-content" data-aos="fade-up">
            <form id="itinerarioForm">
                <input type="hidden" name="itinerario_id" value="<?php echo $edit_mode ? $itinerario_data['id'] : ''; ?>">
                
                <!-- Paso 1 -->
                <div class="step-content active" data-step="1">
                    <h3 class="mb-4"><i class="bi bi-info-circle-fill text-primary me-2"></i>Información Básica</h3>
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre del Itinerario *</label>
                            <input type="text" class="form-control form-control-lg" name="nombre_itinerario" 
                                   value="<?php echo $edit_mode ? htmlspecialchars($itinerario_data['nombre_itinerario']) : ''; ?>" 
                                   placeholder="Ej: Aventura en Malabo" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Estado</label>
                            <select class="form-select form-select-lg" name="estado">
                                <option value="planificacion" <?php echo ($edit_mode && $itinerario_data['estado'] == 'planificacion') ? 'selected' : ''; ?>>Planificación</option>
                                <option value="confirmado" <?php echo ($edit_mode && $itinerario_data['estado'] == 'confirmado') ? 'selected' : ''; ?>>Confirmado</option>
                                <option value="completado" <?php echo ($edit_mode && $itinerario_data['estado'] == 'completado') ? 'selected' : ''; ?>>Completado</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Fecha de Inicio</label>
                            <input type="date" class="form-control form-control-lg" name="fecha_inicio" 
                                   value="<?php echo $edit_mode ? $itinerario_data['fecha_inicio'] : ''; ?>" 
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Fecha de Fin</label>
                            <input type="date" class="form-control form-control-lg" name="fecha_fin" 
                                   value="<?php echo $edit_mode ? $itinerario_data['fecha_fin'] : ''; ?>" 
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Presupuesto (€)</label>
                            <input type="number" class="form-control form-control-lg" name="presupuesto_estimado" 
                                   value="<?php echo $edit_mode ? $itinerario_data['presupuesto_estimado'] : '0'; ?>" 
                                   step="0.01" min="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ciudad</label>
                            <input type="text" class="form-control form-control-lg" name="ciudad" 
                                   value="<?php echo $edit_mode ? htmlspecialchars($itinerario_data['ciudad']) : ''; ?>" 
                                   placeholder="Malabo">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Notas</label>
                            <textarea class="form-control" name="notas" rows="3"><?php echo $edit_mode ? htmlspecialchars($itinerario_data['notas']) : ''; ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Paso 2 -->
                <div class="step-content" data-step="2">
                    <h3 class="mb-4"><i class="bi bi-geo-alt-fill text-danger me-2"></i>Selecciona Destinos</h3>
                    <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>Haz clic para seleccionar destinos</div>
                    
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <div class="filter-chip active" data-category="all"><i class="bi bi-grid-fill me-2"></i>Todos</div>
                        <?php foreach ($categorias as $cat): ?>
                        <div class="filter-chip" data-category="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="row g-3" id="destinosGrid">
                        <?php foreach ($destinos as $d): ?>
                        <div class="col-md-4 destino-item" data-category="<?php echo htmlspecialchars($d['categoria']); ?>">
                            <div class="card destino-card h-100 <?php echo in_array($d['id'], $destinos_seleccionados) ? 'selected' : ''; ?>" 
                                 data-id="<?php echo $d['id']; ?>" data-nombre="<?php echo htmlspecialchars($d['nombre']); ?>" 
                                 data-precio="<?php echo $d['precio']; ?>">
                                <div class="position-relative">
                                    <img src="<?php echo !empty($d['imagen']) ? 'assets/img/destinos/' . $d['imagen'] : 'assets/img/destinos/default.jpg'; ?>" 
                                         class="card-img-top" style="height: 150px; object-fit: cover;" alt="<?php echo htmlspecialchars($d['nombre']); ?>">
                                    <div class="destino-order-badge">1</div>
                                </div>
                                <div class="card-body">
                                    <h6 class="fw-bold mb-2"><?php echo htmlspecialchars($d['nombre']); ?></h6>
                                    <p class="small text-muted mb-2"><?php echo substr(htmlspecialchars($d['descripcion']), 0, 60); ?>...</p>
                                    <div class="d-flex justify-content-between">
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($d['categoria']); ?></span>
                                        <span class="badge bg-success"><?php echo number_format($d['precio'], 2); ?> €</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="selected-items-preview mt-4" id="destinosPreview" style="display:none;">
                        <h6 class="fw-bold"><i class="bi bi-check-circle text-success me-2"></i>Seleccionados: <span id="destinosCount">0</span></h6>
                        <div id="destinosList"></div>
                        <p class="mb-0 mt-2"><strong>Total: </strong><span class="text-success" id="destinosTotal">0.00 €</span></p>
                    </div>
                </div>

                <!-- Paso 3 -->
                <div class="step-content" data-step="3">
                    <h3 class="mb-4"><i class="bi bi-star-fill text-warning me-2"></i>Servicios Adicionales (Opcional)</h3>
                    
                    <h5 class="mb-3"><i class="bi bi-person-badge me-2"></i>Guías</h5>
                    <div class="row g-3 mb-4">
                        <?php foreach ($guias as $g): ?>
                        <div class="col-md-4">
                            <div class="card service-card h-100" data-type="guia" data-id="<?php echo $g['id']; ?>">
                                <?php if ($g['imagen_perfil']): ?>
                                <img src="assets/img/guias/<?php echo $g['imagen_perfil']; ?>" class="card-img-top" style="height:120px;object-fit:cover;">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h6 class="fw-bold"><?php echo htmlspecialchars($g['nombre_guia']); ?></h6>
                                    <p class="small mb-1"><?php echo htmlspecialchars($g['especialidades']); ?></p>
                                    <p class="text-primary mb-0"><?php echo number_format($g['precio_hora'], 2); ?> €/h</p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <h5 class="mb-3"><i class="bi bi-building me-2"></i>Agencias</h5>
                    <div class="row g-3 mb-4">
                        <?php foreach ($agencias as $a): ?>
                        <div class="col-md-4">
                            <div class="card service-card h-100" data-type="agencia" data-id="<?php echo $a['id']; ?>">
                                <?php if ($a['imagen_perfil']): ?>
                                <img src="assets/img/agencias/<?php echo $a['imagen_perfil']; ?>" class="card-img-top" style="height:120px;object-fit:cover;">
                                <?php endif; ?>
                                <div class="card-body">
                                    <h6 class="fw-bold"><?php echo htmlspecialchars($a['nombre_agencia']); ?></h6>
                                    <p class="small"><?php echo substr(htmlspecialchars($a['descripcion'] ?? ''), 0, 80); ?>...</p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <h5 class="mb-3"><i class="bi bi-shop me-2"></i>Locales</h5>
                    <div class="row g-3">
                        <?php foreach ($locales as $l): ?>
                        <div class="col-md-4">
                            <div class="card service-card h-100" data-type="local" data-id="<?php echo $l['id']; ?>">
                                <?php if ($l['imagen_perfil']): ?>
                                <img src="assets/img/locales/<?php echo $l['imagen_perfil']; ?>" class="card-img-top" style="height:120px;object-fit:cover;">
                                <?php endif; ?>
                                <div class="card-body">
                                    <span class="badge bg-success mb-2"><?php echo htmlspecialchars($l['tipo_local']); ?></span>
                                    <h6 class="fw-bold"><?php echo htmlspecialchars($l['nombre_local']); ?></h6>
                                    <p class="small mb-0"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($l['direccion']); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Paso 4 -->
                <div class="step-content" data-step="4">
                    <h3 class="mb-4"><i class="bi bi-check-circle text-success me-2"></i>Resumen</h3>
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card mb-3">
                                <div class="card-header bg-primary text-white"><h5 class="mb-0">Información</h5></div>
                                <div class="card-body" id="resumenInfo"></div>
                            </div>
                            <div class="card mb-3">
                                <div class="card-header bg-danger text-white"><h5 class="mb-0">Destinos</h5></div>
                                <div class="card-body" id="resumenDestinos"></div>
                            </div>
                            <div class="card">
                                <div class="card-header bg-warning"><h5 class="mb-0">Servicios</h5></div>
                                <div class="card-body" id="resumenServicios"></div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="summary-card">
                                <h5 class="mb-4">Costos</h5>
                                <p>Destinos: <strong id="resTotal1">0 €</strong></p>
                                <p>Presupuesto: <strong id="resTotal2">0 €</strong></p>
                                <hr class="bg-white">
                                <h4>Total: <span id="resTotal">0 €</span></h4>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button type="button" class="btn btn-outline-secondary btn-prev" style="display:none;"><i class="bi bi-arrow-left me-2"></i>Anterior</button>
                    <div class="ms-auto">
                        <button type="button" class="btn btn-primary btn-next">Siguiente<i class="bi bi-arrow-right ms-2"></i></button>
                        <button type="submit" class="btn btn-success btn-submit" style="display:none;"><i class="bi bi-check me-2"></i>Guardar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let currentStep = 1;
let destinos = <?php echo json_encode($destinos_seleccionados); ?>;
let servicios = {guias:[], agencias:[], locales:[]};

document.addEventListener('DOMContentLoaded', function() {
    updateWizard();
    
    // Navegación
    document.querySelector('.btn-next').onclick = nextStep;
    document.querySelector('.btn-prev').onclick = () => { currentStep--; updateWizard(); };
    
    // Filtros
    document.querySelectorAll('.filter-chip').forEach(c => {
        c.onclick = function() {
            document.querySelectorAll('.filter-chip').forEach(x => x.classList.remove('active'));
            this.classList.add('active');
            const cat = this.dataset.category;
            document.querySelectorAll('.destino-item').forEach(item => {
                item.style.display = (cat === 'all' || item.dataset.category === cat) ? '' : 'none';
            });
        };
    });
    
    // Destinos
    document.querySelectorAll('.destino-card').forEach(card => {
        card.onclick = function() {
            const id = parseInt(this.dataset.id);
            const idx = destinos.indexOf(id);
            if (idx > -1) { destinos.splice(idx, 1); this.classList.remove('selected'); }
            else { destinos.push(id); this.classList.add('selected'); }
            updateDestinos();
        };
    });
    
    // Servicios
    document.querySelectorAll('.service-card').forEach(card => {
        card.onclick = function() {
            const type = this.dataset.type;
            let arrayKey = type + 's';
            // Corregir "locals" a "locales"
            if (arrayKey === 'locals') arrayKey = 'locales';
            
            const id = parseInt(this.dataset.id);
            const idx = servicios[arrayKey].indexOf(id);
            if (idx > -1) { 
                servicios[arrayKey].splice(idx, 1); 
                this.classList.remove('selected'); 
            } else { 
                servicios[arrayKey].push(id); 
                this.classList.add('selected'); 
            }
        };
    });
    
    // Submit
    document.getElementById('itinerarioForm').onsubmit = async (e) => {
        e.preventDefault();
        const formData = new FormData(e.target);
        formData.append('destinos', JSON.stringify(destinos));
        formData.append('servicios', JSON.stringify(servicios));
        
        const btn = document.querySelector('.btn-submit');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
        
        try {
            const res = await fetch('api/itinerarios.php', { method: 'POST', body: formData });
            const data = await res.json();
            if (data.success) {
                alert('✅ ' + data.message);
                window.location.href = 'itinerario.php';
            } else {
                alert('❌ ' + data.message);
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check me-2"></i>Guardar';
            }
        } catch (err) {
            alert('Error: ' + err.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check me-2"></i>Guardar';
        }
    };
    
    updateDestinos();
});

function nextStep() {
    if (currentStep === 1 && !document.querySelector('[name="nombre_itinerario"]').value) {
        alert('Ingresa un nombre'); return;
    }
    if (currentStep === 2 && destinos.length === 0) {
        alert('Selecciona al menos un destino'); return;
    }
    if (currentStep < 4) { currentStep++; updateWizard(); }
}

function updateWizard() {
    document.querySelectorAll('.step-content').forEach(s => s.classList.remove('active'));
    document.querySelector(`.step-content[data-step="${currentStep}"]`).classList.add('active');
    
    document.querySelectorAll('.wizard-step').forEach(s => {
        const n = parseInt(s.dataset.step);
        s.classList.remove('active', 'completed');
        if (n === currentStep) s.classList.add('active');
        else if (n < currentStep) s.classList.add('completed');
    });
    
    document.querySelector('.btn-prev').style.display = currentStep > 1 ? 'block' : 'none';
    document.querySelector('.btn-next').style.display = currentStep < 4 ? 'block' : 'none';
    document.querySelector('.btn-submit').style.display = currentStep === 4 ? 'block' : 'none';
    
    if (currentStep === 4) updateResumen();
    window.scrollTo(0, 0);
}

function updateDestinos() {
    const preview = document.getElementById('destinosPreview');
    const list = document.getElementById('destinosList');
    const count = document.getElementById('destinosCount');
    const total = document.getElementById('destinosTotal');
    
    if (destinos.length === 0) { preview.style.display = 'none'; return; }
    
    preview.style.display = 'block';
    count.textContent = destinos.length;
    
    let html = '';
    let sum = 0;
    destinos.forEach((id, idx) => {
        const card = document.querySelector(`.destino-card[data-id="${id}"]`);
        const badge = card.querySelector('.destino-order-badge');
        badge.textContent = idx + 1;
        badge.style.display = 'flex';
        html += `<span class="badge bg-primary me-1 mb-1">${card.dataset.nombre}</span>`;
        sum += parseFloat(card.dataset.precio);
    });
    
    document.querySelectorAll('.destino-card:not(.selected) .destino-order-badge').forEach(b => b.style.display = 'none');
    list.innerHTML = html;
    total.textContent = sum.toFixed(2) + ' €';
}

function updateResumen() {
    const form = document.getElementById('itinerarioForm');
    
    // Info
    document.getElementById('resumenInfo').innerHTML = `
        <p><strong>Nombre:</strong> ${form.nombre_itinerario.value}</p>
        <p><strong>Estado:</strong> ${form.estado.options[form.estado.selectedIndex].text}</p>
        <p><strong>Fechas:</strong> ${form.fecha_inicio.value || 'No definida'} - ${form.fecha_fin.value || 'No definida'}</p>
        <p><strong>Ciudad:</strong> ${form.ciudad.value || 'No definida'}</p>
        <p><strong>Notas:</strong> ${form.notas.value || 'Sin notas'}</p>
    `;
    
    // Destinos
    let html = '<ol>';
    let total = 0;
    destinos.forEach(id => {
        const card = document.querySelector(`.destino-card[data-id="${id}"]`);
        const precio = parseFloat(card.dataset.precio);
        html += `<li>${card.dataset.nombre} - ${precio.toFixed(2)} €</li>`;
        total += precio;
    });
    html += '</ol>';
    document.getElementById('resumenDestinos').innerHTML = html;
    document.getElementById('resTotal1').textContent = total.toFixed(2) + ' €';
    
    // Servicios
    let servHtml = '';
    if (servicios.guias.length) {
        servHtml += '<h6>Guías:</h6><ul>';
        servicios.guias.forEach(id => {
            const card = document.querySelector(`.service-card[data-type="guia"][data-id="${id}"]`);
            servHtml += `<li>${card.querySelector('h6').textContent}</li>`;
        });
        servHtml += '</ul>';
    }
    if (servicios.agencias.length) {
        servHtml += '<h6>Agencias:</h6><ul>';
        servicios.agencias.forEach(id => {
            const card = document.querySelector(`.service-card[data-type="agencia"][data-id="${id}"]`);
            servHtml += `<li>${card.querySelector('h6').textContent}</li>`;
        });
        servHtml += '</ul>';
    }
    if (servicios.locales.length) {
        servHtml += '<h6>Locales:</h6><ul>';
        servicios.locales.forEach(id => {
            const card = document.querySelector(`.service-card[data-type="local"][data-id="${id}"]`);
            servHtml += `<li>${card.querySelector('h6').textContent}</li>`;
        });
        servHtml += '</ul>';
    }
    if (!servHtml) servHtml = '<p class="text-muted">Sin servicios adicionales</p>';
    document.getElementById('resumenServicios').innerHTML = servHtml;
    
    // Totales
    const presup = parseFloat(form.presupuesto_estimado.value) || 0;
    document.getElementById('resTotal2').textContent = presup.toFixed(2) + ' €';
    document.getElementById('resTotal').textContent = (total + presup).toFixed(2) + ' €';
}
</script>

<?php require_once 'includes/footer.php'; ?>
