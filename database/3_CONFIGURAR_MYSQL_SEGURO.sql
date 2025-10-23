-- =============================================
-- CONFIGURACIÓN SEGURA DE MYSQL
-- =============================================
--
-- Este script ayuda a configurar MySQL de forma segura
-- 
-- IMPORTANTE:
-- - Ejecutar en phpMyAdmin o consola MySQL como root
-- - Después de ejecutar, actualizar includes/db_connect.php
-- - ELIMINAR este archivo después de usarlo
--
-- =============================================

-- =============================================
-- PASO 1: Crear usuario específico para la aplicación
-- =============================================

-- Verificar usuarios existentes
SELECT 
    User, 
    Host, 
    authentication_string IS NOT NULL as tiene_password
FROM mysql.user
WHERE User IN ('root', 'gq_turismo_user')
ORDER BY User;

-- Crear nuevo usuario (descomenta para ejecutar)
-- Reemplaza 'PASSWORD_SEGURA_AQUI' con una contraseña fuerte
/*
CREATE USER 'gq_turismo_user'@'localhost' 
IDENTIFIED BY 'PASSWORD_SEGURA_AQUI';
*/

-- =============================================
-- PASO 2: Otorgar permisos necesarios
-- =============================================

-- Permisos mínimos necesarios para la aplicación
/*
GRANT SELECT, INSERT, UPDATE, DELETE 
ON gq_turismo.* 
TO 'gq_turismo_user'@'localhost';

-- Permitir crear tablas temporales (opcional)
GRANT CREATE TEMPORARY TABLES 
ON gq_turismo.* 
TO 'gq_turismo_user'@'localhost';

-- Aplicar cambios
FLUSH PRIVILEGES;
*/

-- =============================================
-- PASO 3: Establecer contraseña para root
-- =============================================

-- Cambiar contraseña de root (si no tiene)
-- Reemplaza 'ROOT_PASSWORD_SEGURA' con tu contraseña
/*
ALTER USER 'root'@'localhost' 
IDENTIFIED BY 'ROOT_PASSWORD_SEGURA';

FLUSH PRIVILEGES;
*/

-- =============================================
-- PASO 4: Verificar configuración
-- =============================================

-- Ver usuarios y sus permisos
SELECT 
    User, 
    Host,
    Select_priv,
    Insert_priv,
    Update_priv,
    Delete_priv,
    Create_priv,
    Drop_priv
FROM mysql.user
WHERE User IN ('root', 'gq_turismo_user')
ORDER BY User;

-- Ver permisos específicos de la base de datos
SHOW GRANTS FOR 'gq_turismo_user'@'localhost';

-- =============================================
-- PASO 5: Actualizar includes/db_connect.php
-- =============================================
--
-- Después de crear el nuevo usuario, actualiza:
-- C:\xampp\htdocs\GQ-Turismo\includes\db_connect.php
--
-- De:
-- $username = "root";
-- $password = "";
--
-- A:
-- $username = "gq_turismo_user";
-- $password = "PASSWORD_SEGURA_AQUI";
--
-- =============================================

-- =============================================
-- PASO 6: Probar conexión
-- =============================================

-- Crear archivo temporal: test_conexion.php
-- 
-- <?php
-- $servername = "localhost";
-- $username = "gq_turismo_user";
-- $password = "PASSWORD_SEGURA_AQUI";
-- $dbname = "gq_turismo";
-- 
-- $conn = new mysqli($servername, $username, $password, $dbname);
-- 
-- if ($conn->connect_error) {
--     die("Error de conexión: " . $conn->connect_error);
-- }
-- echo "✅ Conexión exitosa con el nuevo usuario!";
-- 
-- // Probar permisos
-- $result = $conn->query("SELECT COUNT(*) as total FROM usuarios");
-- if ($result) {
--     $row = $result->fetch_assoc();
--     echo "<br>✅ Permisos de lectura funcionando. Usuarios: " . $row['total'];
-- } else {
--     echo "<br>❌ Error en permisos de lectura";
-- }
-- 
-- $conn->close();
-- ?>
--
-- Acceder a: http://localhost/GQ-Turismo/test_conexion.php
-- Si funciona, ELIMINAR test_conexion.php
--
-- =============================================

-- =============================================
-- CONFIGURACIONES ADICIONALES DE SEGURIDAD
-- =============================================

-- Eliminar usuarios anónimos (si existen)
/*
DELETE FROM mysql.user WHERE User = '';
FLUSH PRIVILEGES;
*/

-- Eliminar base de datos de prueba (si existe)
/*
DROP DATABASE IF EXISTS test;
DELETE FROM mysql.db WHERE Db = 'test' OR Db = 'test\\_%';
FLUSH PRIVILEGES;
*/

-- Deshabilitar acceso remoto a root (solo localhost)
/*
DELETE FROM mysql.user 
WHERE User = 'root' AND Host NOT IN ('localhost', '127.0.0.1', '::1');
FLUSH PRIVILEGES;
*/

-- =============================================
-- RECOMENDACIONES DE SEGURIDAD
-- =============================================
--
-- 1. CONTRASEÑAS:
--    - Mínimo 16 caracteres
--    - Combinar letras, números y símbolos
--    - No usar palabras del diccionario
--    - No reutilizar contraseñas
--    - Usar gestor de contraseñas
--
-- 2. USUARIO ROOT:
--    - Establecer contraseña fuerte
--    - Solo usar para tareas administrativas
--    - No usar en la aplicación
--
-- 3. USUARIO DE APLICACIÓN:
--    - Usar usuario específico con permisos limitados
--    - Solo permisos necesarios (SELECT, INSERT, UPDATE, DELETE)
--    - No dar permisos de CREATE, DROP, ALTER en producción
--
-- 4. ARCHIVO DE CONFIGURACIÓN:
--    - Proteger includes/db_connect.php
--    - No subir a repositorios públicos
--    - Usar variables de entorno en producción
--
-- 5. BACKUP:
--    - Hacer backups regulares
--    - Cifrar backups
--    - Probar restauración
--
-- 6. MONITOREO:
--    - Revisar logs de MySQL regularmente
--    - Detectar intentos de acceso fallidos
--    - Alertas de actividad sospechosa
--
-- =============================================

-- Ver configuración actual de MySQL
SHOW VARIABLES LIKE 'version%';
SHOW VARIABLES LIKE 'port';
SHOW VARIABLES LIKE 'socket';
SHOW VARIABLES LIKE 'datadir';

-- Ver tamaño de la base de datos
SELECT 
    table_schema as 'Base de Datos',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) as 'Tamaño (MB)'
FROM information_schema.tables
WHERE table_schema = 'gq_turismo'
GROUP BY table_schema;

-- =============================================
-- CHECKLIST DE SEGURIDAD MYSQL
-- =============================================
--
-- [ ] Usuario root tiene contraseña
-- [ ] Creado usuario específico para la aplicación
-- [ ] Usuario de aplicación tiene solo permisos necesarios
-- [ ] Actualizado includes/db_connect.php
-- [ ] Probada conexión con nuevo usuario
-- [ ] Eliminados usuarios anónimos
-- [ ] Eliminada base de datos test
-- [ ] Root solo accesible desde localhost
-- [ ] Configurado backup automático
-- [ ] ELIMINADO este archivo
--
-- =============================================

-- =============================================
-- COMANDO PARA BACKUP (ejecutar en CMD/Terminal)
-- =============================================
--
-- Crear backup:
-- mysqldump -u root -p gq_turismo > backup_gq_turismo_FECHA.sql
--
-- Restaurar backup:
-- mysql -u root -p gq_turismo < backup_gq_turismo_FECHA.sql
--
-- Backup automático (Windows Task Scheduler):
-- Crear archivo: backup_gq_turismo.bat
--
-- @echo off
-- set fecha=%date:~-4%-%date:~3,2%-%date:~0,2%
-- "C:\xampp\mysql\bin\mysqldump.exe" -u root -pPASSWORD gq_turismo > "C:\backups\gq_turismo_%fecha%.sql"
--
-- Programar tarea diaria en Windows
--
-- =============================================
