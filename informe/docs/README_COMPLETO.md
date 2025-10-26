# 🌍 GQ-Turismo - Sistema de Gestión Turística

> Plataforma web completa para la gestión de itinerarios turísticos con múltiples tipos de usuarios y proveedores de servicios.

[![PHP](https://img.shields.io/badge/PHP-7.4+-blue)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange)](https://mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-purple)](https://getbootstrap.com)
[![Estado](https://img.shields.io/badge/Estado-Producción-green)](.)

---

## 📋 Tabla de Contenidos

- [Características](#-características)
- [Requisitos](#-requisitos)
- [Instalación](#-instalación)
- [Uso](#-uso)
- [Estructura](#-estructura-del-proyecto)
- [Testing](#-testing)
- [Soporte](#-soporte)

---

## ✨ Características

### Para Turistas 🧳
- ✅ Crear itinerarios personalizados
- ✅ Seleccionar múltiples destinos
- ✅ Solicitar servicios de guías, agencias y locales
- ✅ Seguimiento en tiempo real con mapa de tareas
- ✅ Chat directo con proveedores
- ✅ Ver historial de viajes

### Para Guías Turísticos 🗺️
- ✅ Gestionar perfil profesional
- ✅ Seleccionar destinos donde trabajan
- ✅ Ver y confirmar pedidos
- ✅ Actualizar estado de servicios
- ✅ Chat con clientes
- ✅ Gestión de disponibilidad

### Para Agencias de Viajes ✈️
- ✅ Registro de agencia
- ✅ Gestionar servicios y paquetes
- ✅ Crear menús de servicios
- ✅ Confirmar reservas
- ✅ Chat con clientes
- ✅ Panel de estadísticas

### Para Locales Turísticos 🏨
- ✅ Gestionar establecimiento
- ✅ Publicar servicios (restaurantes, hoteles, tours)
- ✅ Administrar menús y precios
- ✅ Confirmar reservas
- ✅ Chat con clientes

### Para Super Administrador 👨‍💼
- ✅ Gestión completa de usuarios
- ✅ Administración de destinos
- ✅ Gestión de publicidad
- ✅ Estadísticas globales
- ✅ Control total del sistema

### Características Técnicas 🔧
- ✅ Diseño 100% responsive (móvil, tablet, desktop)
- ✅ Sidebar móvil con botón flotante
- ✅ Sistema de mensajería integrado
- ✅ Tracking de itinerarios en tiempo real
- ✅ Multi-idioma preparado (ES)
- ✅ Seguridad con prepared statements
- ✅ Validación de formularios
- ✅ Alertas y notificaciones

---

## 🔧 Requisitos

### Software Necesario:
- **XAMPP** (o similar): Apache + MySQL + PHP
- **PHP**: 7.4 o superior
- **MySQL**: 5.7 o superior
- **Navegador**: Chrome, Firefox, Safari, Edge (moderno)

### Extensiones PHP Requeridas:
- `mysqli` - Conexión a base de datos
- `gd` - Manipulación de imágenes
- `fileinfo` - Información de archivos
- `session` - Manejo de sesiones

---

## 📦 Instalación

### Paso 1: Clonar o Descargar
```bash
# Clonar el repositorio
git clone https://github.com/tu-usuario/GQ-Turismo.git

# O descargar y extraer en:
C:\xampp\htdocs\GQ-Turismo
```

### Paso 2: Configurar Base de Datos
```bash
# 1. Iniciar XAMPP (Apache + MySQL)
# 2. Abrir phpMyAdmin: http://localhost/phpmyadmin

# 3. Crear base de datos
CREATE DATABASE gq_turismo;

# 4. Importar estructura completa
# Desde phpMyAdmin: Importar > Seleccionar archivo
# Archivo: database/gq_turismo_completo.sql

# O desde terminal:
mysql -u root < database/gq_turismo_completo.sql
```

### Paso 3: Configurar Conexión
```php
// Editar: includes/db_connect.php
<?php
$host = 'localhost';
$username = 'root';
$password = ''; // Tu contraseña de MySQL si la tienes
$database = 'gq_turismo';

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>
```

### Paso 4: Ejecutar Correcciones
```bash
# Aplicar correcciones finales
mysql -u root gq_turismo < database/fix_all_complete_system.sql
```

### Paso 5: Crear Super Administrador
```sql
-- En phpMyAdmin o MySQL Workbench:
INSERT INTO usuarios (nombre, email, contrasena, tipo_usuario, es_admin) 
VALUES (
    'Admin',
    'admin@gqturismo.com',
    '$2y$10$YourHashedPasswordHere', -- Cambiar por hash real
    'super_admin',
    1
);

-- Generar hash de contraseña en PHP:
<?php echo password_hash('tu_contraseña_segura', PASSWORD_DEFAULT); ?>
```

### Paso 6: Verificar Instalación
```bash
# Abrir en navegador:
http://localhost/GQ-Turismo/test_system.php

# Debe mostrar:
# ✅ Conexión DB: OK
# ✅ Tablas: 16/16 OK
# ✅ Columnas: OK
# ✅ Archivos PHP: OK
# ✅ Directorios: OK
```

---

## 🚀 Uso

### Acceder al Sistema

#### Sitio Público:
```
http://localhost/GQ-Turismo/
```

#### Panel de Administración:
```
http://localhost/GQ-Turismo/admin/login.php
```

### Crear Usuarios de Prueba

#### Turista:
```sql
INSERT INTO usuarios (nombre, email, contrasena, tipo_usuario) 
VALUES ('Juan Pérez', 'turista@test.com', '$2y$10$hash', 'turista');
```

#### Guía:
```sql
-- 1. Crear usuario
INSERT INTO usuarios (nombre, email, contrasena, tipo_usuario) 
VALUES ('María Guía', 'guia@test.com', '$2y$10$hash', 'guia');

-- 2. Crear perfil de guía (con el ID del usuario creado)
INSERT INTO guias_turisticos (id_usuario, nombre_guia, descripcion, idiomas) 
VALUES (2, 'María Guía Turística', 'Guía profesional', 'Español, Inglés');
```

#### Agencia:
```sql
-- 1. Crear usuario
INSERT INTO usuarios (nombre, email, contrasena, tipo_usuario) 
VALUES ('Viajes SA', 'agencia@test.com', '$2y$10$hash', 'agencia');

-- 2. Crear perfil de agencia
INSERT INTO agencias (id_usuario, nombre_agencia, descripcion, contacto_email) 
VALUES (3, 'Viajes SA', 'Agencia de viajes', 'agencia@test.com');
```

### Flujo Típico de Uso

#### 1. Turista Crea Itinerario:
```
index.php → Crear Itinerario → 
Seleccionar destinos → Agregar fechas → 
Solicitar servicios → Enviar
```

#### 2. Proveedor Confirma:
```
admin/login.php → Mis Pedidos → 
Ver solicitud → Confirmar → 
Chat con turista (opcional)
```

#### 3. Turista Sigue Progreso:
```
Mis Itinerarios → Ver Itinerario → 
Mapa de Tareas → Marcar como completado
```

---

## 📁 Estructura del Proyecto

```
GQ-Turismo/
├── admin/                      # Panel de administración
│   ├── dashboard.php          # Dashboard principal
│   ├── manage_*.php           # Gestión de entidades
│   ├── mis_pedidos.php        # Pedidos del proveedor
│   ├── messages.php           # Sistema de mensajería
│   └── admin_header.php       # Header con sidebar responsive
│
├── api/                        # Endpoints API (futuro)
│
├── assets/                     # Recursos estáticos
│   ├── css/
│   │   ├── modern-ui.css      # Estilos modernos base
│   │   ├── mobile-responsive-admin.css  # Responsive completo
│   │   └── *.css
│   ├── js/
│   └── img/                   # Imágenes
│       ├── destinos/
│       ├── guias/
│       ├── agencias/
│       ├── locales/
│       └── carouseles/
│
├── database/                   # Scripts SQL
│   ├── gq_turismo_completo.sql           # BD completa
│   ├── fix_all_complete_system.sql       # Correcciones
│   └── *.sql                              # Scripts auxiliares
│
├── includes/                   # Archivos PHP compartidos
│   ├── db_connect.php         # Conexión DB
│   ├── header.php             # Header público
│   └── footer.php             # Footer público
│
├── informe/                    # Documentación
│   ├── TRABAJO_COMPLETADO_DEFINITIVO_2025.md
│   ├── analisis/
│   ├── correcciones/
│   └── guias/
│
├── trash/                      # Archivos obsoletos
│
├── index.php                   # Página principal
├── destinos.php               # Catálogo de destinos
├── guias.php                  # Catálogo de guías
├── locales.php                # Catálogo de locales
├── agencias.php               # Catálogo de agencias
├── crear_itinerario.php       # Crear itinerario
├── itinerario.php             # Mis itinerarios
├── seguimiento_itinerario.php # Ver seguimiento
├── mapa_itinerario.php        # Mapa de tareas
├── tracking_itinerario.php    # API tracking
└── test_system.php            # Tests del sistema
```

---

## 🧪 Testing

### Test Automático del Sistema:
```
http://localhost/GQ-Turismo/test_system.php
```

### Tests Incluidos:
- ✅ Conexión a base de datos
- ✅ Verificación de 16 tablas
- ✅ Validación de columnas críticas
- ✅ Sintaxis de archivos PHP
- ✅ Existencia de directorios
- ✅ Permisos de escritura
- ✅ Extensiones PHP
- ✅ Versión PHP

### Test Manual:
```bash
# 1. Registrar usuario turista
# 2. Crear itinerario
# 3. Solicitar servicio
# 4. Login como proveedor
# 5. Confirmar servicio
# 6. Verificar actualización en tiempo real
```

---

## 🐛 Solución de Problemas

### Error: "Cannot modify header information"
```php
// Solución: Verificar que no haya output antes de headers
// Eliminar BOM de archivos PHP
// Verificar que session_start() esté al inicio
```

### Error: "Table doesn't exist"
```sql
-- Solución: Ejecutar script de corrección
mysql -u root gq_turismo < database/fix_all_complete_system.sql
```

### Error: "Unknown column"
```sql
-- Solución: Verificar estructura de tabla
DESCRIBE nombre_tabla;

-- Agregar columna si falta
ALTER TABLE nombre_tabla ADD COLUMN columna_faltante TYPE;
```

### Sidebar no funciona en móvil:
```javascript
// Verificar que existan los elementos:
// 1. <aside id="adminSidebar">
// 2. <button id="sidebarToggle">
// 3. <div id="sidebarOverlay">
// 4. JavaScript en admin_footer.php
```

---

## 📞 Soporte

### Documentación Completa:
- `informe/TRABAJO_COMPLETADO_DEFINITIVO_2025.md` - Resumen completo
- `informe/guias/` - Guías de usuario
- `informe/documentacion/` - Documentación técnica

### Logs de Errores:
```bash
# PHP Errors
C:\xampp\php\logs\php_error_log

# Apache Errors
C:\xampp\apache\logs\error.log

# MySQL Errors
C:\xampp\mysql\data\*.err
```

### Recursos:
- Bootstrap 5: https://getbootstrap.com/docs/5.3
- PHP Manual: https://www.php.net/manual/es
- MySQL Docs: https://dev.mysql.com/doc

---

## 🤝 Contribuir

### Para desarrolladores:
```bash
# 1. Fork el proyecto
# 2. Crear rama feature
git checkout -b feature/nueva-funcionalidad

# 3. Hacer cambios y commit
git commit -am 'Agregar nueva funcionalidad'

# 4. Push a la rama
git push origin feature/nueva-funcionalidad

# 5. Crear Pull Request
```

---

## 📄 Licencia

Este proyecto es privado y confidencial. Todos los derechos reservados.

---

## 👥 Créditos

**Desarrollado por:** Equipo GQ-Turismo
**Versión:** 2.0
**Fecha:** 2025
**Stack:** PHP + MySQL + Bootstrap 5

---

## 🎉 ¡Gracias por usar GQ-Turismo!

Si tienes preguntas o sugerencias, no dudes en contactarnos.

**¡Felices viajes! 🌍✈️🗺️**
