# ⚠️ ALERTA DE SEGURIDAD CRÍTICA
## Fecha: 23 de Octubre de 2025

---

## 🚨 ARCHIVOS PELIGROSOS DETECTADOS - ACCIÓN INMEDIATA REQUERIDA

Se han detectado múltiples archivos que representan **VULNERABILIDADES CRÍTICAS DE SEGURIDAD** en el sistema. Estos archivos deben ser eliminados INMEDIATAMENTE antes de poner el sitio en producción.

---

## 🔴 ARCHIVOS A ELIMINAR URGENTEMENTE

### **1. generar_hash.php** (RAÍZ DEL PROYECTO)
**Ubicación**: `C:\xampp\htdocs\GQ-Turismo\generar_hash.php`

**Peligro**: 
- ⚠️ Expone contraseñas en texto plano
- ⚠️ Muestra el hash de la contraseña del super admin
- ⚠️ Puede ser usado para generar bypass de autenticación
- ⚠️ No tiene control de acceso

**Contenido Expuesto**:
```php
$password_plana = 'mX7#Aq!D9v^H5tPz@w3*LuG2s$RkJ8yBn%fC1eQxZo6T!MhKjVr4pW0Nd^Ub';
```

**Impacto**: CRÍTICO - Cualquiera puede acceder y ver la contraseña del super admin

---

### **2. database/add_admin.php**
**Ubicación**: `C:\xampp\htdocs\GQ-Turismo\database\add_admin.php`

**Peligro**:
- ⚠️ Crea cuentas de administrador sin autenticación
- ⚠️ Accesible desde navegador web
- ⚠️ Expone contraseñas en texto plano en pantalla
- ⚠️ No requiere permisos para ejecutar

**Contenido Expuesto**:
```php
$nombre = 'admin';
$email = 'admin@gqturismo.com';
$contrasena = 'admin123'; // ← EXPUESTO
```

**Impacto**: CRÍTICO - Cualquiera puede crear cuentas de administrador

---

### **3. database/add_super_admin.php**
**Ubicación**: `C:\xampp\htdocs\GQ-Turismo\database\add_super_admin.php`

**Peligro**:
- ⚠️ Crea cuentas de SUPER ADMINISTRADOR sin autenticación
- ⚠️ Accesible desde navegador web sin restricciones
- ⚠️ Expone credenciales del propietario del sistema
- ⚠️ No tiene validación ni control de acceso

**Contenido Expuesto**:
```php
$name = 'Eteba Chale Group';
$email = 'etebachalegroup@gmail.com';
$password = 'mX7#Aq!D9v^H5tPz@w3*LuG2s$RkJ8yBn%fC1eQxZo6T!MhKjVr4pW0Nd^Ub'; // ← EXPUESTO
```

**Impacto**: CATASTRÓFICO - Control total del sistema comprometido

---

### **4. database/update_db.php** (EVALUAR ELIMINACIÓN)
**Ubicación**: `C:\xampp\htdocs\GQ-Turismo\database\update_db.php`

**Peligro**:
- ⚠️ Expone estructura completa de la base de datos
- ⚠️ Puede crear/modificar tablas sin autenticación
- ⚠️ Inserta datos de prueba con contraseñas débiles
- ⚠️ Accesible desde navegador

**Contenido Sensible**:
```php
$password_admin = password_hash('admin', PASSWORD_DEFAULT); // Contraseña: admin
$password_agencia = password_hash('password', PASSWORD_DEFAULT);
$password_guia = password_hash('password', PASSWORD_DEFAULT);
$password_local = password_hash('password', PASSWORD_DEFAULT);
```

**Impacto**: ALTO - Permite modificación no autorizada de la BD

---

## 📋 INSTRUCCIONES DE ELIMINACIÓN

### **Método 1: Eliminación Manual**
1. Navegar a `C:\xampp\htdocs\GQ-Turismo\`
2. Eliminar `generar_hash.php`
3. Navegar a `C:\xampp\htdocs\GQ-Turismo\database\`
4. Eliminar `add_admin.php`
5. Eliminar `add_super_admin.php`
6. **Evaluar** eliminar `update_db.php` (si ya se ejecutó una vez, eliminar)

### **Método 2: Usando PowerShell/CMD**
```powershell
# Ejecutar en CMD como Administrador
cd C:\xampp\htdocs\GQ-Turismo
del /F /Q generar_hash.php
cd database
del /F /Q add_admin.php
del /F /Q add_super_admin.php
del /F /Q update_db.php
echo Archivos de bypass eliminados
```

### **Método 3: Usando explorador de archivos**
1. Abrir explorador de Windows
2. Navegar a `C:\xampp\htdocs\GQ-Turismo\`
3. Seleccionar `generar_hash.php` → Shift+Delete (eliminación permanente)
4. Navegar a `database\`
5. Seleccionar `add_admin.php` → Shift+Delete
6. Seleccionar `add_super_admin.php` → Shift+Delete
7. Seleccionar `update_db.php` → Shift+Delete

---

## 🔐 ACCIONES DE SEGURIDAD ADICIONALES

### **1. Cambiar Contraseñas Comprometidas**

La contraseña del super admin ha sido expuesta en código fuente:
```
Email: etebachalegroup@gmail.com
Contraseña: mX7#Aq!D9v^H5tPz@w3*LuG2s$RkJ8yBn%fC1eQxZo6T!MhKjVr4pW0Nd^Ub
```

**ACCIÓN REQUERIDA**: Cambiar esta contraseña INMEDIATAMENTE en la base de datos.

#### Script para cambiar contraseña del super admin:
```sql
-- Ejecutar en phpMyAdmin
UPDATE usuarios 
SET contrasena = PASSWORD_HASH('NUEVA_CONTRASEÑA_SEGURA_AQUÍ', PASSWORD_DEFAULT) 
WHERE email = 'etebachalegroup@gmail.com';
```

O crear un archivo temporal `cambiar_password.php`:
```php
<?php
require_once 'includes/db_connect.php';

$email = 'etebachalegroup@gmail.com';
$nueva_contraseña = 'TU_NUEVA_CONTRASEÑA_MUY_SEGURA_AQUÍ';
$hash = password_hash($nueva_contraseña, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE usuarios SET contrasena = ? WHERE email = ?");
$stmt->bind_param("ss", $hash, $email);

if ($stmt->execute()) {
    echo "Contraseña actualizada exitosamente. ELIMINA ESTE ARCHIVO AHORA.";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
```

**IMPORTANTE**: Eliminar `cambiar_password.php` inmediatamente después de usarlo.

---

### **2. Eliminar Usuarios de Prueba**

Los siguientes usuarios tienen contraseñas débiles:

| Email | Contraseña | Tipo |
|-------|------------|------|
| admin@gqturismo.com | admin | super_admin |
| agencia@example.com | password | agencia |
| guia@example.com | password | guia |
| guia2@example.com | password | guia |
| local@example.com | password | local |

**Script SQL para eliminar usuarios de prueba**:
```sql
-- Ejecutar en phpMyAdmin
DELETE FROM usuarios WHERE email IN (
    'admin@gqturismo.com',
    'agencia@example.com',
    'guia@example.com',
    'guia2@example.com',
    'local@example.com'
);
```

**NOTA**: Esto eliminará en cascada todas las relaciones (agencias, guías, locales, pedidos, etc.)

---

### **3. Proteger Carpeta Database**

Añadir `.htaccess` en la carpeta `database/` para bloquear acceso web:

**Ubicación**: `C:\xampp\htdocs\GQ-Turismo\database\.htaccess`

```apache
# Denegar acceso a archivos PHP en esta carpeta
<Files "*.php">
    Order Allow,Deny
    Deny from all
</Files>

# Denegar acceso a archivos SQL
<Files "*.sql">
    Order Allow,Deny
    Deny from all
</Files>

# Denegar listado de directorio
Options -Indexes
```

---

### **4. Proteger includes/db_connect.php**

Asegurarse de que las credenciales de BD no estén expuestas:

**Verificar**: `C:\xampp\htdocs\GQ-Turismo\includes\db_connect.php`

```php
<?php
$servername = "localhost";
$username = "root"; // ⚠️ Cambiar en producción
$password = "";     // ⚠️ Establecer contraseña fuerte
$dbname = "gq_turismo";

// Prevenir acceso directo al archivo
if (!defined('ACCESS_ALLOWED')) {
    die('Acceso directo no permitido');
}

// Resto del código...
?>
```

Luego en cada archivo que use db_connect.php:
```php
<?php
define('ACCESS_ALLOWED', true);
require_once 'includes/db_connect.php';
?>
```

---

### **5. Configuración de Seguridad Apache**

Añadir `.htaccess` en la raíz del proyecto:

**Ubicación**: `C:\xampp\htdocs\GQ-Turismo\.htaccess`

```apache
# Protección básica
Options -Indexes
ServerSignature Off

# Proteger archivos sensibles
<FilesMatch "\.(md|sql|log|git)$">
    Order Allow,Deny
    Deny from all
</FilesMatch>

# Proteger archivo de configuración
<Files "db_connect.php">
    Order Allow,Deny
    Deny from all
</Files>

# Headers de seguridad
<IfModule mod_headers.c>
    Header set X-XSS-Protection "1; mode=block"
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set Referrer-Policy "no-referrer-when-downgrade"
</IfModule>

# Prevenir hotlinking de imágenes
RewriteEngine on
RewriteCond %{HTTP_REFERER} !^$
RewriteCond %{HTTP_REFERER} !^http(s)?://(www\.)?gqturismo\.com [NC]
RewriteRule \.(jpg|jpeg|png|gif)$ - [NC,F,L]
```

---

### **6. Validación de Sesiones**

Añadir validación en todas las páginas protegidas:

```php
<?php
session_start();

// Validar sesión
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Validar timeout de sesión (30 minutos)
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: index.php?timeout=1");
    exit();
}
$_SESSION['LAST_ACTIVITY'] = time();

// Regenerar ID de sesión periódicamente
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} else if (time() - $_SESSION['CREATED'] > 1800) {
    session_regenerate_id(true);
    $_SESSION['CREATED'] = time();
}
?>
```

---

## 📊 NIVEL DE RIESGO

| Vulnerabilidad | Nivel | Impacto | Urgencia |
|----------------|-------|---------|----------|
| generar_hash.php | 🔴 CRÍTICO | Control total | INMEDIATA |
| add_admin.php | 🔴 CRÍTICO | Creación de admins | INMEDIATA |
| add_super_admin.php | 🔴 CATASTRÓFICO | Control total | INMEDIATA |
| update_db.php | 🟠 ALTO | Modificación BD | ALTA |
| Contraseñas débiles | 🟠 ALTO | Acceso no autorizado | ALTA |
| Sin .htaccess | 🟡 MEDIO | Exposición de archivos | MEDIA |

---

## ✅ CHECKLIST DE SEGURIDAD

- [ ] **INMEDIATO**: Eliminar `generar_hash.php`
- [ ] **INMEDIATO**: Eliminar `database/add_admin.php`
- [ ] **INMEDIATO**: Eliminar `database/add_super_admin.php`
- [ ] **INMEDIATO**: Cambiar contraseña del super admin
- [ ] **URGENTE**: Eliminar `database/update_db.php` (si ya se ejecutó)
- [ ] **URGENTE**: Eliminar usuarios de prueba
- [ ] **IMPORTANTE**: Crear `.htaccess` en `/database/`
- [ ] **IMPORTANTE**: Crear `.htaccess` en raíz
- [ ] **IMPORTANTE**: Proteger `db_connect.php`
- [ ] **IMPORTANTE**: Implementar validación de sesiones
- [ ] **RECOMENDADO**: Cambiar contraseña de MySQL root
- [ ] **RECOMENDADO**: Habilitar HTTPS
- [ ] **RECOMENDADO**: Configurar firewall
- [ ] **RECOMENDADO**: Logs de auditoría

---

## 🔍 VERIFICACIÓN POST-ELIMINACIÓN

Ejecutar las siguientes verificaciones:

### **1. Verificar archivos eliminados**
```cmd
dir C:\xampp\htdocs\GQ-Turismo\generar_hash.php
dir C:\xampp\htdocs\GQ-Turismo\database\add_admin.php
dir C:\xampp\htdocs\GQ-Turismo\database\add_super_admin.php
```

Debe mostrar "El sistema no puede encontrar el archivo especificado"

### **2. Intentar acceso desde navegador**
- http://localhost/GQ-Turismo/generar_hash.php → Debe dar 404
- http://localhost/GQ-Turismo/database/add_admin.php → Debe dar 403 o 404
- http://localhost/GQ-Turismo/database/add_super_admin.php → Debe dar 403 o 404

### **3. Verificar contraseña cambiada**
```sql
SELECT email, contrasena FROM usuarios WHERE email = 'etebachalegroup@gmail.com';
```

El hash debe ser diferente al original.

---

## 📝 RECOMENDACIONES ADICIONALES

### **Para Desarrollo Local**:
1. Usar entorno de desarrollo separado
2. Nunca usar contraseñas de producción en desarrollo
3. Mantener `.git` actualizado con `.gitignore` adecuado

### **Para Producción**:
1. Cambiar TODAS las contraseñas
2. Usuario de BD diferente a `root` con permisos limitados
3. Habilitar SSL/TLS (HTTPS)
4. Configurar firewall
5. Implementar rate limiting
6. Logs de auditoría
7. Backups automáticos cifrados
8. Monitoreo de seguridad

### **Archivos que NO deben estar en Git**:
```gitignore
# Credenciales y configuración
includes/db_connect.php
.env

# Archivos de bypass
generar_hash.php
database/add_*.php
database/update_db.php

# Logs y temporales
*.log
*.tmp

# Backups
*.sql
*.backup
```

---

## 🚨 RESUMEN EJECUTIVO

**ESTADO ACTUAL**: 🔴 SISTEMA COMPROMETIDO

**ARCHIVOS PELIGROSOS DETECTADOS**: 4
- 3 archivos de bypass activos
- 1 archivo de configuración expuesto

**CONTRASEÑAS EXPUESTAS**: 
- 1 super admin
- 5 usuarios de prueba

**ACCIONES CRÍTICAS**:
1. Eliminar 3 archivos inmediatamente
2. Cambiar contraseña del super admin
3. Implementar protecciones .htaccess

**TIEMPO ESTIMADO DE REMEDIACIÓN**: 15-30 minutos

**PRIORIDAD**: 🔴 MÁXIMA - NO PONER EN PRODUCCIÓN SIN REMEDIAR

---

**Fecha de Detección**: 23 de Octubre de 2025  
**Analista de Seguridad**: GitHub Copilot CLI  
**Estado**: ⚠️ VULNERABILIDADES CRÍTICAS DETECTADAS - ACCIÓN REQUERIDA
