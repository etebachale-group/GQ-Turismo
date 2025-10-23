# ☑️ CHECKLIST DE IMPLEMENTACIÓN GQ-TURISMO

## ⚡ GUÍA RÁPIDA VISUAL

```
┌─────────────────────────────────────────────────────────────┐
│  ESTADO ACTUAL: CORRECCIONES COMPLETADAS                   │
│  PRÓXIMO PASO: EJECUTAR SQL Y ACCIONES DE SEGURIDAD       │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔵 FASE 1: BASE DE DATOS (OBLIGATORIO)

### ☐ Tarea 1.1: Ejecutar Script de Correcciones
```
Archivo: database/correciones_criticas.sql
Método: phpMyAdmin
Tiempo: 2 minutos
```

**Pasos**:
```
[ ] 1. Abrir http://localhost/phpmyadmin
[ ] 2. Click en 'gq_turismo' (panel izquierdo)
[ ] 3. Click en pestaña 'SQL'
[ ] 4. Abrir: database/correciones_criticas.sql
[ ] 5. Ctrl+A (seleccionar todo) → Ctrl+C (copiar)
[ ] 6. Pegar en phpMyAdmin
[ ] 7. Click 'Continuar'
[ ] 8. Verificar mensaje: "X consultas ejecutadas"
```

**Verificación**:
```sql
[ ] DESCRIBE pedidos_servicios;
    → Debe mostrar columna 'nombre_servicio'
    
[ ] SHOW COLUMNS FROM pedidos_servicios WHERE Field = 'estado';
    → Type debe incluir: ...'pagado'...
```

---

## 🔴 FASE 2: SEGURIDAD (CRÍTICO)

### ☐ Tarea 2.1: Eliminar Archivos de Bypass

**Desde Explorador de Windows**:
```
[ ] Abrir: C:\xampp\htdocs\GQ-Turismo\
    
    [ ] Buscar y ELIMINAR (Shift+Delete):
        [ ] generar_hash.php
        [ ] eliminar_bypass.bat
        [ ] mover_informe.bat
    
[ ] Abrir: C:\xampp\htdocs\GQ-Turismo\database\
    
    [ ] Buscar y ELIMINAR (Shift+Delete):
        [ ] add_admin.php
        [ ] add_super_admin.php
        [ ] update_db.php (si existe)
```

**Desde CMD** (alternativa):
```cmd
[ ] cd C:\xampp\htdocs\GQ-Turismo

[ ] del /F /Q generar_hash.php
[ ] del /F /Q eliminar_bypass.bat
[ ] del /F /Q mover_informe.bat

[ ] cd database
[ ] del /F /Q add_admin.php
[ ] del /F /Q add_super_admin.php
[ ] del /F /Q update_db.php
```

### ☐ Tarea 2.2: Cambiar Contraseña Super Admin

**Paso A: Generar Hash**
```
[ ] Crear archivo: generar_nuevo_hash.php
    
    <?php
    $nueva_contraseña = 'TuContraseñaSegura123!@#';
    echo password_hash($nueva_contraseña, PASSWORD_DEFAULT);
    ?>

[ ] Abrir: http://localhost/GQ-Turismo/generar_nuevo_hash.php
[ ] COPIAR el hash generado
[ ] ELIMINAR generar_nuevo_hash.php INMEDIATAMENTE
```

**Paso B: Actualizar BD**
```sql
[ ] Abrir phpMyAdmin → gq_turismo → SQL

[ ] UPDATE usuarios 
    SET contrasena = 'PEGA_EL_HASH_AQUÍ' 
    WHERE email = 'etebachalegroup@gmail.com';

[ ] Click 'Continuar'
[ ] Verificar mensaje: "1 fila afectada"
```

### ☐ Tarea 2.3: Eliminar Usuarios de Prueba (Opcional)

```sql
[ ] DELETE FROM usuarios WHERE email IN (
        'admin@gqturismo.com',
        'agencia@example.com',
        'guia@example.com',
        'guia2@example.com',
        'local@example.com'
    );
```

---

## 🟢 FASE 3: VERIFICACIÓN (IMPORTANTE)

### ☐ Tarea 3.1: Probar pagar.php

```
[ ] Abrir navegador
[ ] Ir a: http://localhost/GQ-Turismo
[ ] Iniciar sesión como turista
[ ] Ir a: Mis Pedidos
[ ] Seleccionar un pedido 'confirmado'
[ ] Click 'Pagar'
[ ] Verificar: No hay errores SQL
[ ] Verificar: Estado cambia a 'pagado'
[ ] Verificar: Se muestra nombre del servicio
```

### ☐ Tarea 3.2: Probar admin/reservas.php

```
[ ] Iniciar sesión como super_admin
[ ] Ir a: Panel Admin → Reservas/Pedidos
[ ] Verificar: Página carga sin errores
[ ] Verificar: Se ven 2 tabs
    [ ] "Reservas de Itinerarios"
    [ ] "Pedidos de Servicios"
[ ] Click en tab "Pedidos de Servicios"
[ ] Verificar: Se muestran pedidos
[ ] Verificar: Columna 'Servicio' muestra nombres
[ ] Verificar: No hay errores de SQL
```

### ☐ Tarea 3.3: Verificar Diseño Responsivo

**Desktop**:
```
[ ] Abrir en pantalla grande (>1200px)
[ ] Verificar: Navegación completa visible
[ ] Verificar: Sidebar admin fijo a la izquierda
[ ] Verificar: Grids de múltiples columnas
```

**Tablet**:
```
[ ] Redimensionar navegador a ~800px
[ ] Verificar: Navegación se adapta
[ ] Verificar: Sidebar sigue visible
[ ] Verificar: Grids de 2 columnas
```

**Móvil**:
```
[ ] Redimensionar navegador a ~400px
[ ] Verificar: Menú hamburguesa aparece
[ ] Verificar: Botón de sidebar aparece (esquina inferior izquierda)
[ ] Click en menú hamburguesa
[ ] Verificar: Menú se despliega
[ ] Click en botón sidebar
[ ] Verificar: Sidebar se desliza desde la izquierda
[ ] Verificar: Grids de 1 columna
```

---

## 🟡 FASE 4: SEGURIDAD ADICIONAL (RECOMENDADO)

### ☐ Tarea 4.1: Verificar .htaccess

```
[ ] Verificar existe: database/.htaccess
[ ] Verificar existe: .htaccess (raíz)
[ ] Probar acceso bloqueado:
    [ ] http://localhost/GQ-Turismo/database/correciones_criticas.sql
        → Debe dar Error 403
```

### ☐ Tarea 4.2: Cambiar Contraseña MySQL Root (Producción)

```sql
[ ] mysql -u root

[ ] ALTER USER 'root'@'localhost' IDENTIFIED BY 'nueva_contraseña_segura';

[ ] FLUSH PRIVILEGES;

[ ] Actualizar includes/db_connect.php con nueva contraseña
```

---

## 📊 PROGRESO TOTAL

```
FASE 1: Base de Datos       [ ] 0/1 completado
FASE 2: Seguridad           [ ] 0/3 completado
FASE 3: Verificación        [ ] 0/3 completado
FASE 4: Seguridad Adicional [ ] 0/2 completado

TOTAL: [ ] 0/9 tareas completadas (0%)
```

---

## ✅ CUANDO TODO ESTÉ MARCADO

```
┌─────────────────────────────────────────────────────────────┐
│  ✅ IMPLEMENTACIÓN COMPLETADA                              │
│  ✅ SISTEMA LISTO PARA TESTING                             │
│  ✅ SEGURIDAD BÁSICA IMPLEMENTADA                          │
└─────────────────────────────────────────────────────────────┘
```

**Siguiente Paso**: Testing exhaustivo con usuarios reales

---

## 🆘 SI ALGO SALE MAL

| Problema | Solución |
|----------|----------|
| Error SQL al ejecutar script | Verifica que estás en BD 'gq_turismo' |
| Archivo no se elimina | Cierra editor de texto / IDE primero |
| Error en pagar.php | Verifica que ejecutaste el script SQL |
| Admin/reservas vacío | Crea datos de prueba primero |
| Página da 404 | Verifica que XAMPP esté corriendo |
| Error de conexión BD | Verifica includes/db_connect.php |

---

## 📚 DOCUMENTACIÓN DE REFERENCIA

| Para | Lee |
|------|-----|
| Empezar | `INSTRUCCIONES_IMPLEMENTACION.md` |
| Detalles completos | `RESUMEN_EJECUTIVO_FINAL.md` |
| Problemas de seguridad | `informe/SEGURIDAD_CRITICA.md` |
| Errores corregidos | `informe/CORRECCIONES_COMPLETADAS.md` |
| Vista general | `ANALISIS_GENERAL.md` |

---

**ÚLTIMA ACTUALIZACIÓN**: 23 de Octubre de 2025  
**VERSIÓN**: 1.0  
**ESTADO**: ⚠️ PENDIENTE DE IMPLEMENTACIÓN

```
🎯 TU PRÓXIMO PASO: Marcar la primera casilla de FASE 1 → Tarea 1.1
```
