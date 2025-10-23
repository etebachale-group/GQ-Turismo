# 🚀 Instrucciones de Instalación - Sistema de Seguimiento
**Fecha:** 23 de Octubre de 2025  
**Versión:** 2.2  

---

## ⚠️ IMPORTANTE: Ejecutar Script SQL

Para que el sistema de seguimiento de itinerarios funcione correctamente, **debes ejecutar uno de los siguientes scripts SQL:**

---

## 📝 Opción 1: Script Simple (Recomendado para primera instalación)

**Archivo:** `database/add_estado_columns.sql`

### Desde phpMyAdmin:
1. Abrir phpMyAdmin
2. Seleccionar base de datos `gq_turismo`
3. Ir a pestaña "SQL"
4. Copiar y pegar el contenido de `add_estado_columns.sql`
5. Click en "Continuar"

### Desde línea de comandos:
```bash
mysql -u root -p gq_turismo < database/add_estado_columns.sql
```

### Desde XAMPP Shell:
```bash
cd C:\xampp\htdocs\GQ-Turismo
mysql -u root gq_turismo < database\add_estado_columns.sql
```

---

## 📝 Opción 2: Script Seguro (Recomendado si ya ejecutaste el script antes)

**Archivo:** `database/add_estado_columns_safe.sql`

Este script **verifica si las columnas ya existen** antes de agregarlas, evitando errores.

### Desde phpMyAdmin:
1. Abrir phpMyAdmin
2. Seleccionar base de datos `gq_turismo`
3. Ir a pestaña "SQL"
4. Copiar y pegar el contenido de `add_estado_columns_safe.sql`
5. Click en "Continuar"

### Desde línea de comandos:
```bash
mysql -u root -p gq_turismo < database/add_estado_columns_safe.sql
```

---

## ✅ Verificar Instalación

Después de ejecutar el script, verifica que las columnas se agregaron correctamente:

### SQL de Verificación:
```sql
-- Verificar itinerario_destinos
SHOW COLUMNS FROM itinerario_destinos LIKE 'estado';

-- Verificar itinerario_guias
SHOW COLUMNS FROM itinerario_guias WHERE Field IN ('estado', 'notas', 'fecha_confirmacion', 'fecha_completado');

-- Verificar itinerario_agencias
SHOW COLUMNS FROM itinerario_agencias WHERE Field IN ('estado', 'notas', 'fecha_confirmacion', 'fecha_completado');

-- Verificar itinerario_locales
SHOW COLUMNS FROM itinerario_locales WHERE Field IN ('estado', 'notas', 'fecha_confirmacion', 'fecha_completado');
```

### Resultado Esperado:
Deberías ver las siguientes columnas en cada tabla:

**itinerario_destinos:**
- `estado` - ENUM('pendiente', 'en_progreso', 'completado', 'cancelado')

**itinerario_guias:**
- `estado` - ENUM('pendiente', 'confirmado', 'completado', 'cancelado')
- `notas` - TEXT
- `fecha_confirmacion` - DATETIME
- `fecha_completado` - DATETIME

**itinerario_agencias:**
- `estado` - ENUM('pendiente', 'confirmado', 'completado', 'cancelado')
- `notas` - TEXT
- `fecha_confirmacion` - DATETIME
- `fecha_completado` - DATETIME

**itinerario_locales:**
- `estado` - ENUM('pendiente', 'confirmado', 'completado', 'cancelado')
- `notas` - TEXT
- `fecha_confirmacion` - DATETIME
- `fecha_completado` - DATETIME

---

## 🔧 Solución de Problemas

### Error: "Column already exists"
**Solución:** Usa el script seguro `add_estado_columns_safe.sql`

### Error: "Unknown column 'precio'"
**Solución:** Ya está corregido en ambos scripts. La columna se llama `precio_estimado` no `precio`.

### Error: "Access denied"
**Solución:** Asegúrate de tener permisos de administrador en MySQL
```bash
mysql -u root -p
# Ingresa tu contraseña cuando la pida
```

### Error: "Database not found"
**Solución:** Primero selecciona la base de datos:
```sql
USE gq_turismo;
```

---

## 🧪 Probar el Sistema

Una vez ejecutado el script SQL correctamente:

### 1. Crear Itinerario de Prueba
1. Login como **turista**
2. Ir a "Crear Itinerario"
3. Agregar nombre: "Mi Viaje a Malabo"
4. Agregar destinos: Malabo, Luba, etc.
5. Guardar

### 2. Confirmar Servicio (Como Proveedor)
1. Login como **guía** (o agencia/local)
2. Ir a "Mis Pedidos" o "Dashboard"
3. Ver el pedido del itinerario
4. Cambiar estado a "confirmado"

### 3. Acceder al Seguimiento (Como Turista)
1. Login como **turista**
2. Ir a "Mis Itinerarios"
3. Deberías ver botón azul **"Seguimiento"** en itinerarios confirmados
4. Click en "Seguimiento"
5. Verás el timeline visual

### 4. Iniciar y Completar Destinos
1. En el timeline, click **"Iniciar"** en el primer destino
2. El marcador cambiará a amarillo pulsante (en progreso)
3. Cuando termines, click **"Completar"**
4. El marcador cambiará a verde con check (completado)
5. Continúa con los siguientes destinos

### 5. Vista del Guía
1. Login como el **guía asignado**
2. Ir a URL: `seguimiento_itinerario.php?id=1` (cambia el ID)
3. Podrás ver el timeline completo
4. Ver el progreso del turista en tiempo real

---

## 📱 Funcionalidades Disponibles

### Para Turistas:
- ✅ Ver timeline de todos los destinos
- ✅ Iniciar destinos (pendiente → en progreso)
- ✅ Completar destinos (en progreso → completado)
- ✅ Ver información de cada destino
- ✅ Ver servicios asignados (guías, agencias, locales)
- ✅ Ver estado de confirmación de proveedores
- ✅ Ver mapa preview de ubicaciones

### Para Guías/Agencias/Locales:
- ✅ Ver timeline del itinerario
- ✅ Ver progreso del turista
- ✅ Confirmar su servicio
- ✅ Marcar servicio como completado
- ✅ Agregar notas al servicio
- ✅ Ver información de contacto del turista

---

## 🎨 Estados del Sistema

### Estados de Destinos (Turista):
```
pendiente → en_progreso → completado
                ↓
            cancelado
```

### Estados de Servicios (Proveedores):
```
pendiente → confirmado → completado
                ↓
            cancelado
```

---

## 📊 Flujo Completo

```
1. TURISTA crea itinerario
   └─> Estado inicial: "planificacion"
   └─> Todos los destinos: "pendiente"
   └─> Todos los servicios: "pendiente"

2. PROVEEDOR confirma servicio
   └─> Servicio: "pendiente" → "confirmado"
   └─> Se registra fecha_confirmacion

3. ITINERARIO se activa
   └─> Estado: "planificacion" → "confirmado"
   └─> Botón "Seguimiento" aparece

4. TURISTA inicia seguimiento
   └─> Ve timeline visual
   └─> Inicia primer destino: "en_progreso"
   └─> Marcador amarillo pulsante

5. TURISTA completa destino
   └─> Destino: "en_progreso" → "completado"
   └─> Marcador verde con check
   └─> Continúa con siguiente

6. PROVEEDOR completa su servicio
   └─> Servicio: "confirmado" → "completado"
   └─> Se registra fecha_completado

7. TODO COMPLETADO
   └─> Todos destinos: "completado"
   └─> Todos servicios: "completado"
   └─> Itinerario: "confirmado" → "completado"
```

---

## 🔐 URLs del Sistema

### Páginas Principales:
- **Crear Itinerario:** `crear_itinerario.php`
- **Mis Itinerarios:** `itinerario.php`
- **Seguimiento:** `seguimiento_itinerario.php?id={ID}`
- **Mis Pedidos (Proveedores):** `admin/mis_pedidos.php`

### APIs:
- **Actualizar Destino:** `api/update_destino_status.php`
- **Actualizar Servicio:** `api/update_servicio_status.php`

---

## ⚡ Comandos Rápidos

### Ejecutar script simple:
```bash
cd C:\xampp\htdocs\GQ-Turismo
mysql -u root gq_turismo < database\add_estado_columns.sql
```

### Ejecutar script seguro:
```bash
cd C:\xampp\htdocs\GQ-Turismo
mysql -u root gq_turismo < database\add_estado_columns_safe.sql
```

### Verificar columnas:
```bash
mysql -u root gq_turismo -e "SHOW COLUMNS FROM itinerario_destinos LIKE 'estado';"
```

### Resetear estados (si es necesario):
```sql
UPDATE itinerario_destinos SET estado = 'pendiente';
UPDATE itinerario_guias SET estado = 'pendiente', notas = NULL, fecha_confirmacion = NULL, fecha_completado = NULL;
UPDATE itinerario_agencias SET estado = 'pendiente', notas = NULL, fecha_confirmacion = NULL, fecha_completado = NULL;
UPDATE itinerario_locales SET estado = 'pendiente', notas = NULL, fecha_confirmacion = NULL, fecha_completado = NULL;
```

---

## 📞 Soporte

Si encuentras algún problema:

1. **Verificar logs de error de MySQL**
2. **Verificar que las tablas existen:**
   ```sql
   SHOW TABLES LIKE 'itinerario_%';
   ```
3. **Verificar estructura de tabla:**
   ```sql
   DESCRIBE itinerario_destinos;
   ```
4. **Revisar errores PHP en:** `C:\xampp\apache\logs\error.log`

---

## ✅ Checklist de Instalación

- [ ] Script SQL ejecutado correctamente
- [ ] Columnas verificadas en base de datos
- [ ] Sin errores en logs de MySQL
- [ ] Sin errores en logs de Apache/PHP
- [ ] Página de seguimiento accesible
- [ ] Botón "Seguimiento" visible en itinerarios
- [ ] Timeline se muestra correctamente
- [ ] Botones "Iniciar" y "Completar" funcionan
- [ ] Estados se actualizan correctamente
- [ ] Proveedores pueden confirmar servicios

---

**🎉 ¡Listo! El sistema de seguimiento está completamente funcional.**

---

**Elaborado por:** GitHub Copilot AI  
**Fecha:** 23 de Octubre de 2025  
**Versión del Sistema:** 2.2  
**Soporte:** Documentación en `/informe/`
