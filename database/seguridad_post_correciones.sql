-- ============================================
-- ACCIONES DE SEGURIDAD - GQ-TURISMO
-- ⚠️ EJECUTAR DESPUÉS DE correciones_criticas.sql
-- ============================================

USE gq_turismo;

-- ============================================
-- 1. CAMBIAR CONTRASEÑA DEL SUPER ADMIN
-- ============================================
-- IMPORTANTE: Reemplaza 'TU_NUEVA_CONTRASEÑA_SEGURA' con una contraseña fuerte

-- Paso 1: Generar el hash en PHP primero
-- Ejecuta esto en un archivo PHP temporal:
-- <?php echo password_hash('TU_NUEVA_CONTRASEÑA_SEGURA', PASSWORD_DEFAULT); ?>

-- Paso 2: Usa el hash generado aquí
-- UPDATE usuarios 
-- SET contrasena = '$2y$10$HASH_GENERADO_AQUI' 
-- WHERE email = 'etebachalegroup@gmail.com';

-- Por ejemplo (CAMBIAR EL HASH):
-- UPDATE usuarios 
-- SET contrasena = '$2y$10$NuevoHashGeneradoConPasswordHashFunction' 
-- WHERE email = 'etebachalegroup@gmail.com';

-- ============================================
-- 2. ELIMINAR USUARIOS DE PRUEBA (OPCIONAL)
-- ============================================
-- ADVERTENCIA: Esto eliminará en cascada todas las relaciones
-- (agencias, guías, locales, pedidos, etc.)

-- Descomentar si deseas eliminar usuarios de prueba:
/*
DELETE FROM usuarios WHERE email IN (
    'admin@gqturismo.com',
    'agencia@example.com',
    'guia@example.com',
    'guia2@example.com',
    'local@example.com'
);
*/

-- ============================================
-- 3. ELIMINAR USUARIO ADMIN SI EXISTE
-- ============================================
-- Solo si existe un usuario "Admin" genérico

-- Descomentar si deseas eliminar:
/*
DELETE FROM usuarios 
WHERE email = 'admin@gqturismo.com' 
  AND nombre = 'Admin';
*/

-- ============================================
-- 4. VERIFICACIÓN DE SEGURIDAD
-- ============================================

-- Verificar que no haya contraseñas débiles conocidas
SELECT id, nombre, email, tipo_usuario, fecha_registro
FROM usuarios
WHERE email LIKE '%example.com%'
   OR email = 'admin@gqturismo.com'
ORDER BY fecha_registro DESC;

-- Verificar usuarios super_admin
SELECT id, nombre, email, fecha_registro
FROM usuarios
WHERE tipo_usuario = 'super_admin'
ORDER BY fecha_registro DESC;

-- ============================================
-- 5. LIMPIEZA DE DATOS DE PRUEBA (OPCIONAL)
-- ============================================

-- Eliminar pedidos de prueba antiguos
-- Descomentar si deseas limpiar:
/*
DELETE FROM pedidos_servicios 
WHERE fecha_solicitud < DATE_SUB(NOW(), INTERVAL 30 DAY)
  AND estado = 'pendiente';
*/

-- Eliminar mensajes antiguos no leídos
-- Descomentar si deseas limpiar:
/*
DELETE FROM mensajes 
WHERE timestamp < DATE_SUB(NOW(), INTERVAL 90 DAY)
  AND is_read = 0;
*/

-- ============================================
-- 6. OPTIMIZACIÓN DE TABLAS
-- ============================================

OPTIMIZE TABLE usuarios;
OPTIMIZE TABLE pedidos_servicios;
OPTIMIZE TABLE reservas;
OPTIMIZE TABLE mensajes;
OPTIMIZE TABLE itinerarios;
OPTIMIZE TABLE destinos;

-- ============================================
-- 7. VERIFICACIÓN FINAL
-- ============================================

-- Resumen de usuarios por tipo
SELECT tipo_usuario, COUNT(*) as total
FROM usuarios
GROUP BY tipo_usuario
ORDER BY total DESC;

-- Total de pedidos por estado
SELECT estado, COUNT(*) as total, SUM(precio_total) as valor_total
FROM pedidos_servicios
GROUP BY estado
ORDER BY total DESC;

-- Total de reservas por estado
SELECT estado, COUNT(*) as total
FROM reservas
GROUP BY estado
ORDER BY total DESC;

-- ============================================
-- FIN DE SCRIPT DE SEGURIDAD
-- ============================================

-- SIGUIENTE PASO:
-- 1. Eliminar archivos PHP de bypass manualmente
-- 2. Crear .htaccess en carpeta database/
-- 3. Actualizar .htaccess en raíz del proyecto
-- 4. Cambiar contraseña de MySQL root
-- 5. Realizar pruebas completas del sistema
