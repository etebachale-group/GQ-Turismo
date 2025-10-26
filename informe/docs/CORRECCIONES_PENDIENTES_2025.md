# CORRECCIONES Y MEJORAS PENDIENTES - GQ TURISMO

## Fecha: 2025-10-23

### PROBLEMAS IDENTIFICADOS Y SOLUCIONES

#### 1. ERRORES DE BASE DE DATOS

**Problema 1: Columna 'telefono' no existe en tabla usuarios**
- Ubicación: `admin/mis_pedidos.php` línea 70
- Solución: Ejecutar `database/fix_all_current_errors.sql`
- SQL: `ALTER TABLE usuarios ADD COLUMN telefono VARCHAR(20) AFTER email;`

**Problema 2: Columna 'precio' no existe en itinerario_destinos**  
- Solución: Ejecutar `database/fix_all_current_errors.sql`
- SQL: `ALTER TABLE itinerario_destinos ADD COLUMN precio DECIMAL(10,2) DEFAULT 0.00;`

**Problema 3: Columnas fecha_inicio, fecha_fin, descripcion no existen en itinerarios**
- Ubicación: `seguimiento_itinerario.php` línea 320
- Solución: Ejecutar `database/fix_all_current_errors.sql`
- SQL: Ver archivo SQL para ALTER TABLE completo

**Problema 4: Tabla 'publicidad_carousel' no existe**
- Solución: Ejecutar `database/create_publicidad_carousel.sql`

**Problema 5: Tabla 'guias_destinos' no existe**
- Solución: Ejecutar `database/fix_all_current_errors.sql`
- Esta tabla permite que guías elijan destinos donde trabajar

#### 2. ERRORES DE SESSION Y HEADERS

**Problema: Headers already sent en mapa_itinerario.php**
- Causa: Posible BOM o espacios antes de <?php
- Solución: Verificar que no haya salida antes de session_start()
- Estado: Revisar encoding UTF-8 sin BOM

#### 3. WARNINGS DE ARRAYS

**Problema: Undefined array key en varios archivos**
- `seguimiento_itinerario.php`: keys "fecha_inicio", "fecha_fin", "descripcion"
- `manage_publicidad_carousel.php`: key "imagen"
- Solución: Usar `isset()` o `??` operador coalescente nulo antes de acceder

#### 4. PROBLEMAS DE DISEÑO MÓVIL

**Problema 1: Sidebar no se despliega en móvil**
- Archivos afectados: Todas las páginas admin
- Solución: Implementar sidebar responsive basado en `test_sidebar_mobile.html`
- Ya funciona en: `admin/dashboard.php`
- Pendiente: Aplicar a todas las páginas admin

**Problema 2: Páginas más anchas que resolución móvil**
- Ejemplo: `admin/manage_agencias.php`
- Solución: Agregar media queries responsive
- CSS necesario:
  ```css
  @media (max-width: 768px) {
      .table-responsive { overflow-x: auto; }
      .container-fluid { padding: 0.5rem; }
  }
  ```

**Problema 3: Navbar no funciona en móvil**
- Solución: Asegurar Bootstrap JS está cargado
- Verificar toggle button funciona correctamente

#### 5. FUNCIONALIDADES FALTANTES

**A. Sistema de Tracking de Itinerarios para Turistas**
- Crear interfaz mapa de tareas
- Mostrar progreso del itinerario
- Permitir marcar tareas como completadas
- Archivo: `mapa_tareas_itinerario.php`

**B. Vista de Mapa para Guías**
- Los guías deben ver el mismo mapa de tareas
- Ver progreso del turista
- Confirmar cumplimiento de servicios

**C. Panel de Confirmación para Proveedores**
- Locales: Confirmar estado de servicio solicitado
- Agencias: Confirmar estado de servicio solicitado
- Actualizar estado en tiempo real

**D. Sistema de Selección de Destinos para Guías**
- Los guías eligen destinos donde pueden trabajar
- Basado en destinos creados por super_admin
- Usar tabla `guias_destinos`

#### 6. MEJORAS DE UX/UI

**Pendiente:**
- [ ] Diseñar estilo moderno para `manage_publicidad_carousel.php`
- [ ] Optimizar todas las páginas para móvil
- [ ] Implementar sidebar responsive en todas las páginas admin
- [ ] Añadir indicadores de progreso visuales
- [ ] Mejorar feedback de acciones del usuario

### INSTRUCCIONES DE IMPLEMENTACIÓN

#### PASO 1: Corregir Base de Datos
```bash
1. Abrir phpMyAdmin
2. Seleccionar base de datos gq_turismo
3. Ir a pestaña SQL
4. Ejecutar: database/fix_all_current_errors.sql
5. Verificar en test_system.php que todo esté OK
```

#### PASO 2: Corregir Warnings PHP
- Revisar cada archivo mencionado
- Agregar isset() o ?? donde sea necesario
- Usar COALESCE en queries SQL

#### PASO 3: Implementar Sidebar Móvil
- Copiar código de test_sidebar_mobile.html
- Aplicar a todas las páginas admin que usan admin_header.php
- Probar en dispositivos móviles

#### PASO 4: Crear Sistema de Tracking
- Mejorar mapa_itinerario.php
- Agregar actualización AJAX de estados
- Crear vista específica para guías
- Añadir panel de confirmación para proveedores

#### PASO 5: Optimizar para Móvil
- Agregar meta viewport en todas las páginas
- Implementar CSS responsive
- Probar en diferentes resoluciones
- Ajustar tamaños de fuente y botones

### ARCHIVOS CRÍTICOS A REVISAR

1. `admin/mis_pedidos.php` - Error columna telefono
2. `seguimiento_itinerario.php` - Warnings array keys
3. `mapa_itinerario.php` - Error headers y session
4. `admin/manage_publicidad_carousel.php` - Warning imagen, falta diseño
5. `admin/admin_header.php` - Implementar sidebar móvil
6. Todas las páginas admin - Responsive design

### SCRIPTS SQL CREADOS

1. `database/fix_all_current_errors.sql` - Correcciones completas
2. `database/create_publicidad_carousel.sql` - Tabla carousel
3. `test_system.php` - Sistema de verificación actualizado

### TESTING

**Para verificar que todo funciona:**
1. Abrir `test_system.php` en navegador
2. Verificar que todas las tablas existen
3. Verificar que no hay columnas faltantes
4. Probar cada funcionalidad en móvil y desktop

### NOTAS

- Todos los archivos .md ya están en `informe/`
- Todos los archivos .sql ya están en `database/`
- Archivos bypass ya están en `trash/`
- Sistema organizado y estructurado

### PRIORIDAD DE TAREAS

**ALTA:**
1. ✅ Ejecutar scripts SQL de corrección
2. ⏳ Corregir warnings en seguimiento_itinerario.php
3. ⏳ Implementar sidebar móvil en todas páginas
4. ⏳ Crear sistema tracking completo

**MEDIA:**
5. ⏳ Diseñar manage_publicidad_carousel.php
6. ⏳ Optimizar todas las páginas para móvil
7. ⏳ Implementar panel de confirmación proveedores

**BAJA:**
8. Mejorar animaciones y transiciones
9. Agregar más feedback visual
10. Optimizar queries de base de datos

---

**Última actualización:** 2025-10-23
**Estado general:** En progreso - Correcciones críticas identificadas
