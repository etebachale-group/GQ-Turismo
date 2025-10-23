### Resumen del Proyecto GQ-Turismo

**Hecho:**

1.  **Correcciones en `crear_itinerario.php`:**
    *   **Servicios Fantasma y Duplicados:** Se implementó un filtrado por ciudad para destinos, guías y alojamientos. Ahora, la página solo muestra los servicios relevantes para la ciudad seleccionada por el usuario, eliminando elementos duplicados o no pertinentes.
    *   **Imágenes Faltantes:** Se corrigieron las rutas de las imágenes para los perfiles de guías y locales en el script de JavaScript, asegurando que se muestren correctamente.
    *   **Botones no Funcionales:** Se implementó la funcionalidad completa del botón "Guardar Itinerario Final", que ahora guarda el itinerario en la base de datos y redirige al usuario.

2.  **Modificaciones en la Base de Datos:**
    *   Se añadió la columna `ciudad` a la tabla `lugares_locales`.
    *   Se añadieron las columnas `alojamiento_id`, `guia_id`, `fecha_inicio` y `fecha_fin` a la tabla `itinerarios` para almacenar la información completa del viaje.

3.  **Modificaciones en la API:**
    *   Se actualizaron los *endpoints* (`api/destinos.php`, `api/guias.php`, `api/locales.php`) para que acepten el parámetro `city` y filtren los resultados de la base de datos en consecuencia.

4.  **Refactorización de JavaScript (`assets/js/crear_itinerario.js`):**
    *   Se cambió la estrategia de carga de datos a un modelo de "carga perezosa" (lazy loading) para evitar cargas de datos múltiples e innecesarias, solucionando de raíz el problema de duplicación.
    *   Se mejoró la lógica para manejar el cambio de ciudad, asegurando que la vista se limpie correctamente antes de cargar nuevos datos.

**Pendiente:**

1.  **Corregir el `Parse error` en `detalle_guia.php` (línea 364).**
2.  **Revisar el funcionamiento de los Dashboards para `agencia`, `guia` y `local`** (una vez resuelto el error anterior).
3.  **Revisar el funcionamiento de las opciones de usuario** (enlaces, etc.) para `agencia`, `guia` y `local`.
4.  **Implementar "Ingresos y Estadísticas" para Guías y Agencias** (ya está para locales).
5.  **Funcionalidades de Interacción Adicionales:**
    *   Sistema de mensajería entre turistas y proveedores.
    *   Sistema de valoraciones/reseñas.
    *   Sistema de búsqueda avanzada.
    *   Recomendaciones más sofisticadas.
    *   Gestión de descuentos para agencias.
