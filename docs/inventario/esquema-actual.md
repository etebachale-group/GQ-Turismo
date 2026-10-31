# INVENTARIO DEL ESQUEMA ACTUAL DE BASE DE DATOS (DISC-02)

**Base de Datos:** `gq_turismo` (MySQL Local XAMPP)
**Total de Tablas Existentes:** 37

## 1. Resumen de Tablas, Registros y Estado de Uso

| Tabla | Filas en BD | Referencias en Código PHP | Estado / Diagnóstico |
| :--- | :---: | :---: | :--- |
| `agencias` | 1 | 132 | ✅ En Uso Activo |
| `carouseles` | 3 | 11 | ✅ En Uso Activo |
| `confirmacion_servicios` | 0 | 7 | ✅ En Uso Activo |
| `confirmaciones_servicios` | 0 | 1 | ✅ En Uso Activo |
| `descuentos` | 1 | 10 | ✅ En Uso Activo |
| `destinos` | 3 | 258 | ✅ En Uso Activo |
| `guia_destinos` | 0 | 8 | ✅ En Uso Activo |
| `guia_destinos_disponibles` | 0 | 0 | ✅ En Uso Activo |
| `guias_destinos` | 0 | 4 | ✅ En Uso Activo |
| `guias_turisticos` | 1 | 44 | ✅ En Uso Activo |
| `imagenes_destino` | 1 | 10 | ✅ En Uso Activo |
| `imagenes_guia` | 1 | 9 | ✅ En Uso Activo |
| `imagenes_local` | 1 | 9 | ✅ En Uso Activo |
| `itinerario_agencias` | 0 | 4 | ✅ En Uso Activo |
| `itinerario_destinos` | 7 | 22 | ✅ En Uso Activo |
| `itinerario_guias` | 0 | 7 | ✅ En Uso Activo |
| `itinerario_locales` | 0 | 4 | ✅ En Uso Activo |
| `itinerario_servicios` | 7 | 10 | ✅ En Uso Activo |
| `itinerario_tareas` | 0 | 22 | ✅ En Uso Activo |
| `itinerarios` | 3 | 72 | ✅ En Uso Activo |
| `locales_turisticos` | 0 | 1 | ✅ En Uso Activo |
| `lugares_locales` | 2 | 34 | ✅ En Uso Activo |
| `mensajes` | 21 | 29 | ✅ En Uso Activo |
| `menus_agencia` | 0 | 14 | ✅ En Uso Activo |
| `menus_local` | 1 | 13 | ✅ En Uso Activo |
| `notificaciones` | 0 | 3 | ✅ En Uso Activo |
| `pedidos` | 0 | 97 | ✅ En Uso Activo |
| `pedidos_servicios` | 8 | 40 | ✅ En Uso Activo |
| `publicidad_carousel` | 0 | 5 | ✅ En Uso Activo |
| `publicidades` | 1 | 15 | ✅ En Uso Activo |
| `reserva_servicios` | 19 | 10 | ✅ En Uso Activo |
| `reservas` | 4 | 46 | ✅ En Uso Activo |
| `servicios_agencia` | 1 | 14 | ✅ En Uso Activo |
| `servicios_guia` | 1 | 14 | ✅ En Uso Activo |
| `servicios_local` | 2 | 13 | ✅ En Uso Activo |
| `usuarios` | 7 | 58 | ✅ En Uso Activo |
| `valoraciones` | 0 | 14 | ✅ En Uso Activo |

## 2. Detalle Estructural por Tabla

### Tabla: `agencias`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_usuario` | `int(11)` | NO | UNI | NULL |  |
| `nombre_agencia` | `varchar(255)` | NO |  | NULL |  |
| `descripcion` | `text` | YES |  | NULL |  |
| `contacto_email` | `varchar(255)` | YES |  | NULL |  |
| `contacto_telefono` | `varchar(50)` | YES |  | NULL |  |
| `imagen_perfil` | `varchar(255)` | YES |  | NULL |  |
| `fecha_registro` | `timestamp` | NO |  | current_timestamp() |  |
| `latitude` | `decimal(10,8)` | YES |  | NULL |  |
| `longitude` | `decimal(11,8)` | YES |  | NULL |  |

### Tabla: `carouseles`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `nombre` | `varchar(255)` | NO |  | NULL |  |
| `ruta_imagen` | `varchar(255)` | NO |  | NULL |  |
| `enlace` | `varchar(255)` | YES |  | NULL |  |
| `orden` | `int(11)` | NO |  | 0 |  |
| `activo` | `tinyint(1)` | NO |  | 1 |  |
| `fecha_creacion` | `timestamp` | NO |  | current_timestamp() |  |

### Tabla: `confirmacion_servicios`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_pedido_servicio` | `int(11)` | NO | MUL | NULL |  |
| `id_proveedor` | `int(11)` | NO | MUL | NULL |  |
| `tipo_proveedor` | `enum('agencia','guia','local')` | NO |  | NULL |  |
| `estado` | `enum('pendiente','confirmado','rechazado','completado')` | YES | MUL | pendiente |  |
| `notas` | `text` | YES |  | NULL |  |
| `fecha_confirmacion` | `datetime` | YES |  | NULL |  |
| `fecha_completado` | `datetime` | YES |  | NULL |  |
| `fecha_creacion` | `timestamp` | NO |  | current_timestamp() |  |
| `fecha_actualizacion` | `timestamp` | NO |  | current_timestamp() | on update current_timestamp() |

### Tabla: `confirmaciones_servicios`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_pedido_servicio` | `int(11)` | NO |  | NULL |  |
| `id_proveedor` | `int(11)` | NO | MUL | NULL |  |
| `tipo_proveedor` | `enum('agencia','guia','local')` | NO |  | NULL |  |
| `estado_confirmacion` | `enum('pendiente','confirmado','rechazado','completado')` | YES | MUL | pendiente |  |
| `fecha_confirmacion` | `datetime` | YES |  | NULL |  |
| `fecha_servicio` | `datetime` | YES |  | NULL |  |
| `notas_proveedor` | `text` | YES |  | NULL |  |
| `valoracion` | `int(1)` | YES |  | NULL |  |
| `comentario_valoracion` | `text` | YES |  | NULL |  |
| `fecha_valoracion` | `datetime` | YES |  | NULL |  |

### Tabla: `descuentos`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `agency_id` | `int(11)` | NO | MUL | NULL |  |
| `discount_code` | `varchar(50)` | NO | UNI | NULL |  |
| `percentage` | `decimal(5,2)` | NO |  | NULL |  |
| `start_date` | `date` | NO |  | NULL |  |
| `end_date` | `date` | NO |  | NULL |  |
| `is_active` | `tinyint(1)` | YES |  | 1 |  |
| `created_at` | `datetime` | YES |  | current_timestamp() |  |

### Tabla: `destinos`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `nombre` | `varchar(255)` | NO |  | NULL |  |
| `descripcion` | `text` | NO |  | NULL |  |
| `categoria` | `varchar(100)` | NO | MUL | NULL |  |
| `imagen` | `varchar(255)` | NO |  | NULL |  |
| `precio` | `decimal(10,2)` | NO | MUL | NULL |  |
| `latitude` | `decimal(10,8)` | YES |  | NULL |  |
| `longitude` | `decimal(11,8)` | YES |  | NULL |  |
| `ciudad` | `varchar(255)` | YES | MUL | NULL |  |

### Tabla: `guia_destinos`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_guia` | `int(11)` | NO | MUL | NULL |  |
| `id_destino` | `int(11)` | NO | MUL | NULL |  |
| `experiencia_anos` | `int(11)` | YES |  | 0 |  |
| `descripcion` | `text` | YES |  | NULL |  |
| `certificaciones` | `text` | YES |  | NULL |  |
| `idiomas` | `varchar(255)` | YES |  | NULL |  |
| `tarifa_dia` | `decimal(10,2)` | YES |  | 0.00 |  |
| `disponible` | `tinyint(1)` | YES |  | 1 |  |
| `creado_en` | `timestamp` | NO |  | current_timestamp() |  |

### Tabla: `guia_destinos_disponibles`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_guia` | `int(11)` | NO | MUL | NULL |  |
| `id_destino` | `int(11)` | NO | MUL | NULL |  |
| `precio_base` | `decimal(10,2)` | YES |  | 0.00 |  |
| `disponible` | `tinyint(1)` | YES |  | 1 |  |
| `notas` | `text` | YES |  | NULL |  |
| `fecha_agregado` | `timestamp` | NO |  | current_timestamp() |  |

### Tabla: `guias_destinos`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_guia` | `int(11)` | NO | MUL | NULL |  |
| `id_destino` | `int(11)` | NO | MUL | NULL |  |

### Tabla: `guias_turisticos`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_usuario` | `int(11)` | NO | UNI | NULL |  |
| `nombre_guia` | `varchar(255)` | NO |  | NULL |  |
| `descripcion` | `text` | YES |  | NULL |  |
| `especialidades` | `varchar(255)` | YES |  | NULL |  |
| `precio_hora` | `decimal(10,2)` | NO |  | 0.00 |  |
| `contacto_email` | `varchar(255)` | YES |  | NULL |  |
| `contacto_telefono` | `varchar(50)` | YES |  | NULL |  |
| `imagen_perfil` | `varchar(255)` | YES |  | NULL |  |
| `fecha_registro` | `timestamp` | NO |  | current_timestamp() |  |
| `current_latitude` | `decimal(10,8)` | YES |  | NULL |  |
| `current_longitude` | `decimal(11,8)` | YES |  | NULL |  |
| `last_updated` | `datetime` | YES |  | NULL |  |
| `ciudad_operacion` | `varchar(255)` | YES | MUL | NULL |  |
| `ciudad` | `varchar(255)` | YES |  | NULL |  |

### Tabla: `imagenes_destino`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_destino` | `int(11)` | NO | MUL | NULL |  |
| `ruta_imagen` | `varchar(255)` | NO |  | NULL |  |
| `descripcion` | `varchar(255)` | YES |  | NULL |  |
| `fecha_subida` | `datetime` | YES |  | current_timestamp() |  |

### Tabla: `imagenes_guia`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_guia` | `int(11)` | NO | MUL | NULL |  |
| `ruta_imagen` | `varchar(255)` | NO |  | NULL |  |
| `descripcion` | `varchar(255)` | YES |  | NULL |  |
| `fecha_subida` | `timestamp` | NO |  | current_timestamp() |  |

### Tabla: `imagenes_local`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_local` | `int(11)` | NO | MUL | NULL |  |
| `ruta_imagen` | `varchar(255)` | NO |  | NULL |  |
| `descripcion` | `varchar(255)` | YES |  | NULL |  |
| `fecha_subida` | `timestamp` | NO |  | current_timestamp() |  |

### Tabla: `itinerario_agencias`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_itinerario` | `int(11)` | NO | MUL | NULL |  |
| `id_agencia` | `int(11)` | NO | MUL | NULL |  |
| `fecha_agregado` | `timestamp` | NO |  | current_timestamp() |  |
| `estado` | `enum('pendiente','confirmado','completado','cancelado')` | YES |  | pendiente |  |
| `notas` | `text` | YES |  | NULL |  |
| `fecha_confirmacion` | `datetime` | YES |  | NULL |  |
| `fecha_completado` | `datetime` | YES |  | NULL |  |

### Tabla: `itinerario_destinos`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_itinerario` | `int(11)` | NO | MUL | NULL |  |
| `id_destino` | `int(11)` | NO | MUL | NULL |  |
| `orden` | `int(11)` | NO |  | NULL |  |
| `precio` | `decimal(10,2)` | YES |  | 0.00 |  |
| `estado` | `enum('pendiente','en_progreso','completado','cancelado')` | YES |  | pendiente |  |
| `fecha_inicio` | `date` | YES |  | NULL |  |
| `fecha_fin` | `date` | YES |  | NULL |  |
| `descripcion` | `text` | YES |  | NULL |  |

### Tabla: `itinerario_guias`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_itinerario` | `int(11)` | NO | MUL | NULL |  |
| `id_guia` | `int(11)` | NO | MUL | NULL |  |
| `fecha_agregado` | `timestamp` | NO |  | current_timestamp() |  |
| `estado` | `enum('pendiente','confirmado','completado','cancelado')` | YES |  | pendiente |  |
| `notas` | `text` | YES |  | NULL |  |
| `fecha_confirmacion` | `datetime` | YES |  | NULL |  |
| `fecha_completado` | `datetime` | YES |  | NULL |  |

### Tabla: `itinerario_locales`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_itinerario` | `int(11)` | NO | MUL | NULL |  |
| `id_local` | `int(11)` | NO | MUL | NULL |  |
| `fecha_agregado` | `timestamp` | NO |  | current_timestamp() |  |
| `estado` | `enum('pendiente','confirmado','completado','cancelado')` | YES |  | pendiente |  |
| `notas` | `text` | YES |  | NULL |  |
| `fecha_confirmacion` | `datetime` | YES |  | NULL |  |
| `fecha_completado` | `datetime` | YES |  | NULL |  |

### Tabla: `itinerario_servicios`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_itinerario` | `int(11)` | NO | MUL | NULL |  |
| `tipo_servicio` | `enum('guia','agencia','local')` | NO | MUL | NULL |  |
| `id_servicio` | `int(11)` | NO |  | NULL |  |
| `notas` | `text` | YES |  | NULL |  |
| `fecha_agregado` | `timestamp` | NO |  | current_timestamp() |  |

### Tabla: `itinerario_tareas`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_itinerario` | `int(11)` | NO | MUL | NULL |  |
| `id_destino` | `int(11)` | YES |  | NULL |  |
| `titulo` | `varchar(255)` | NO |  | NULL |  |
| `descripcion` | `text` | YES |  | NULL |  |
| `tipo_tarea` | `enum('transporte','alojamiento','actividad','comida','guia','otro')` | YES |  | actividad |  |
| `fecha_hora_inicio` | `datetime` | YES | MUL | NULL |  |
| `fecha_hora_fin` | `datetime` | YES |  | NULL |  |
| `ubicacion` | `varchar(255)` | YES |  | NULL |  |
| `estado` | `enum('pendiente','en_progreso','completado','cancelado')` | YES | MUL | pendiente |  |
| `prioridad` | `enum('baja','media','alta')` | YES |  | media |  |
| `id_proveedor` | `int(11)` | YES | MUL | NULL |  |
| `tipo_proveedor` | `enum('agencia','guia','local')` | YES |  | NULL |  |
| `creado_por` | `int(11)` | NO |  | NULL |  |
| `completado_por` | `int(11)` | YES |  | NULL |  |
| `fecha_creacion` | `datetime` | YES |  | current_timestamp() |  |
| `fecha_completado` | `datetime` | YES |  | NULL |  |
| `notas` | `text` | YES |  | NULL |  |
| `ubicacion_inicio` | `varchar(255)` | YES |  | NULL |  |
| `ubicacion_fin` | `varchar(255)` | YES |  | NULL |  |

### Tabla: `itinerarios`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_turista` | `int(11)` | YES | MUL | NULL |  |
| `id_usuario` | `int(11)` | NO | MUL | NULL |  |
| `nombre_itinerario` | `varchar(255)` | NO |  | NULL |  |
| `descripcion` | `text` | YES |  | NULL |  |
| `precio_total` | `decimal(10,2)` | NO |  | 0.00 |  |
| `fecha_creacion` | `timestamp` | NO |  | current_timestamp() |  |
| `ciudad` | `varchar(255)` | YES |  | NULL |  |
| `alojamiento_id` | `int(11)` | YES |  | NULL |  |
| `guia_id` | `int(11)` | YES |  | NULL |  |
| `fecha_inicio` | `date` | YES |  | NULL |  |
| `fecha_fin` | `date` | YES |  | NULL |  |
| `presupuesto_estimado` | `decimal(10,2)` | YES |  | 0.00 |  |
| `notas` | `text` | YES |  | NULL |  |
| `estado` | `enum('planificacion','confirmado','completado')` | YES |  | planificacion |  |

### Tabla: `locales_turisticos`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_usuario` | `int(11)` | NO | MUL | NULL |  |
| `nombre` | `varchar(255)` | NO |  | NULL |  |
| `descripcion` | `text` | YES |  | NULL |  |
| `direccion` | `varchar(500)` | YES |  | NULL |  |
| `ciudad` | `varchar(100)` | YES |  | NULL |  |
| `pais` | `varchar(100)` | YES |  | NULL |  |
| `latitud` | `decimal(10,8)` | YES |  | NULL |  |
| `longitud` | `decimal(11,8)` | YES |  | NULL |  |
| `telefono` | `varchar(20)` | YES |  | NULL |  |
| `email` | `varchar(255)` | YES |  | NULL |  |
| `sitio_web` | `varchar(500)` | YES |  | NULL |  |
| `tipo_local` | `varchar(50)` | YES |  | NULL |  |
| `horario` | `text` | YES |  | NULL |  |
| `calificacion` | `decimal(3,2)` | YES |  | 0.00 |  |
| `imagen_principal` | `varchar(500)` | YES |  | NULL |  |
| `estado` | `enum('activo','inactivo','pendiente')` | YES |  | activo |  |
| `verificado` | `tinyint(1)` | YES |  | 0 |  |
| `fecha_registro` | `timestamp` | NO |  | current_timestamp() |  |
| `updated_at` | `timestamp` | NO |  | current_timestamp() | on update current_timestamp() |

### Tabla: `lugares_locales`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_usuario` | `int(11)` | NO | UNI | NULL |  |
| `nombre_local` | `varchar(255)` | NO |  | NULL |  |
| `descripcion` | `text` | YES |  | NULL |  |
| `tipo_local` | `varchar(100)` | YES | MUL | NULL |  |
| `direccion` | `varchar(255)` | YES |  | NULL |  |
| `contacto_email` | `varchar(255)` | YES |  | NULL |  |
| `contacto_telefono` | `varchar(50)` | YES |  | NULL |  |
| `imagen_perfil` | `varchar(255)` | YES |  | NULL |  |
| `fecha_registro` | `timestamp` | NO |  | current_timestamp() |  |
| `latitude` | `decimal(10,8)` | YES |  | NULL |  |
| `longitude` | `decimal(11,8)` | YES |  | NULL |  |
| `ciudad` | `varchar(255)` | YES |  | NULL |  |

### Tabla: `mensajes`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `sender_id` | `int(11)` | NO | MUL | NULL |  |
| `sender_type` | `enum('turista','agencia','guia','local')` | NO |  | NULL |  |
| `receiver_id` | `int(11)` | NO | MUL | NULL |  |
| `receiver_type` | `enum('turista','agencia','guia','local')` | NO |  | NULL |  |
| `message` | `text` | NO |  | NULL |  |
| `timestamp` | `datetime` | YES | MUL | current_timestamp() |  |
| `is_read` | `tinyint(1)` | YES |  | 0 |  |
| `id_emisor` | `int(11)` | YES | MUL | NULL |  |
| `id_receptor` | `int(11)` | YES | MUL | NULL |  |
| `leido` | `tinyint(1)` | YES |  | 0 |  |
| `fecha_lectura` | `datetime` | YES |  | NULL |  |
| `tipo_mensaje` | `varchar(50)` | YES |  | texto |  |
| `archivo_adjunto` | `varchar(500)` | YES |  | NULL |  |

### Tabla: `menus_agencia`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_agencia` | `int(11)` | NO | MUL | NULL |  |
| `nombre_menu` | `varchar(255)` | NO |  | NULL |  |
| `descripcion` | `text` | YES |  | NULL |  |
| `precio_total` | `decimal(10,2)` | NO |  | NULL |  |
| `fecha_creacion` | `timestamp` | NO |  | current_timestamp() |  |

### Tabla: `menus_local`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_local` | `int(11)` | NO | MUL | NULL |  |
| `nombre_menu` | `varchar(255)` | NO |  | NULL |  |
| `descripcion` | `text` | YES |  | NULL |  |
| `precio_total` | `decimal(10,2)` | NO |  | NULL |  |
| `fecha_creacion` | `timestamp` | NO |  | current_timestamp() |  |

### Tabla: `notificaciones`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_usuario` | `int(11)` | NO | MUL | NULL |  |
| `tipo` | `enum('itinerario','reserva','mensaje','confirmacion','sistema')` | NO |  | NULL |  |
| `titulo` | `varchar(255)` | NO |  | NULL |  |
| `mensaje` | `text` | NO |  | NULL |  |
| `url` | `varchar(255)` | YES |  | NULL |  |
| `leido` | `tinyint(1)` | YES |  | 0 |  |
| `fecha_creacion` | `datetime` | YES | MUL | current_timestamp() |  |

### Tabla: `pedidos`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_usuario` | `int(11)` | NO | MUL | NULL |  |
| `id_reserva` | `int(11)` | YES | MUL | NULL |  |
| `total` | `decimal(10,2)` | NO |  | NULL |  |
| `estado` | `enum('pendiente','procesando','completado','cancelado')` | YES |  | pendiente |  |
| `metodo_pago` | `varchar(50)` | YES |  | NULL |  |
| `fecha_pedido` | `timestamp` | NO |  | current_timestamp() |  |
| `fecha_actualizacion` | `timestamp` | NO |  | current_timestamp() | on update current_timestamp() |

### Tabla: `pedidos_servicios`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_turista` | `int(11)` | NO | MUL | NULL |  |
| `tipo_proveedor` | `enum('agencia','guia','local')` | NO | MUL | NULL |  |
| `id_proveedor` | `int(11)` | NO | MUL | NULL |  |
| `id_servicio_o_menu` | `int(11)` | NO |  | NULL |  |
| `tipo_item` | `enum('servicio','menu')` | NO |  | NULL |  |
| `nombre_servicio` | `varchar(255)` | YES |  | NULL |  |
| `id_itinerario` | `int(11)` | YES | MUL | NULL |  |
| `fecha_solicitud` | `timestamp` | NO |  | current_timestamp() |  |
| `fecha_servicio` | `date` | YES |  | NULL |  |
| `cantidad_personas` | `int(11)` | NO |  | 1 |  |
| `precio_unitario` | `decimal(10,2)` | NO |  | NULL |  |
| `precio_total` | `decimal(10,2)` | NO |  | NULL |  |
| `estado` | `enum('pendiente','confirmado','cancelado','completado','pagado')` | NO | MUL | pendiente |  |
| `id_destino` | `int(11)` | YES | MUL | NULL |  |
| `confirmado_proveedor` | `tinyint(1)` | YES |  | 0 |  |
| `fecha_confirmacion` | `datetime` | YES |  | NULL |  |
| `notas_proveedor` | `text` | YES |  | NULL |  |
| `hora_servicio` | `time` | YES |  | NULL |  |
| `numero_personas` | `int(11)` | YES |  | 1 |  |
| `notas_especiales` | `text` | YES |  | NULL |  |

### Tabla: `publicidad_carousel`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `titulo` | `varchar(255)` | NO |  | NULL |  |
| `descripcion` | `text` | YES |  | NULL |  |
| `imagen` | `varchar(500)` | YES |  | NULL |  |
| `enlace` | `varchar(255)` | YES |  | NULL |  |
| `orden` | `int(11)` | YES |  | 0 |  |
| `activo` | `tinyint(1)` | YES | MUL | 1 |  |
| `fecha_inicio` | `date` | YES |  | NULL |  |
| `fecha_fin` | `date` | YES |  | NULL |  |
| `fecha_creacion` | `timestamp` | NO |  | current_timestamp() |  |

### Tabla: `publicidades`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `titulo` | `varchar(255)` | NO |  | NULL |  |
| `descripcion` | `text` | YES |  | NULL |  |
| `imagen` | `varchar(255)` | NO |  | NULL |  |
| `enlace` | `varchar(255)` | YES |  | NULL |  |
| `fecha_inicio` | `date` | YES |  | NULL |  |
| `fecha_fin` | `date` | YES |  | NULL |  |
| `activo` | `tinyint(1)` | NO |  | 1 |  |
| `fecha_creacion` | `timestamp` | NO |  | current_timestamp() |  |

### Tabla: `reserva_servicios`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_reserva` | `int(11)` | NO | MUL | NULL |  |
| `tipo_servicio` | `enum('guia','agencia','local','destino')` | NO | MUL | NULL |  |
| `id_servicio` | `int(11)` | NO |  | NULL |  |
| `estado` | `enum('pendiente','confirmado','cancelado')` | YES | MUL | pendiente |  |
| `fecha_servicio` | `date` | YES |  | NULL |  |
| `precio` | `decimal(10,2)` | YES |  | 0.00 |  |
| `notas` | `text` | YES |  | NULL |  |
| `fecha_creacion` | `timestamp` | NO |  | current_timestamp() |  |

### Tabla: `reservas`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_itinerario` | `int(11)` | NO | MUL | NULL |  |
| `id_usuario` | `int(11)` | NO | MUL | NULL |  |
| `fecha_reserva` | `date` | NO | MUL | NULL |  |
| `personas` | `int(11)` | NO |  | NULL |  |
| `metodo_pago` | `varchar(50)` | NO |  | NULL |  |
| `total_precio` | `decimal(10,2)` | NO |  | NULL |  |
| `estado` | `varchar(50)` | NO | MUL | pendiente |  |

### Tabla: `servicios_agencia`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_agencia` | `int(11)` | NO | MUL | NULL |  |
| `nombre_servicio` | `varchar(255)` | NO |  | NULL |  |
| `descripcion` | `text` | YES |  | NULL |  |
| `precio` | `decimal(10,2)` | NO |  | NULL |  |
| `fecha_creacion` | `timestamp` | NO |  | current_timestamp() |  |

### Tabla: `servicios_guia`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_guia` | `int(11)` | NO | MUL | NULL |  |
| `nombre_servicio` | `varchar(255)` | NO |  | NULL |  |
| `descripcion` | `text` | YES |  | NULL |  |
| `precio` | `decimal(10,2)` | NO |  | NULL |  |
| `fecha_creacion` | `timestamp` | NO |  | current_timestamp() |  |

### Tabla: `servicios_local`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `id_local` | `int(11)` | NO | MUL | NULL |  |
| `nombre_servicio` | `varchar(255)` | NO |  | NULL |  |
| `descripcion` | `text` | YES |  | NULL |  |
| `precio` | `decimal(10,2)` | NO |  | NULL |  |
| `fecha_creacion` | `timestamp` | NO |  | current_timestamp() |  |

### Tabla: `usuarios`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `nombre` | `varchar(255)` | NO |  | NULL |  |
| `email` | `varchar(255)` | NO | UNI | NULL |  |
| `telefono` | `varchar(20)` | YES |  | NULL |  |
| `contrasena` | `varchar(255)` | NO |  | NULL |  |
| `es_admin` | `tinyint(1)` | NO |  | 0 |  |
| `tipo_usuario` | `enum('turista','agencia','guia','local','super_admin')` | NO |  | turista |  |
| `fecha_registro` | `timestamp` | NO |  | current_timestamp() |  |
| `estado_servicio` | `enum('disponible','ocupado','no_disponible')` | YES |  | disponible |  |
| `ultima_actualizacion` | `timestamp` | NO |  | current_timestamp() | on update current_timestamp() |

### Tabla: `valoraciones`

| Columna | Tipo de Dato | Nulo | Clave | Por Defecto | Extra |
| :--- | :--- | :---: | :---: | :--- | :--- |
| `id` | `int(11)` | NO | PRI | NULL | auto_increment |
| `reviewer_id` | `int(11)` | NO | MUL | NULL |  |
| `provider_id` | `int(11)` | NO |  | NULL |  |
| `provider_type` | `enum('agencia','guia','local')` | NO |  | NULL |  |
| `rating` | `int(11)` | NO |  | NULL |  |
| `comment` | `text` | YES |  | NULL |  |
| `timestamp` | `datetime` | YES |  | current_timestamp() |  |

