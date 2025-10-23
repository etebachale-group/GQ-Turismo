-- Desactivar temporalmente las comprobaciones de clave foránea para evitar errores de eliminación
SET FOREIGN_KEY_CHECKS = 0;

-- Eliminar datos de tablas dependientes primero
DELETE FROM guias_destinos;
DELETE FROM itinerario_destinos;
DELETE FROM imagenes_destino;
DELETE FROM imagenes_guia;
DELETE FROM imagenes_local;
DELETE FROM menus_agencia;
DELETE FROM menus_local;
DELETE FROM pedidos_servicios;
DELETE FROM reservas;
DELETE FROM servicios_agencia;
DELETE FROM servicios_guia;
DELETE FROM servicios_local;
DELETE FROM descuentos;
DELETE FROM mensajes;
DELETE FROM valoraciones;
DELETE FROM itinerarios;

-- Eliminar datos de tablas principales
DELETE FROM destinos;
DELETE FROM agencias;
DELETE FROM guias_turisticos;
DELETE FROM lugares_locales;
DELETE FROM carouseles;
DELETE FROM publicidades;

-- Eliminar *TODOS* los usuarios (incluyendo super administradores)
DELETE FROM usuarios;

-- Reactivar las comprobaciones de clave foránea
SET FOREIGN_KEY_CHECKS = 1;