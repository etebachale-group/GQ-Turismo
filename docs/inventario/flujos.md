# FLUJOS ACTUALES "AS-IS" DEL SISTEMA (DISC-03)

**Proyecto:** GQ-Turismo  
**Fecha:** 31 de Octubre de 2026  
**Objetivo:** Documentar los recorridos funcionales actuales de cada tipo de usuario antes de la modernización.

---

## 1. Recorrido del Turista (Viajero)

```mermaid
flowchart TD
    A[Inicio / Landing Page: index.php] --> B[Exploración de Catálogo]
    B --> B1[Destinos: destinos.php]
    B --> B2[Agencias: agencias.php]
    B --> B3[Guías: guias.php]
    B --> B4[Locales: locales.php]
    
    B1 --> C[Ficha Detalle: detalle_destino.php]
    B2 --> C1[Ficha Agencia: detalle_agencia.php]
    B3 --> C2[Ficha Guía: detalle_guia.php]
    B4 --> C3[Ficha Local: detalle_local.php]
    
    C --> D[Crear Itinerario: crear_itinerario.php]
    D --> E[Selección de Destinos, Fechas y Servicios]
    E --> F[Guardar Itinerario en BD: api/itinerarios.php]
    F --> G[Visualización / Mapa: mapa_itinerario.php]
    
    C --> H[Reserva Directa / Pedido: api/pedidos.php]
    H --> I[Mis Pedidos: mis_pedidos.php]
    I --> J[Pago Simulado: pagar.php]
    J --> K[Confirmación y Seguimiento: tracking_itinerario.php]
    
    K --> L[Chat con Proveedores: mis_mensajes.php]
```

### Detalle de Pasos Actuales:
1. **Descubrimiento:** El turista entra a `index.php`, busca destinos por categoría o ciudad, y consulta listados de agencias, guías y locales.
2. **Consulta de Detalle:** En `detalle_destino.php`, visualiza descripción, galería de imágenes y sugerencias de guías y locales cercanos.
3. **Planificación (Itinerarios):**
   - Accede a `crear_itinerario.php` (requiere login como `turista`).
   - Define título, ciudad, fechas y selecciona destinos con checkboxes.
   - Selecciona servicios de guías, agencias y locales.
   - El frontend envía JSON a `api/itinerarios.php`.
4. **Reserva y Contratación:**
   - Desde la ficha o el itinerario, se genera un pedido en `pedidos_servicios` vía `api/pedidos.php`.
   - **Falla actual:** El cliente envía los precios en el payload y se registran sin verificar contra la BD.
5. **Pago:**
   - Accede a `pagar.php?id=X`.
   - Al pulsar "Pagar", un `UPDATE` local cambia el estado a `completado` sin pasar por pasarela real.
6. **Coordinación y Mensajería:**
   - En `mis_mensajes.php`, consulta y envía mensajes vía `api/get_conversation.php` y `api/messages.php`.
   - **Falla actual:** Los remitentes/destinatarios proveedores no coinciden si se busca por `id` en vez de `id_usuario`.

---

## 2. Recorrido del Proveedor (Agencia, Guía o Local)

```mermaid
flowchart TD
    P1[Login Proveedor: admin/login.php] --> P2[Dashboard: admin/dashboard.php]
    P2 --> P3[Confirmación de Servicios: admin/confirmacion_servicios.php]
    P2 --> P4[Mis Destinos Asignados: admin/mis_destinos.php]
    P2 --> P5[Gestión de Mensajes / Chat: admin/messages.php]
    P2 --> P6[Edición de Perfil y Servicios]
```

### Detalle de Pasos Actuales:
1. **Autenticación:** Inicia sesión con credenciales de usuario con rol `agencia`, `guia` o `local`.
2. **Dashboard de Operaciones:** En `admin/dashboard.php`, visualiza métricas de pedidos pendientes y completados.
3. **Confirmación de Servicios:**
   - En `admin/confirmacion_servicios.php`, el proveedor ve los pedidos asignados a su ID.
   - Puede cambiar el estado a `confirmado` o `rechazado` vía `api/confirmar_servicio_proveedor.php`.
4. **Gestión de Disponibilidad / Destinos:**
   - Los guías acceden a `admin/mis_destinos.php` para asociarse o desvincularse de destinos turísticos.
5. **Comunicación:**
   - En `admin/messages.php`, responden consultas de turistas interesados en sus servicios.

---

## 3. Recorrido del Super Administrador

```mermaid
flowchart TD
    A1[Login Admin: admin/login.php] --> A2[Dashboard Global: admin/dashboard.php]
    A2 --> A3[Gestión de Usuarios: admin/manage_users.php]
    A2 --> A4[Gestión de Destinos: admin/manage_destinos.php]
    A2 --> A5[Gestión de Agencias: admin/manage_agencias.php]
    A2 --> A6[Gestión de Guías: admin/manage_guias.php]
    A2 --> A7[Gestión de Locales: admin/manage_locales.php]
    A2 --> A8[Publicidad y Carrusel: admin/manage_publicidad_carousel.php]
    A2 --> A9[Supervisión de Reservas: admin/reservas.php]
```

### Detalle de Pasos Actuales:
1. **Control de Usuarios:** En `admin/manage_users.php`, el administrador puede cambiar roles (`turista`, `guia`, `agencia`, `local`, `super_admin`) o eliminar cuentas.
2. **Gestión de Catálogo (CRUDs):**
   - Crea, edita y elimina registros en `destinos`, `agencias`, `guias_turisticos` y `lugares_locales`.
   - Carga imágenes para cada entidad.
3. **Publicidad:**
   - En `admin/manage_publicidad_carousel.php`, gestiona banners que se muestran en el carrusel de la página de inicio.
4. **Monitoreo de Reservas:**
   - En `admin/reservas.php`, consulta todas las reservas globales registradas en el sistema.
