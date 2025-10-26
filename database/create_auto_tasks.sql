-- Script para crear tareas automáticas cuando se confirman pedidos

USE gq_turismo;

DELIMITER //

-- Trigger para crear tareas cuando se confirma un servicio
CREATE TRIGGER IF NOT EXISTS after_servicio_confirmado
AFTER UPDATE ON pedidos_servicios
FOR EACH ROW
BEGIN
    -- Si el servicio cambió a confirmado y tiene itinerario
    IF NEW.estado = 'confirmado' AND OLD.estado != 'confirmado' AND NEW.id_itinerario IS NOT NULL THEN
        -- Verificar si no existe ya una tarea para este pedido
        IF NOT EXISTS (
            SELECT 1 FROM itinerario_tareas 
            WHERE id_itinerario = NEW.id_itinerario 
            AND id_proveedor = NEW.id_proveedor
            AND titulo LIKE CONCAT('%', 
                CASE 
                    WHEN NEW.tipo_item = 'servicio' THEN 'Servicio'
                    WHEN NEW.tipo_item = 'menu' THEN 'Menú'
                    WHEN NEW.tipo_item = 'guia_destino' THEN 'Guía'
                    ELSE 'Actividad'
                END, '%')
            LIMIT 1
        ) THEN
            -- Crear tarea automáticamente
            INSERT INTO itinerario_tareas (
                id_itinerario,
                id_destino,
                id_proveedor,
                tipo_proveedor,
                tipo_tarea,
                titulo,
                descripcion,
                fecha_hora_inicio,
                fecha_hora_fin,
                estado
            ) VALUES (
                NEW.id_itinerario,
                NEW.id_destino,
                NEW.id_proveedor,
                NEW.tipo_proveedor,
                CASE 
                    WHEN NEW.tipo_proveedor = 'agencia' THEN 'transporte'
                    WHEN NEW.tipo_proveedor = 'guia' THEN 'guia'
                    WHEN NEW.tipo_proveedor = 'local' AND NEW.tipo_item = 'menu' THEN 'comida'
                    WHEN NEW.tipo_proveedor = 'local' THEN 'actividad'
                    ELSE 'otro'
                END,
                CONCAT(
                    CASE 
                        WHEN NEW.tipo_item = 'servicio' THEN 'Servicio de '
                        WHEN NEW.tipo_item = 'menu' THEN 'Menú de '
                        WHEN NEW.tipo_item = 'guia_destino' THEN 'Guía en '
                        ELSE 'Actividad con '
                    END,
                    NEW.tipo_proveedor
                ),
                CONCAT('Pedido confirmado. Personas: ', NEW.numero_personas),
                NEW.fecha_servicio,
                DATE_ADD(NEW.fecha_servicio, INTERVAL 2 HOUR),
                'pendiente'
            );
        END IF;
    END IF;
END//

DELIMITER ;

-- Crear tareas para pedidos ya confirmados que no tienen tarea
INSERT INTO itinerario_tareas (
    id_itinerario,
    id_destino,
    id_proveedor,
    tipo_proveedor,
    tipo_tarea,
    titulo,
    descripcion,
    fecha_hora_inicio,
    fecha_hora_fin,
    estado
)
SELECT DISTINCT
    ps.id_itinerario,
    ps.id_destino,
    ps.id_proveedor,
    ps.tipo_proveedor,
    CASE 
        WHEN ps.tipo_proveedor = 'agencia' THEN 'transporte'
        WHEN ps.tipo_proveedor = 'guia' THEN 'guia'
        WHEN ps.tipo_proveedor = 'local' AND ps.tipo_item = 'menu' THEN 'comida'
        WHEN ps.tipo_proveedor = 'local' THEN 'actividad'
        ELSE 'otro'
    END,
    CONCAT(
        CASE 
            WHEN ps.tipo_item = 'servicio' THEN 'Servicio de '
            WHEN ps.tipo_item = 'menu' THEN 'Menú de '
            WHEN ps.tipo_item = 'guia_destino' THEN 'Guía en '
            ELSE 'Actividad con '
        END,
        ps.tipo_proveedor
    ),
    CONCAT('Pedido #', ps.id, '. Personas: ', ps.numero_personas),
    ps.fecha_servicio,
    DATE_ADD(ps.fecha_servicio, INTERVAL 2 HOUR),
    'pendiente'
FROM pedidos_servicios ps
WHERE ps.estado = 'confirmado'
    AND ps.id_itinerario IS NOT NULL
    AND NOT EXISTS (
        SELECT 1 FROM itinerario_tareas it
        WHERE it.id_itinerario = ps.id_itinerario
        AND it.id_proveedor = ps.id_proveedor
        AND it.fecha_hora_inicio = ps.fecha_servicio
    );

SELECT 'Tareas creadas automáticamente' as Resultado;
