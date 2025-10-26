-- Script para popular confirmacion_servicios con pedidos existentes
USE gq_turismo;

-- Crear entradas en confirmacion_servicios para todos los pedidos_servicios existentes
INSERT INTO confirmacion_servicios (
    id_pedido_servicio,
    id_proveedor,
    tipo_proveedor,
    estado,
    fecha_confirmacion,
    fecha_completado
)
SELECT 
    ps.id,
    ps.id_proveedor,
    ps.tipo_proveedor,
    CASE 
        WHEN ps.estado = 'confirmado' THEN 'confirmado'
        WHEN ps.estado = 'completado' THEN 'completado'
        WHEN ps.estado = 'cancelado' THEN 'rechazado'
        ELSE 'pendiente'
    END,
    CASE 
        WHEN ps.estado IN ('confirmado', 'completado') THEN ps.fecha_solicitud
        ELSE NULL
    END,
    CASE 
        WHEN ps.estado = 'completado' THEN ps.fecha_solicitud
        ELSE NULL
    END
FROM pedidos_servicios ps
INNER JOIN usuarios u ON ps.id_proveedor = u.id  -- Solo si el proveedor existe
WHERE NOT EXISTS (
    SELECT 1 FROM confirmacion_servicios cs 
    WHERE cs.id_pedido_servicio = ps.id 
    AND cs.id_proveedor = ps.id_proveedor
)
AND ps.id_proveedor IS NOT NULL;

-- Mostrar resumen
SELECT 
    COUNT(*) as total_confirmaciones,
    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
    SUM(CASE WHEN estado = 'confirmado' THEN 1 ELSE 0 END) as confirmados,
    SUM(CASE WHEN estado = 'completado' THEN 1 ELSE 0 END) as completados,
    SUM(CASE WHEN estado = 'rechazado' THEN 1 ELSE 0 END) as rechazados
FROM confirmacion_servicios;
