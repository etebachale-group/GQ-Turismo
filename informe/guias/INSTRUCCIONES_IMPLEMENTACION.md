# 🚀 INSTRUCCIONES DE IMPLEMENTACIÓN - GQ-TURISMO

## ⚠️ ACCIONES CRÍTICAS REQUERIDAS

Antes de poner el sitio en producción, **DEBES** completar los siguientes pasos:

---

## 📋 PASO 1: EJECUTAR CORRECCIONES DE BASE DE DATOS

### Opción A: Desde phpMyAdmin (Recomendado)
```
1. Abrir navegador → http://localhost/phpmyadmin
2. Seleccionar base de datos 'gq_turismo' (panel izquierdo)
3. Click en pestaña 'SQL' (arriba)
4. Abrir archivo: C:\xampp\htdocs\GQ-Turismo\database\correciones_criticas.sql
5. Copiar TODO el contenido
6. Pegar en el campo SQL de phpMyAdmin
7. Click en botón 'Continuar' o 'Go'
8. Verificar mensaje: "X consultas ejecutadas correctamente"
```

### Opción B: Desde Línea de Comandos
```cmd
cd C:\xampp\mysql\bin
mysql -u root -p gq_turismo < C:\xampp\htdocs\GQ-Turismo\database\correciones_criticas.sql
```

### Verificación:
```sql
-- Ejecutar en phpMyAdmin para verificar:
DESCRIBE pedidos_servicios;
-- Debe mostrar columna 'nombre_servicio'

SHOW COLUMNS FROM pedidos_servicios WHERE Field = 'estado';
-- El Type debe incluir: 'pendiente','confirmado','cancelado','completado','pagado'
```

---

## 🔒 PASO 2: ELIMINAR ARCHIVOS DE BYPASS (CRÍTICO)

### Archivos a Eliminar:

#### Desde Explorador de Windows:
```
1. Abrir: C:\xampp\htdocs\GQ-Turismo\
2. Buscar y ELIMINAR (Shift+Delete):
   ❌ generar_hash.php
   ❌ eliminar_bypass.bat
   ❌ mover_informe.bat

3. Abrir: C:\xampp\htdocs\GQ-Turismo\database\
4. Buscar y ELIMINAR (Shift+Delete):
   ❌ add_admin.php
   ❌ add_super_admin.php
   ❌ update_db.php (si ya fue ejecutado)
```

#### Desde CMD (Alternativa):
```cmd
cd C:\xampp\htdocs\GQ-Turismo

del /F /Q generar_hash.php 2>nul
del /F /Q eliminar_bypass.bat 2>nul
del /F /Q mover_informe.bat 2>nul

cd database
del /F /Q add_admin.php 2>nul
del /F /Q add_super_admin.php 2>nul
del /F /Q update_db.php 2>nul

echo Archivos de bypass eliminados exitosamente
```

### Verificación:
```cmd
cd C:\xampp\htdocs\GQ-Turismo
dir generar_hash.php
-- Debe mostrar: "El sistema no puede encontrar el archivo especificado"

cd database
dir add_*.php
-- Debe mostrar: "El sistema no puede encontrar el archivo especificado"
```

---

## 🔐 PASO 3: CAMBIAR CONTRASEÑAS COMPROMETIDAS

### 3.1 Generar Nueva Contraseña Hash

**Crear archivo temporal**: `C:\xampp\htdocs\GQ-Turismo\generar_nuevo_hash.php`

```php
<?php
// ARCHIVO TEMPORAL - ELIMINAR DESPUÉS DE USAR

$nueva_contraseña = 'TU_CONTRASEÑA_SUPER_SEGURA_AQUI_123!@#';
$hash = password_hash($nueva_contraseña, PASSWORD_DEFAULT);

echo "Contraseña: " . $nueva_contraseña . "\n";
echo "Hash: " . $hash . "\n";
echo "\n";
echo "Copia el hash y úsalo en el siguiente paso SQL.";
echo "\n\n";
echo "⚠️ ELIMINA ESTE ARCHIVO INMEDIATAMENTE DESPUÉS DE COPIAR EL HASH";
?>
```

**Ejecutar**:
```
1. Abrir navegador → http://localhost/GQ-Turismo/generar_nuevo_hash.php
2. COPIAR el hash generado
3. ELIMINAR el archivo generar_nuevo_hash.php INMEDIATAMENTE
```

### 3.2 Actualizar Contraseña en Base de Datos

**Desde phpMyAdmin**:
```sql
-- Reemplaza $2y$10$TU_HASH_COPIADO_AQUI con el hash que generaste

UPDATE usuarios 
SET contrasena = '$2y$10$TU_HASH_COPIADO_AQUI' 
WHERE email = 'etebachalegroup@gmail.com';

-- Verificar el cambio
SELECT email, contrasena, tipo_usuario 
FROM usuarios 
WHERE email = 'etebachalegroup@gmail.com';
```

### 3.3 Eliminar Usuarios de Prueba (Opcional pero Recomendado)

```sql
-- ADVERTENCIA: Esto eliminará datos relacionados en cascada

DELETE FROM usuarios WHERE email IN (
    'admin@gqturismo.com',
    'agencia@example.com',
    'guia@example.com',
    'guia2@example.com',
    'local@example.com'
);
```

---

## 🛡️ PASO 4: VERIFICAR PROTECCIÓN DE ARCHIVOS

### 4.1 Verificar .htaccess en database/

**Archivo**: `C:\xampp\htdocs\GQ-Turismo\database\.htaccess`

✅ Ya existe - No requiere acción

### 4.2 Verificar .htaccess en raíz (Opcional)

**Archivo**: `C:\xampp\htdocs\GQ-Turismo\.htaccess`

✅ Ya existe - No requiere acción

### 4.3 Probar Protección

**Desde navegador**:
```
http://localhost/GQ-Turismo/database/correciones_criticas.sql
-- Debe mostrar: Error 403 Forbidden

http://localhost/GQ-Turismo/generar_hash.php
-- Debe mostrar: Error 404 Not Found (si fue eliminado correctamente)
```

---

## ✅ PASO 5: PRUEBAS FUNCIONALES

### 5.1 Probar Página de Pago

```
1. Iniciar sesión como turista
2. Ir a: Mis Pedidos
3. Seleccionar un pedido en estado 'confirmado'
4. Click en 'Pagar'
5. Verificar que el pago se procesa correctamente
6. Estado debe cambiar a 'pagado'
```

### 5.2 Probar Admin - Reservas y Pedidos

```
1. Iniciar sesión como super_admin
2. Ir a: Panel Admin → Reservas/Pedidos
3. Verificar que se muestran tabs: "Reservas de Itinerarios" y "Pedidos de Servicios"
4. Click en tab "Pedidos de Servicios"
5. Verificar que se muestran todos los pedidos con nombre de servicio
6. Verificar que no hay errores de SQL
```

### 5.3 Probar Diseño Responsivo

```
Desktop (1200px+):
- Navegación completa visible
- Sidebar admin fijo
- Grids de 3-4 columnas

Tablet (768-1199px):
- Navegación colapsable
- Sidebar admin fijo
- Grids de 2 columnas

Móvil (<768px):
- Menú hamburguesa
- Sidebar admin con toggle
- Grids de 1 columna
- Botones full-width
```

---

## 📊 CHECKLIST FINAL

### Base de Datos:
- [ ] ✅ Script correciones_criticas.sql ejecutado
- [ ] ✅ Columna nombre_servicio existe en pedidos_servicios
- [ ] ✅ ENUM estado incluye 'pagado'
- [ ] ✅ Contraseña super admin cambiada
- [ ] ✅ Usuarios de prueba eliminados (opcional)

### Seguridad:
- [ ] ✅ generar_hash.php eliminado
- [ ] ✅ database/add_admin.php eliminado
- [ ] ✅ database/add_super_admin.php eliminado
- [ ] ✅ database/update_db.php eliminado
- [ ] ✅ eliminar_bypass.bat eliminado
- [ ] ✅ mover_informe.bat eliminado
- [ ] ✅ database/.htaccess verificado
- [ ] ✅ .htaccess raíz verificado

### Funcionalidad:
- [ ] ✅ pagar.php funciona sin errores
- [ ] ✅ admin/reservas.php funciona sin errores
- [ ] ✅ Tabs en reservas cambian correctamente
- [ ] ✅ Nombres de servicios se muestran correctamente
- [ ] ✅ Estados de pedidos se actualizan

### UX/UI:
- [ ] ✅ Responsive en móvil probado
- [ ] ✅ Responsive en tablet probado
- [ ] ✅ Menú móvil funciona
- [ ] ✅ Sidebar admin funciona
- [ ] ✅ Todas las páginas tienen diseño consistente

---

## 🎯 SIGUIENTE PASO (PRODUCCIÓN)

### Antes de Deployment en Servidor Real:

1. **Cambiar Configuración de Base de Datos**:
   ```php
   // includes/db_connect.php
   $servername = "localhost";
   $username = "usuario_produccion"; // NO usar 'root'
   $password = "contraseña_segura_produccion";
   $dbname = "gq_turismo";
   ```

2. **Configurar MySQL Seguro**:
   ```sql
   -- Crear usuario con permisos limitados
   CREATE USER 'gq_turismo_user'@'localhost' IDENTIFIED BY 'contraseña_segura';
   GRANT SELECT, INSERT, UPDATE, DELETE ON gq_turismo.* TO 'gq_turismo_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

3. **Habilitar HTTPS**:
   - Obtener certificado SSL (Let's Encrypt gratuito)
   - Configurar Apache para redirigir HTTP → HTTPS
   - Actualizar .htaccess

4. **Configurar Variables de Entorno**:
   - Mover credenciales a archivo .env
   - No subir .env a Git
   - Actualizar .gitignore

5. **Backups Automáticos**:
   - Configurar backup diario de base de datos
   - Configurar backup de archivos subidos
   - Almacenar backups en ubicación segura

---

## 📞 SOPORTE

### Documentación Disponible:

- **Resumen Ejecutivo**: `RESUMEN_EJECUTIVO_FINAL.md`
- **Análisis del Sistema**: `ANALISIS_GENERAL.md`
- **Seguridad Crítica**: `informe/SEGURIDAD_CRITICA.md`
- **Correcciones Técnicas**: `informe/CORRECCIONES_COMPLETADAS.md`

### Archivos SQL Importantes:

- **Correcciones**: `database/correciones_criticas.sql`
- **Seguridad Post**: `database/seguridad_post_correciones.sql`
- **BD Completa**: `database/gq_turismo_completo.sql` o `este.sql`

---

## ⚠️ ADVERTENCIAS FINALES

1. **NUNCA** subir archivos de bypass a Git o servidor de producción
2. **SIEMPRE** cambiar contraseñas antes de deployment
3. **VERIFICAR** que .htaccess esté activo en Apache
4. **PROBAR** todo en entorno local antes de producción
5. **BACKUP** de base de datos antes de cualquier cambio mayor

---

## 🎉 ¡LISTO!

Si completaste todos los pasos, tu sistema GQ-Turismo está:
- ✅ Funcionalmente completo
- ✅ Visualmente moderno y responsivo
- ✅ Razonablemente seguro para desarrollo/testing
- ✅ Listo para pruebas finales

**Próximo paso**: Testing exhaustivo con usuarios reales

---

**Fecha de Implementación**: 23 de Octubre de 2025  
**Versión**: 1.0  
**Estado**: ✅ INSTRUCCIONES COMPLETADAS
