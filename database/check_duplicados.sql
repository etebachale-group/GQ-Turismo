-- Script para identificar y limpiar duplicados en destinos
USE gq_turismo;

-- 1. IDENTIFICAR DUPLICADOS
SELECT nombre, categoria, ciudad, COUNT(*) as veces
FROM destinos
GROUP BY nombre, categoria, ciudad
HAVING COUNT(*) > 1;

-- 2. VER DETALLES DE DUPLICADOS
SELECT d.id, d.nombre, d.categoria, d.ciudad, d.fecha_creacion,
       (SELECT COUNT(*) FROM itinerario_destinos WHERE id_destino = d.id) as en_itinerarios
FROM destinos d
WHERE (d.nombre, d.categoria, d.ciudad) IN (
    SELECT nombre, categoria, ciudad
    FROM destinos
    GROUP BY nombre, categoria, ciudad
    HAVING COUNT(*) > 1
)
ORDER BY d.nombre, d.categoria, d.ciudad, d.id;

-- 3. INFORMACIÓN PARA DECISIÓN MANUAL
-- Este script NO elimina automáticamente, solo muestra información
-- Para eliminar duplicados manualmente:

-- OPCIÓN A: Mantener el registro más antiguo y eliminar los demás
-- (Ejecutar solo después de verificar)
/*
DELETE d1 FROM destinos d1
INNER JOIN destinos d2
WHERE d1.id > d2.id
AND d1.nombre = d2.nombre
AND d1.categoria = d2.categoria
AND d1.ciudad = d2.ciudad
AND (SELECT COUNT(*) FROM itinerario_destinos WHERE id_destino = d1.id) = 0;
*/

-- OPCIÓN B: Mantener el que tiene más referencias
-- Ver primero cuál tiene más referencias:
SELECT d.id, d.nombre, d.categoria, d.ciudad,
       COUNT(id_dest.id) as referencias
FROM destinos d
LEFT JOIN itinerario_destinos id_dest ON d.id = id_dest.id_destino
WHERE (d.nombre, d.categoria, d.ciudad) IN (
    SELECT nombre, categoria, ciudad
    FROM destinos
    GROUP BY nombre, categoria, ciudad
    HAVING COUNT(*) > 1
)
GROUP BY d.id
ORDER BY d.nombre, referencias DESC;

-- 4. VERIFICACIÓN FINAL
-- Después de limpiar, ejecutar esto para verificar:
SELECT 
    COUNT(*) as total_destinos,
    COUNT(DISTINCT CONCAT(nombre, '-', categoria, '-', ciudad)) as destinos_unicos,
    COUNT(*) - COUNT(DISTINCT CONCAT(nombre, '-', categoria, '-', ciudad)) as posibles_duplicados
FROM destinos;

-- 5. VERIFICAR INTEGRIDAD
SELECT 'Destinos sin imagen' as problema, COUNT(*) as cantidad
FROM destinos
WHERE imagen IS NULL OR imagen = ''
UNION ALL
SELECT 'Destinos sin categoría', COUNT(*)
FROM destinos
WHERE categoria IS NULL OR categoria = ''
UNION ALL
SELECT 'Destinos sin ciudad', COUNT(*)
FROM destinos
WHERE ciudad IS NULL OR ciudad = ''
UNION ALL
SELECT 'Destinos sin precio', COUNT(*)
FROM destinos
WHERE precio IS NULL OR precio = 0;
