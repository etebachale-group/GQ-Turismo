# Verificación Manual de Endpoints de e-Visa (VI-04)

**Fecha de Verificación:** 2026-10-31  
**Responsable:** Agente Antigravity / GQ-Turismo Modernización  
**Documento de Referencia:** `GQ-TURISMO_PLAN_MAESTRO_ANTIGRAVITY.md` (Anexo A)

---

## 1. Fuentes Oficiales Registradas

El registro central reside en [`config/fuentes_oficiales.php`](../../config/fuentes_oficiales.php).

| ID | Nombre | Estado | URL Canónica | Host Whitelisted |
|---|---|---|---|---|
| `evisa_gnq` | Solicitud de e-Visa de Guinea Ecuatorial (portal oficial) | **Activo** | `https://egonline.vfsevisa.com/gnq/en/on-boarding` | `egonline.vfsevisa.com` |
| `evisa_gnq_referencia` | Portal e-Visa GNQ (comunicado 2023) | *Inactivo* (Respaldo) | `https://equatorialguinea-evisa.com/` | `equatorialguinea-evisa.com` |

---

## 2. Criterios de Seguridad y Verificación Técnica

1. **Protocolo:** Estrictamente `https://`. Peticiones sobre `http://` son rechazadas en [`includes/evisa.php`](../../includes/evisa.php).
2. **Lista Blanca de Hosts:** Solo se admiten:
   - `egonline.vfsevisa.com`
   - `equatorialguinea-evisa.com`
3. **Parámetros de Seguimiento Prohibidos:** Se prohíben cadenas con `?`, `_ga`, `_gl`, `utm_*` o fragmentos `#` para proteger la privacidad del turista.
4. **Atributos de Enlace:**
   - `target="_blank"`
   - `rel="noopener noreferrer"` (prevención de reverse tabnabbing y fugas de referrer).

---

## 3. Puntos de Integración en la Plataforma

1. **Navegación Superior Global (VI-01):**
   - Archivo: [`includes/header.php`](../../includes/header.php)
   - Ubicación: Menú de escritorio y menú móvil.
2. **Página de Inicio / Home (VI-02):**
   - Archivo: [`index.php`](../../index.php)
   - Ubicación: Hero de bienvenida y sección contextual de trámite oficial.
3. **Ficha de Detalle de Destino (VI-03):**
   - Archivo: [`detalle_destino.php`](../../detalle_destino.php)
   - Ubicación: Barra lateral de reserva para turistas extranjeros.
4. **Panel de Mis Pedidos (VI-05):**
   - Archivo: [`mis_pedidos.php`](../../mis_pedidos.php)
   - Ubicación: Banner informativo de requisitos de entrada antes de viajar.

---

## 4. Procedimiento de Verificación Manual (Paso a Paso)

1. Abrir la página principal local: `http://localhost/GQ-Turismo/index.php`.
2. Verificar que en la barra superior aparece el botón **"Solicitar e-Visa"**.
3. Hacer clic en el enlace y comprobar:
   - Se abre en una pestaña nueva del navegador.
   - La barra de direcciones carga `https://egonline.vfsevisa.com/gnq/en/on-boarding`.
   - Inspeccionar el elemento HTML y verificar que tiene `rel="noopener noreferrer"`.
4. Navegar a cualquier destino (ej. `http://localhost/GQ-Turismo/detalle_destino.php?id=1`):
   - En la columna lateral derecha, verificar el bloque *"¿Vienes desde fuera de Guinea Ecuatorial?"* con el botón *"Solicitar mi e-Visa en el sitio oficial"*.
5. Iniciar sesión como turista e ir a `mis_pedidos.php`:
   - Verificar la presencia del banner superior alertando sobre la visa previa al vuelo.
6. Ejecutar la suite automatizada:
   ```bash
   php tests/evisa_test.php
   ```
   Resultado esperado: 32 pruebas superadas, 0 fallidas.
