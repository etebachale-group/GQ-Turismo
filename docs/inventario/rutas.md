# INVENTARIO DE RUTAS Y ENTRY POINTS (DISC-01)

**Fecha de Generación:** 2026-10-31
**Total de Endpoints Identificados:** 66

| Archivo / Endpoint | Método HTTP | Requiere Autenticación | Rol(es) Permitidos | Tablas Principales Afectadas |
| :--- | :---: | :---: | :--- | :--- |
| [`./about.php`](file:///C:/xampp/htdocs/GQ-Turismo/./about.php) | GET | No | Público | Ninguna / N/A |
| [`./agencias.php`](file:///C:/xampp/htdocs/GQ-Turismo/./agencias.php) | GET | No | Público | Ninguna / N/A |
| [`./contacto.php`](file:///C:/xampp/htdocs/GQ-Turismo/./contacto.php) | GET | No | Público | Ninguna / N/A |
| [`./crear_itinerario.php`](file:///C:/xampp/htdocs/GQ-Turismo/./crear_itinerario.php) | GET | Sí | Cualquier usuario autenticado | `itinerarios`, `itinerario_destinos`, `destinos`, `guias_turisticos`... |
| [`./destinos.php`](file:///C:/xampp/htdocs/GQ-Turismo/./destinos.php) | GET | No | Público | `destinos` |
| [`./detalle_agencia.php`](file:///C:/xampp/htdocs/GQ-Turismo/./detalle_agencia.php) | GET | No | Público | `agencias`, `servicios_agencia`, `menus_agencia` |
| [`./detalle_destino.php`](file:///C:/xampp/htdocs/GQ-Turismo/./detalle_destino.php) | GET | No | Público | `destinos`, `imagenes_destino`, `guias_turisticos`, `lugares_locales` |
| [`./detalle_guia.php`](file:///C:/xampp/htdocs/GQ-Turismo/./detalle_guia.php) | GET | No | Público | `guias_turisticos`, `imagenes_guia`, `servicios_guia` |
| [`./detalle_local.php`](file:///C:/xampp/htdocs/GQ-Turismo/./detalle_local.php) | GET | No | Público | `lugares_locales`, `imagenes_local`, `servicios_local`, `menus_local` |
| [`./guias.php`](file:///C:/xampp/htdocs/GQ-Turismo/./guias.php) | GET | No | Público | Ninguna / N/A |
| [`./index.php`](file:///C:/xampp/htdocs/GQ-Turismo/./index.php) | GET | No | Público | `carouseles`, `itinerarios`, `pedidos_servicios`, `usuarios`... |
| [`./itinerario.php`](file:///C:/xampp/htdocs/GQ-Turismo/./itinerario.php) | GET | Sí | Cualquier usuario autenticado | `itinerarios`, `itinerario_destinos`, `guias_turisticos`, `lugares_locales`... |
| [`./locales.php`](file:///C:/xampp/htdocs/GQ-Turismo/./locales.php) | GET | No | Público | Ninguna / N/A |
| [`./logout.php`](file:///C:/xampp/htdocs/GQ-Turismo/./logout.php) | GET | No | Público | Ninguna / N/A |
| [`./mapa_itinerario.php`](file:///C:/xampp/htdocs/GQ-Turismo/./mapa_itinerario.php) | GET | Sí | Cualquier usuario autenticado | `itinerarios`, `itinerario_tareas`, `destinos`, `usuarios`... |
| [`./mapa_tareas_itinerario.php`](file:///C:/xampp/htdocs/GQ-Turismo/./mapa_tareas_itinerario.php) | GET | Sí | Cualquier usuario autenticado | `itinerarios`, `itinerario_guias`, `guias_turisticos`, `itinerario_tareas`... |
| [`./mis_mensajes.php`](file:///C:/xampp/htdocs/GQ-Turismo/./mis_mensajes.php) | GET | Sí | Cualquier usuario autenticado | `agencias`, `guias_turisticos`, `lugares_locales`, `mensajes`... |
| [`./mis_pedidos.php`](file:///C:/xampp/htdocs/GQ-Turismo/./mis_pedidos.php) | GET | Sí | Cualquier usuario autenticado | `pedidos_servicios`, `agencias`, `guias_turisticos`, `lugares_locales`... |
| [`./pagar.php`](file:///C:/xampp/htdocs/GQ-Turismo/./pagar.php) | POST, GET | Sí | Cualquier usuario autenticado | `pedidos_servicios`, `agencias`, `guias_turisticos`, `lugares_locales`... |
| [`./reservas.php`](file:///C:/xampp/htdocs/GQ-Turismo/./reservas.php) | GET | Sí | Cualquier usuario autenticado | `itinerarios`, `itinerario_destinos`, `destinos`, `itinerario_guias`... |
| [`./search_results.php`](file:///C:/xampp/htdocs/GQ-Turismo/./search_results.php) | GET | No | Público | `destinos`, `agencias`, `guias_turisticos`, `lugares_locales` |
| [`./seguimiento_itinerario.php`](file:///C:/xampp/htdocs/GQ-Turismo/./seguimiento_itinerario.php) | GET | Sí | Cualquier usuario autenticado | `itinerarios`, `itinerario_guias`, `guias_turisticos`, `itinerario_agencias`... |
| [`./tracking_itinerario.php`](file:///C:/xampp/htdocs/GQ-Turismo/./tracking_itinerario.php) | POST, GET | Sí | `super_admin` | `itinerario_tareas`, `itinerarios`, `usuarios`, `destinos` |
| [`admin/admin_footer.php`](file:///C:/xampp/htdocs/GQ-Turismo/admin/admin_footer.php) | GET | Sí | Cualquier usuario autenticado | Ninguna / N/A |
| [`admin/admin_header.php`](file:///C:/xampp/htdocs/GQ-Turismo/admin/admin_header.php) | GET | Sí | `super_admin` | `styles`, `thead`, `tbody` |
| [`admin/confirmacion_servicios.php`](file:///C:/xampp/htdocs/GQ-Turismo/admin/confirmacion_servicios.php) | POST | Sí | Cualquier usuario autenticado | `confirmacion_servicios`, `pedidos_servicios`, `usuarios`, `itinerarios`... |
| [`admin/dashboard.php`](file:///C:/xampp/htdocs/GQ-Turismo/admin/dashboard.php) | GET | Sí | `super_admin` | `usuarios`, `agencias`, `guias_turisticos`, `lugares_locales`... |
| [`admin/login.php`](file:///C:/xampp/htdocs/GQ-Turismo/admin/login.php) | POST | No | Público | `usuarios` |
| [`admin/logout.php`](file:///C:/xampp/htdocs/GQ-Turismo/admin/logout.php) | GET | Sí | Cualquier usuario autenticado | Ninguna / N/A |
| [`admin/manage_agencias.php`](file:///C:/xampp/htdocs/GQ-Turismo/admin/manage_agencias.php) | POST, GET | Sí | `super_admin` | `agencias`, `pedidos_servicios`, `servicios_agencia`, `menus_agencia`... |
| [`admin/manage_destinos.php`](file:///C:/xampp/htdocs/GQ-Turismo/admin/manage_destinos.php) | POST, GET | Sí | `super_admin` | `existing`, `destinos`, `imagenes_destino`, `itinerario_destinos`... |
| [`admin/manage_guias.php`](file:///C:/xampp/htdocs/GQ-Turismo/admin/manage_guias.php) | POST, GET | Sí | `super_admin` | `guias_turisticos`, `pedidos_servicios`, `guias_destinos`, `destinos`... |
| [`admin/manage_locales.php`](file:///C:/xampp/htdocs/GQ-Turismo/admin/manage_locales.php) | POST, GET | Sí | `super_admin` | `lugares_locales`, `pedidos_servicios`, `imagenes_local`, `servicios_local`... |
| [`admin/manage_publicidad_carousel.php`](file:///C:/xampp/htdocs/GQ-Turismo/admin/manage_publicidad_carousel.php) | POST, GET | Sí | `super_admin` | `publicidades`, `publicidad_carousel` |
| [`admin/manage_users.php`](file:///C:/xampp/htdocs/GQ-Turismo/admin/manage_users.php) | POST, GET | Sí | Cualquier usuario autenticado | `usuarios`, `card`, `class`, `tbody` |
| [`admin/messages.php`](file:///C:/xampp/htdocs/GQ-Turismo/admin/messages.php) | GET | Sí | `super_admin` | `mensajes`, `usuarios`, `current`, `chat`... |
| [`admin/mis_destinos.php`](file:///C:/xampp/htdocs/GQ-Turismo/admin/mis_destinos.php) | POST | Sí | `guia,agencia,local` | `tarifa_especial`, `destinos` |
| [`admin/mis_destinos_guia.php`](file:///C:/xampp/htdocs/GQ-Turismo/admin/mis_destinos_guia.php) | POST, GET | Sí | Cualquier usuario autenticado | `guias_turisticos`, `guia_destinos`, `destinos` |
| [`admin/mis_pedidos.php`](file:///C:/xampp/htdocs/GQ-Turismo/admin/mis_pedidos.php) | GET | Sí | `agencia,guia,local` | `agencias`, `guias_turisticos`, `lugares_locales`, `pedidos_servicios`... |
| [`admin/reservas.php`](file:///C:/xampp/htdocs/GQ-Turismo/admin/reservas.php) | GET | Sí | `agencia,guia,local,super_admin` | `reservas`, `itinerarios`, `usuarios`, `pedidos_servicios`... |
| [`admin/sidebar.php`](file:///C:/xampp/htdocs/GQ-Turismo/admin/sidebar.php) | GET | Sí | `super_admin` | Ninguna / N/A |
| [`api/actualizar_estado_tarea.php`](file:///C:/xampp/htdocs/GQ-Turismo/api/actualizar_estado_tarea.php) | GET | Sí | Cualquier usuario autenticado | `itinerario_tareas`, `itinerarios`, `itinerario_guias`, `guias_turisticos` |
| [`api/agencias.php`](file:///C:/xampp/htdocs/GQ-Turismo/api/agencias.php) | GET | No | Público | `agencias` |
| [`api/auth.php`](file:///C:/xampp/htdocs/GQ-Turismo/api/auth.php) | POST | No | Público | `usuarios` |
| [`api/confirmar_servicio_proveedor.php`](file:///C:/xampp/htdocs/GQ-Turismo/api/confirmar_servicio_proveedor.php) | GET | Sí | Cualquier usuario autenticado | `pedidos_servicios`, `confirmacion_servicios` |
| [`api/contact.php`](file:///C:/xampp/htdocs/GQ-Turismo/api/contact.php) | POST | No | Público | Ninguna / N/A |
| [`api/destinos.php`](file:///C:/xampp/htdocs/GQ-Turismo/api/destinos.php) | GET | Sí | `super_admin` | `destinos`, `imagenes_destino`, `itinerario_destinos` |
| [`api/get_conversation.php`](file:///C:/xampp/htdocs/GQ-Turismo/api/get_conversation.php) | GET | Sí | Cualquier usuario autenticado | `usuarios`, `agencias`, `guias_turisticos`, `lugares_locales`... |
| [`api/get_proveedores_por_destino.php`](file:///C:/xampp/htdocs/GQ-Turismo/api/get_proveedores_por_destino.php) | GET | No | Público | `guias_turisticos`, `guia_destinos`, `agencias`, `agencia_destinos`... |
| [`api/guias.php`](file:///C:/xampp/htdocs/GQ-Turismo/api/guias.php) | GET | No | Público | `guias_turisticos` |
| [`api/itinerario_tracking.php`](file:///C:/xampp/htdocs/GQ-Turismo/api/itinerario_tracking.php) | GET | Sí | Cualquier usuario autenticado | `itinerario_tareas`, `itinerarios`, `pedidos_servicios` |
| [`api/itinerarios.php`](file:///C:/xampp/htdocs/GQ-Turismo/api/itinerarios.php) | POST, GET | Sí | Cualquier usuario autenticado | `destinos`, `itinerarios`, `itinerario_destinos`, `itinerario_servicios` |
| [`api/itinerary_tasks.php`](file:///C:/xampp/htdocs/GQ-Turismo/api/itinerary_tasks.php) | GET | Sí | Cualquier usuario autenticado | `itinerarios`, `itinerario_guias`, `guias_turisticos`, `itinerario_tareas`... |
| [`api/locales.php`](file:///C:/xampp/htdocs/GQ-Turismo/api/locales.php) | GET | No | Público | `lugares_locales` |
| [`api/location.php`](file:///C:/xampp/htdocs/GQ-Turismo/api/location.php) | GET | Sí | Cualquier usuario autenticado | `guias_turisticos` |
| [`api/messages.php`](file:///C:/xampp/htdocs/GQ-Turismo/api/messages.php) | GET | Sí | Cualquier usuario autenticado | `mensajes`, `usuarios` |
| [`api/pedidos.php`](file:///C:/xampp/htdocs/GQ-Turismo/api/pedidos.php) | GET | Sí | `super_admin` | `pedidos_servicios`, `usuarios` |
| [`api/reservas.php`](file:///C:/xampp/htdocs/GQ-Turismo/api/reservas.php) | POST, GET | Sí | Cualquier usuario autenticado | `itinerarios`, `reservas`, `usuarios`, `itinerario_destinos`... |
| [`api/reviews.php`](file:///C:/xampp/htdocs/GQ-Turismo/api/reviews.php) | GET | Sí | Cualquier usuario autenticado | `valoraciones`, `usuarios` |
| [`api/start_conversation.php`](file:///C:/xampp/htdocs/GQ-Turismo/api/start_conversation.php) | GET | Sí | Cualquier usuario autenticado | `agencias`, `guias_turisticos`, `lugares_locales`, `mensajes` |
| [`api/test_db.php`](file:///C:/xampp/htdocs/GQ-Turismo/api/test_db.php) | GET | No | Público | `itinerarios`, `itinerario_destinos`, `itinerario_servicios`, `reserva_servicios`... |
| [`api/update_destino_status.php`](file:///C:/xampp/htdocs/GQ-Turismo/api/update_destino_status.php) | GET | Sí | Cualquier usuario autenticado | `itinerario_destinos`, `itinerarios` |
| [`api/update_servicio_estado.php`](file:///C:/xampp/htdocs/GQ-Turismo/api/update_servicio_estado.php) | GET | Sí | Cualquier usuario autenticado | `pedidos_servicios`, `confirmaciones_servicios`, `estado_confirmacion`, `servicios_guia`... |
| [`api/update_servicio_status.php`](file:///C:/xampp/htdocs/GQ-Turismo/api/update_servicio_status.php) | GET | Sí | Cualquier usuario autenticado | Ninguna / N/A |
| [`api/update_task_notes.php`](file:///C:/xampp/htdocs/GQ-Turismo/api/update_task_notes.php) | GET | Sí | Cualquier usuario autenticado | `itinerario_tareas` |
| [`api/update_task_status.php`](file:///C:/xampp/htdocs/GQ-Turismo/api/update_task_status.php) | GET | Sí | Cualquier usuario autenticado | `itinerario_tareas`, `notificaciones` |
