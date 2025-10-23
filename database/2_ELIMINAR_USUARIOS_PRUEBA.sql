-- =============================================
-- ELIMINAR USUARIOS DE PRUEBA
-- =============================================
--
-- IMPORTANTE:
-- Este script eliminará usuarios de prueba con contraseñas débiles
-- También eliminará en cascada sus datos relacionados
--
-- ANTES DE EJECUTAR:
-- 1. Verificar que NO necesitas estos datos
-- 2. Hacer backup de la base de datos
-- 3. Revisar qué usuarios se eliminarán
--
-- DESPUÉS DE EJECUTAR:
-- 1. Verificar que tus usuarios reales siguen existiendo
-- 2. ELIMINAR este archivo
--
-- =============================================

-- Ver usuarios que serán eliminados
SELECT 
    id,
    nombre,
    email,
    tipo_usuario,
    fecha_registro
FROM usuarios
WHERE email IN (
    'admin@gqturismo.com',
    'agencia@example.com',
    'guia@example.com',
    'guia2@example.com',
    'local@example.com'
)
ORDER BY tipo_usuario, nombre;

-- =============================================
-- PASO 1: Verificar datos relacionados
-- =============================================

-- Ver agencias que serán eliminadas
SELECT a.id, a.nombre_agencia, u.email
FROM agencias a
JOIN usuarios u ON a.id_usuario = u.id
WHERE u.email IN (
    'agencia@example.com'
);

-- Ver guías que serán eliminados
SELECT g.id, g.nombre_guia, u.email
FROM guias_turisticos g
JOIN usuarios u ON g.id_usuario = u.id
WHERE u.email IN (
    'guia@example.com',
    'guia2@example.com'
);

-- Ver locales que serán eliminados
SELECT l.id, l.nombre_local, u.email
FROM lugares_locales l
JOIN usuarios u ON l.id_usuario = u.id
WHERE u.email IN (
    'local@example.com'
);

-- =============================================
-- PASO 2: Eliminar usuarios de prueba
-- =============================================
--
-- DESCOMENTA las siguientes líneas para ejecutar
-- ADVERTENCIA: Esta acción NO se puede deshacer
--
-- =============================================

-- Eliminar usuarios de prueba (las foreign keys eliminarán datos relacionados)
/*
DELETE FROM usuarios
WHERE email IN (
    'admin@gqturismo.com',
    'agencia@example.com',
    'guia@example.com',
    'guia2@example.com',
    'local@example.com'
);
*/

-- =============================================
-- PASO 3: Verificar eliminación
-- =============================================

-- Verificar que los usuarios fueron eliminados
SELECT 
    COUNT(*) as usuarios_restantes,
    GROUP_CONCAT(email SEPARATOR ', ') as emails_que_aun_existen
FROM usuarios
WHERE email IN (
    'admin@gqturismo.com',
    'agencia@example.com',
    'guia@example.com',
    'guia2@example.com',
    'local@example.com'
);

-- Ver todos los usuarios que quedaron
SELECT 
    id,
    nombre,
    email,
    tipo_usuario,
    fecha_registro
FROM usuarios
ORDER BY tipo_usuario, fecha_registro DESC;

-- =============================================
-- PASO 4: Limpieza adicional (OPCIONAL)
-- =============================================

-- Eliminar itinerarios huérfanos (sin usuario)
-- Solo si deseas limpiar datos de prueba adicionales
/*
DELETE FROM itinerarios
WHERE id_usuario NOT IN (SELECT id FROM usuarios);

DELETE FROM reservas
WHERE id_usuario NOT IN (SELECT id FROM usuarios);

DELETE FROM pedidos_servicios
WHERE id_turista NOT IN (SELECT id FROM usuarios);

DELETE FROM mensajes
WHERE sender_id NOT IN (SELECT id FROM usuarios)
   OR receiver_id NOT IN (SELECT id FROM usuarios);

DELETE FROM valoraciones
WHERE reviewer_id NOT IN (SELECT id FROM usuarios);
*/

-- =============================================
-- RESUMEN DE ELIMINACIONES
-- =============================================

-- Usuarios
SELECT 'Usuarios' as tabla, COUNT(*) as total FROM usuarios;

-- Agencias
SELECT 'Agencias' as tabla, COUNT(*) as total FROM agencias;

-- Guías
SELECT 'Guías' as tabla, COUNT(*) as total FROM guias_turisticos;

-- Locales
SELECT 'Locales' as tabla, COUNT(*) as total FROM lugares_locales;

-- Itinerarios
SELECT 'Itinerarios' as tabla, COUNT(*) as total FROM itinerarios;

-- Pedidos
SELECT 'Pedidos' as tabla, COUNT(*) as total FROM pedidos_servicios;

-- Mensajes
SELECT 'Mensajes' as tabla, COUNT(*) as total FROM mensajes;

-- Valoraciones
SELECT 'Valoraciones' as tabla, COUNT(*) as total FROM valoraciones;

-- =============================================
-- NOTAS IMPORTANTES:
-- =============================================
--
-- 1. Las eliminaciones son EN CASCADA debido a las foreign keys
-- 2. Se eliminarán automáticamente:
--    - Agencias del usuario
--    - Guías del usuario
--    - Locales del usuario
--    - Servicios asociados
--    - Imágenes asociadas
--    - Pedidos realizados/recibidos
--    - Mensajes enviados/recibidos
--    - Valoraciones realizadas
--    - Itinerarios creados
--
-- 3. NO se puede deshacer
-- 4. Hacer BACKUP antes de ejecutar
-- 5. ELIMINAR este archivo después de ejecutar
--
-- =============================================

-- Backup rápido (ejecutar ANTES de eliminar)
-- En consola MySQL:
-- mysqldump -u root -p gq_turismo > backup_antes_eliminar_usuarios.sql
--
-- Para restaurar (si algo sale mal):
-- mysql -u root -p gq_turismo < backup_antes_eliminar_usuarios.sql
--
-- =============================================
