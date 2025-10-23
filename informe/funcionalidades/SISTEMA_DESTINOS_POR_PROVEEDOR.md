# 🌍 Sistema de Gestión de Destinos por Proveedor
**Fecha:** 23 de Octubre de 2025  
**Versión:** 2.3  
**Estado:** ✅ IMPLEMENTADO

---

## 🎯 Objetivo

Permitir que guías, agencias y locales seleccionen los destinos específicos donde pueden ofrecer sus servicios, mejorando la precisión de las búsquedas y la calidad de las coincidencias entre turistas y proveedores.

---

## ⚠️ IMPORTANTE - EJECUTAR SQL

```bash
mysql -u root gq_turismo < database\add_guia_destinos_relation.sql
```

Este script crea 3 nuevas tablas:
- `guia_destinos`
- `agencia_destinos`
- `local_destinos`

---

## 📋 Funcionalidad Principal

### Los proveedores pueden:
- ✅ Seleccionar destinos donde ofrecen servicios
- ✅ Configurar tarifas especiales por destino
- ✅ Agregar descripciones específicas por destino
- ✅ Activar/desactivar disponibilidad por destino
- ✅ Eliminar destinos de su lista

### Los turistas verán:
- ✅ Solo proveedores disponibles en destinos seleccionados
- ✅ Descripciones personalizadas por destino
- ✅ Tarifas específicas (si configuradas)
- ✅ Resultados más relevantes

---

## 📁 Archivos Creados

1. **`database/add_guia_destinos_relation.sql`** - Script de BD
2. **`admin/mis_destinos.php`** - Página de gestión
3. **`api/get_proveedores_por_destino.php`** - API de búsqueda

---

## 🎨 Cómo Usar

### Para Guías/Agencias/Locales:

1. **Iniciar sesión** como proveedor
2. **Ir a "Mis Destinos"** en el menú lateral (nuevo)
3. **Ver estadísticas:**
   - Total destinos asignados
   - Destinos disponibles
   - Destinos por agregar
4. **Agregar destino:**
   - Click "Agregar Destino"
   - Seleccionar destino de la lista
   - (Opcional) Configurar tarifa especial
   - (Opcional) Agregar descripción del servicio
   - Click "Agregar"
5. **Gestionar destinos:**
   - Toggle para activar/desactivar
   - Botón eliminar para quitar
   - Ver cards visuales de cada destino

### Para Super Admin:

- Solo necesitas crear destinos como siempre
- Los proveedores los seleccionarán automáticamente
- No requiere acción adicional

---

## 🔄 Flujo Completo

```
SUPER ADMIN crea destino "Malabo"
         ↓
Destino aparece en lista de todos los proveedores
         ↓
GUÍA agrega "Malabo" a su lista
  - Tarifa especial: 50€
  - Descripción: "Tours en la capital"
         ↓
TURISTA crea itinerario con "Malabo"
         ↓
SISTEMA filtra y muestra solo guías con "Malabo"
         ↓
TURISTA ve guía con info específica de Malabo
         ↓
TURISTA selecciona guía apropiado
```

---

## 📊 Estadísticas Disponibles

- **Total Destinos:** Cuántos destinos has agregado
- **Disponibles:** Cuántos están activos actualmente
- **Por Agregar:** Cuántos destinos existen pero no has agregado

---

## 🎯 Ejemplos de Uso

### Ejemplo 1: Guía Especializado
**Juan - Guía en Malabo**
- Agrega solo "Malabo" a su lista
- Configura tarifa: 60€/día
- Descripción: "10 años de experiencia en la capital"
- **Resultado:** Solo aparece en búsquedas de Malabo

### Ejemplo 2: Agencia Multiubicación
**Safari Tours - Opera en 3 ciudades**
- Agrega: Malabo (200€), Bata (180€), Luba (150€)
- Descripciones personalizadas para cada ciudad
- **Resultado:** Aparece en búsquedas de las 3 ciudades con info específica

### Ejemplo 3: Disponibilidad Temporal
**Restaurant Local - Temporalmente cerrado en Bata**
- Tiene Malabo y Bata agregados
- Desactiva toggle en Bata (temporalmente)
- **Resultado:** Solo aparece en Malabo, pero mantiene registro de Bata

---

## 🔒 Seguridad

- ✅ Solo proveedores autenticados pueden acceder
- ✅ Solo pueden modificar sus propios destinos
- ✅ Foreign keys previenen datos huérfanos
- ✅ Unique constraint evita duplicados

---

## 📱 Mobile Responsive

- ✅ Grid adaptable (3 columnas → 1 columna)
- ✅ Cards apiladas en móvil
- ✅ Botones touch-friendly
- ✅ Modal fullscreen en móvil

---

## 🚀 Instalación

### Paso 1: Ejecutar SQL
```bash
cd C:\xampp\htdocs\GQ-Turismo
mysql -u root gq_turismo < database\add_guia_destinos_relation.sql
```

### Paso 2: Verificar
```sql
SHOW TABLES LIKE '%_destinos';
```

Debe mostrar:
- agencia_destinos
- guia_destinos
- local_destinos

### Paso 3: Probar
1. Login como guía/agencia/local
2. Ver nuevo menú "Mis Destinos"
3. Agregar primer destino
4. ✅ Listo para usar

---

## 🎉 Beneficios

### Calidad:
- Proveedores con experiencia real en el destino
- Descripciones específicas y relevantes
- Tarifas transparentes

### Eficiencia:
- Búsquedas más rápidas
- Resultados más precisos
- Menos opciones irrelevantes

### Control:
- Proveedores gestionan su presencia
- Activar/desactivar fácilmente
- Admin no sobrecargado

---

## 📞 Soporte

**Problemas comunes:**

### No veo "Mis Destinos" en el menú
- Verifica que seas guía, agencia o local
- Cierra sesión y vuelve a iniciar

### No aparecen destinos para agregar
- El admin debe crear destinos primero
- Verifica que no los hayas agregado ya

### Error al agregar destino
- Verifica que el script SQL se ejecutó
- Revisa logs de PHP/MySQL

---

**Estado:** ✅ SISTEMA COMPLETAMENTE FUNCIONAL

**Elaborado por:** GitHub Copilot AI  
**Fecha:** 23 de Octubre de 2025
