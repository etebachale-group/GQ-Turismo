# Plan de Corrección Sistema GQ Turismo
## Fecha: 23 de Octubre 2025

## 🎯 Objetivos Principales

### 1. Correcciones de Errores Críticos
- [x] Error columna 'telefono' en mis_pedidos.php
- [x] Error session_start() en mapa_itinerario.php
- [x] Error columna 'precio' en itinerario_destinos
- [x] Warning array key "fecha_inicio" en seguimiento_itinerario.php
- [x] Warning array key "imagen" en manage_publicidad_carousel.php

### 2. Funcionalidades a Implementar

#### 2.1 Sistema de Mapa de Tareas para Turistas
- [ ] Crear interfaz de mapa interactivo
- [ ] Permitir marcar tareas como completadas
- [ ] Sistema de tracking en tiempo real
- [ ] Timeline visual del progreso

#### 2.2 Panel para Guías
- [ ] Acceso al mapa de itinerario del turista
- [ ] Visualización de tareas en tiempo real
- [ ] Sistema de confirmación de servicios

#### 2.3 Panel para Proveedores (Locales y Agencias)
- [ ] Interfaz para confirmar estado de servicios
- [ ] Notificaciones de solicitudes
- [ ] Sistema de aceptación/rechazo

#### 2.4 Sistema de Destinos para Guías
- [ ] Permitir a guías seleccionar destinos de lista global
- [ ] Vincular destinos con servicios de guía
- [ ] Sistema de administración de destinos por guía

### 3. Mejoras de Diseño Responsive

#### 3.1 Navbar/Sidebar Móvil
- [x] Implementar sidebar funcional en test_sidebar_mobile.html
- [x] Funciona en admin/dashboard.php
- [ ] Aplicar a TODAS las páginas del sistema

#### 3.2 Páginas que requieren optimización móvil
- [ ] manage_agencias.php
- [ ] manage_guias.php
- [ ] manage_locales.php
- [ ] manage_destinos.php
- [ ] manage_publicidad_carousel.php
- [ ] mis_pedidos.php
- [ ] itinerario.php
- [ ] seguimiento_itinerario.php
- [ ] mapa_itinerario.php

### 4. Estructura del Proyecto

#### 4.1 Organización de Archivos MD
```
informe/
├── analisis/
├── correcciones/
├── diseno-ux/
├── documentacion/
├── funcionalidades/
├── guias/
├── progreso/
├── reportes_md/  (Todos los MD nuevos aquí)
├── resumen/
└── seguridad/
```

## 📋 Plan de Ejecución

### Fase 1: Correcciones Críticas (COMPLETADO)
1. ✅ Corregir errores de base de datos
2. ✅ Solucionar warnings de PHP
3. ✅ Arreglar problemas de sesión

### Fase 2: Implementación de Funcionalidades
1. **Mapa de Tareas del Itinerario**
   - Crear API para actualizar estados
   - Implementar interfaz visual
   - Sistema de notificaciones

2. **Panel de Guías**
   - Sistema de gestión de destinos
   - Acceso a tracking de itinerarios
   - Confirmación de servicios

3. **Panel de Proveedores**
   - Interfaz de confirmación
   - Sistema de notificaciones
   - Gestión de pedidos

### Fase 3: Optimización Móvil
1. **Implementar Sidebar Universal**
   - Crear componente reutilizable
   - Integrar en todas las páginas
   - Asegurar funcionalidad en móvil

2. **Optimizar Tablas y Formularios**
   - Hacer tablas scrollables horizontalmente
   - Cards para móviles en lugar de tablas
   - Botones y controles táctiles

3. **Mejorar UX/UI General**
   - Menus desplegables
   - Modales responsivos
   - Imágenes adaptables

### Fase 4: Pruebas y Validación
1. Probar en diferentes dispositivos
2. Validar todas las funcionalidades
3. Actualizar test_system.php

## 🔧 Archivos Modificados Hasta Ahora

### Base de Datos
- usuarios: No tiene columna 'telefono'
- itinerario_destinos: No tiene columna 'precio'
- carouseles: Columna se llama 'ruta_imagen' no 'imagen'

### PHP Corregidos
- admin/mis_pedidos.php
- seguimiento_itinerario.php
- mapa_itinerario.php
- admin/manage_publicidad_carousel.php

## 📱 Especificaciones Responsive

### Breakpoints
- Mobile: < 576px
- Tablet: 576px - 768px
- Desktop: > 768px

### Elementos Críticos
- Sidebar colapsable en móvil
- Tablas convertir a cards en móvil
- Formularios en columna única en móvil
- Imágenes fluid
- Botones full-width en móvil

## 🚀 Próximos Pasos

1. Implementar sistema de mapa de tareas
2. Crear API para actualización de estados
3. Aplicar sidebar a todas las páginas
4. Optimizar tablas para móvil
5. Actualizar test_system.php
6. Documentar cambios

## 📝 Notas

- Todos los nuevos archivos MD se guardarán en `informe/reportes_md/`
- Mantener compatibilidad con versión desktop
- Priorizar funcionalidades críticas del negocio
- Testing en Chrome, Firefox, Safari móvil
