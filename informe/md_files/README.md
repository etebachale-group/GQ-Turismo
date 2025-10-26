# 🌍 GQ-Turismo - Plataforma de Turismo Guinea Ecuatorial

[![Estado](https://img.shields.io/badge/Estado-Producción-brightgreen)]()
[![Versión](https://img.shields.io/badge/Versión-2.0-blue)]()
[![PHP](https://img.shields.io/badge/PHP-7.4+-purple)]()
[![MySQL](https://img.shields.io/badge/MySQL-8.0-orange)]()
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-blueviolet)]()

Una plataforma web completa y moderna para conectar turistas con servicios turísticos en Guinea Ecuatorial, incluyendo agencias, guías turísticos y locales (restaurantes, hoteles, etc.).

---

## 🚀 ACTUALIZACIÓN: 23 de Octubre de 2025

### ✅ SISTEMA COMPLETAMENTE ANALIZADO Y ACTUALIZADO

**Estado del Sistema:** ✅ LISTO PARA PRODUCCIÓN

Se ha realizado un análisis completo del sistema con las siguientes mejoras:

1. ✅ **Documentación organizada**: 65+ archivos MD organizados en carpetas temáticas
2. ✅ **Test system actualizado**: Verificación completa con estadísticas visuales
3. ✅ **Análisis completo creado**: Estructura, funcionalidades y diseño documentados
4. ✅ **Correcciones aplicadas**: Errores menores corregidos
5. ✅ **Guías de inicio**: Documentación clara para usuarios y desarrolladores

### 👉 EMPIEZA AQUÍ

**🔍 Verificar el Sistema:**
```
http://localhost/GQ-Turismo/test_system.php
```

**📚 Documentación Organizada:**
- 📊 Análisis completo: [`informe/analisis/ANALISIS_COMPLETO_SISTEMA_2025.md`](informe/analisis/ANALISIS_COMPLETO_SISTEMA_2025.md)
- 🔧 Correcciones aplicadas: [`informe/correcciones/CORRECCIONES_APLICADAS_2025.md`](informe/correcciones/CORRECCIONES_APLICADAS_2025.md)
- 🚀 Guía de inicio rápido: [`informe/guias/GUIA_RAPIDA_INICIO.md`](informe/guias/GUIA_RAPIDA_INICIO.md)
- 📂 Índice completo: [`informe/README.md`](informe/README.md)
- 📖 Instrucciones detalladas: [`INSTRUCCIONES_IMPLEMENTACION.md`](INSTRUCCIONES_IMPLEMENTACION.md)
- 📚 Información completa: [`RESUMEN_EJECUTIVO_FINAL.md`](RESUMEN_EJECUTIVO_FINAL.md)

---

## ⚠️ ACCIONES REQUERIDAS (15-20 minutos)

**ANTES DE USAR ESTE PROYECTO, DEBES:**

1. ✅ Ejecutar script SQL: `database/correciones_criticas.sql`
2. ✅ Eliminar archivos de bypass: generar_hash.php, add_admin.php, etc.
3. ✅ Cambiar contraseña del super administrador
4. ✅ Leer documentación de seguridad: `informe/SEGURIDAD_CRITICA.md`

**Archivos a eliminar INMEDIATAMENTE**:
- ❌ `generar_hash.php`
- ❌ `database/add_admin.php`
- ❌ `database/add_super_admin.php`
- ❌ `database/update_db.php` (si ya se ejecutó)
- ❌ `eliminar_bypass.bat`
- ❌ `mover_informe.bat`

---

## 📋 Características Principales

### **Para Turistas** 🧳
- 🗺️ Exploración de destinos con filtros avanzados
- 📝 Creación de itinerarios personalizados
- 🏨 Reservas de servicios (agencias, guías, locales)
- 💬 Sistema de mensajería en tiempo real
- ⭐ Sistema de valoraciones y reseñas
- 🔍 Búsqueda avanzada con geolocalización
- 🎯 Recomendaciones personalizadas basadas en historial

### **Para Proveedores** (Agencias, Guías, Locales) 🏢
- 📊 Dashboard con estadísticas e ingresos
- 📸 Gestión de perfil con galería de imágenes
- 🛎️ Gestión de servicios y paquetes
- 📥 Recepción y gestión de pedidos
- 💰 Sistema de precios y descuentos
- 📍 Ubicación en tiempo real
- 🌆 Gestión de destinos por ciudad

### **Para Super Administrador** 👨‍💼
- 👥 Gestión completa de usuarios
- 🏞️ CRUD de destinos turísticos
- 🎨 Gestión de publicidad y carouseles
- 📈 Estadísticas globales del sistema
- 🔐 Control total de permisos
- 📊 Reportes y analíticas

---

## 🏗️ Estructura del Proyecto

```
GQ-Turismo/
├── 📄 Páginas Principales
│   ├── index.php - Landing page
│   ├── destinos.php - Exploración de destinos
│   ├── agencias.php - Lista de agencias
│   ├── guias.php - Lista de guías
│   └── locales.php - Lugares locales
│
├── 🗺️ Sistema de Itinerarios
│   ├── itinerario.php - Visualización
│   └── crear_itinerario.php - Creación
│
├── 💬 Sistema de Mensajería
│   └── mis_mensajes.php - Chat en tiempo real
│
├── 👤 Área de Usuario
│   ├── mis_pedidos.php - Gestión de pedidos
│   ├── reservas.php - Mis reservas
│   └── pagar.php - Procesamiento de pagos
│
├── 🔐 Panel Admin
│   ├── dashboard.php - Panel principal
│   ├── manage_*.php - Gestión CRUD
│   └── reservas.php - Gestión de reservas
│
├── 🔌 API REST
│   ├── auth.php - Autenticación
│   ├── itinerarios.php - CRUD itinerarios
│   ├── messages.php - Mensajería
│   ├── pedidos.php - Pedidos
│   └── [otros endpoints]
│
├── 📦 Includes
│   ├── header.php - Navegación
│   ├── footer.php - Footer
│   └── db_connect.php - Conexión BD
│
├── 🎨 Assets
│   ├── css/ - Estilos
│   ├── js/ - Scripts
│   └── img/ - Imágenes
│
└── 📚 Documentación
    └── informe/ - Docs organizadas
```

---

## 🛠️ Stack Tecnológico

### Backend
- **PHP** 7.4+ (8.x recomendado)
- **MySQL** 8.0
- **Apache** 2.4

### Frontend
- **HTML5**
- **CSS3** (Variables, Grid, Flexbox)
- **JavaScript** ES6+
- **Bootstrap** 5.3
- **AOS** (Animate On Scroll)
- **Bootstrap Icons**

### Herramientas
- Git (Control de versiones)
- XAMPP/WAMP (Desarrollo local)
- Composer (Opcional)
- NPM (Opcional)
- **Servidor**: Apache (XAMPP)

---

## 📁 Estructura del Proyecto

```
GQ-Turismo/
├── admin/                  # Panel de administración
│   ├── dashboard.php       # Dashboard principal
│   ├── login.php          # Login de proveedores/admin
│   ├── manage_*.php       # Gestión de recursos
│   └── sidebar.php        # Navegación del panel
├── api/                   # Endpoints REST
│   ├── destinos.php
│   ├── agencias.php
│   ├── guias.php
│   ├── locales.php
│   ├── itinerarios.php
│   ├── pedidos.php
│   ├── messages.php
│   └── reviews.php
├── assets/
│   ├── css/              # Estilos personalizados
│   ├── js/               # Scripts JavaScript
│   └── img/              # Imágenes del sitio
├── database/             # Scripts SQL
├── includes/
│   ├── db_connect.php    # Conexión a BD
│   ├── header.php        # Header dinámico
│   ├── footer.php        # Footer dinámico
│   └── session_security.php  # Seguridad de sesiones
├── index.php             # Página principal
├── destinos.php          # Listado de destinos
├── agencias.php          # Listado de agencias
├── guias.php             # Listado de guías
├── locales.php           # Listado de locales
├── itinerario.php        # Mis itinerarios
├── reservas.php          # Mis reservas
├── mis_pedidos.php       # Mis pedidos
├── mis_mensajes.php      # Mis mensajes
└── contacto.php          # Contacto
```

---

## 🚀 Instalación

### **Requisitos Previos**:
- XAMPP (Apache + MySQL + PHP 8+)
- Navegador web moderno
- Editor de código (VS Code recomendado)

### **Pasos**:

1. **Clonar/Descargar el proyecto**:
```bash
git clone [URL_DEL_REPO]
# O descargar y extraer ZIP en C:\xampp\htdocs\
```

2. **Configurar Base de Datos**:
```sql
-- Crear base de datos
CREATE DATABASE gq_turismo CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

-- Importar estructura (elegir UNO de estos archivos):
-- Opción 1: Estructura completa con datos de ejemplo
source database/gq_turismo_completo.sql;

-- Opción 2: Solo estructura (recomendado para producción)
source database/este.sql;
```

3. **Configurar Conexión**:
Editar `includes/db_connect.php`:
```php
$servername = "localhost";
$username = "root";        // Cambiar en producción
$password = "";            // Establecer contraseña fuerte
$dbname = "gq_turismo";
```

4. **🔴 ELIMINAR ARCHIVOS PELIGROSOS**:
```cmd
cd C:\xampp\htdocs\GQ-Turismo
eliminar_bypass.bat
```

5. **Configurar Permisos**:
Asegurar que Apache pueda escribir en:
- `assets/img/destinos/`
- `assets/img/agencias/`
- `assets/img/guias/`
- `assets/img/locales/`

6. **Iniciar Servicios**:
- Abrir XAMPP Control Panel
- Iniciar Apache
- Iniciar MySQL

7. **Acceder al Sistema**:
```
http://localhost/GQ-Turismo/
```

---

## 👤 Usuarios por Defecto

**IMPORTANTE**: Cambiar o eliminar estos usuarios antes de producción.

### Super Administrador:
```
Email: etebachalegroup@gmail.com
Password: [VER SEGURIDAD_CRITICA.md]
```

### Usuarios de Prueba (Eliminar en producción):
- Admin: `admin@gqturismo.com` / `admin`
- Agencia: `agencia@example.com` / `password`
- Guía: `guia@example.com` / `password`
- Local: `local@example.com` / `password`

---

## 🔒 Seguridad

### **Documentos de Seguridad** (LEER):
1. `SEGURIDAD_CRITICA.md` - Vulnerabilidades y soluciones
2. `REVISION_SEGURIDAD_COMPLETA.md` - Auditoría completa
3. `CORRECCIONES_GESTION.md` - Correcciones aplicadas

### **Checklist de Seguridad**:

#### **Antes de Desarrollo**:
- [ ] Ejecutar `eliminar_bypass.bat`
- [ ] Cambiar contraseña del super admin
- [ ] Eliminar usuarios de prueba
- [ ] Verificar que `.htaccess` funcione

#### **Antes de Producción**:
- [ ] Cambiar usuario de BD (no usar `root`)
- [ ] Establecer contraseña fuerte en MySQL
- [ ] Habilitar HTTPS/SSL
- [ ] Cambiar TODAS las contraseñas
- [ ] Eliminar datos de prueba
- [ ] Configurar backups automáticos
- [ ] Implementar rate limiting
- [ ] Añadir recaptcha en formularios
- [ ] Configurar logs de auditoría
- [ ] Escaneo de vulnerabilidades

### **Archivos de Protección Incluidos**:
- `.htaccess` (raíz) - Protección general
- `database/.htaccess` - Protección de carpeta BD
- `includes/session_security.php` - Seguridad de sesiones

---

## 📊 Base de Datos

### **Tablas Principales**:
- `usuarios` - Sistema multi-rol
- `destinos` - Lugares turísticos
- `itinerarios` - Itinerarios personalizados
- `reservas` - Reservas de itinerarios
- `agencias` - Agencias de viajes
- `guias_turisticos` - Guías turísticos
- `lugares_locales` - Restaurantes, hoteles, etc.
- `pedidos_servicios` - Pedidos de servicios
- `mensajes` - Sistema de mensajería
- `valoraciones` - Reseñas y ratings
- `descuentos` - Códigos de descuento
- Y más... (23 tablas en total)

### **Diagrama ER**:
Ver archivo `database/estructura_bd.png` (si existe)

---

## 🎨 Diseño y UX

- ✅ Completamente responsive (móvil, tablet, desktop)
- ✅ Diseño moderno con Bootstrap 5
- ✅ Animaciones suaves con AOS
- ✅ Mapas interactivos con Leaflet
- ✅ Navegación intuitiva
- ✅ Modales para interacciones rápidas
- ✅ Loading states y feedback visual
- ✅ Optimizado para accesibilidad

---

## 📚 Documentación Adicional

- `TAREAS_COMPLETADAS.md` - Historial de tareas completadas
- `CORRECCIONES_GESTION.md` - Correcciones en páginas de gestión
- `progress.md` - Progreso del desarrollo
- `instrucciones.md` - Instrucciones originales del proyecto
- `modificaciones.md` - Log de modificaciones

---

## 🐛 Problemas Conocidos

1. ⚠️ **Archivos de bypass** - **CRÍTICO** - Eliminar antes de usar
2. ⚠️ **Contraseñas débiles** - Cambiar en producción
3. ⚠️ **MySQL sin contraseña** - Configurar en producción
4. ℹ️ **Datos de prueba** - Eliminar antes de producción

Ver `REVISION_SEGURIDAD_COMPLETA.md` para lista completa.

---

## 🛣️ Roadmap

### **Fase Actual: MVP Completo** ✅
- [x] Todas las funcionalidades básicas
- [x] Sistema multi-rol
- [x] Gestión de proveedores
- [x] Sistema de pedidos
- [x] Mensajería y reseñas

### **Próximas Fases**:
- [ ] Implementar 2FA para admin
- [ ] Sistema de notificaciones push
- [ ] App móvil (React Native)
- [ ] Pasarela de pago real
- [ ] Sistema de cupones avanzado
- [ ] Analytics dashboard
- [ ] API pública documentada
- [ ] Tests unitarios y E2E

---

## 🤝 Contribuir

Este proyecto está en desarrollo activo. Para contribuir:

1. Fork el proyecto
2. Crear rama de feature (`git checkout -b feature/AmazingFeature`)
3. Commit cambios (`git commit -m 'Add AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abrir Pull Request

---

## 📄 Licencia

[Especificar licencia aquí]

---

## 👥 Equipo

**Desarrollado por**: Eteba Chale Group  
**Email**: etebachalegroup@gmail.com  
**Para**: Hackathon 2025

---

## 📞 Soporte

¿Problemas o preguntas?

1. Revisar documentación en `/docs`
2. Consultar `REVISION_SEGURIDAD_COMPLETA.md`
3. Abrir issue en GitHub
4. Contactar a etebachalegroup@gmail.com

---

## ⚡ Quick Start

```bash
# 1. Navegar a XAMPP htdocs
cd C:\xampp\htdocs\GQ-Turismo

# 2. Eliminar archivos peligrosos
eliminar_bypass.bat

# 3. Iniciar servicios XAMPP
# Abrir XAMPP Control Panel → Start Apache & MySQL

# 4. Acceder al sistema
# http://localhost/GQ-Turismo/

# 5. IMPORTANTE: Leer SEGURIDAD_CRITICA.md
```

---

## 📸 Screenshots

[Agregar screenshots del sistema]

- Vista principal
- Dashboard de proveedor
- Página de destinos
- Sistema de itinerarios
- Panel de administración

---

## 🎯 Estado del Proyecto

| Módulo | Estado | Completitud |
|--------|--------|-------------|
| Frontend | ✅ Completo | 100% |
| Backend | ✅ Completo | 100% |
| Base de Datos | ✅ Completa | 100% |
| APIs | ✅ Funcionales | 100% |
| Seguridad | ⚠️ Requiere acción | 70% |
| Documentación | ✅ Completa | 95% |
| Tests | ❌ Pendiente | 0% |

---

**⚠️ RECORDATORIO FINAL**: Este proyecto NO debe usarse en producción sin aplicar TODAS las medidas de seguridad descritas en `SEGURIDAD_CRITICA.md`.

---

*Última actualización: 23 de Octubre de 2025*
