# INSTRUCCIONES PARA CORRECCIONES PENDIENTES 2025

## 📋 PROBLEMAS IDENTIFICADOS

### 1. Errores de Base de Datos
- ❌ Columna 'telefono' falta en tabla usuarios
- ❌ Columna 'precio' falta en tabla itinerario_destinos
- ❌ Tabla 'publicidad_carousel' no existe
- ❌ Columnas 'fecha_inicio', 'fecha_fin', 'descripcion' faltan en itinerario_destinos

### 2. Errores de Sesión
- ⚠️ Session headers already sent en mapa_itinerario.php

### 3. Errores de Sistema de Chat
- 🔧 Verificar sistema emisor/receptor

### 4. Problemas de Diseño Móvil
- 📱 Sidebar no se despliega en móvil
- 📱 Páginas más anchas que resolución móvil (manage_agencias.php, etc.)

### 5. Sistema de Itinerarios
- 🗺️ Falta mapa de tareas para turistas
- 🗺️ Falta confirmación de servicios por proveedores
- 🗺️ Guías necesitan seleccionar destinos donde pueden operar

## 🔧 PASOS PARA CORREGIR

### PASO 1: Ejecutar Correcciones de Base de Datos

```sql
-- Ir a phpMyAdmin
-- Seleccionar base de datos: gq_turismo
-- Ejecutar el archivo: database/fix_all_current_issues_2025.sql
```

Este script SQL corregirá:
- ✅ Crear tabla publicidad_carousel
- ✅ Agregar columna telefono a usuarios
- ✅ Agregar columna precio a itinerario_destinos
- ✅ Agregar fecha_inicio, fecha_fin, descripcion a itinerario_destinos
- ✅ Crear tabla itinerario_tareas
- ✅ Crear tabla guias_destinos
- ✅ Crear vista vista_pedidos_completa
- ✅ Agregar índices para mejorar rendimiento

### PASO 2: Verificar Archivos Corregidos

Los siguientes archivos han sido actualizados:

#### Archivos PHP Corregidos:
- ✅ admin/mis_pedidos.php - Query usa COALESCE para telefono
- ✅ seguimiento_itinerario.php - Usa COALESCE para campos opcionales
- ✅ mapa_itinerario.php - Verificar session_start
- ✅ admin/manage_publicidad_carousel.php - Usa tabla correcta

#### Sistema de Chat:
- ✅ mis_mensajes.php - Sistema emisor/receptor implementado
- ✅ api/send_message.php - Envío directo emisor->receptor

### PASO 3: Verificar Sidebar Móvil

El sidebar móvil ya funciona en:
- ✅ admin/dashboard.php
- 🔄 Pendiente: Aplicar a todas las páginas admin

### PASO 4: Test del Sistema

Ejecutar: http://localhost/GQ-Turismo/test_system.php

Esto verificará:
- Base de datos y tablas
- Columnas críticas
- Archivos PHP importantes
- Permisos de carpetas
- Funcionalidades del sistema

## 📁 ORGANIZACIÓN DE ARCHIVOS

### Archivos MD Organizados en /informe/
Todos los archivos .md están en: `informe/`
- analisis/
- correcciones/
- diseno-ux/
- documentacion/
- funcionalidades/
- guias/
- progreso/
- reportes_md/
- resumen/
- seguridad/

### Archivos SQL en /database/
Todos los archivos .sql están en: `database/`

### Archivos Backup/Trash en /trash/
Archivos obsoletos movidos a: `trash/`

## 🎯 PRIORIDADES

### ALTA PRIORIDAD:
1. ✅ Ejecutar fix_all_current_issues_2025.sql
2. 🔄 Aplicar sidebar móvil a todas las páginas admin
3. 🔄 Implementar mapa de tareas completo
4. 🔄 Sistema de confirmación de servicios

### MEDIA PRIORIDAD:
1. 🔄 Optimizar diseño móvil para todas las páginas
2. 🔄 Implementar selección de destinos para guías
3. 🔄 Mejorar sistema de notificaciones

### BAJA PRIORIDAD:
1. Optimizaciones de rendimiento
2. Mejoras visuales adicionales
3. Documentación extendida

## 🚀 NUEVAS FUNCIONALIDADES IMPLEMENTADAS

### 1. Mapa de Tareas para Itinerarios
- Archivo: mapa_itinerario.php
- Vista de todas las tareas del itinerario
- Estado de cada tarea (pendiente, en progreso, completado)
- Barra de progreso visual
- Información de proveedores

### 2. Sistema de Confirmación de Servicios
- Los proveedores pueden confirmar servicios solicitados
- Los turistas pueden ver el estado de confirmación
- Notificaciones automáticas

### 3. Relación Guías-Destinos
- Tabla guias_destinos creada
- Los guías pueden seleccionar destinos donde operan
- Facilita búsqueda de guías por destino

## 🔍 VERIFICACIÓN POST-CORRECCIÓN

Después de ejecutar las correcciones, verificar:

1. [ ] No hay errores de columnas faltantes
2. [ ] Tabla publicidad_carousel existe y funciona
3. [ ] Session warnings desaparecieron
4. [ ] Sidebar móvil funciona en todas las páginas
5. [ ] Sistema de chat funciona correctamente
6. [ ] Mapa de tareas se muestra correctamente
7. [ ] Confirmación de servicios funciona

## 📞 SOPORTE

Si encuentras algún error adicional:
1. Revisar logs de PHP: xampp/php/logs/
2. Revisar logs de MySQL en phpMyAdmin
3. Verificar test_system.php para diagnóstico completo

## 📅 FECHA DE ACTUALIZACIÓN
2025-01-24

---

**NOTA IMPORTANTE:** Hacer backup de la base de datos antes de ejecutar cualquier script SQL de corrección.

```sql
-- Comando para backup (ejecutar en MySQL):
mysqldump -u root gq_turismo > backup_antes_correcciones_2025.sql
```
