### 21/10/2025 - Inicio de la Reestructuración (Fase 0)

- Creado el archivo `progress.md` para el seguimiento de cambios.
- Reorganización completa de la estructura de archivos y carpetas según `modificaciones.md`.

### 21/10/2025 - Actualización de Plantillas Globales

- **`includes/header.php`**: Modernizado a HTML5, añadido Bootstrap 5, Bootstrap Icons, Google Fonts (Poppins, Lato) y la nueva barra de navegación fija.
- **`includes/footer.php`**: Actualizado para incluir los scripts de Bootstrap 5 y los nuevos archivos JS (`carousel.js`, `map.js`).
- Se ha establecido una estructura de página flexible (Flexbox) para que el footer se mantenga al final de la página.

### 21/10/2025 - Rediseño de la Página Principal (Fase 1)

- **`index.html`**: Se ha implementado un diseño completamente nuevo y moderno.
  - Añadida una sección "Hero" con imagen de fondo, superposición y un llamado a la acción (CTA).
  - Creada una sección de "Características" con iconos para destacar las funcionalidades clave.
  - Implementado un carrusel de "Destinos Populares" con Bootstrap, mostrando destinos de ejemplo en tarjetas estilizadas.
- **`assets/css/style.css`**: Actualizado con los nuevos estilos para la página de inicio.

### 21/10/2025 - Página de Destinos Dinámica (Fase 1)

- **`api/destinos.php`**: Creado un endpoint que consulta la base de datos y devuelve todos los destinos en formato JSON.
- **`destinos.html`**: Implementada la estructura de la página con un estado de carga.
- **`assets/js/main.js`**: Añadida la lógica para hacer una petición `fetch` a la API y renderizar dinámicamente las tarjetas de los destinos.

### 21/10/2025 - Página de Detalle de Destino (Fase 1)

- **`detalle_destino.php`**: Creada una página dinámica que:
  - Recibe y valida un `id` de destino desde la URL.
  - Utiliza una consulta preparada para buscar de forma segura la información del destino.
  - Muestra los detalles completos (imagen, descripción, precio, etc.) en un diseño de dos columnas.
  - Previene vulnerabilidades de SQL Injection y XSS.

### 21/10/2025 - Carrusel Dinámico en Página Principal (Fase 2)

- **`index.html`**: Modificado el carrusel de destinos para ser dinámico. Se reemplazó el contenido estático por un contenedor y un indicador de carga.
- **`assets/js/carousel.js`**: Creada la lógica para obtener los destinos desde `api/destinos.php` y renderizarlos dinámicamente en el carrusel de la página principal.

### 21/10/2025 - Implementación de Animaciones (Fase 2)

- **`includes/header.php`**: Añadido el CSS de la librería AOS (Animate On Scroll).
- **`includes/footer.php`**: Añadido el JS de la librería AOS.
- **`assets/js/main.js`**: Inicializada la librería AOS y añadida la lógica para animar las tarjetas de destinos cargadas dinámicamente.
- **`index.html`**: Agregados atributos `data-aos` para animar las secciones de la página de inicio.

### 21/10/2025 - Mejoras de Diseño Responsivo (Fase 2)

- **`assets/css/responsive.css`**: Añadidas media queries para mejorar la visualización en dispositivos móviles, ajustando tamaños de fuente y espaciados.

### 21/10/2025 - Hero con Fondo de Video (Fase 2)

- **`index.html`**: Reemplazada la imagen de fondo de la sección hero por un video de fondo para un mayor dinamismo.
- **`assets/css/style.css`**: Ajustados los estilos de la sección hero para asegurar que el video se muestre correctamente.

### 21/10/2025 - Página de Contacto (Fase 3)

- **`contacto.php`**: Creada la página de contacto con un formulario para que los usuarios envíen mensajes.
- **`assets/js/contact.js`**: Añadida la lógica para la validación del formulario de contacto y el envío de datos de forma asíncrona.
- **`api/contact.php`**: Creado el endpoint para recibir los datos del formulario de contacto (simulado, sin envío de correo real).

### 21/10/2025 - Sistema de Reservas (Fase 3)

- **`reservas.php`**: Creada una página protegida para que los usuarios autenticados puedan crear reservas.
- **`assets/js/reservas.js`**: Implementada la lógica para enviar los datos del formulario de reserva a la API.
- **`api/reservas.php`**: Creado el endpoint para validar y guardar las reservas en la base de datos.
- **`database/gq_turismo.sql`**: Actualizada la tabla `reservas` para incluir `id_usuario` y `personas`, y la relación con la tabla `usuarios`.
- **`includes/header.php`**: Añadido el enlace a la página de reservas en el menú de navegación.

### 21/10/2025 - Panel de Administración (Fase 4)

- **`admin/login.php`**: Creada la página de login para el administrador.
- **`admin/dashboard.php`**: Creada la página principal del panel de administración.
- **`admin/destinos.php`**: Creada la página para listar y gestionar los destinos.
- **`admin/add_destino.php`**: Implementada la funcionalidad para añadir nuevos destinos.
- **`admin/edit_destino.php`**: Implementada la funcionalidad para editar destinos existentes.
- **`admin/delete_destino.php`**: Implementada la funcionalidad para eliminar destinos.
- **`admin/reservas.php`**: Creada la página para listar las reservas.
- **`database/add_admin.php`**: Creado un script para añadir un administrador con contraseña hasheada.

### 21/10/2025 - Creación de Itinerarios (Fase 5)

- **`crear_itinerario.php`**: Creada la página para que los usuarios puedan crear itinerarios personalizados, seleccionando hasta 5 destinos.
- **`itinerario.html`**: Modificada para listar los itinerarios creados por el usuario y permitir su eliminación.
- **`api/itinerarios.php`**: Creado el endpoint para manejar la creación y eliminación de itinerarios en la base de datos.
- **`assets/js/itinerario.js`**: Implementada la lógica completa para la selección de destinos, validación y comunicación con la API para crear y eliminar itinerarios.

- **`admin/login.php`**: Creada la página de login para el administrador.
- **`admin/dashboard.php`**: Creada la página principal del panel de administración.
- **`admin/destinos.php`**: Creada la página para listar y gestionar los destinos.
- **`admin/add_destino.php`**: Implementada la funcionalidad para añadir nuevos destinos.
- **`admin/edit_destino.php`**: Implementada la funcionalidad para editar destinos existentes.
- **`admin/delete_destino.php`**: Implementada la funcionalidad para eliminar destinos.
- **`admin/reservas.php`**: Creada la página para listar las reservas.
- **`database/add_admin.php`**: Creado un script para añadir un administrador con contraseña hasheada.