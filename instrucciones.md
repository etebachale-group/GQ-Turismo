🧭 Prompt para Gemini CLI — Proyecto “GQ-TURISMO”

# Proyecto: GQ-TURISMO 🌍  
**Tipo:** Plataforma web de turismo  
**Objetivo:** Crear un MVP totalmente funcional y responsive que permita explorar, reservar y gestionar destinos turísticos en Guinea Ecuatorial.

---

## 🔧 Tecnologías a usar

- **Lenguaje del servidor:** PHP (versión 8+)
- **Entorno local:** XAMPP (Apache + MySQL)
- **Base de datos:** MySQL administrado con phpMyAdmin
- **Frontend:** HTML5, CSS3, Bootstrap 5 y JavaScript (vanilla)
- **Gestión de datos:** PHP + MySQL (CRUD)
- **Estilo visual:** Moderno, limpio, tropical y optimizado para móviles.

---

## 🧩 Estructura del proyecto

GQ-Turismo/ │ ├── index.php               # Página principal ├── destinos.php            # Listado de destinos ├── detalle_destino.php     # Detalles de un destino específico ├── reservas.php            # Gestión de reservas ├── contacto.php            # Página de contacto │ ├── includes/ │   ├── header.php │   ├── footer.php │   └── db.php              # Conexión a la base de datos │ ├── assets/ │   ├── css/ │   │   ├── style.css │   │   └── bootstrap.min.css │   ├── js/ │   │   ├── main.js │   │   └── bootstrap.bundle.min.js │   └── images/ │       ├── logo.png │       └── destinos/ │ ├── database/ │   └── gq_turismo.sql      # Script SQL para crear tablas │ └── progress.md             # Documento de seguimiento del avance

---

## 🧱 Funcionalidades principales

1. **Página de inicio (index.php):**
   - Mostrar los destinos más populares.
   - Barra de navegación y banner principal.
   - Sección “Explora Guinea Ecuatorial”.

2. **Página de destinos (destinos.php):**
   - Mostrar todos los destinos con paginación.
   - Filtros por tipo de destino (playa, montaña, ciudad, cultura, etc.).
   - Cada destino incluye imagen, descripción breve y botón “Ver más”.

3. **Detalles de destino (detalle_destino.php):**
   - Mostrar información detallada: descripción, galería, ubicación, y botón “Reservar”.
   - Cargar los datos dinámicamente desde la base de datos.

4. **Reservas (reservas.php):**
   - Formulario para que el usuario seleccione fecha, cantidad de personas y destino.
   - Almacenar las reservas en la base de datos.

5. **Panel de administración (admin/):**
   - Login básico con PHP y MySQL.
   - CRUD de destinos y reservas.

6. **Base de datos (gq_turismo.sql):**
   - Tabla `destinos` → id, nombre, descripción, categoría, imagen, precio.
   - Tabla `reservas` → id, id_destino, nombre_usuario, email, fecha, estado.
   - Tabla `usuarios` → id, nombre, email, contraseña (hash).

---

## 💡 Requisitos técnicos

- El sitio debe ser **totalmente responsive** (móvil, tablet y escritorio).
- Todas las páginas deben incluir **header y footer dinámicos** mediante `include`.
- Conexión a MySQL desde `includes/db.php`.
- Validación de formularios con **JavaScript y PHP**.
- Implementar protección básica contra inyección SQL.
- Usar **Bootstrap 5** para el diseño general.
- Comentarios claros en el código.

---

## 📁 Archivo de seguimiento (progress.md)

Gemini debe crear y mantener automáticamente un archivo `progress.md` con el siguiente formato:

```markdown
# Progreso del Proyecto GQ-Turismo

## Última actualización: [fecha actual]

### Avances completados
- [ ] Configuración del entorno local con XAMPP
- [ ] Creación de la base de datos MySQL y tablas iniciales
- [ ] Diseño de la página principal (index.php)
- [ ] Implementación de la lógica de destinos
- [ ] Sistema de reservas
- [ ] Panel de administración

### Próximas tareas
- [ ] Mejorar el diseño responsive
- [ ] Conectar los formularios con la base de datos
- [ ] Añadir validaciones y seguridad
- [ ] Test de usabilidad y optimización de carga

### Observaciones
- Mantener la coherencia del diseño.
- Seguir la jerarquía de carpetas establecida.


---

🧠 Instrucción para Gemini CLI

> “Actúa como un desarrollador web experto. Crea el proyecto GQ-TURISMO desde cero siguiendo todas las especificaciones anteriores.
Genera el código completo, archivos y carpetas necesarios para tener una página web funcional y responsive en entorno XAMPP.
Guarda el progreso y los cambios en progress.md.
Verifica que cada módulo (inicio, destinos, reservas, contacto) esté correctamente vinculado.
Usa PHP para la lógica y MySQL para los datos.
Bootstrap para el diseño y JavaScript para las interacciones dinámicas.”




---

✅ Resultado esperado

Una página web MVP totalmente funcional, que permita:

Ver destinos turísticos de Guinea Ecuatorial.

Reservar experiencias.

Gestionar datos desde un panel admin básico.

Correr localmente en XAMPP.

Ser usada como demo para el Hackathon 2025.
