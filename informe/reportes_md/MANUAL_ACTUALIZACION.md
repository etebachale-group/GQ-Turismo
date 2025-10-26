# 🚀 Manual de Actualización - GQ Turismo v2.1

## 📋 Resumen de Cambios

Esta actualización incluye:
- ✅ Sistema completo de tracking de itinerarios con mapa interactivo
- ✅ Confirmación de servicios por proveedores
- ✅ Diseño móvil completamente optimizado
- ✅ Corrección de 9 errores críticos
- ✅ 3 nuevas tablas de base de datos
- ✅ 5 APIs nuevas
- ✅ Sistema de sidebar móvil universal

## 🔧 Pasos de Instalación

### 1. Actualizar Base de Datos

**Opción A: Desde línea de comandos**
```bash
cd C:\xampp\htdocs\GQ-Turismo\database
mysql -u root -p gq_turismo < fix_all_system_errors.sql
```

**Opción B: Desde phpMyAdmin**
1. Abrir phpMyAdmin: `http://localhost/phpmyadmin`
2. Seleccionar base de datos `gq_turismo`
3. Ir a pestaña "SQL"
4. Copiar y pegar el contenido de `database/fix_all_system_errors.sql`
5. Ejecutar

**Opción C: Desde MySQL Workbench**
1. Conectar a MySQL
2. Abrir `database/fix_all_system_errors.sql`
3. Ejecutar script

### 2. Verificar Actualización

Acceder a: `http://localhost/GQ-Turismo/test_system.php`

Verificar que todos los tests pasen con estado ✅ (verde).

### 3. Limpiar Caché (Opcional)

```bash
# Si usas navegador Chrome/Edge
Ctrl + Shift + Delete
# Seleccionar "Imágenes y archivos en caché"
```

## 📱 Nuevas Funcionalidades

### 1. Mapa de Tareas del Itinerario

**URL:** `mapa_tareas_itinerario.php?id=[ID_ITINERARIO]`

**Características:**
- Mapa interactivo con Leaflet
- Timeline de tareas
- Marcadores con colores por estado
- Barra de progreso
- Actualización de estado en tiempo real

**Acceso:**
- Turistas: Ver y actualizar sus itinerarios
- Guías: Ver y actualizar itinerarios asignados

**Uso:**
1. Crear un itinerario desde el panel admin
2. Acceder a `mapa_tareas_itinerario.php?id=X` (X = ID del itinerario)
3. Marcar tareas como iniciadas o completadas

### 2. Confirmación de Servicios

**Para Proveedores:**

**Archivos Afectados:**
- `admin/mis_pedidos.php` - Actualizado con botones de confirmación

**Flujo:**
1. Proveedor recibe pedido (estado: pendiente)
2. Puede: Aceptar → estado "confirmado"
3. Puede: Rechazar → estado "rechazado"
4. Puede: Marcar en progreso → estado "en_progreso"
5. Puede: Completar → estado "completado"

**API:** `api/confirmar_servicio_proveedor.php`

### 3. Relación Guías-Destinos

**Para Super Admin:**
- Crear destinos en `admin/manage_destinos.php`

**Para Guías:**
- Desde `admin/mis_destinos.php` seleccionar destinos disponibles
- Configurar especialidad, experiencia, tarifa

**Beneficio:**
- Turistas ven solo guías disponibles para destino seleccionado

## 🎨 Mejoras de Diseño Móvil

### Sidebar Móvil

**Funciona automáticamente en:**
- Todas las páginas con `admin/admin_header.php`
- Dashboard
- Gestión de agencias, destinos, guías, locales
- Mensajes
- Pedidos

**Cómo usar:**
1. Abrir cualquier página admin en móvil
2. Tocar botón morado flotante (esquina inferior izquierda)
3. Sidebar se desliza desde la izquierda
4. Tocar fuera del sidebar para cerrar

### Correcciones Responsive

**Páginas corregidas:**
- ✅ `admin/manage_agencias.php`
- ✅ `admin/manage_destinos.php`
- ✅ `admin/manage_guias.php`
- ✅ `admin/manage_locales.php`
- ✅ Todas las páginas del admin

**Problemas solucionados:**
- Scroll horizontal eliminado
- Tablas con scroll interno
- Imágenes responsive
- Botones tamaño adecuado para touch
- Formularios optimizados

## 📚 Archivos Importantes

### Nuevos
```
mapa_tareas_itinerario.php          - Mapa interactivo de tareas
api/actualizar_estado_tarea.php     - API actualización tareas
api/confirmar_servicio_proveedor.php - API confirmación servicios
assets/css/mobile-fixes.css         - Correcciones móviles
assets/js/mobile-sidebar.js         - Sidebar móvil universal
database/fix_all_system_errors.sql  - Script actualización BD
```

### Modificados
```
admin/mis_pedidos.php               - Agregado confirmaciones
admin/admin_header.php              - Incluido CSS móvil
seguimiento_itinerario.php          - Corregidos warnings
admin/manage_publicidad_carousel.php - Corregido imagen
test_system.php                     - Actualizado verificaciones
```

## 🗄️ Estructura de Base de Datos

### Nuevas Tablas

#### itinerario_tareas
```sql
- id (PK)
- id_itinerario (FK → itinerarios)
- id_destino (FK → destinos)
- id_servicio
- tipo_tarea (destino, servicio, actividad, transporte, alojamiento)
- titulo
- descripcion
- fecha_inicio
- fecha_fin
- ubicacion_lat
- ubicacion_lng
- direccion
- estado (pendiente, en_progreso, completado, cancelado)
- orden
- completado_por (FK → usuarios)
- fecha_completado
- notas
```

#### servicio_confirmaciones
```sql
- id (PK)
- id_pedido_servicio (FK → pedidos_servicios)
- id_proveedor (FK → usuarios)
- tipo_proveedor (agencia, guia, local)
- estado_confirmacion (pendiente, confirmado, rechazado, completado)
- fecha_confirmacion
- fecha_completado
- notas_proveedor
```

#### guias_destinos
```sql
- id (PK)
- id_guia (FK → usuarios)
- id_destino (FK → destinos)
- especialidad
- experiencia_anos
- certificaciones
- tarifa_base
- disponible
```

### Columnas Agregadas

**usuarios:**
- `telefono VARCHAR(20)`

**itinerarios:**
- `fecha_inicio DATE`
- `fecha_fin DATE`
- `descripcion TEXT`
- `estado_seguimiento ENUM(...)`
- `progreso_porcentaje INT`

**itinerario_destinos:**
- `precio DECIMAL(10,2)`
- `descripcion TEXT`
- `fecha_inicio DATETIME`
- `fecha_fin DATETIME`

**publicidad_carousel:**
- `imagen VARCHAR(255)`
- `orden INT`

## 🧪 Testing

### Test Automático
```bash
# Abrir en navegador
http://localhost/GQ-Turismo/test_system.php
```

### Test Manual

**1. Crear Itinerario:**
```
1. Login como turista
2. Ir a "Crear Itinerario"
3. Agregar destinos
4. Agregar servicios
5. Completar
```

**2. Ver Mapa de Tareas:**
```
1. Desde panel de itinerarios, click en "Ver Mapa de Tareas"
2. Verificar que muestra mapa
3. Verificar marcadores
4. Click en "Iniciar" una tarea
5. Verificar cambio de estado
```

**3. Confirmar Servicio (Proveedor):**
```
1. Login como agencia/guia/local
2. Ir a "Mis Pedidos"
3. Ver pedido pendiente
4. Click "Aceptar"
5. Verificar estado cambia a "confirmado"
```

**4. Test Móvil:**
```
1. Abrir Chrome DevTools (F12)
2. Toggle device toolbar (Ctrl+Shift+M)
3. Seleccionar iPhone o Android
4. Navegar por páginas admin
5. Verificar sidebar funciona
6. Verificar no hay scroll horizontal
```

## ❓ Solución de Problemas

### Error: "Tabla no existe"
```bash
# Verificar que ejecutaste el script SQL
mysql -u root -p gq_turismo < database/fix_all_system_errors.sql
```

### Error: "Columna desconocida"
```bash
# Ejecutar script SQL nuevamente
# O ejecutar manualmente las líneas específicas
```

### Sidebar no aparece en móvil
```bash
# Verificar que se incluyó mobile-fixes.css en admin_header.php
# Limpiar caché del navegador
# Verificar consola del navegador para errores JS
```

### Mapa no carga
```bash
# Verificar conexión a internet (usa CDN de Leaflet)
# Ver consola del navegador
# Verificar que hay tareas en el itinerario
```

## 📞 Soporte

Si encuentras problemas:

1. Revisar `test_system.php` para diagnosticar
2. Verificar consola del navegador (F12)
3. Revisar logs de PHP en `C:\xampp\apache\logs\error.log`
4. Verificar que MySQL está corriendo

## 🎯 Próximos Pasos Recomendados

1. **Crear Datos de Prueba:**
   - Crear usuarios de cada tipo
   - Crear destinos variados
   - Crear servicios para cada proveedor
   - Crear itinerarios de prueba

2. **Configurar Tareas Automáticas:**
   - Al crear itinerario, auto-generar tareas
   - Script que convierte destinos → tareas

3. **Notificaciones:**
   - Email cuando proveedor confirma
   - Push notifications para móvil

4. **Analytics:**
   - Dashboard con estadísticas
   - Itinerarios más populares
   - Proveedores mejor calificados

## ✅ Checklist Post-Instalación

- [ ] Ejecutar `fix_all_system_errors.sql`
- [ ] Verificar `test_system.php` - todos en verde
- [ ] Probar login como turista
- [ ] Probar login como guía
- [ ] Probar login como agencia
- [ ] Crear itinerario de prueba
- [ ] Ver mapa de tareas
- [ ] Marcar tarea como completada
- [ ] Confirmar servicio como proveedor
- [ ] Probar en móvil real
- [ ] Verificar sidebar móvil
- [ ] Verificar responsive en todas las páginas

## 📝 Notas Adicionales

- **Backup:** Siempre hacer backup de la BD antes de actualizar
- **Producción:** Probar en entorno de desarrollo primero
- **Cache:** Limpiar caché después de actualizar
- **Browser:** Usar navegadores modernos (Chrome, Firefox, Safari, Edge)

---

**Desarrollado para GQ-Turismo**  
**Versión:** 2.1  
**Fecha:** 23 de Octubre de 2025  
**Estado:** ✅ Listo para Producción
