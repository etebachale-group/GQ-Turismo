# 🌍 GQ-TURISMO - Sistema de Gestión Turística

## 🚀 INICIO RÁPIDO

### ⚠️ IMPORTANTE - LEER PRIMERO

**Si acabas de descargar este proyecto, sigue estos pasos en orden:**

### PASO 1: Configurar Base de Datos

1. Abre **phpMyAdmin**: `http://localhost/phpmyadmin`
2. Crea una base de datos llamada: `gq_turismo`
3. Ve a la pestaña **"SQL"**
4. Ejecuta los siguientes scripts en orden:

```sql
-- 1. Ejecutar estructura completa
database/gq_turismo_completo.sql

-- 2. Ejecutar correcciones
database/fix_all_current_errors.sql
```

### PASO 2: Configurar Conexión

Edita el archivo: `includes/db_connect.php`

```php
$servername = "localhost";
$username = "root";
$password = "";  // Cambia si tu MySQL tiene contraseña
$dbname = "gq_turismo";
```

### PASO 3: Verificar Instalación

Abre en tu navegador: `http://localhost/GQ-Turismo/test_system.php`

✅ Si ves todas las tablas en verde, ¡todo está listo!
⚠️ Si hay errores, revisa los scripts SQL

### PASO 4: Iniciar Sesión

**Super Administrador:**
- Email: `admin@gqturismo.com`
- Password: (verifica en la base de datos)

**Crear usuario de prueba:**
Ve a `http://localhost/GQ-Turismo/` y registra un nuevo usuario

---

## 📁 ESTRUCTURA DEL PROYECTO

```
GQ-Turismo/
├── admin/                  # Panel de administración
│   ├── dashboard.php      # Dashboard principal
│   ├── manage_*.php       # Gestión de recursos
│   └── mis_pedidos.php    # Pedidos de proveedores
├── api/                   # APIs y endpoints
├── assets/                # Recursos estáticos
│   ├── css/              # Estilos
│   ├── img/              # Imágenes
│   └── js/               # JavaScript
├── database/              # Scripts SQL
│   ├── gq_turismo_completo.sql
│   └── fix_all_current_errors.sql
├── includes/              # Archivos compartidos
│   ├── db_connect.php    # Conexión BD
│   ├── header.php        # Encabezado
│   └── footer.php        # Pie de página
├── informe/               # Documentación
│   ├── CORRECCIONES_PENDIENTES_2025.md
│   └── RESUMEN_CORRECCIONES_APLICADAS_2025.md
├── index.php              # Página principal
├── tracking_itinerario.php # Seguimiento de itinerarios
└── test_system.php        # Sistema de verificación
```

---

## 🎯 FUNCIONALIDADES PRINCIPALES

### Para Turistas
- ✅ Crear y gestionar itinerarios personalizados
- ✅ Seleccionar destinos turísticos
- ✅ Contratar servicios (guías, agencias, locales)
- ✅ Seguimiento en tiempo real de su itinerario
- ✅ Sistema de mensajería con proveedores
- ✅ Gestión de reservas y pagos

### Para Guías Turísticos
- ✅ Seleccionar destinos donde trabajan
- ✅ Gestionar servicios ofrecidos
- ✅ Ver y aceptar solicitudes de turistas
- ✅ Seguimiento de itinerarios asignados
- ✅ Sistema de mensajería

### Para Agencias de Viajes
- ✅ Gestionar servicios y paquetes
- ✅ Ver y gestionar reservas
- ✅ Confirmar pedidos de turistas
- ✅ Sistema de mensajería

### Para Locales/Restaurantes
- ✅ Publicar menús y servicios
- ✅ Gestionar reservas
- ✅ Confirmar pedidos
- ✅ Sistema de mensajería

### Para Super Administrador
- ✅ Gestión completa de usuarios
- ✅ Gestión de destinos turísticos
- ✅ Gestión de publicidad y carousel
- ✅ Reportes y estadísticas
- ✅ Control total del sistema

---

## 🔥 NUEVAS CARACTERÍSTICAS

### Sistema de Tracking de Itinerarios
**Archivo:** `tracking_itinerario.php`

- Timeline visual de todas las tareas
- Actualización de estados en tiempo real (AJAX)
- Barra de progreso visual
- Estadísticas completas
- Responsive 100% móvil
- Auto-refresh automático
- Permisos configurados por rol

**Cómo usar:**
```
http://localhost/GQ-Turismo/tracking_itinerario.php?id=1
```
(Reemplaza "1" con el ID del itinerario)

### Sidebar Responsive
Implementado en todas las páginas admin:
- Botón flotante en móviles
- Animaciones suaves
- Overlay oscuro
- Eventos touch optimizados

---

## 📱 RESPONSIVE DESIGN

El sistema está completamente optimizado para:
- 📱 Móviles (320px - 768px)
- 📱 Tablets (768px - 1024px)
- 💻 Laptops (1024px - 1440px)
- 🖥️ Desktop (1440px+)

---

## 🛠️ TECNOLOGÍAS UTILIZADAS

- **Backend:** PHP 7.4+
- **Base de Datos:** MySQL 5.7+
- **Frontend:** 
  - HTML5
  - CSS3 (Flexbox, Grid)
  - JavaScript (ES6+)
  - Bootstrap 5.3
  - Bootstrap Icons
- **AJAX:** Fetch API
- **Fuentes:** Google Fonts (Inter, Poppins)

---

## 🔒 SEGURIDAD

- ✅ Sesiones PHP seguras
- ✅ Prepared statements (PDO/MySQLi)
- ✅ Validación de inputs
- ✅ Escape de outputs (XSS protection)
- ✅ Permisos por rol de usuario
- ✅ CSRF protection en formularios

---

## 📚 DOCUMENTACIÓN

### Documentos Importantes

1. **`informe/RESUMEN_CORRECCIONES_APLICADAS_2025.md`**
   - Lista de todas las correcciones aplicadas
   - Funcionalidades implementadas
   - Estado actual del proyecto

2. **`informe/CORRECCIONES_PENDIENTES_2025.md`**
   - Tareas pendientes
   - Problemas conocidos
   - Plan de desarrollo

3. **`test_system.php`**
   - Verificación del sistema
   - Estado de tablas
   - Diagnóstico completo

---

## 🐛 SOLUCIÓN DE PROBLEMAS

### Error: "Tabla no existe"
**Solución:** Ejecuta `database/fix_all_current_errors.sql`

### Error: "Columna no encontrada"
**Solución:** Ejecuta `database/fix_all_current_errors.sql`

### Error: "Headers already sent"
**Solución:** Asegúrate de que no hay espacios antes de `<?php` y que el archivo está en UTF-8 sin BOM

### Sidebar no funciona en móvil
**Solución:** Verifica que `admin_header.php` y `admin_footer.php` estén incluidos correctamente

### No se muestran imágenes
**Solución:** Verifica permisos de carpeta `assets/img/` (debe ser 755 o 777)

---

## 📞 SOPORTE

Para reportar problemas o sugerencias:
1. Revisa la documentación en `informe/`
2. Verifica `test_system.php`
3. Consulta los archivos de correcciones

---

## 🚀 ROADMAP

### Versión Actual: 2.0

**Próximas funcionalidades:**
- [ ] Sistema de notificaciones push
- [ ] Integración con Google Maps
- [ ] Sistema de chat en vivo
- [ ] Reportes PDF descargables
- [ ] Sistema de calificaciones y reseñas
- [ ] Multi-idioma (ES, EN, PT)
- [ ] App móvil nativa

---

## 👥 ROLES Y PERMISOS

| Función | Turista | Guía | Agencia | Local | Super Admin |
|---------|---------|------|---------|-------|-------------|
| Crear itinerarios | ✅ | ❌ | ❌ | ❌ | ✅ |
| Contratar servicios | ✅ | ❌ | ❌ | ❌ | ✅ |
| Ofrecer servicios | ❌ | ✅ | ✅ | ✅ | ❌ |
| Gestionar destinos | ❌ | ❌ | ❌ | ❌ | ✅ |
| Gestionar usuarios | ❌ | ❌ | ❌ | ❌ | ✅ |
| Ver estadísticas | ❌ | ✅ | ✅ | ✅ | ✅ |

---

## 📊 ESTADO DEL PROYECTO

- **Base de Datos:** ⚠️ Requiere ejecutar scripts de actualización
- **Frontend:** ✅ Completamente responsive
- **Backend:** ✅ Funcional con correcciones aplicadas
- **Seguridad:** ✅ Implementada
- **Documentación:** ✅ Completa y actualizada
- **Testing:** ⏳ En progreso

---

## ⚡ QUICK START (Desarrolladores)

```bash
# 1. Clonar/Descargar proyecto
cd C:\xampp\htdocs\GQ-Turismo

# 2. Importar base de datos (phpMyAdmin)
database/gq_turismo_completo.sql
database/fix_all_current_errors.sql

# 3. Verificar instalación
http://localhost/GQ-Turismo/test_system.php

# 4. Acceder al sistema
http://localhost/GQ-Turismo/
```

---

## 📝 LICENCIA

Este proyecto es propiedad de GQ-Turismo.
Todos los derechos reservados.

---

## 🎉 ¡LISTO PARA USAR!

Si todos los pasos fueron completados correctamente:

1. ✅ Base de datos configurada
2. ✅ Conexión establecida
3. ✅ Sistema verificado con test_system.php
4. ✅ Usuario creado o login como admin

**¡Disfruta de GQ-Turismo! 🌍✈️🏖️**

---

**Última actualización:** 23 de Octubre de 2025
**Versión:** 2.0.0
**Desarrollado con ❤️ para GQ-Turismo**
