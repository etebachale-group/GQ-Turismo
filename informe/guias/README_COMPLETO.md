# 🌍 GQ-Turismo - Sistema de Gestión Turística
## Guinea Ecuatorial Tourism Management System

![Estado](https://img.shields.io/badge/Estado-Funcional-success)
![Versión](https://img.shields.io/badge/Versión-2.0-blue)
![PHP](https://img.shields.io/badge/PHP-8.0+-purple)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-orange)

---

## 📋 Tabla de Contenidos
- [Descripción](#descripción)
- [Características](#características)
- [Instalación](#instalación)
- [Configuración](#configuración)
- [Uso del Sistema](#uso-del-sistema)
- [Estructura del Proyecto](#estructura-del-proyecto)
- [Correcciones Recientes](#correcciones-recientes)
- [Solución de Problemas](#solución-de-problemas)

---

## 🎯 Descripción

GQ-Turismo es una plataforma completa de gestión turística diseñada específicamente para Guinea Ecuatorial. Conecta turistas con proveedores de servicios turísticos incluyendo:
- 🏢 Agencias de viaje
- 👤 Guías turísticos
- 🍽️ Restaurantes y locales
- 🗺️ Destinos y atracciones

---

## ✨ Características

### Para Turistas
- ✅ **Creación de Itinerarios Personalizados**
  - Sistema wizard de 4 pasos
  - Selección múltiple de destinos
  - Agregar servicios (guías, agencias, locales)
  - Cálculo automático de costos
  
- ✅ **Sistema de Reservas**
  - Reserva completa desde itinerarios
  - Notificaciones automáticas a proveedores
  - Seguimiento de estado de reserva
  
- ✅ **Chat en Tiempo Real**
  - Comunicación directa con proveedores
  - Historial de conversaciones
  - Notificaciones de mensajes nuevos

### Para Proveedores
- ✅ **Gestión de Servicios**
  - Panel de administración
  - Actualización en tiempo real
  - Visibilidad inmediata
  
- ✅ **Recepción de Reservas**
  - Notificaciones vía mensajes
  - Detalles completos del turista
  - Sistema de respuesta integrado

### Características Técnicas
- 🔐 Sistema de autenticación robusto
- 💾 Base de datos optimizada con índices
- 📱 Diseño responsive (móvil y escritorio)
- ⚡ Carga rápida y eficiente
- 🔄 Actualización en tiempo real

---

## 🚀 Instalación

### Requisitos Previos
- XAMPP (PHP 8.0+, MySQL 8.0+, Apache)
- Navegador web moderno
- Git (opcional)

### Pasos de Instalación

1. **Clonar o Descargar el Proyecto**
```bash
cd C:\xampp\htdocs
git clone https://github.com/tu-usuario/GQ-Turismo.git
# O descomprimir el archivo ZIP en C:\xampp\htdocs\GQ-Turismo
```

2. **Iniciar XAMPP**
   - Abrir XAMPP Control Panel
   - Iniciar Apache
   - Iniciar MySQL

3. **Crear la Base de Datos**
   - Abrir phpMyAdmin (http://localhost/phpmyadmin)
   - Crear nueva base de datos llamada `gq_turismo`
   - Importar el archivo `database/gq_turismo.sql`

4. **Aplicar Correcciones**
```bash
# Desde el directorio mysql de XAMPP
cd C:\xampp\mysql\bin
.\mysql.exe -u root -e "SOURCE C:/xampp/htdocs/GQ-Turismo/fix_complete_database.sql"
```

5. **Configurar Conexión**
   - Verificar `includes/db_connect.php`
   - Ajustar credenciales si es necesario:
```php
$host = 'localhost';
$dbname = 'gq_turismo';
$username = 'root';
$password = '';
```

6. **Verificar Instalación**
   - Abrir: http://localhost/GQ-Turismo/test_verificacion.html
   - Hacer clic en "Ejecutar Todas las Pruebas"
   - Verificar que todas las pruebas pasen ✅

---

## ⚙️ Configuración

### Configuración de la Base de Datos

El archivo `fix_complete_database.sql` contiene todas las correcciones necesarias:

- ✅ Tablas optimizadas
- ✅ Foreign keys con CASCADE
- ✅ Índices para rendimiento
- ✅ Eliminación de duplicados

### Estructura de Tablas Principales

**itinerarios**
- Información básica del itinerario
- Fechas, presupuesto, ciudad, notas
- Estado: planificacion, confirmado, completado

**itinerario_destinos**
- Relación itinerario-destinos
- Campo `orden` para secuencia

**itinerario_servicios**
- Relación itinerario-servicios
- Soporta: guías, agencias, locales

**reservas**
- Reservas realizadas por turistas
- Vinculadas a itinerarios

**reserva_servicios**
- Detalle de cada servicio reservado
- Estados independientes

**mensajes**
- Sistema de chat
- Soporta múltiples tipos de usuario

---

## 📖 Uso del Sistema

### Como Turista

#### 1. Registro e Inicio de Sesión
1. Ir a http://localhost/GQ-Turismo
2. Hacer clic en "Registrarse"
3. Seleccionar tipo "Turista"
4. Completar el formulario
5. Iniciar sesión

#### 2. Crear un Itinerario
1. Ir a "Mis Itinerarios"
2. Clic en "Crear Nuevo Itinerario"
3. **Paso 1 - Información Básica:**
   - Nombre del itinerario
   - Estado
   - Fechas (inicio y fin)
   - Presupuesto
   - Ciudad
   - Notas opcionales

4. **Paso 2 - Seleccionar Destinos:**
   - Usar filtros por categoría
   - Hacer clic en destinos para seleccionar
   - Ver resumen de seleccionados
   - Mínimo 1 destino requerido

5. **Paso 3 - Servicios Adicionales (Opcional):**
   - Seleccionar guías turísticos
   - Seleccionar agencias
   - Seleccionar locales/restaurantes

6. **Paso 4 - Confirmar y Guardar:**
   - Revisar resumen completo
   - Verificar costos totales
   - Guardar itinerario

#### 3. Reservar un Itinerario
1. Desde "Mis Itinerarios"
2. Clic en "Reservar" en el itinerario deseado
3. Seleccionar:
   - Fecha de la reserva
   - Número de personas
   - Método de pago
   - Comentarios opcionales
4. Confirmar reserva
5. ✅ Los proveedores reciben notificación automática

#### 4. Chatear con Proveedores
1. Ir a "Mis Mensajes"
2. Seleccionar una conversación existente
   O iniciar nueva desde perfil de proveedor
3. Escribir mensaje
4. Enviar
5. Mensajes se actualizan cada 5 segundos

### Como Proveedor

#### 1. Gestionar Servicios
**Agencias:**
- Panel: `admin/manager_agencia.php`
- Crear/editar destinos
- Actualizar información

**Guías:**
- Panel: `admin/manager_guia.php`
- Gestionar disponibilidad
- Actualizar especialidades y precios

**Locales:**
- Panel: `admin/manager_local.php`
- Actualizar menú/servicios
- Gestionar horarios

#### 2. Recibir y Responder Reservas
1. Ir a "Mis Mensajes"
2. Ver notificación de nueva reserva
3. Leer detalles del turista
4. Responder para coordinar
5. Confirmar servicio

---

## 📁 Estructura del Proyecto

```
GQ-Turismo/
├── admin/                      # Paneles de administración
│   ├── manager_agencia.php
│   ├── manager_guia.php
│   ├── manager_local.php
│   └── manager_destino.php
├── api/                        # APIs REST
│   ├── itinerarios.php        # CRUD de itinerarios
│   ├── reservas.php           # Gestión de reservas
│   ├── messages.php           # Envío de mensajes
│   ├── get_conversation.php   # Obtener conversaciones
│   ├── test_db.php            # Pruebas de BD
│   └── ...
├── assets/                     # Recursos estáticos
│   ├── css/
│   ├── js/
│   └── img/
│       ├── destinos/
│       ├── guias/
│       ├── agencias/
│       └── locales/
├── includes/                   # Archivos incluidos
│   ├── header.php
│   ├── footer.php
│   └── db_connect.php
├── database/                   # Scripts SQL
│   └── gq_turismo.sql
├── index.php                   # Página principal
├── itinerario.php             # Listado de itinerarios
├── crear_itinerario.php       # Crear/editar itinerario
├── mis_mensajes.php           # Sistema de chat
├── destinos.php               # Catálogo de destinos
├── detalle_destino.php        # Detalle de destino
├── reservas.php               # Proceso de reserva
├── guias.php                  # Catálogo de guías
├── agencias.php               # Catálogo de agencias
├── locales.php                # Catálogo de locales
├── fix_complete_database.sql  # Correcciones de BD
├── test_verificacion.html     # Pruebas del sistema
├── CORRECCIONES_COMPLETAS.md  # Documentación de cambios
└── README.md                  # Este archivo
```

---

## 🔧 Correcciones Recientes

### Versión 2.0 - Correcciones Completas

✅ **Base de Datos:**
- Agregadas columnas faltantes a `itinerarios`
- Creada tabla `itinerario_destinos`
- Creada tabla `itinerario_servicios`
- Creada tabla `reserva_servicios`
- Eliminados duplicados en `destinos`
- Optimizados índices

✅ **Sistema de Itinerarios:**
- Recreado completamente `crear_itinerario.php`
- Sistema wizard paso a paso
- Selección de locales funcionando
- API completa de CRUD

✅ **Sistema de Mensajes:**
- Chat funcional turista-proveedor
- Actualización en tiempo real
- Notificaciones de reservas

✅ **Sistema de Reservas:**
- Integración con itinerarios
- Notificaciones automáticas
- Sin errores de columnas faltantes

✅ **Correcciones de Errores:**
- "session_start() already active" - RESUELTO
- "Unknown column presupuesto_estimado" - RESUELTO
- "Table itinerario_guias doesn't exist" - RESUELTO
- "Unknown column fecha_inicio" - RESUELTO
- Destinos duplicados - RESUELTO

Ver archivo `CORRECCIONES_COMPLETAS.md` para detalles completos.

---

## 🐛 Solución de Problemas

### Problema: "session_start() already active"
**Solución:** Ya corregido en `includes/header.php`. Actualizar archivo.

### Problema: "Unknown column 'X' in 'field list'"
**Solución:** Ejecutar `fix_complete_database.sql`
```bash
cd C:\xampp\mysql\bin
.\mysql.exe -u root -e "SOURCE C:/xampp/htdocs/GQ-Turismo/fix_complete_database.sql"
```

### Problema: "Table 'X' doesn't exist"
**Solución:** Mismo que arriba, ejecutar el script de corrección.

### Problema: No se muestran destinos/servicios
**Solución:**
1. Verificar que haya datos en las tablas
2. Limpiar caché del navegador
3. Verificar permisos de base de datos

### Problema: Los proveedores no reciben notificaciones
**Solución:**
1. Verificar que la tabla `mensajes` existe
2. Verificar la API `api/messages.php`
3. Revisar que el campo `sender_type` y `receiver_type` sean correctos

### Problema: Error al eliminar itinerario
**Solución:**
1. Ejecutar script de corrección (crea foreign keys con CASCADE)
2. Verificar API `api/itinerarios.php` actualizada

---

## 📊 Verificación del Sistema

### Pruebas Automatizadas
Abrir: http://localhost/GQ-Turismo/test_verificacion.html

Verifica:
- ✅ Estructura de tablas
- ✅ Columnas necesarias
- ✅ APIs funcionando
- ✅ Sin duplicados
- ✅ Índices correctos

### Pruebas Manuales

**Flujo Completo de Turista:**
1. Registrarse
2. Crear itinerario (4 pasos)
3. Reservar itinerario
4. Verificar mensaje enviado a proveedor
5. Chatear con proveedor

**Flujo Completo de Proveedor:**
1. Iniciar sesión
2. Crear/editar servicio
3. Recibir notificación de reserva
4. Responder al turista

---

## 👥 Tipos de Usuario

### Turista
- Crear itinerarios
- Reservar servicios
- Chatear con proveedores
- Gestionar reservas

### Agencia
- Publicar destinos
- Recibir reservas
- Responder consultas
- Gestionar catálogo

### Guía
- Publicar servicios de guía
- Recibir solicitudes
- Chatear con turistas
- Actualizar disponibilidad

### Local (Restaurante/Hotel)
- Publicar establecimientos
- Recibir reservas
- Responder consultas
- Actualizar servicios

---

## 🔒 Seguridad

- ✅ Validación de sesiones
- ✅ Prepared statements (SQL injection prevention)
- ✅ Validación de tipos de usuario
- ✅ Sanitización de entradas
- ✅ Control de acceso por rol

---

## 📝 Changelog

### v2.0 - 2024-01-XX
- Reconstrucción completa del sistema de itinerarios
- Nuevo sistema de mensajería en tiempo real
- Optimización de base de datos
- Corrección de todos los bugs reportados
- Mejora de UX/UI

### v1.0 - 2024-XX-XX
- Lanzamiento inicial
- Sistema básico de reservas
- Catálogo de destinos
- Perfiles de proveedores

---

## 🤝 Contribución

Para contribuir al proyecto:
1. Fork el repositorio
2. Crear una rama (`git checkout -b feature/nueva-caracteristica`)
3. Commit cambios (`git commit -am 'Agregar nueva característica'`)
4. Push a la rama (`git push origin feature/nueva-caracteristica`)
5. Crear Pull Request

---

## 📧 Contacto

**Proyecto:** GQ-Turismo  
**Descripción:** Sistema de Gestión Turística para Guinea Ecuatorial  
**Versión:** 2.0  
**Estado:** ✅ Funcional

---

## 📄 Licencia

Este proyecto está bajo la Licencia MIT - ver archivo LICENSE para detalles.

---

**¿Necesitas ayuda?** Ver `CORRECCIONES_COMPLETAS.md` para documentación detallada de todas las correcciones.

**¿Encontraste un bug?** Ejecuta las pruebas en `test_verificacion.html` primero.

---

✨ **¡Disfruta usando GQ-Turismo!** ✨
