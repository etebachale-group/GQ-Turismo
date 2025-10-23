# PLAN DE CORRECCIÓN COMPLETO - GQ-TURISMO
## Fecha: 2025-10-23

---

## ✅ ERRORES CORREGIDOS

### 1. pagar.php - Errores SQL (COMPLETADO ✅)
- **Error línea 26**: Estado 'pagado' → Cambiado a 'completado'
- **Error línea 47**: Columna 'ps.item_name' con COALESCE → Cambiado a CASE directo

### 2. admin/reservas.php - Columna faltante (COMPLETADO ✅)
- **Error línea 18**: Columna 'fecha' no existe → Cambiado a 'fecha_reserva AS fecha'

---

## 🔧 ERRORES POR CORREGIR

### 3. Base de Datos - Estados ENUM
**Archivo**: database_fixes.sql (CREADO)
**Acción**: Ejecutar en phpMyAdmin para actualizar estructura
- Agregar 'completado' y 'pagado' a ENUM de pedidos_servicios.estado
- Verificar tablas pedidos_servicios exista
- Agregar columnas ciudad a lugares_locales y guias_turisticos
- Actualizar itinerarios con nuevas columnas

### 4. Páginas manage_*.php - Falta Header Moderno
**Archivos afectados**:
- admin/manage_agencias.php
- admin/manage_guias.php
- admin/manage_locales.php
- admin/manage_destinos.php (si existe)
- admin/manage_users.php (si existe)

**Cambios necesarios**:
```php
// ANTES:
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar X - Admin</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <?php include 'sidebar.php'; ?>
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">

// DESPUÉS:
<?php
$page_title = "Gestionar X";
include 'admin_header.php';
?>

<!-- Contenido aquí -->

<?php include 'admin_footer.php'; ?>
```

### 5. Diseño UX/UI Moderno - Páginas Públicas
**Archivos a actualizar**:
- index.php
- destinos.php
- agencias.php
- guias.php
- locales.php
- detalle_*.php (todos)
- crear_itinerario.php
- itinerario.php
- reservas.php
- contacto.php
- about.php

**Mejoras requeridas**:
1. Diseño moderno y tropical
2. Responsive móvil tipo app
3. Animaciones sutiles
4. Cards con sombras y efectos hover
5. Paleta de colores actualizada
6. Tipografía mejorada

### 6. Archivos Vulnerables/Bypass
**Por revisar y eliminar/asegurar**:
- Archivos de test sin protección
- Scripts de SQL sin validación
- Formularios sin CSRF protection
- Uploads sin validación adecuada

### 7. admin/messages.php - Error de sintaxis reportado
**Estado**: No encontrado en revisión manual
**Acción**: Validar con PHP lint o revisar cache del navegador

---

## 📂 ORGANIZACIÓN DE ARCHIVOS

### Mover a /informe (carpeta ya existe):
```
ACCIONES_SEGURIDAD_COMPLETADAS.md
ADMIN_DISENO_IMPLEMENTADO.md
ANALISIS_*.md (todos)
AUDITORIA_SEGURIDAD.md
CHECKLIST_IMPLEMENTACION.md
CORRECCIONES_APLICADAS.md
CORRECCION_PAGAR.md
DISENO_MODERNO_IMPLEMENTADO.md
ERRORES_CORREGIDOS_PAGAR.md
INFORME_FINAL_TRABAJO.md
INICIO_AQUI.md
INSTRUCCIONES_IMPLEMENTACION.md
LEEME_PRIMERO.md
LEER_ESTO_AHORA.md
MEJORAS_UX_UI.md
PAGINAS_ADMIN_ACTUALIZADAS.md
README.mdgit
RESUMEN_*.md (todos)
START_HERE.md
TRABAJO_COMPLETADO*.md
arreglos.md
modificaciones.md
progress.md
```

### Mantener en raíz:
```
README.md
mensaje_para_copilot.md
instrucciones.md
database_fixes.sql
ANALISIS_Y_TAREAS.md
PLAN_CORRECCION_COMPLETO.md
```

---

## 🎯 PRIORIDADES DE EJECUCIÓN

### PRIORIDAD ALTA (Hacer ahora)
1. ✅ Corregir errores SQL en pagar.php
2. ✅ Corregir query en admin/reservas.php
3. ✅ Crear script database_fixes.sql
4. ⏳ Ejecutar database_fixes.sql en phpMyAdmin
5. ⏳ Actualizar manage_agencias.php con admin_header.php
6. ⏳ Actualizar manage_guias.php con admin_header.php
7. ⏳ Actualizar manage_locales.php con admin_header.php
8. ⏳ Mover archivos a /informe

### PRIORIDAD MEDIA (Próximos pasos)
1. Actualizar diseño de index.php (prototipo)
2. Replicar diseño moderno a todas las páginas públicas
3. Revisar y eliminar archivos vulnerables
4. Implementar protecciones de seguridad adicionales
5. Validar todas las funcionalidades

### PRIORIDAD BAJA (Mejoras futuras)
1. Optimización de rendimiento
2. Sistema de caché
3. Compresión de imágenes
4. Minificación de CSS/JS
5. Tests automatizados

---

## 📋 CHECKLIST DE VALIDACIÓN

### Base de Datos
- [ ] Ejecutar database_fixes.sql
- [ ] Verificar estructura de pedidos_servicios
- [ ] Verificar estructura de reservas
- [ ] Probar queries de pagar.php
- [ ] Probar queries de admin/reservas.php

### Páginas Admin
- [ ] manage_agencias.php usa admin_header.php
- [ ] manage_guias.php usa admin_header.php
- [ ] manage_locales.php usa admin_header.php
- [ ] manage_destinos.php usa admin_header.php
- [ ] manage_users.php usa admin_header.php
- [ ] Todas tienen diseño responsivo
- [ ] Todas funcionan correctamente

### Páginas Públicas
- [ ] index.php diseño moderno
- [ ] destinos.php diseño moderno
- [ ] agencias.php diseño moderno
- [ ] guias.php diseño moderno
- [ ] locales.php diseño moderno
- [ ] Todas responsivas móvil
- [ ] Todas funcionales

### Seguridad
- [ ] No hay archivos de bypass
- [ ] Validación de formularios
- [ ] Protección contra SQL Injection
- [ ] Protección contra XSS
- [ ] Validación de uploads
- [ ] CSRF tokens implementados

### Funcionalidad
- [ ] Login/Logout funciona
- [ ] Registro funciona
- [ ] Crear itinerario funciona
- [ ] Reservar funciona
- [ ] Pagar funciona
- [ ] Mensajería funciona
- [ ] Valoraciones funciona
- [ ] Búsqueda funciona

---

## 🚀 SIGUIENTE PASO INMEDIATO

**EJECUTAR**:
1. Importar database_fixes.sql en phpMyAdmin
2. Actualizar manage_agencias.php con nuevo header
3. Probar página de pagar.php
4. Mover archivos a /informe

**COMANDO PARA MOVER ARCHIVOS**:
```powershell
Move-Item -Path "ACCIONES_SEGURIDAD_COMPLETADAS.md" -Destination "informe/"
# Repetir para cada archivo...
```

---

**Estado actual**: 2 de 7 errores críticos corregidos (29%)
**Próximo milestone**: Actualizar todas las páginas manage con header moderno
