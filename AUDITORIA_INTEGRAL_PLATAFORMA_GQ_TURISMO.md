# AUDITORÍA INTEGRAL DEL ESTADO DE LA PLATAFORMA GQ-TURISMO

**Fecha de Auditoría:** 31 de Octubre de 2026  
**Proyecto:** GQ-Turismo (Guía y Gestión Integral de Turismo)  
**Entorno Analizado:** `C:\xampp\htdocs\GQ-Turismo`  
**Servidor Local:** Apache/2.4 + PHP 8.2.12 + MySQL (XAMPP)  
**Repositorio Git:** `origin -> https://github.com/etebachale-group/GQ-Turismo.git` (Rama `main`)  
**Auditor:** Antigravity Engineering & Security Diagnostic  

---

## 1. RESUMEN EJECUTIVO

| Métrica / Dimensión | Calificación | Estado | Comentario Clave |
| :--- | :---: | :---: | :--- |
| **Seguridad de la Información** | **F (Crítico)** | 🔴 Alto Riesgo | Exposición de credenciales de producción, RCE por subida de archivos, elevación de privilegios inmediata y SQLi. |
| **Arquitectura de Base de Datos** | **D (Deficiente)** | 🟠 Inconsistente | Múltiples tablas duplicadas/redundantes y 46 scripts de fixes repetitivos sin sistema de migraciones. |
| **Control de Acceso y Sesiones** | **D (Deficiente)** | 🟠 Vulnerable | Falta de control estricto de roles en headers, CSRF en borrado y módulo de seguridad de sesiones huérfano. |
| **Lógica de Negocio y Pagos** | **D (Deficiente)** | 🟠 Inseguro | Precios manipulables 100% desde el cliente; pasarela de pago puramente simulada. |
| **Frontend y Experiencia Móvil** | **C- (Regular)** | 🟡 Caótico | 10 hojas de estilo CSS superpuestas para parches móviles compitiendo por especificidad. |
| **Mantenibilidad y Código** | **D+ (Bajo)** | 🟠 Deuda Técnica | Código procedimental monolítico, archivos de prueba en webroot y decenas de reportes redundantes en `informe/`. |
| **CALIFICACIÓN GLOBAL** | **D (2.2 / 10)** | ⚠️ **REQUIERE INTERVENCIÓN URGENTE ANTES DE PRODUCCIÓN** |

---

## 2. HALLAZGOS CRÍTICOS DE SEGURIDAD (Vulnerabilidades OWASP Top 10)

### 🔴 Hallazgo 1: Fuga de Credenciales y Base de Datos Completa en Git y Webroot (CVSS 9.8 - Crítico)
* **Ubicación:** `includes/db_connect.php` y `if0_39858318_gq_turismo.sql` (raíz).
* **Detalle:**
  - `includes/db_connect.php` tiene credenciales en texto plano para el servidor remoto:
    ```php
    $servername = "sql107.infinityfree.com";
    $username = "if0_39858318";
    $password = "r8HgmKsHZRU";
    $dbname = "if0_39858318_gq_turismo";
    ```
  - Ambos archivos están commiteados en el repositorio Git público/remoto (`https://github.com/etebachale-group/GQ-Turismo.git`).
  - El archivo `if0_39858318_gq_turismo.sql` (72.8 KB) está alojado en el webroot accesible públicamente. Contiene registros reales de usuarios con nombres, correos electrónicos y hashes de contraseñas de administradores.
* **Impacto:** Compromiso total de la base de datos de producción y exposición de información sensible de usuarios (PII).

### 🔴 Hallazgo 2: Elevación de Privilegios Inmediata a `super_admin` en Registro (CVSS 9.8 - Crítico)
* **Ubicación:** `api/auth.php` (Líneas 25 y 31-46).
* **Detalle:**
  El endpoint de registro público permite especificar libremente el `tipo_usuario` sin validación de privilegios:
  ```php
  $tipo_usuario = $_POST['tipo_usuario'] ?? 'turista';
  ...
  } elseif (!in_array($tipo_usuario, ['turista', 'agencia', 'guia', 'local', 'super_admin'])) {
  ...
  $insert_stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, contrasena, tipo_usuario) VALUES (?, ?, ?, ?)");
  ```
* **Impacto:** Cualquier atacante anónimo puede enviar un POST con `tipo_usuario=super_admin` y obtener acceso total al panel de administración del sistema sin requerir aprobación.

### 🔴 Hallazgo 3: Ejecución Remota de Código (RCE) por Carga Arbitraria de Archivos (CVSS 9.8 - Crítico)
* **Ubicación:** 
  - `admin/manage_destinos.php` (Líneas 32-40, 154)
  - `admin/manage_agencias.php` (Línea 82)
  - `admin/manage_guias.php` (Líneas 173, 262)
  - `admin/manage_locales.php` (Líneas 84, 161)
  - `admin/manage_publicidad_carousel.php` (Líneas 35, 102)
* **Detalle:**
  Los archivos se mueven a disco usando `move_uploaded_file()` con el nombre original sin validar extensiones permitidas (`.jpg`, `.png`, etc.), tipo MIME ni inspección con `getimagesize()`:
  ```php
  $main_image_name = uniqid() . "_" . basename($_FILES['imagen']['name']);
  $target_file = $target_dir . $main_image_name;
  move_uploaded_file($_FILES['imagen']['tmp_name'], $target_file);
  ```
  Además, no existe un archivo `.htaccess` en `assets/img/` que desactive el motor de ejecución de PHP (`php_flag engine off`).
* **Impacto:** Un usuario puede subir un archivo `payload.php` camuflado y ejecutar comandos directamente en el servidor anfitrión.

### 🔴 Hallazgo 4: Inyecciones SQL Directas (SQL Injection) (CVSS 8.5 - Alto)
* **Ubicación:**
  - `api/itinerarios.php` (Líneas 81-82, 180-181):
    ```php
    $conn->query("DELETE FROM itinerario_destinos WHERE id_itinerario = $itinerario_id");
    $conn->query("DELETE FROM itinerario_servicios WHERE id_itinerario = $itinerario_id");
    ```
  - `api/destinos.php` (Líneas 95-96):
    ```php
    $conn->query("DELETE FROM imagenes_destino WHERE id_destino = $destino_id");
    $conn->query("DELETE FROM itinerario_destinos WHERE id_destino = $destino_id");
    ```
  - `api/itinerario_tracking.php` (Línea 70, 72, 167):
    ```php
    $conn->query("UPDATE itinerarios SET estado_itinerario = 'completado' WHERE id = $id_itinerario");
    ```
* **Impacto:** Vulnerabilidad a inyección SQL de segundo orden y manipulación de parámetros POST no casteados a enteros, permitiendo vaciar tablas completas de itinerarios o destinos.

### 🔴 Hallazgo 5: Alteración de Precios desde el Cliente (Price Tampering) (CVSS 8.1 - Alto)
* **Ubicación:** `api/pedidos.php` (Líneas 37-38, 75-76).
* **Detalle:**
  El precio unitario y el precio total son enviados directamente desde el payload JSON del cliente y guardados sin verificación cruzada contra la base de datos:
  ```php
  $precio_unitario = $input['precio_unitario'] ?? 0.00;
  $precio_total = $input['precio_total'] ?? 0.00;
  ```
* **Impacto:** Un cliente puede modificar la petición en la consola del navegador y comprar cualquier tour, reserva de guía o servicio pagando $0.01.

### 🟠 Hallazgo 6: Eliminación de Usuarios vía GET sin Token CSRF (CVSS 6.5 - Medio)
* **Ubicación:** `admin/manage_users.php` (Líneas 38-49).
* **Detalle:**
  La acción de eliminación se dispara mediante un enlace HTTP GET:
  `manage_users.php?action=delete&id=X` sin validación de token CSRF ni confirmación de contraseña.
* **Impacto:** Un atacante puede engañar a un administrador autenticado para que visite una URL o cargue una etiqueta `<img>` maliciosa, eliminando cuentas de usuario silenciosamente.

### 🟠 Hallazgo 7: Código de Seguridad de Sesiones Huérfano (Dead Code)
* **Ubicación:** `includes/session_security.php`
* **Detalle:**
  El archivo implementa utilidades como `require_login()`, control de inactividad de sesión y timeout, pero **no es incluido por ningún archivo del proyecto**. Cada archivo PHP reimplementa de forma parcial y dispersa la verificación de `$_SESSION['user_id']`.

---

## 3. AUDITORÍA DE BASE DE DATOS Y MODELADO DE DATOS

### 3.1 Proliferación de Tablas Duplicadas y Redundantes
Se constató que la base de datos presenta tablas redundantes generadas en diferentes etapas del desarrollo sin consolidación:

| Entidad Funcional | Tablas Detectadas en el Esquema | Problema Detectado |
| :--- | :--- | :--- |
| **Locales y Negocios** | `locales_turisticos` y `lugares_locales` | Discrepancia de columnas (`nombre` vs `nombre_local`, `latitud` vs `latitude`). Unos scripts usan una y otros usan otra. |
| **Publicidad y Banners** | `carouseles`, `publicidades` y `publicidad_carousel` | Tres tablas para el mismo propósito funcional. |
| **Confirmaciones de Servicios** | `confirmacion_servicios` y `confirmaciones_servicios` | Ambas coexisten con diferentes charsets (`utf8mb4_unicode_ci` vs `utf8mb4_general_ci`). |
| **Guías y Destinos** | `guias_destinos`, `guia_destinos` y `guia_destinos_disponibles` | Tres tablas intermedias relacionales no sincronizadas. |
| **Pedidos y Reservas** | `pedidos` / `pedidos_servicios` y `reservas` / `reserva_servicios` | Duplicidad de flujo conceptual entre pedidos y reservas. |

### 3.2 El "Bug de Identidad" Proveedor vs Usuario (Causa raíz de fallos en el Chat)
* En `usuarios`, cada cuenta tiene un `id` único (ej. ID = 18 para la agencia).
* En `agencias`, la agencia tiene su propio `id = 4` y un campo `id_usuario = 18`.
* En `api/auth.php`, se almacena `$_SESSION['user_id'] = $user['id']` (el ID de usuario = 18).
* En `api/get_conversation.php` y `api/messages.php`, el código consulta:
  ```sql
  SELECT nombre_agencia FROM agencias WHERE id = m.sender_id
  ```
  donde `m.sender_id` contiene el ID de usuario (18), pero la agencia tiene ID = 4. Esto provocaba que las conversaciones aparecieran vacías o fallaran al enlazar mensajes.

### 3.3 Deuda Técnica en la Carpeta `database/`
* Existen **46 scripts SQL** con nombres como `fix_all_complete_system.sql`, `fix_all_critical_errors.sql`, `fix_complete_system.sql`, `fix_chat_system_complete.sql`.
* No existe un sistema de migraciones automatizado (tipo Phinx, Flyway o Laravel Migrations).
* Muchos scripts se solapan, sobreescriben o agregan índices redundantes repetidamente.

---

## 4. AUDITORÍA DE ARQUITECTURA DE SOFTWARE Y CÓDIGO

### 4.1 Estructura del Backend
- **Paradigma:** PHP procedimental clásico sin arquitectura en capas ni MVC.
- **Acoplamiento:** Consultas SQL, lógica de negocio y presentación HTML conviven en los mismos archivos (ej. `detalle_destino.php`, `crear_itinerario.php` de más de 500 líneas cada uno).
- **Control de Entornos:** No hay uso de variables de entorno (`.env`). La configuración de base de datos está estática en código.
- **Pasarela de Pagos:** `pagar.php` no integra pasarelas reales (ni Stripe, ni PayPal, ni Redsys ni MercadoPago); es una actualización manual simulada de base de datos.

### 4.2 Estructura del Frontend y Responsive
- **Sobrecarga de CSS Móvil:** Existen 10 archivos CSS enfocados en adaptabilidad móvil:
  1. `admin-mobile.css`
  2. `mobile-app.css`
  3. `mobile-enhancements.css`
  4. `mobile-fixes.css`
  5. `mobile-optimization.css`
  6. `mobile-responsive-admin.css`
  7. `mobile-responsive-final.css`
  8. `mobile-responsive.css`
  9. `mobile-sidebar.css`
  10. `responsive.css`
  Esta acumulación de archivos introduce reglas que se anulan entre sí, abuso de `!important` y dificultad para depurar la UI en dispositivos móviles reales.

### 4.3 Archivos Residuales y Contaminación del Workspace
- **Archivos de prueba en producción:** En la raíz existen `test_system.php`, `test_system_complete.php`, `verificar_sistema.php`, `verificar_sistema_itinerarios.php`, `verify_system.php`, los cuales exponen diagnósticos y esquemas de tablas a cualquiera que conozca la URL.
- **Carpeta `trash/`:** Conserva copias de scripts viejos y pruebas en el servidor web.
- **Carpeta `informe/`:** Contiene más de 30 documentos dispersos en subcarpetas (`guias/`, `md_files/`, `progreso/`, `reportes_md/`, `resumen/`, `seguridad/`) con reportes desactualizados o redundantes.

---

## 5. PLAN DE ACCIÓN Y ROADMAP DE REMEDIACIÓN

### Fase 1: Remediación Crítica de Seguridad (Inmediata - 24 a 48 horas)
1. **Revocación y Protección de Credenciales:**
   - Cambiar inmediatamente la contraseña de la base de datos en InfinityFree/hosting de producción.
   - Eliminar `if0_39858318_gq_turismo.sql` de la raíz del proyecto.
   - Configurar variables de entorno o archivo `.env` fuera del webroot y añadirlo a `.gitignore`.
2. **Corrección de Registro:**
   - En `api/auth.php`, forzar que el registro público asigne únicamente `tipo_usuario = 'turista'`. La asignación de roles de proveedor o superadmin debe requerir un panel administrativo.
3. **Asegurar Carga de Archivos:**
   - Implementar lista blanca estricta de extensiones (`['jpg', 'jpeg', 'png', 'webp']`).
   - Validar tipo MIME con `mime_content_type()` y comprobar encabezados con `getimagesize()`.
   - Renombrar archivos usando UUID v4 criptográfico.
   - Crear un archivo `.htaccess` en `assets/img/` con:
     ```apache
     <FilesMatch "\.php$">
         Order Deny,Allow
         Deny from all
     </FilesMatch>
     php_flag engine off
     ```
4. **Sanitización SQL:**
   - Convertir todas las llamadas `$conn->query("DELETE ... $var")` a prepared statements parametrizados.
5. **Corrección de Precios:**
   - En `api/pedidos.php`, calcular el precio total exclusivamente desde la base de datos consultando la tarifa registrada del servicio o guía, ignorando cualquier precio enviado en el cuerpo de la petición.

### Fase 2: Saneamiento y Estabilización (1 a 2 semanas)
1. **Unificación del Esquema de Base de Datos:**
   - Migrar datos y eliminar tablas duplicadas: consolidar en `lugares_locales`, `publicidad_carousel`, `confirmacion_servicios`, `guias_destinos` y `pedidos_servicios`.
   - Generar un único archivo `schema.sql` canónico y eliminar los 46 scripts de fixes dispersos.
2. **Corrección de Identificadores (Mapeo Usuario - Proveedor):**
   - Estandarizar si las sesiones almacenan tanto `user_id` como `provider_id`, o actualizar las consultas del chat y reservas para realizar los `JOIN` correctos mediante `id_usuario`.
3. **Limpieza del Workspace:**
   - Mover scripts de verificación y diagnóstico a una carpeta protegida o eliminarlos de la raíz.
   - Mover la carpeta `trash/` y consolidar la carpeta `informe/`.

### Fase 3: Modernización y Calidad (Medio Plazo - 3 a 4 semanas)
1. **Consolidación Frontend:**
   - Unificar los 10 archivos CSS móviles en una única hoja de estilos modular y depurada.
2. **Integración de Pasarela de Pago Real:**
   - Reemplazar la simulación de `pagar.php` con integración real mediante Webhooks (Stripe / PayPal / Mercado Pago).
3. **Refactorización de Sesiones:**
   - Activar y aplicar `includes/session_security.php` en todos los puntos de entrada, con expiración por inactividad y tokens anti-CSRF en todos los formularios POST.

---

*Documento generado como auditoría técnica completa del repositorio GQ-Turismo.*
