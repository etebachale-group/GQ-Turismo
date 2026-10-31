# VERIFICACIÓN DE HALLAZGOS CONTRA EL CÓDIGO FUENTE (DISC-04)

**Proyecto:** GQ-Turismo  
**Fecha:** 31 de Octubre de 2026  
**Metodología:** Análisis estático de código, inspección de archivos, consultas al catálogo de base de datos y pruebas dinámicas.

---

## Matriz de Verificación (Anexo B del Plan)

| # | Afirmación / Hallazgo Auditado | Archivo y Línea Verificada | Evidencia Concreta | Estado |
| :-: | :--- | :--- | :--- | :-: |
| **1** | `includes/session_security.php` no es incluido por ningún archivo | `includes/session_security.php` | Búsqueda exhaustiva (`grep -r "session_security" .`) arrojó **0 coincidencias** en todo el código base. El archivo existe pero es código muerto. | **CONFIRMADO** |
| **2** | No existía protección `.htaccess` en `assets/img/` | `assets/img/` | No existía ningún archivo `.htaccess` en la carpeta de imágenes, permitiendo que scripts `.php` subidos se ejecutaran directamente con HTTP 200. Mitigado en Fase 0 (`CONT-07`) devolviendo ahora HTTP 403. | **CONFIRMADO** (Mitigado en F0) |
| **3** | `$_POST['tipo_usuario']` aceptaba `super_admin` en registro público | [`api/auth.php:31`](file:///C:/xampp/htdocs/GQ-Turismo/api/auth.php) | El array de validación contenía `['turista', 'agencia', 'guia', 'local', 'super_admin']` e insertaba directamente el tipo solicitado sin verificar sesión de administrador. Mitigado en Fase 0 (`CONT-05`) forzando `$tipo_usuario = 'turista'`. | **CONFIRMADO** (Mitigado en F0) |
| **4** | `move_uploaded_file()` sin validación de extensión ni MIME | [`admin/manage_destinos.php:32-37`](file:///C:/xampp/htdocs/GQ-Turismo/admin/manage_destinos.php#L32-L37)<br>[`admin/manage_agencias.php:82`](file:///C:/xampp/htdocs/GQ-Turismo/admin/manage_agencias.php#L82) | Se construye `$main_image_name = uniqid() . "_" . basename($_FILES['imagen']['name'])` y se mueve directamente a disco sin comprobar extensión permitida, tipo MIME ni `getimagesize()`. | **CONFIRMADO** |
| **5** | Inyección SQL por falta de casteo/parametrización en `DELETE` | [`api/itinerarios.php:81-82`](file:///C:/xampp/htdocs/GQ-Turismo/api/itinerarios.php#L81-L82)<br>[`api/destinos.php:95-96`](file:///C:/xampp/htdocs/GQ-Turismo/api/destinos.php#L95-L96) | Se ejecutan consultas crudas: `$conn->query("DELETE FROM itinerario_destinos WHERE id_itinerario = $itinerario_id")` donde `$itinerario_id` proviene de `$_POST['itinerario_id']` sin prepared statement ni `(int)`. | **CONFIRMADO** |
| **6** | Precios enviados por el cliente y aceptados sin validar | [`api/pedidos.php:37-38`](file:///C:/xampp/htdocs/GQ-Turismo/api/pedidos.php#L37-L38) | `$precio_unitario = $input['precio_unitario'] ?? 0.00; $precio_total = $input['precio_total'] ?? 0.00;` se insertan directamente en la tabla `pedidos_servicios` sin cotejar con los precios reales de `destinos` o `servicios_*`. | **CONFIRMADO** |
| **7** | `pagar.php` no integra pasarela real de pagos | [`pagar.php:24-32`](file:///C:/xampp/htdocs/GQ-Turismo/pagar.php#L24-L32) | El archivo procesa el pago simplemente haciendo: `$nuevo_estado = 'completado'; UPDATE pedidos_servicios SET estado = ? WHERE id = ?`. No existe ninguna llamada a Stripe, PayPal, Redsys, banco ni webhook. | **CONFIRMADO** |
| **8** | Bug de Identidad en el Chat (ID Usuario vs ID Proveedor) | [`api/get_conversation.php:34-42`](file:///C:/xampp/htdocs/GQ-Turismo/api/get_conversation.php#L34-L42)<br>[`api/auth.php:78`](file:///C:/xampp/htdocs/GQ-Turismo/api/auth.php#L78) | Al loguearse la agencia, `$_SESSION['user_id'] = $user['id']` (ej. 18 de la tabla `usuarios`), pero `get_conversation.php` hace `SELECT nombre_agencia FROM agencias WHERE id = m.sender_id`, buscando el ID en `agencias.id` (que es 4), provocando datos nulos o conversaciones vacías. | **CONFIRMADO** |
| **9** | Tablas duplicadas con distinto collation/charset | Base de datos `gq_turismo` | `confirmacion_servicios` usa `utf8mb4_unicode_ci` mientras `confirmaciones_servicios` usa `utf8mb4_general_ci`. Igualmente coexisten `locales_turisticos` y `lugares_locales`. | **CONFIRMADO** |
| **10** | Eliminación de usuarios vía GET sin CSRF | [`admin/manage_users.php:38-49`](file:///C:/xampp/htdocs/GQ-Turismo/admin/manage_users.php#L38-L49) | `if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id']))` ejecuta `DELETE FROM usuarios WHERE id = ?` sin requerir formulario POST ni token anti-CSRF. | **CONFIRMADO** |

---

### Resumen del Diagnóstico
- **Total de afirmaciones auditadas:** 10
- **Confirmadas al 100%:** 10
- **Parciales:** 0
- **Falsos positivos:** 0
- **Conclusión:** Todos los hallazgos señalados en la auditoría técnica son reales y reproducibles en el código del repositorio.
