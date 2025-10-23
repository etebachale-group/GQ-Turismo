# NUEVO SISTEMA DE ITINERARIOS - COMPLETAMENTE RECREADO

## Fecha: 2025-10-23

## Resumen

Se ha recreado completamente el sistema de itinerarios con un diseño moderno, funcionalidad mejorada y un wizard paso a paso para crear itinerarios.

---

## 🎯 Archivos Creados/Modificados

### 1. **crear_itinerario.php** - RECREADO COMPLETAMENTE
**Ubicación:** `/crear_itinerario.php`

**Características:**
- ✅ Wizard de 4 pasos con navegación fluida
- ✅ Modo creación y edición
- ✅ Validación en cada paso
- ✅ Preview en tiempo real
- ✅ Diseño moderno y responsivo

#### Paso 1: Información Básica
- Nombre del itinerario (obligatorio)
- Estado (planificación/confirmado/completado)
- Fecha de inicio
- Fecha de fin
- Presupuesto estimado
- Ciudad principal
- Notas adicionales

#### Paso 2: Seleccionar Destinos
- Grid con todos los destinos disponibles
- Filtros por categoría (todos/playa/montaña/ciudad/etc)
- Click para seleccionar/deseleccionar
- Badges numerados que muestran el orden
- Preview de destinos seleccionados
- Cálculo automático del precio total
- Mínimo 1 destino requerido

#### Paso 3: Servicios Adicionales (Opcional)
**Guías Turísticos:**
- Grid de guías disponibles
- Imagen de perfil
- Nombre y especialidades
- Precio por hora
- Selección múltiple

**Agencias de Viaje:**
- Grid de agencias disponibles
- Imagen de perfil
- Nombre y descripción
- Selección múltiple

**Locales y Restaurantes:**
- Grid de locales disponibles
- Imagen de perfil
- Tipo de local (restaurante/bar/hotel/etc)
- Dirección
- Selección múltiple

#### Paso 4: Resumen y Confirmación
**Resumen de Información Básica:**
- Muestra todos los datos ingresados
- Estado del itinerario
- Fechas del viaje
- Ciudad
- Notas

**Resumen de Destinos:**
- Lista ordenada de destinos
- Precio individual de cada destino
- Precio total de destinos

**Resumen de Servicios:**
- Lista de guías seleccionados
- Lista de agencias seleccionadas
- Lista de locales seleccionados

**Resumen de Costos:**
- Total destinos
- Presupuesto estimado
- **TOTAL GENERAL**

**Botón de Guardar:**
- Crea el itinerario en la base de datos
- Guarda todos los destinos con orden
- Guarda todos los servicios seleccionados
- Feedback visual al usuario
- Redirección automática a itinerario.php

---

### 2. **itinerario.php** - RECREADO COMPLETAMENTE
**Ubicación:** `/itinerario.php`

**Características:**
- ✅ Hero section con gradiente animado
- ✅ Panel de estadísticas
- ✅ Búsqueda y filtros en tiempo real
- ✅ Lista de itinerarios con tarjetas
- ✅ Recomendaciones de servicios
- ✅ Diseño moderno y responsivo

#### Hero Section
- Título grande con icono
- Descripción
- Botón destacado "Crear Nuevo Itinerario"
- Fondo con gradiente morado

#### Panel de Estadísticas
Muestra 4 tarjetas con:
- **Total Itinerarios:** Cantidad total creados
- **Destinos Únicos:** Destinos diferentes visitados
- **En Planificación:** Itinerarios en proceso
- **Completados:** Itinerarios finalizados

Cada tarjeta tiene:
- Icono circular con color
- Número grande
- Descripción
- Hover effect

#### Barra de Búsqueda y Filtros
- **Campo de búsqueda:** Busca por nombre de itinerario
- **Selector de estado:** Filtra por planificación/confirmado/completado
- Funciona en tiempo real sin recargar

#### Lista de Itinerarios
Cada tarjeta muestra:
- **Nombre del itinerario** (título grande)
- **Badge de estado** con color (amarillo/azul/verde)
- **Fecha de inicio** (si existe)
- **Fecha de fin** (si existe)
- **Total de destinos**
- **Presupuesto estimado** (si > 0)
- **Ciudad principal** (si existe)
- **Notas** (preview de 120 caracteres)

**Acciones disponibles:**
- **Botón Editar:** Abre crear_itinerario.php en modo edición
- **Botón Reservar:** Va a reservas.php con el itinerario
- **Botón Eliminar:** Elimina con confirmación

**Animaciones:**
- Fade up en cada tarjeta
- Delay progresivo (100ms entre tarjetas)
- Hover effect con elevación

#### Estado Vacío
Si no hay itinerarios:
- Icono grande de mapa
- Mensaje: "No tienes itinerarios aún"
- Descripción motivacional
- Botón grande "Crear Primer Itinerario"
- Botón secundario "Explorar Destinos"

#### Recomendaciones de Servicios
**Guías Recomendados (6):**
- Tarjetas con imagen
- Nombre del guía
- Especialidades
- Precio por hora
- Botón "Ver Perfil"
- Link "Ver Todos" en el header

**Locales y Restaurantes (6):**
- Tarjetas con imagen
- Badge con tipo de local
- Nombre del local
- Dirección
- Botón "Ver Más"
- Link "Ver Todos" en el header

**Agencias de Viaje (3):**
- Tarjetas más grandes con imagen
- Nombre de agencia
- Descripción (80 caracteres)
- Botón "Ver Detalles"
- Link "Ver Todas" en el header

---

### 3. **api/itinerarios.php** - ACTUALIZADO COMPLETAMENTE
**Ubicación:** `/api/itinerarios.php`

**Métodos Soportados:**

#### POST - Crear/Actualizar Itinerario
**Parámetros:**
```
- itinerario_id (opcional, para editar)
- nombre_itinerario (obligatorio)
- estado (planificacion/confirmado/completado)
- fecha_inicio
- fecha_fin
- presupuesto_estimado
- ciudad
- notas
- destinos (JSON array de IDs)
- servicios (JSON object: {guias:[], agencias:[], locales:[]})
```

**Proceso:**
1. Valida datos obligatorios
2. Calcula precio total de destinos
3. Inicia transacción
4. Crea/actualiza itinerario
5. Inserta destinos con orden
6. Inserta servicios seleccionados
7. Commit de transacción
8. Retorna éxito con ID

**Respuesta:**
```json
{
    "success": true,
    "message": "Itinerario creado exitosamente",
    "data": {
        "id": 123
    }
}
```

#### DELETE - Eliminar Itinerario
**Parámetros:**
```
?action=delete&id=123
```

**Proceso:**
1. Verifica pertenencia al usuario
2. Elimina destinos asociados
3. Elimina servicios asociados
4. Elimina itinerario
5. Retorna éxito

**Respuesta:**
```json
{
    "success": true,
    "message": "Itinerario eliminado exitosamente"
}
```

#### GET - Obtener Itinerarios
**Acción: list**
```
?action=list
```
Retorna todos los itinerarios del usuario con total de destinos

**Acción: get_one**
```
?action=get_one&id=123
```
Retorna un itinerario específico con sus destinos

---

### 4. **fix_itinerarios_servicios.sql** - NUEVO
**Ubicación:** `/fix_itinerarios_servicios.sql`

**Tablas Creadas:**

#### itinerario_guias
- id (PK auto_increment)
- id_itinerario (FK → itinerarios)
- id_guia (FK → guias_turisticos)
- fecha_asociacion (timestamp)
- UNIQUE (id_itinerario, id_guia)

#### itinerario_agencias
- id (PK auto_increment)
- id_itinerario (FK → itinerarios)
- id_agencia (FK → agencias)
- fecha_asociacion (timestamp)
- UNIQUE (id_itinerario, id_agencia)

#### itinerario_locales
- id (PK auto_increment)
- id_itinerario (FK → itinerarios)
- id_local (FK → lugares_locales)
- fecha_asociacion (timestamp)
- UNIQUE (id_itinerario, id_local)

**Modificaciones:**
- Columna `orden` en `itinerario_destinos`
- Índices para mejor rendimiento

**Relaciones:**
- ON DELETE CASCADE (al eliminar itinerario, elimina servicios)
- UNIQUE KEY para evitar duplicados

---

## 🎨 Diseño y UX

### Colores del Sistema
- **Primary:** #667eea (morado)
- **Secondary:** #764ba2 (morado oscuro)
- **Success:** #28a745 (verde)
- **Warning:** #ffc107 (amarillo)
- **Danger:** #dc3545 (rojo)
- **Info:** #17a2b8 (azul)

### Gradientes
- **Hero:** `linear-gradient(135deg, #667eea 0%, #764ba2 100%)`
- **Botones Primary:** Mismo gradiente
- **Cards Summary:** Mismo gradiente

### Animaciones
- **Fade Up:** Entrada de elementos (AOS)
- **Fade In:** Cambio de pasos del wizard
- **Hover:** Elevación de tarjetas (-5px)
- **Transform:** Scale en imágenes al hover

### Tipografía
- **Headings:** fw-bold (Font Weight 700)
- **Body:** Regular (Font Weight 400)
- **Labels:** fw-bold (Font Weight 600)
- **Small Text:** small class

### Espaciado
- **Padding Cards:** 2rem
- **Gap Grid:** g-3, g-4
- **Margin Bottom:** mb-3, mb-4, mb-5

---

## 🔧 Funcionalidades Técnicas

### JavaScript Modular
**Variables Globales:**
```javascript
let currentStep = 1;  // Paso actual del wizard
let destinos = [];    // Array de IDs de destinos
let servicios = {     // Object de servicios
    guias: [],
    agencias: [],
    locales: []
};
```

**Funciones Principales:**
- `nextStep()` - Avanza al siguiente paso con validación
- `prevStep()` - Retrocede al paso anterior
- `updateWizard()` - Actualiza UI del wizard
- `updateDestinos()` - Actualiza preview de destinos
- `updateResumen()` - Genera resumen final
- `handleSubmit()` - Envía formulario via AJAX

### Validaciones
**Paso 1:**
- Nombre no vacío

**Paso 2:**
- Al menos 1 destino seleccionado

**Paso 3:**
- Sin validación (opcional)

**Paso 4:**
- Validación final antes de guardar

### Fetch API
```javascript
fetch('api/itinerarios.php', {
    method: 'POST',
    body: formData
})
.then(response => response.json())
.then(data => {
    if (data.success) {
        // Éxito
    } else {
        // Error
    }
})
```

### Event Listeners
- Click en filtros de categoría
- Click en tarjetas de destinos
- Click en tarjetas de servicios
- Submit del formulario
- Input en búsqueda
- Change en selector de estado

---

## 📊 Base de Datos

### Estructura Completa de Tablas

#### itinerarios
```sql
- id (PK)
- id_usuario (FK)
- nombre_itinerario
- estado (ENUM)
- fecha_inicio (DATE)
- fecha_fin (DATE)
- presupuesto_estimado (DECIMAL)
- ciudad (VARCHAR)
- notas (TEXT)
- precio_total (DECIMAL)
- fecha_creacion (TIMESTAMP)
```

#### itinerario_destinos
```sql
- id (PK)
- id_itinerario (FK)
- id_destino (FK)
- orden (INT)
```

#### itinerario_guias
```sql
- id (PK)
- id_itinerario (FK)
- id_guia (FK)
- fecha_asociacion (TIMESTAMP)
```

#### itinerario_agencias
```sql
- id (PK)
- id_itinerario (FK)
- id_agencia (FK)
- fecha_asociacion (TIMESTAMP)
```

#### itinerario_locales
```sql
- id (PK)
- id_itinerario (FK)
- id_local (FK)
- fecha_asociacion (TIMESTAMP)
```

---

## 🚀 Cómo Usar

### Para Crear un Itinerario

1. **Navegar a Crear Itinerario:**
   ```
   http://localhost/GQ-Turismo/crear_itinerario.php
   ```

2. **Paso 1 - Información Básica:**
   - Ingresa nombre del itinerario
   - Selecciona estado
   - Elige fechas (opcional)
   - Ingresa presupuesto (opcional)
   - Ingresa ciudad (opcional)
   - Agrega notas (opcional)
   - Clic en "Siguiente"

3. **Paso 2 - Seleccionar Destinos:**
   - Usa filtros por categoría si deseas
   - Haz clic en los destinos para seleccionar
   - Los destinos se numeran automáticamente
   - Ve el preview y precio total
   - Clic en "Siguiente"

4. **Paso 3 - Servicios Adicionales:**
   - Opcional: Selecciona guías
   - Opcional: Selecciona agencias
   - Opcional: Selecciona locales
   - Clic en "Siguiente"

5. **Paso 4 - Resumen:**
   - Revisa toda la información
   - Verifica destinos y servicios
   - Ve el total de costos
   - Clic en "Guardar"

6. **Confirmación:**
   - Mensaje de éxito
   - Redirección a itinerario.php

### Para Editar un Itinerario

1. **Desde itinerario.php:**
   - Clic en botón "Editar" del itinerario

2. **Wizard en Modo Edición:**
   - Todos los campos prellenados
   - Destinos ya seleccionados
   - Mismo proceso de 4 pasos
   - Botón dice "Actualizar Itinerario"

### Para Eliminar un Itinerario

1. **Desde itinerario.php:**
   - Clic en botón "Eliminar" (icono basura)
   - Confirmar en el diálogo
   - Itinerario eliminado

### Para Buscar Itinerarios

1. **Barra de Búsqueda:**
   - Escribe el nombre del itinerario
   - Filtra automáticamente en tiempo real

2. **Selector de Estado:**
   - Selecciona estado deseado
   - Filtra automáticamente

---

## 🎯 Casos de Uso

### Turista Planificando Viaje
1. Crea itinerario "Vacaciones en Malabo"
2. Selecciona 3 destinos: Playa, Centro Ciudad, Restaurante
3. Agrega guía turístico local
4. Agrega restaurante recomendado
5. Guarda con presupuesto 500€

### Turista Editando Viaje
1. Edita itinerario existente
2. Agrega un destino más
3. Cambia estado a "Confirmado"
4. Actualiza presupuesto
5. Guarda cambios

### Turista Revisando Historial
1. Ve estadísticas generales
2. Busca itinerario por nombre
3. Filtra por completados
4. Revisa detalles de viaje anterior

---

## 📝 Notas de Implementación

### Seguridad
- ✅ Verificación de sesión
- ✅ Verificación de tipo de usuario
- ✅ Prepared statements en SQL
- ✅ Validación de datos en backend
- ✅ Sanitización de HTML

### Performance
- ✅ Consultas optimizadas con JOINs
- ✅ Índices en tablas
- ✅ Transacciones para integridad
- ✅ Lazy loading potencial

### Compatibilidad
- ✅ Bootstrap 5.3
- ✅ Bootstrap Icons
- ✅ AOS (Animate On Scroll)
- ✅ Fetch API (moderno)
- ✅ ES6+ JavaScript

### Responsive Design
- ✅ Móvil: 1 columna
- ✅ Tablet: 2 columnas
- ✅ Desktop: 3-4 columnas
- ✅ Wizard adaptativo

---

## 🐛 Problemas Conocidos y Soluciones

### Problema: Error al guardar itinerario
**Solución:** Ejecutar `fix_itinerarios_servicios.sql`

### Problema: Destinos no se muestran
**Solución:** Verificar que hay destinos en la BD

### Problema: No se pueden seleccionar servicios
**Solución:** Verificar que existen guías, agencias, locales

---

## 🔄 Migración desde Sistema Antiguo

### Pasos Realizados:
1. ✅ Backup de archivos antiguos en `/backup_itinerarios/`
2. ✅ Recreación completa de crear_itinerario.php
3. ✅ Recreación completa de itinerario.php
4. ✅ Actualización completa de api/itinerarios.php
5. ✅ Creación de tablas de servicios
6. ✅ Agregado de columna orden

### Archivos de Backup:
- `backup_itinerarios/itinerario_backup.php`
- `backup_itinerarios/crear_itinerario_backup.php`

---

## ✅ Checklist de Funcionalidades

### Crear Itinerario
- ✅ Wizard de 4 pasos
- ✅ Información básica
- ✅ Selección de destinos
- ✅ Selección de servicios
- ✅ Resumen y confirmación
- ✅ Validación en cada paso
- ✅ Cálculo automático de precios
- ✅ Preview en tiempo real
- ✅ Guardar en base de datos
- ✅ Redirección después de guardar

### Editar Itinerario
- ✅ Cargar datos existentes
- ✅ Preseleccionar destinos
- ✅ Mismo wizard que crear
- ✅ Actualizar en base de datos

### Ver Itinerarios
- ✅ Lista con tarjetas
- ✅ Estadísticas generales
- ✅ Búsqueda por nombre
- ✅ Filtro por estado
- ✅ Información detallada
- ✅ Acciones (editar/reservar/eliminar)

### Eliminar Itinerario
- ✅ Confirmación de eliminación
- ✅ Eliminación en cascada
- ✅ Feedback al usuario

### Recomendaciones
- ✅ Guías turísticos
- ✅ Locales y restaurantes
- ✅ Agencias de viaje

---

## 📞 Soporte

**Documentación Relacionada:**
- `SISTEMA_GESTION_TIEMPO_REAL.md` - Sistema de gestión
- `CORRECCION_DESTINOS.md` - Corrección de destinos
- `CHAT_SYSTEM_FIXED.md` - Sistema de chat

**Para Problemas:**
1. Ejecutar `verify_system.php`
2. Revisar logs de MySQL
3. Verificar permisos de archivos
4. Comprobar servicios (Apache, MySQL)

---

**Última actualización:** 2025-10-23
**Estado:** ✅ COMPLETADO Y FUNCIONAL
**Versión:** 2.0
