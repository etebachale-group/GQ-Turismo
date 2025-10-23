# 🏝️ Proyecto: GQ-Turismo — Plataforma Web de Turismo en Guinea Ecuatorial

## 🚀 Descripción general
**GQ-Turismo** es una plataforma web moderna, dinámica y totalmente responsiva que busca promover el turismo en Guinea Ecuatorial. Permite a los visitantes descubrir destinos, planificar itinerarios personalizados, seleccionar opciones de pago (prepago o postpago) y recibir notificaciones en tiempo real mediante un mapa interactivo.

La web debe tener un **diseño llamativo, con iconos, ventanas flotantes, animaciones suaves y carouseles** que transmitan energía tropical, modernidad y confianza.

---

## 🧩 Objetivo principal
Impulsar el turismo nacional e internacional, brindando a los usuarios una experiencia cómoda, visualmente atractiva y funcional para explorar Guinea Ecuatorial.

---

## ⚙️ Tecnologías
- **Backend:** PHP 8 (con XAMPP)
- **Base de datos:** MySQL (PhpMyAdmin)
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla), Bootstrap 5
- **Estilos:** CSS moderno + Bootstrap Icons + animaciones JS
- **Servidor local:** XAMPP (Apache + MySQL)

---

## 🧱 Estructura del Proyecto
```
GQ-Turismo/
├── index.html
├── about.html
├── destinos.html
├── itinerario.html
├── contacto.html
├── assets/
│   ├── css/
│   │   ├── style.css
│   │   └── responsive.css
│   ├── js/
│   │   ├── main.js
│   │   ├── carousel.js
│   │   └── map.js
│   ├── img/
│   │   ├── destinos/
│   │   ├── icons/
│   │   └── banners/
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── db_connect.php
├── admin/
│   ├── dashboard.php
│   └── manage_destinos.php
├── api/
│   ├── destinos.php
│   ├── itinerarios.php
│   └── usuarios.php
├── database/
│   ├── gqturismo.sql
├── progress.md
└── README.md
```

---

## 🗺️ Funcionalidades principales
- Registro e inicio de sesión de usuarios.
- Visualización de destinos turísticos (imágenes + descripciones + reseñas).
- Creación de itinerarios personalizados (hasta 5 destinos).
- Selección de método de pago (prepago / postpago).
- Mapa interactivo con geolocalización y rutas.
- Sistema de notificaciones y seguimiento en tiempo real.
- Panel de administración para gestionar destinos, usuarios e itinerarios.

---

## 🎨 Diseño y estilo visual
### 💡 Requisitos de diseño para Gemini Code Assist:
- **Paleta de colores:** tonos turquesa, dorado y blanco.
- **Tipografía:** Poppins / Lato.
- **Componentes visuales:**
  - **Navbar fija con sombra suave.**
  - **Hero principal con video o imagen a pantalla completa y botón CTA flotante.**
  - **Ventanas modales flotantes** para registro y contacto.
  - **Carouseles animados** con transición suave (Bootstrap o JS personalizado).
  - **Iconos ilustrativos** (Bootstrap Icons o FontAwesome).
  - **Animaciones suaves on-scroll** (AOS o animaciones CSS).
  - **Diseño 100% responsivo** con media queries personalizadas.

---

## 🧠 Instrucciones para Gemini Code Assist
1. **Analiza el código existente del proyecto** y mantén compatibilidad con la estructura actual.
2. **Implementa los nuevos elementos visuales:** carouseles, ventanas modales, iconos y animaciones.
3. **Optimiza el CSS y el JavaScript** para un rendimiento fluido.
4. **Asegura compatibilidad total con móviles y tablets.**
5. **Crea un documento `progress.md`** que registre cada avance y modificación (fecha, archivo, descripción del cambio).
6. **Aplica buenas prácticas:**
   - Código modular y limpio.
   - Comentarios claros.
   - Separación frontend/backend.
   - Validación de formularios (cliente y servidor).

---

## 📘 Archivo `progress.md`
Debe contener el registro cronológico de todos los avances del proyecto.

**Ejemplo:**
```
### 21/10/2025 - Actualización visual
- Añadido carousel en index.html con tres slides.
- Implementada animación fade-in para la sección de destinos.
- Corregido bug de navbar en dispositivos móviles.
```

---

## 🧭 Fases del desarrollo (MVP)
| Sprint | Tareas | Objetivo |
|---------|---------|----------|
| Sprint 0 | Estructura de carpetas y conexión a BD | Base del proyecto y entorno local |
| Sprint 1 | Backend (PHP + MySQL) | CRUD de destinos, itinerarios y usuarios |
| Sprint 2 | Frontend (HTML + CSS + JS) | Diseño responsivo, animaciones y pruebas |
| Sprint 3 | Optimización y presentación | Ajustes finales, test y demo para el hackathon |

---

## 🧩 Resultado esperado
Un **MVP funcional, moderno y atractivo**, listo para presentación en hackathon: 
- Totalmente navegable.
- Compatible con móvil, tablet y PC.
- Código limpio y documentado.
- Con diseño tropical, moderno y profesional.

---

## 🏁 Llamado final para Gemini
> "Genera la web completa de GQ-Turismo siguiendo todas las instrucciones anteriores. Usa PHP con conexión MySQL, estilos modernos con Bootstrap, animaciones suaves y una experiencia de usuario fluida. Asegúrate de mantener un diseño visualmente llamativo, tropical y completamente responsivo."