# 📚 GUÍA DE USO - GQ TURISMO

## 🚀 INICIO RÁPIDO

### 1. Verificar que el sistema funciona

1. Abrir navegador y visitar: `http://localhost/GQ-Turismo/test_system.php`
2. Verificar que todos los checks estén en verde ✅
3. Si hay errores, revisar el archivo `SISTEMA_ARREGLADO_COMPLETO.md`

### 2. Iniciar sesión

**Como Turista:**
- Usuario: turista@example.com
- Contraseña: (tu contraseña configurada)

**Como Proveedor (Agencia/Guía/Local):**
- Usuario: (email del proveedor)
- Contraseña: (contraseña configurada)

**Como Super Admin:**
- Usuario: admin@gqturismo.com
- Contraseña: (contraseña admin)

---

## 👥 PARA TURISTAS

### 📍 Explorar Destinos

1. **Ver todos los destinos:**
   - Click en "Destinos" en el menú
   - Filtrar por categoría
   - Ver detalles de cualquier destino

2. **Ver detalle de un destino:**
   - Click en "Ver Detalles"
   - Ver galería de imágenes
   - Ver guías recomendados
   - Ver locales cercanos
   - Agregar a itinerario o reservar

### 🗺️ Crear Itinerario

1. **Ir a "Mi Itinerario"**
2. **Click en "Crear Nuevo Itinerario"**
3. **Paso 1 - Información Básica:**
   - Nombre del itinerario
   - Fechas (opcional)
   - Presupuesto (opcional)
   - Ciudad
   - Notas

4. **Paso 2 - Seleccionar Destinos:**
   - Click en las cards de destinos
   - Se numeran automáticamente (1, 2, 3...)
   - Filtrar por categoría
   - Ver preview con precio total

5. **Paso 3 - Servicios (Opcional):**
   - Seleccionar guías turísticos
   - Seleccionar agencias de viaje
   - Seleccionar locales/restaurantes

6. **Paso 4 - Confirmar:**
   - Revisar toda la información
   - Ver resumen de costos
   - Click en "Guardar"

### ✏️ Editar Itinerario

1. Ir a "Mi Itinerario"
2. Click en "Editar" en cualquier itinerario
3. Modificar lo que necesites
4. Guardar cambios

### 🗑️ Eliminar Itinerario

1. Ir a "Mi Itinerario"
2. Click en el icono de papelera
3. Confirmar eliminación

### 💬 Enviar Mensajes

1. **Ir a "Mensajes"**
2. **Dos formas de iniciar conversación:**

   **Opción A - Desde un destino:**
   - Ver detalle de destino
   - Click en guía/agencia/local recomendado
   - Click en "Enviar Mensaje"

   **Opción B - Desde la página de mensajes:**
   - Si ya enviaste un mensaje antes, aparecerá en la lista
   - Click en la conversación
   - Escribir mensaje

3. **Chat en tiempo real:**
   - Escribe tu mensaje
   - Click en "Enviar" o presiona Enter
   - Los mensajes se actualizan automáticamente cada 5 segundos
   - Los mensajes no leídos tienen un badge rojo

### 📅 Hacer Reservas

1. Ir a detalle del destino/guía/local
2. Click en "Reservar Ahora"
3. Llenar formulario de reserva
4. Confirmar

### 📦 Ver Mis Pedidos

1. Click en "Mis Pedidos"
2. Ver historial de reservas
3. Ver estado de cada pedido

---

## 🏢 PARA PROVEEDORES (Agencias, Guías, Locales)

### 📊 Dashboard

1. Iniciar sesión como proveedor
2. Click en "Dashboard"
3. Ver panel de control:
   - Estadísticas
   - Reservas pendientes
   - Mensajes nuevos

### 💬 Responder Mensajes de Turistas

1. **Ir a "Mensajes"**
2. **Ver lista de conversaciones:**
   - Turistas que te han escrito aparecen en la lista
   - Badge rojo indica mensajes no leídos

3. **Responder:**
   - Click en la conversación
   - Escribir respuesta
   - Enviar

4. **Actualización automática:**
   - Los mensajes nuevos aparecen automáticamente
   - No necesitas refrescar la página

### 📝 Gestionar Servicios

**Para Agencias:**
1. Dashboard → Gestionar Agencia
2. Editar información
3. Actualizar servicios ofrecidos

**Para Guías:**
1. Dashboard → Gestionar Perfil
2. Actualizar especialidades
3. Cambiar tarifa por hora

**Para Locales:**
1. Dashboard → Gestionar Local
2. Actualizar menú/servicios
3. Cambiar horarios

### 📋 Ver Reservas

1. Dashboard → Mis Reservas
2. Ver todas las reservas
3. Filtrar por estado:
   - Pendientes
   - Confirmadas
   - Completadas
   - Canceladas

4. Actualizar estado de reservas

---

## 👨‍💼 PARA SUPER ADMIN

### 🗺️ Gestionar Destinos

1. **Dashboard → Gestionar Destinos**

2. **Crear nuevo destino:**
   - Click en "Nuevo Destino"
   - Llenar formulario:
     - Nombre *
     - Descripción *
     - Categoría *
     - Precio *
     - Ciudad *
     - Imagen principal *
     - Coordenadas (opcional)
   - Click en "Guardar"

3. **Editar destino:**
   - Click en "Editar" en cualquier destino
   - Modificar información
   - Cambiar imagen (opcional)
   - Guardar

4. **Eliminar destino:**
   - Click en "Eliminar"
   - Confirmar
   - Se eliminan también:
     - Imágenes físicas del servidor
     - Imágenes de galería
     - Referencias en itinerarios

5. **Agregar imágenes a la galería:**
   - Editar destino
   - Sección "Galería de Imágenes"
   - Subir imagen
   - Agregar descripción
   - Guardar

### 👥 Gestionar Proveedores

1. **Dashboard → Gestionar [Agencias/Guías/Locales]**
2. Aprobar/rechazar nuevos proveedores
3. Editar información de proveedores
4. Desactivar/activar cuentas

### 📊 Ver Estadísticas

1. Dashboard → Estadísticas
2. Ver:
   - Total de usuarios
   - Reservas por mes
   - Destinos más populares
   - Ingresos

---

## 🔧 SOLUCIÓN DE PROBLEMAS

### Error: "session already active"
✅ **Solucionado** - El header.php ya maneja esto correctamente

### Error: "Unknown column presupuesto_estimado"
✅ **Solucionado** - Ejecutar `fix_complete_system.sql`

### Destinos duplicados
✅ **Solucionado** - La consulta SQL ya evita duplicados

### No puedo eliminar itinerario
**Verificar:**
1. Tabla `itinerario_guias` existe
2. Ejecutar: `fix_complete_system.sql`
3. Verificar con `test_system.php`

### Mensajes no se envían
**Verificar:**
1. Tabla `mensajes` existe
2. Usuario tiene sesión activa
3. Destinatario existe en la BD

### No puedo seleccionar locales en crear itinerario
✅ **Solucionado** - JavaScript corregido para manejar "locales"

---

## 📱 FUNCIONALIDADES MÓVILES

### Bottom Navigation
- Visible solo en móviles
- Acceso rápido a:
  - Inicio
  - Explorar
  - Pedidos
  - Mensajes
  - Perfil

### Responsive
- Todas las páginas se adaptan a móviles
- Cards se reorganizan en columnas
- Wizard de itinerarios adaptado
- Chat optimizado para móviles

---

## 🔐 SEGURIDAD

### Sesiones
- Todas las páginas verifican sesión activa
- Timeout después de inactividad
- Datos sensibles encriptados

### Permisos
- Turistas: Solo ven funciones de turista
- Proveedores: Solo gestionan sus servicios
- Admin: Acceso completo

### SQL Injection
- Todas las consultas usan prepared statements
- Validación de inputs
- Sanitización de datos

---

## 🎯 MEJORES PRÁCTICAS

### Para Turistas
1. Completar perfil antes de crear itinerarios
2. Verificar fechas antes de reservar
3. Comunicarse con proveedores mediante chat
4. Revisar bien el resumen antes de confirmar

### Para Proveedores
1. Mantener información actualizada
2. Responder mensajes rápidamente
3. Actualizar disponibilidad
4. Confirmar reservas a tiempo

### Para Administradores
1. Revisar destinos regularmente
2. Moderar contenido
3. Verificar calidad de imágenes
4. Mantener respaldo de BD

---

## 📞 AYUDA ADICIONAL

### Archivos de Documentación
- `SISTEMA_ARREGLADO_COMPLETO.md` - Resumen técnico de arreglos
- `README.md` - Información general del proyecto

### Verificación del Sistema
- Ejecutar: `http://localhost/GQ-Turismo/test_system.php`

### Logs de Error
- Apache: `C:\xampp\apache\logs\error.log`
- MySQL: `C:\xampp\mysql\data\*.err`
- PHP: Verificar `php.ini` para ubicación

---

## ✅ CHECKLIST DE FUNCIONALIDADES

### Sistema de Autenticación
- [x] Login de usuarios
- [x] Registro de turistas
- [x] Registro de proveedores
- [x] Recuperación de contraseña
- [x] Gestión de sesiones

### Para Turistas
- [x] Ver destinos
- [x] Ver detalle de destino
- [x] Crear itinerario (4 pasos)
- [x] Editar itinerario
- [x] Eliminar itinerario
- [x] Ver mis itinerarios
- [x] Enviar mensajes a proveedores
- [x] Recibir respuestas
- [x] Hacer reservas
- [x] Ver mis pedidos

### Para Proveedores
- [x] Dashboard personalizado
- [x] Gestionar servicios
- [x] Recibir mensajes
- [x] Responder mensajes
- [x] Ver reservas
- [x] Actualizar estado de reservas

### Para Administradores
- [x] Gestionar destinos
- [x] Crear/editar/eliminar destinos
- [x] Subir imágenes de galería
- [x] Gestionar proveedores
- [x] Ver estadísticas

### Base de Datos
- [x] Todas las tablas creadas
- [x] Relaciones correctas
- [x] Índices optimizados
- [x] Sin duplicados
- [x] Integridad referencial

### APIs
- [x] API de itinerarios
- [x] API de mensajes
- [x] API de conversaciones
- [x] API de destinos
- [x] API de reservas

---

**¡El sistema está 100% funcional y listo para usar!** 🎉
