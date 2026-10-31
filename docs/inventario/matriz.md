# MATRIZ DE FUNCIONALIDADES (DISC-05)

**Proyecto:** GQ-Turismo  
**Fecha:** 31 de Octubre de 2026  
**Criterio de Decisión:**
- **Mantener:** Concepto y reglas de negocio válidas que pasarán a la nueva arquitectura.
- **Rehacer:** Funcionalidad crítica que debe reimplementarse bajo estándares modernos (seguridad, validación server-side, MVC).
- **Eliminar:** Deuda técnica, duplicidades, código huérfano o malas prácticas a descartar.

---

## 1. Módulos y Funcionalidades Analizadas

| Módulo / Funcionalidad | Estado Actual | Decisión | Justificación Técnica y de Negocio |
| :--- | :--- | :---: | :--- |
| **Catálogo de Destinos** (`destinos.php`, `detalle_destino.php`) | Listado y fichas informativas con fotos | **Rehacer** | El catálogo es el núcleo de atracción turística. Debe rehacerse con slugs amigables, búsqueda eficiente, SEO y carga optimizada WebP. |
| **Catálogo de Proveedores** (Agencias, Guías, Locales) | Listados y perfiles individuales | **Rehacer** | Debe consolidarse. Los guías y agencias deben poder gestionar su propio catálogo bajo aprobación previa de un administrador. |
| **Constructor de Itinerarios** (`crear_itinerario.php`) | Selección de destinos por checkboxes y fechas | **Rehacer** | Es una funcionalidad diferencial del proyecto. Debe rehacerse con mejor UX/UI interactiva, cálculo real de tiempos y costes en servidor. |
| **Mapa Interactivo de Itinerarios** (`mapa_itinerario.php`) | Renderizado con Leaflet | **Mantener** | La integración con Leaflet + OpenStreetMap es ligera y sin costos de API. Se mantiene y optimiza para móvil. |
| **Sistema de Pedidos / Reservas** (`api/pedidos.php`, `reservas.php`) | Inserción directa con precios enviados por el cliente | **Rehacer** | Vulnerabilidad crítica de manipulación de precios. Debe rehacerse calculando snapshots de tarifas en el backend. |
| **Pasarela de Pago** (`pagar.php`) | Simulación mediante botón POST sin pasarela | **Rehacer** | No existe procesamiento financiero real. Debe crearse la capa de abstracción `PaymentGateway` con soporte para pasarelas reales y opción de pago manual con comprobante. |
| **Sistema de Chat / Mensajería** (`mis_mensajes.php`, `api/messages.php`) | Polling rudimentario con bug en IDs de proveedor | **Rehacer** | La comunicación turista-proveedor es vital. Debe rehacerse apuntando siempre a `users.id` y asociando el chat al contexto de una reserva. |
| **Autenticación y Registro** (`api/auth.php`) | Registro vulnerable que aceptaba `super_admin` | **Rehacer** | Debe usar autenticación estándar con hashing seguro, throttling, verificación de correo y roles estrictos. |
| **Panel de Administración** (`admin/*.php`) | CRUDs procedimentales sin protección CSRF | **Rehacer** | Reconstruir con arquitectura limpia, control de acceso basado en roles (RBAC) y operaciones destructivas mediante POST + CSRF. |
| **Scripts de Verificación Sueltos** (`test_system.php`, etc.) | Archivos de prueba expuestos en webroot | **Eliminar** | Exponen el esquema y salud del sistema. Se eliminan del webroot y se reemplazan por suites de pruebas automatizadas (PHPUnit/Pest). |
| **10 Hojas de Estilo Móvil** (`mobile-*.css`) | 10 archivos compitiendo por especificidad | **Eliminar** | Se descartan en favor de un sistema de diseño unificado mobile-first con Tailwind CSS / CSS modular. |
| **Tablas Duplicadas de la BD** (`locales_turisticos`, etc.) | Esquemas divergentes no normalizados | **Eliminar** | Se purgan las tablas redundantes migrando sus datos hacia un esquema consolidado y único. |
| **Carpeta `trash/` y scripts temporales** | Archivos residuales acumulados | **Eliminar** | Se limpian definitivamente del repositorio. |
