# 🔧 CORRECCIONES DE COLUMNAS EN BASE DE DATOS

## Error: Unknown column 'ps.created_at'

### **Problema:**
Los archivos `manage_agencias.php` y `manage_guias.php` estaban intentando acceder a una columna `created_at` que no existe en la tabla `pedidos_servicios`.

### **Causa Raíz:**
La tabla `pedidos_servicios` usa `fecha_solicitud` como columna para la fecha de creación, no `created_at`.

---

## ✅ CORRECCIONES APLICADAS

### **1. manage_guias.php (líneas 468, 473)**

**ANTES:**
```php
ps.created_at
...
ORDER BY ps.created_at DESC
```

**DESPUÉS:**
```php
ps.fecha_solicitud
...
ORDER BY ps.fecha_solicitud DESC
```

### **2. manage_agencias.php (líneas 314, 320)**

**ANTES:**
```php
ps.created_at
...
ORDER BY ps.created_at DESC
```

**DESPUÉS:**
```php
ps.fecha_solicitud
...
ORDER BY ps.fecha_solicitud DESC
```

---

## 📋 ESTRUCTURA CORRECTA DE LA TABLA

```sql
-- Verificar estructura de pedidos_servicios
DESCRIBE pedidos_servicios;

-- Columnas principales:
-- - id (INT, PRIMARY KEY, AUTO_INCREMENT)
-- - id_turista (INT)
-- - tipo_proveedor (ENUM: 'agencia', 'guia', 'local')
-- - id_proveedor (INT)
-- - id_servicio_o_menu (INT)
-- - tipo_item (ENUM: 'servicio', 'menu', 'guia_destino')
-- - id_itinerario (INT, NULL)
-- - fecha_solicitud (DATETIME, DEFAULT CURRENT_TIMESTAMP) ← Esta es la correcta
-- - fecha_servicio (DATE)
-- - cantidad_personas (INT)
-- - precio_unitario (DECIMAL)
-- - precio_total (DECIMAL)
-- - estado (ENUM: 'pendiente', 'confirmado', 'cancelado', 'completado')
-- - id_destino (INT, NULL)
```

---

## 🧪 VERIFICACIÓN

### **Probar manage_guias.php:**
```
1. Login como super_admin
2. Ir a: /GQ-Turismo/admin/manage_guias.php
3. Click en "Ver Detalles" de cualquier guía
4. Verificar que no aparece el error
5. Verificar que se muestran los pedidos correctamente
```

### **Probar manage_agencias.php:**
```
1. Login como super_admin
2. Ir a: /GQ-Turismo/admin/manage_agencias.php
3. Click en "Ver Detalles" de cualquier agencia
4. Verificar que no aparece el error
5. Verificar que se muestran los pedidos correctamente
```

---

## 📊 ARCHIVOS AFECTADOS

| Archivo | Líneas Modificadas | Estado |
|---------|-------------------|--------|
| `admin/manage_guias.php` | 468, 473 | ✅ Corregido |
| `admin/manage_agencias.php` | 314, 320 | ✅ Corregido |
| `admin/manage_locales.php` | N/A | ✅ OK (no tenía el error) |

---

## ⚠️ NOTA IMPORTANTE

Si en el futuro necesitas agregar una columna `created_at` a la tabla `pedidos_servicios` para mantener consistencia con otras tablas, puedes ejecutar:

```sql
-- Agregar columna created_at (OPCIONAL - no es necesario por ahora)
ALTER TABLE pedidos_servicios 
ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
AFTER fecha_solicitud;

-- Copiar datos existentes de fecha_solicitud a created_at
UPDATE pedidos_servicios 
SET created_at = fecha_solicitud 
WHERE created_at IS NULL;
```

Sin embargo, **NO es necesario** hacer esto ahora, ya que `fecha_solicitud` cumple perfectamente la función.

---

## ✅ RESUMEN

- ✅ Error corregido en `manage_guias.php`
- ✅ Error corregido en `manage_agencias.php`
- ✅ Columna correcta: `fecha_solicitud` (no `created_at`)
- ✅ Ambos archivos ahora funcionan correctamente
- ✅ No se requieren cambios en la base de datos

---

**Estado:** ✅ COMPLETADO  
**Fecha:** 23 de Octubre 2025  
**Prioridad:** ALTA (Error crítico que impedía ver detalles)
