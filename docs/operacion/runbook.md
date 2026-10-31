# RUNBOOK DE OPERACIÓN Y MANTENIMIENTO LOCAL — GQ-TURISMO

**Entorno:** Local (XAMPP - Apache/MySQL)  
**Versión:** 2.0 Modernizada  
**Fecha:** 31 de Octubre de 2026  

---

## 1. Requisitos y Servicios
- **Servidor Web:** Apache 2.4 en XAMPP (puerto 80 / 443).
- **Base de Datos:** MySQL 8 / MariaDB en XAMPP (puerto 3306).
- **Intérprete PHP:** PHP 8.2+ con extensiones `mysqli`, `pdo_mysql`, `fileinfo`, `gd`, `openssl`, `mbstring`.
- **Gestor de Paquetes:** Composer (vía `php composer.phar`).

---

## 2. Puesta en Marcha Local
1. Iniciar **Apache** y **MySQL** desde el panel de control de XAMPP.
2. Verificar que el archivo `.env` exista en la raíz con la configuración local:
   ```env
   DB_HOST=localhost
   DB_USER=root
   DB_PASS=
   DB_NAME=gq_turismo
   DB_PORT=3306
   APP_ENV=local
   APP_DEBUG=true
   ```
3. Generar / actualizar el autoloader de Composer si se añaden clases:
   ```bash
   php composer.phar dump-autoload
   ```
4. Abrir en el navegador:
   - Sitio público: `http://localhost/GQ-Turismo/`
   - Panel de administración: `http://localhost/GQ-Turismo/admin/login.php`

---

## 3. Ejecución de Pruebas Automatizadas
Para verificar la integridad del sistema, seguridad y conexión en cualquier momento:
```bash
php bin/test_runner.php
```
Todas las pruebas deben concluir con resultado `✅ PASÓ`.

---

## 4. Respaldos y Restauración de Base de Datos
- **Generar Backup:**
  ```bash
  C:\xampp\mysql\bin\mysqldump.exe -u root gq_turismo > database/backup_$(Get-Date -Format 'yyyyMMdd_HHmmss').sql
  ```
- **Restaurar Dataset de Pruebas:**
  ```bash
  C:\xampp\mysql\bin\mysql.exe -u root gq_turismo < database/dataset_prueba_anonimizado.sql
  ```

---

## 5. Cuentas de Acceso de Prueba (Dataset Anonimizado)
- **Contraseña Común:** `Password123!`
- **Super Administrador:** `admin14@gqturismo.local`
- **Turista:** `turista16@ejemplo.local`
- **Guía Turístico:** `guia17@ejemplo.local`
- **Agencia de Viajes:** `agencia18@ejemplo.local`
- **Local Gastronómico/Hotel:** `local19@ejemplo.local`
