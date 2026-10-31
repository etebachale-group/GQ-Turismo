# REGISTRO DE VERIFICACIÓN DE HALLAZGOS (Anexo C)

**Fecha:** 2026-10-31 16:17:14
**Repositorio:** C:\xampp\htdocs\GQ-Turismo

## Bloque 1: Credenciales y dump en historial y árbol
- Historial con password de InfinityFree:
`
4920380 Fase 0: Contencion local y seguridad (CONT-01, CONT-02, CONT-04, CONT-05, CONT-06, CONT-07)
7d4eb33 Create AUDITORIA_INTEGRAL_PLATAFORMA_GQ_TURISMO.md
57bbd99 actual
`
- Historial del archivo dump:
`
4920380 Fase 0: Contencion local y seguridad (CONT-01, CONT-02, CONT-04, CONT-05, CONT-06, CONT-07)
57bbd99 actual
`
- Archivos .sql o .env rastreados actualmente en Git:
`
database/1_CAMBIAR_PASSWORD_ADMIN.sql
database/2_ELIMINAR_USUARIOS_PRUEBA.sql
database/3_CONFIGURAR_MYSQL_SEGURO.sql
database/EJECUTAR_CORRECCIONES_2025.sql
database/FIX_ALL_ERRORS.sql
database/add_city_to_destinos.sql
database/add_city_to_guias.sql
database/add_city_to_locales.sql
database/add_estado_columns.sql
database/add_estado_columns_safe.sql
database/add_guia_destinos_relation.sql
database/add_id_destino_to_pedidos_servicios.sql
database/add_location_to_agencias.sql
database/add_location_to_locales.sql
database/check_duplicados.sql
database/correciones_criticas.sql
database/create_auto_tasks.sql
database/create_destination_images_table.sql
database/create_discounts_table.sql
database/create_guias_destinos_table.sql
database/create_messages_table.sql
database/create_publicidad_carousel.sql
database/create_reviews_table.sql
database/database_fixes.sql
database/este.sql
database/fix_all_complete_system.sql
database/fix_all_critical_columns_2025.sql
database/fix_all_critical_errors.sql
database/fix_all_critical_issues_2025.sql
database/fix_all_current_errors.sql
database/fix_all_current_issues_2025.sql
database/fix_all_database_errors_complete.sql
database/fix_all_missing_elements.sql
database/fix_all_system_errors.sql
database/fix_all_system_issues.sql
database/fix_chat_system.sql
database/fix_chat_system_complete.sql
database/fix_complete_database.sql
database/fix_complete_system.sql
database/fix_critical_errors.sql
database/fix_itinerarios_servicios.sql
database/fix_itinerarios_table.sql
database/fix_missing_columns.sql
database/fix_reservas_itinerarios.sql
database/gq_turismo_completo.sql
database/itinerario_tracking_system.sql
database/itinerarios.sql
database/limpieza_total_gq_turismo.sql
database/populate_confirmaciones.sql
database/seguridad_post_correciones.sql
database/update_destinos_table_add_location.sql
database/update_guias_table_add_location.sql
database/update_itinerarios_add_city.sql
database/update_itinerarios_table.sql
database/update_to_v2.0.sql
if0_39858318_gq_turismo.sql
`
- Estado de .env en .gitignore:
`

`

## Bloque 2: Consultas SQL con variables concatenadas
`
C:\xampp\htdocs\GQ-Turismo\api\agencias.php:12:    $result = $conn->query($sql);
C:\xampp\htdocs\GQ-Turismo\api\destinos.php:95:        $conn->query("DELETE FROM imagenes_destino WHERE id_destino = $destino_id");
C:\xampp\htdocs\GQ-Turismo\api\destinos.php:96:        $conn->query("DELETE FROM itinerario_destinos WHERE id_destino = $destino_id");
C:\xampp\htdocs\GQ-Turismo\api\destinos.php:115:            $result_categories = $conn->query($sql_categories);
C:\xampp\htdocs\GQ-Turismo\api\itinerarios.php:81:            $conn->query("DELETE FROM itinerario_destinos WHERE id_itinerario = $itinerario_id");
C:\xampp\htdocs\GQ-Turismo\api\itinerarios.php:82:            $conn->query("DELETE FROM itinerario_servicios WHERE id_itinerario = $itinerario_id");
C:\xampp\htdocs\GQ-Turismo\api\itinerarios.php:180:            $conn->query("DELETE FROM itinerario_destinos WHERE id_itinerario = $id");
C:\xampp\htdocs\GQ-Turismo\api\itinerarios.php:181:            $conn->query("DELETE FROM itinerario_servicios WHERE id_itinerario = $id");
C:\xampp\htdocs\GQ-Turismo\api\itinerario_tracking.php:70:            $conn->query("UPDATE itinerarios SET estado_itinerario = 'completado' WHERE id = $id_itinerario");
C:\xampp\htdocs\GQ-Turismo\api\itinerario_tracking.php:72:            $conn->query("UPDATE itinerarios SET estado_itinerario = 'en_curso' WHERE id = $id_itinerario");
C:\xampp\htdocs\GQ-Turismo\api\itinerario_tracking.php:167:    $provider = $conn->query("SELECT id FROM $table WHERE id_usuario = $user_id")->fetch_assoc();
C:\xampp\htdocs\GQ-Turismo\api\test_db.php:105:            $result = $conn->query("SHOW INDEX FROM $table");
C:\xampp\htdocs\GQ-Turismo\admin\manage_agencias.php:265:    $result = $conn->query($query);
C:\xampp\htdocs\GQ-Turismo\admin\manage_destinos.php:222:    $result = $conn->query($query);
C:\xampp\htdocs\GQ-Turismo\admin\manage_guias.php:420:    $result = $conn->query($query);
C:\xampp\htdocs\GQ-Turismo\admin\manage_locales.php:378:    $result = $conn->query($query);
C:\xampp\htdocs\GQ-Turismo\admin\manage_users.php:57:$result = $conn->query($query);
C:\xampp\htdocs\GQ-Turismo\admin\mis_destinos.php:115:$destinos_disponibles = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
C:\xampp\htdocs\GQ-Turismo\admin\reservas.php:24:$result = $conn->query($query);
C:\xampp\htdocs\GQ-Turismo\admin\reservas.php:58:$result_pedidos = $conn->query($query_pedidos);
`

## Bloque 3: Consultas que usan prepared statements en api/
- Total de ocurrencias de prepare( en pi/: **102**

## Bloque 4: Subidas de archivos
- Llamadas directas a move_uploaded_file:
`
C:\xampp\htdocs\GQ-Turismo\admin\manage_agencias.php:82:                    if (!move_uploaded_file($_FILES['imagen_perfil']['tmp_name'], $target_file)) {
C:\xampp\htdocs\GQ-Turismo\admin\manage_destinos.php:37:        if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $target_file)) {
C:\xampp\htdocs\GQ-Turismo\admin\manage_destinos.php:154:        if (move_uploaded_file($_FILES['gallery_imagen']['tmp_name'], $target_file)) {
C:\xampp\htdocs\GQ-Turismo\admin\manage_guias.php:173:                    if (!move_uploaded_file($_FILES['imagen_perfil']['tmp_name'], $target_file)) {
C:\xampp\htdocs\GQ-Turismo\admin\manage_guias.php:262:                if (move_uploaded_file($image_file['tmp_name'], $target_file)) {
C:\xampp\htdocs\GQ-Turismo\admin\manage_locales.php:84:                    if (!move_uploaded_file($_FILES['imagen_perfil']['tmp_name'], $target_file)) {
C:\xampp\htdocs\GQ-Turismo\admin\manage_locales.php:161:                if (move_uploaded_file($image_file['tmp_name'], $target_file)) {
C:\xampp\htdocs\GQ-Turismo\admin\manage_publicidad_carousel.php:35:        if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $target_file)) {
C:\xampp\htdocs\GQ-Turismo\admin\manage_publicidad_carousel.php:102:        if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $target_file)) {
`
- Archivos que usan la clase FileUpload:
`

`

## Bloque 5: Registro público y 	ipo_usuario
`
C:\xampp\htdocs\GQ-Turismo\api\auth.php:25:            $tipo_usuario = $_POST['tipo_usuario'] ?? 'turista'; // Default to 'turista'
C:\xampp\htdocs\GQ-Turismo\api\auth.php:31:            } elseif (!in_array($tipo_usuario, ['turista', 'agencia', 'guia', 'local', 'super_admin'])) {
C:\xampp\htdocs\GQ-Turismo\api\auth.php:45:                    $insert_stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, contrasena, tipo_usuario) VALUES (?, ?, ?, ?)");
C:\xampp\htdocs\GQ-Turismo\api\auth.php:46:                    $insert_stmt->bind_param("ssss", $nombre, $email, $hash, $tipo_usuario);
C:\xampp\htdocs\GQ-Turismo\api\auth.php:51:                        if ($tipo_usuario === 'guia') {
C:\xampp\htdocs\GQ-Turismo\api\auth.php:70:                $stmt = $conn->prepare("SELECT id, nombre, contrasena, tipo_usuario FROM usuarios WHERE email = ?");
C:\xampp\htdocs\GQ-Turismo\api\auth.php:80:                        $_SESSION['user_type'] = $user['tipo_usuario']; // Store user type
`

## Bloque 6: Eliminación por GET en administración
`
C:\xampp\htdocs\GQ-Turismo\admin\manage_agencias.php:172:if (isset($_GET['action']) && $_GET['action'] == 'delete_service' && isset($_GET['id'])) {
C:\xampp\htdocs\GQ-Turismo\admin\manage_agencias.php:233:if (isset($_GET['action']) && $_GET['action'] == 'delete_menu' && isset($_GET['id'])) {
C:\xampp\htdocs\GQ-Turismo\admin\manage_agencias.php:413:        if (isset($_GET['action']) && $_GET['action'] == 'delete_discount' && isset($_GET['id'])) {
C:\xampp\htdocs\GQ-Turismo\admin\manage_agencias.php:567:                                                        <a href="manage_agencias.php?action=delete_service&id=<?= htmlspecialchars($service['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar este servicio?');">Eliminar</a>
C:\xampp\htdocs\GQ-Turismo\admin\manage_agencias.php:621:                                                        <a href="manage_agencias.php?action=delete_menu&id=<?= htmlspecialchars($menu['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar este menú?');">Eliminar</a>
C:\xampp\htdocs\GQ-Turismo\admin\manage_agencias.php:743:                                                        <a href="manage_agencias.php?action=delete_discount&id=<?= htmlspecialchars($discount['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar este descuento?');">Eliminar</a>
C:\xampp\htdocs\GQ-Turismo\admin\manage_agencias.php:841:                                                <a href="manage_agencias.php?action=delete&id=<?php echo htmlspecialchars($agency['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar esta agencia?');">Eliminar</a>
C:\xampp\htdocs\GQ-Turismo\admin\manage_destinos.php:77:if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
C:\xampp\htdocs\GQ-Turismo\admin\manage_destinos.php:172:if (isset($_GET['action']) && $_GET['action'] == 'delete_gallery_image' && isset($_GET['id'])) {
C:\xampp\htdocs\GQ-Turismo\admin\manage_destinos.php:422:                                <a href="manage_destinos.php?action=delete_gallery_image&id=<?= htmlspecialchars($image['id']) ?>&edit_id=<?= htmlspecialchars($edit_destino['id']) ?>" 
C:\xampp\htdocs\GQ-Turismo\admin\manage_destinos.php:498:                                            <a href="manage_destinos.php?action=delete&id=<?= htmlspecialchars($destino_item['id']) ?>" 
C:\xampp\htdocs\GQ-Turismo\admin\manage_guias.php:301:if (isset($_GET['action']) && $_GET['action'] == 'delete_image' && isset($_GET['id'])) {
C:\xampp\htdocs\GQ-Turismo\admin\manage_guias.php:389:if (isset($_GET['action']) && $_GET['action'] == 'delete_service' && isset($_GET['id'])) {
C:\xampp\htdocs\GQ-Turismo\admin\manage_guias.php:650:                                                        <a href="manage_guias.php?action=delete_image&id=<?= htmlspecialchars($image['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar esta imagen?');">Eliminar</a>
C:\xampp\htdocs\GQ-Turismo\admin\manage_guias.php:703:                                                        <a href="manage_guias.php?action=delete_service&id=<?= htmlspecialchars($service['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar este servicio?');">Eliminar</a>
C:\xampp\htdocs\GQ-Turismo\admin\manage_guias.php:936:                                                <a href="manage_guias.php?action=delete&id=<?= htmlspecialchars($guia['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar a este guía?');">Eliminar</a>
C:\xampp\htdocs\GQ-Turismo\admin\manage_locales.php:200:if (isset($_GET['action']) && $_GET['action'] == 'delete_image' && isset($_GET['id'])) {
C:\xampp\htdocs\GQ-Turismo\admin\manage_locales.php:288:if (isset($_GET['action']) && $_GET['action'] == 'delete_service' && isset($_GET['id'])) {
C:\xampp\htdocs\GQ-Turismo\admin\manage_locales.php:346:if (isset($_GET['action']) && $_GET['action'] == 'delete_menu' && isset($_GET['id'])) {
C:\xampp\htdocs\GQ-Turismo\admin\manage_locales.php:579:                                                        <a href="manage_locales.php?action=delete_image&id=<?= htmlspecialchars($image['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar esta imagen?');">Eliminar</a>
C:\xampp\htdocs\GQ-Turismo\admin\manage_locales.php:632:                                                        <a href="manage_locales.php?action=delete_service&id=<?= htmlspecialchars($service['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar este servicio?');">Eliminar</a>
C:\xampp\htdocs\GQ-Turismo\admin\manage_locales.php:685:                                                        <a href="manage_locales.php?action=delete_menu&id=<?= htmlspecialchars($menu['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar este menú?');">Eliminar</a>
C:\xampp\htdocs\GQ-Turismo\admin\manage_locales.php:786:                                                <a href="manage_locales.php?action=delete&id=<?= htmlspecialchars($local['id']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar este local?');">Eliminar</a>
C:\xampp\htdocs\GQ-Turismo\admin\manage_publicidad_carousel.php:73:if (isset($_GET['action']) && $_GET['action'] == 'delete_publicidad' && isset($_GET['id'])) {
C:\xampp\htdocs\GQ-Turismo\admin\manage_publicidad_carousel.php:140:if (isset($_GET['action']) && $_GET['action'] == 'delete_carousel' && isset($_GET['id'])) {
C:\xampp\htdocs\GQ-Turismo\admin\manage_publicidad_carousel.php:508:                            <a href="?action=delete_publicidad&id=<?= $pub['id'] ?>" class="btn-modern btn-danger-gradient" onclick="return confirm('¿Eliminar esta publicidad?')">
C:\xampp\htdocs\GQ-Turismo\admin\manage_publicidad_carousel.php:579:                            <a href="?action=delete_carousel&id=<?= $car['id'] ?>" class="btn-modern btn-danger-gradient" onclick="return confirm('¿Eliminar este slide?')">
C:\xampp\htdocs\GQ-Turismo\admin\manage_users.php:38:if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
C:\xampp\htdocs\GQ-Turismo\admin\manage_users.php:198:                                        <a href="manage_users.php?action=delete&id=<?= $user['id'] ?>" 
`

## Bloque 7: Hojas CSS antiguas todavía enlazadas
`




`

## Bloque 8: Base de datos configurada (.env vs .env.testing)
- .env local:
`
# ConfiguraciÃ³n de Entorno Local GQ-Turismo
DB_HOST=localhost
DB_USER=root
DB_PASS=
DB_NAME=gq_turismo
DB_PORT=3306
APP_ENV=local
APP_DEBUG=true
`
- ¿Existe .env.testing?: **False**

## Bloque 9: Rol administrativo en encabezado de admin
`

`

## Bloque 10: Suite de pruebas
`
Could not open input file: bin/test_runner.php
`

