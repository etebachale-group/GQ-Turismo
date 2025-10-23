-- =============================================
-- CAMBIAR CONTRASEÑA DEL SUPER ADMINISTRADOR
-- =============================================
--
-- INSTRUCCIONES:
-- 1. Ejecutar este script en phpMyAdmin
-- 2. Reemplazar 'NUEVA_CONTRASEÑA_AQUI' con tu contraseña deseada
-- 3. ELIMINAR este archivo después de ejecutarlo
--
-- REQUISITOS DE LA CONTRASEÑA:
-- - Mínimo 12 caracteres
-- - Incluir mayúsculas y minúsculas
-- - Incluir números
-- - Incluir caracteres especiales (!@#$%^&*)
--
-- =============================================

-- Verificar usuario actual
SELECT id, nombre, email, tipo_usuario 
FROM usuarios 
WHERE email = 'etebachalegroup@gmail.com';

-- =============================================
-- IMPORTANTE: Reemplaza 'NUEVA_CONTRASEÑA_AQUI' 
-- con tu nueva contraseña segura
-- =============================================

-- Cambiar contraseña (descomenta y modifica la siguiente línea)
-- UPDATE usuarios 
-- SET contrasena = '$2y$10$xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx'
-- WHERE email = 'etebachalegroup@gmail.com';

-- =============================================
-- GENERAR HASH DE CONTRASEÑA
-- =============================================
-- 
-- Si no sabes cómo generar el hash, usa este PHP:
-- 
-- <?php
-- $password = 'TU_NUEVA_CONTRASEÑA_SEGURA';
-- echo password_hash($password, PASSWORD_DEFAULT);
-- ?>
--
-- O ejecuta esto en MySQL 8.0+:
-- SELECT SHA2('TU_NUEVA_CONTRASEÑA_SEGURA', 256);
--
-- Para usar password_hash de PHP correctamente:
-- 1. Crea un archivo temporal: generate_hash.php
-- 2. Copia este código:
--
-- <?php
-- $password = 'TU_NUEVA_CONTRASEÑA_SEGURA';
-- $hash = password_hash($password, PASSWORD_DEFAULT);
-- echo "Hash generado: " . $hash;
-- ?>
--
-- 3. Accede a: http://localhost/GQ-Turismo/generate_hash.php
-- 4. Copia el hash generado
-- 5. Pégalo en la consulta UPDATE arriba
-- 6. ELIMINA generate_hash.php
-- 7. Ejecuta el UPDATE
-- 8. ELIMINA ESTE ARCHIVO
--
-- =============================================

-- Verificar cambio exitoso
SELECT 
    id, 
    nombre, 
    email, 
    tipo_usuario,
    SUBSTRING(contrasena, 1, 20) as password_hash_preview,
    fecha_registro
FROM usuarios 
WHERE email = 'etebachalegroup@gmail.com';

-- =============================================
-- NOTAS DE SEGURIDAD:
-- =============================================
-- 
-- 1. NUNCA uses contraseñas simples como 'admin123'
-- 2. Usa un gestor de contraseñas para generar y guardar
-- 3. Este archivo debe ser eliminado después de su uso
-- 4. No compartas el hash de contraseña
-- 5. Cambia contraseñas regularmente (cada 90 días)
--
-- =============================================
