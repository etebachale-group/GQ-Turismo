# GQ-TURISMO · PLAN MAESTRO Y PROTOCOLO DE EJECUCIÓN PARA ANTIGRAVITY

**Versión:** 2.0 (consolidada)
**Fecha de referencia:** 31/10/2026
**Repositorio local:** `C:\xampp\htdocs\GQ-Turismo` (Apache 2.4, PHP 8.2, MySQL, XAMPP)
**Repositorio remoto:** `https://github.com/etebachale-group/GQ-Turismo` (rama `main`)
**Agente ejecutor:** Antigravity
**Este documento reemplaza** a los siguientes archivos anteriores: `PLAN_MODERNIZACION_GQ_TURISMO.md`, `PLAN_REDISENO_GQ_TURISMO_GE.md`, `gq-design-tokens.css`, y todo el contenido de `evisa-integracion/`. Si hay contradicción entre este documento y los anteriores, **manda este**.

---

## ÍNDICE

- **0.** Instrucciones para Antigravity (leer primero)
- **1.** Alcance y decisiones vigentes
- **2.** Diagnóstico de partida y estado de lo declarado
- **3.** Arquitectura, identidad y modelo de datos
- **4.** Fases y tickets
- **5.** Flujo de trabajo, Definición de Hecho y prompts
- **6.** Riesgos y preguntas abiertas
- **7.** Checklist de salida
- **Anexo A.** Código de e-Visa (botones de redirección)
- **Anexo B.** Tokens de diseño CSS (`gq-design-tokens.css`)
- **Anexo C.** Comandos de verificación de hallazgos
- **Anexo D.** `AGENTS.md` para la raíz del repositorio

---

## 0. INSTRUCCIONES PARA ANTIGRAVITY (LEER PRIMERO)

### 0.1 Tu misión

Modernizar y rediseñar GQ-Turismo, una plataforma de turismo para Guinea Ecuatorial. Hay cuatro frentes que deben avanzar en orden:

1. **Contener** los riesgos de seguridad que ya se conocen.
2. **Corregir** los hallazgos críticos del código actual.
3. **Rediseñar** la interfaz completa con el sistema de diseño de este documento.
4. **Adaptar** el flujo de negocio al mercado local: pagos en efectivo y transferencia, idioma y botones de redirección al portal oficial de e-Visa.

No tienes permiso para ampliar el alcance, para introducir dependencias nuevas sin registrar una decisión, ni para reemplazar la arquitectura sin una decisión aprobada (ver D-H en la sección 1).

### 0.2 Reglas no negociables

1. **Ningún secreto en el código ni en el repositorio.** Contraseñas, claves y tokens van en `.env`, que nunca se versiona.
2. **Ningún precio viene del cliente.** El monto se calcula en servidor a partir de la tabla de tarifas.
3. **Ninguna consulta SQL concatena entrada del usuario.** Usa consultas preparadas.
4. **Toda acción que modifica datos** exige POST, token CSRF y verificación de permisos. Nunca GET.
5. **Ningún endpoint público acepta `tipo_usuario` ni `rol`.** El registro público siempre crea un turista.
6. **Ninguna subida de archivo** pasa sin validación de extensión, MIME real (`finfo`), `getimagesize()` y renombrado aleatorio.
7. **La identidad de sesión y de chat es `users.id`.** Un proveedor es `proveedores.id` y se relaciona con `users.id` por `user_id`. Nunca compares uno con el otro.
8. **Ningún color, tamaño de fuente o espacio fijo fuera de los tokens CSS.**
9. **Ningún texto fijo en las vistas.** Todo va a archivos de idioma.
10. **Ninguna URL oficial se escribe a mano en una vista.** Se toma de `config/fuentes_oficiales.php` por ID.
11. **No modifiques el código legado** salvo tickets `H-*`.
12. **No borres datos** sin respaldo previo y sin registro en `docs/`.
13. **No declares un hallazgo cerrado sin evidencia.** Usa los comandos del Anexo C y pega la salida en el reporte.

### 0.3 Antes de tocar código

Ejecuta esta secuencia y guarda la salida en `docs/discovery/arranque.md`:

```powershell
cd C:\xampp\htdocs\GQ-Turismo
git status
git branch --show-current
git log --oneline -n 20
php -v
```

Luego:

1. Crea una rama de trabajo nueva desde `main`: `git checkout -b chore/arranque-antigravity`.
2. Ejecuta los comandos del **Anexo C** y registra cada resultado.
3. Confirma la lista de archivos PHP y CSS enlazados desde `header.php` y las vistas.
4. No avances a la Fase 1 hasta que el reporte de arranque esté completo.

### 0.4 Cómo reportar cada ticket

Al terminar cada ticket entrega un reporte con esta forma:

```markdown
## Reporte [ID del ticket]

**Estado:** Hecho / Parcial / Bloqueado
**Rama:** feat/... o fix/...
**Archivos cambiados:** lista
**Criterios de aceptación:**
- [x] CA-1 ... (evidencia: comando o captura)
- [ ] CA-2 ... (motivo si no se cumple)
**Pruebas ejecutadas:** comandos y resultado (salida pegada)
**Hallazgos nuevos fuera de alcance:** lista (se registran como tickets nuevos)
**Riesgos o dudas:** lista
```

### 0.5 Cuándo parar y preguntar

Detente y pregunta si:

- Un criterio de aceptación contradice una regla de la sección 0.2.
- Necesitas una decisión marcada como pendiente en la sección 1.
- Una prueba falla y no encuentras la causa en un tiempo razonable.
- Vas a eliminar datos, tablas o archivos del servidor.
- Un cambio afecta a pagos reales o a datos personales.

En ese caso escribe la pregunta concreta en el reporte y deja el ticket en estado **Bloqueado**. No asumas.

---

## 1. ALCANCE Y DECISIONES VIGENTES

### 1.1 Decidido

| Tema | Decisión |
|---|---|
| Entorno | Trabajo local en XAMPP hasta nuevo aviso. |
| Objetivo | Modernización completa, rediseño total de la interfaz y adaptación al mercado de Guinea Ecuatorial. |
| Credenciales | No bloquean el desarrollo local. **Sí bloquean cualquier publicación en internet**: ver la puerta P-0 de la sección 4. |
| e-Visa | Solo **botones de redirección** al portal oficial. La plataforma no recoge datos de pasaporte, no cobra el trámite y no ofrece checklist de visado en esta fase. |
| Pagos | Efectivo al proveedor y transferencia bancaria con referencia son las vías principales. La tarjeta es opcional. |
| Hosting de producción | VPS o PaaS. El hosting compartido gratuito no sirve (no tiene SSH, Composer, colas ni WebSockets). |

### 1.2 Pendiente, con valor por defecto aplicado

Si el responsable no decide, Antigravity usa el valor por defecto. Cada uno se registra en `docs/decisiones/`.

| ID | Pregunta | Valor por defecto aplicado |
|---|---|---|
| D-A | Moneda | FCFA (XAF) para todos los precios. Equivalente en euros como referencia secundaria. El franco CFA está vinculado al euro a tasa fija de 655,957 FCFA por euro. |
| D-B | Medios de pago | Efectivo al proveedor y transferencia con código de referencia. Tarjeta solo si el proveedor la tiene habilitada. Dinero móvil desactivado. |
| D-C | Idiomas | Español (por defecto), francés, portugués e inglés. Las cuatro lenguas son oficiales o de uso relevante en el país y entre visitantes. |
| D-D | Hosting | Pendiente. No bloquea la fase local. |
| D-E | Lanzamiento | Reemplazo del sitio anterior con redirecciones 301. |
| D-F | Flujo de reserva | Reserva → confirmación del proveedor → cobro (efectivo, transferencia o tarjeta según disponibilidad). |
| D-G | Dinero móvil | **Pendiente de verificación.** Las fuentes consultadas se contradicen: algunas mencionan Orange Money; otras afirman que no hay sistema de dinero móvil establecido. No se implementa hasta confirmarlo con un operador. |
| D-H | Arquitectura | **Continuar con el PHP modular actual** (PSR-4, ya iniciado). No reescribir en Laravel durante la fase local. Si se decide Laravel, se registra un ADR y se replantean las fases 3 a 6. |
| D-V | Enlace del botón de e-Visa | Ver sección 1.3. |
| D-N | Nombre y logotipo | Se mantienen GQ-Turismo y el logotipo actual. |

### 1.3 Enlace de e-Visa (estado y regla)

- **Fuente oficial confirmada por comunicados de lanzamiento (julio de 2023):** `https://equatorialguinea-evisa.com/`. El servicio lo opera VFS Global como socio técnico del Gobierno.
- **Enlace entregado por el responsable del proyecto:** `https://egonline.vfsevisa.com/gnq/en/on-boarding`. Pertenece al sistema de VFS, pero **no pudo abrirse para verificación** desde el entorno de análisis (el sitio bloqueó el acceso automatizado).
- **Regla hasta completar VI-04:** el botón usa el enlace entregado (`evisa_gnq` en la configuración). El respaldo `evisa_gnq_referencia` queda desactivado.
- **Regla para la URL:** nunca se guarda con parámetros de seguimiento (`_gl`, `_ga`, `_gcl_au`, `utm_*`). La validación lo rechaza.
- **Tiempo de procesamiento y tasas:** las fuentes de 2023 hablan de unos cinco días laborables. No se muestra ninguna cifra de tasa ni de plazo hasta confirmarla en el portal.
- **Requisito de fiebre amarilla:** las fuentes lo citan como obligatorio. No se incluye en la pantalla de botones; si se añade, debe verificarse antes.

---

## 2. DIAGNÓSTICO DE PARTIDA Y ESTADO DE LO DECLARADO

### 2.1 Resumen de la auditoría original

Calificación global D (2.2/10). Hallazgos críticos:

1. Credenciales de base de datos en texto plano en `includes/db_connect.php`, y dump completo `if0_39858318_gq_turismo.sql` en el repositorio y en el webroot (incluye datos personales y hashes de administradores).
2. El registro público acepta `tipo_usuario=super_admin` (`api/auth.php`).
3. Subida de archivos sin validación en cinco archivos `admin/manage_*.php` (ejecución remota de código).
4. Consultas SQL concatenadas en `api/itinerarios.php`, `api/destinos.php` y `api/itinerario_tracking.php`.
5. Precios enviados desde el cliente en `api/pedidos.php`.
6. Eliminación de usuarios por GET sin CSRF en `admin/manage_users.php`.
7. Código de sesión huérfano (`includes/session_security.php`).

Hallazgos de arquitectura: tablas duplicadas (`locales_turisticos` / `lugares_locales`, `carouseles` / `publicidades` / `publicidad_carousel`, `confirmacion_servicios` / `confirmaciones_servicios`, tres tablas de guía-destino, pedidos y reservas paralelos), 46 scripts `fix_*.sql` sin migraciones, diez hojas CSS móviles superpuestas, archivos de prueba en el webroot, y el bug de identidad proveedor/usuario en el chat.

### 2.2 Estado de lo declarado en el informe de autopilot

El informe de autopilot afirma varios cierres. **Ninguno está verificado todavía.** Trátalos como hipótesis hasta que el Anexo C lo demuestre.

| Afirmación | Estado | Cómo verificar | Riesgo si es falsa |
|---|---|---|---|
| Credenciales movidas a `.env` | No verificado | Anexo C, bloque 1 | Alto |
| Registro ignora `tipo_usuario` | No verificado | Anexo C, bloque 4 | Crítico |
| Consultas preparadas en itinerarios, destinos y tracking | Parcial | Anexo C, bloque 3 | Alto |
| Precio calculado en servidor en `pedidos.php` | No verificado | Leer el archivo y probar un pedido con precio alterado | Crítico |
| CSRF en eliminación de usuarios | No verificado | Anexo C, bloque 6 | Alto |
| `FileUpload` implementado | Existe la clase; **no está aplicada** en los cinco `manage_*` | Anexo C, bloque 5 | Crítico |
| Chat corregido ("bug de identidad") | **Parche**: busca por dos identificadores. No es el modelo canónico. Hay que reemplazarlo. | Sección 3.2 | Alto |
| Pagos "con capa de abstracción" | La interfaz existe, pero `ManualPaymentGateway` **simula** el pago. No hay pasarela real ni webhooks. | Leer `pagar.php` | Crítico |
| CSS móvil consolidado | Se creó `gq-modern-mobile.css`; **las diez hojas antiguas pueden seguir enlazadas** | Anexo C, bloque 7 | Medio |
| 7/7 pruebas pasan | Las pruebas son superficiales: verifican clases y una respuesta HTTP. No cubren autorización, subidas, precios ni concurrencia. Además, si `.env` apunta a una base real, el runner **escribe en esa base**. | Leer `bin/test_runner.php` y `.env` | Alto |
| Roles: panel administrativo solo para roles de gestión | `admin_header.php` deja pasar a agencia, guía y local al panel de administración. Los proveedores deben tener su propio panel. | Leer `admin_header.php` | Alto |

**Regla:** un hallazgo solo se marca como cerrado con evidencia de los comandos del Anexo C o de una prueba que falla antes del cambio y pasa después.

---

## 3. ARQUITECTURA, IDENTIDAD Y MODELO DE DATOS

### 3.1 Arquitectura (según D-H)

- **Base:** PHP 8.2 modular, espacio de nombres `App\` mapeado a `app/` con PSR-4 (ya existe `composer.json`).
- **Base de datos:** MySQL 8, `utf8mb4` / `utf8mb4_unicode_ci` en todas las tablas.
- **Configuración:** `.env` leído por un cargador único. Ningún archivo de `config/` contiene secretos.
- **Sesión y seguridad:** un solo punto de entrada que llama a `require_login()` y a la verificación de rol. Ninguna página lee `$_SESSION` directamente.
- **CSRF:** token en sesión, campo oculto en cada formulario POST, verificación en cada acción que modifica datos.
- **Subidas:** servicio único `FileUpload` que valida, renombra y reencodifica a WebP.
- **Páginas públicas:** PHP con HTML renderizado en servidor (indexables, SEO).
- **Frontend:** CSS con tokens (`gq-design-tokens.css`). Sin frameworks adicionales en esta fase.

Si D-H cambia a Laravel, este apartado se reescribe con un ADR.

### 3.2 Identidad (regla canónica)

| Concepto | Tabla | Clave | Uso |
|---|---|---|---|
| Quién inicia sesión | `usuarios` (o `users`) | `id` | Sesión, chat (`conversacion_participantes.user_id`, `mensajes.sender_user_id`), permisos |
| Qué negocio ofrece servicios | `proveedores` | `id`, `user_id` → `usuarios.id` | Servicios, tarifas, confirmaciones, panel de proveedor |

Consecuencias:
- Ninguna consulta compara `usuarios.id` con `proveedores.id` ni con `agencias.id`.
- El chat se consulta siempre por `usuarios.id`. El nombre a mostrar se obtiene de un `JOIN` explícito con `proveedores` solo cuando el participante es proveedor.
- La tabla `agencias`, `guias` y `locales` se consolidan en `proveedores` con un campo `tipo` (`agencia`, `guia`, `local`).

### 3.3 Roles

| Rol | Panel | Puede | No puede |
|---|---|---|---|
| `turista` | Turista | Explorar, itinerarios, reservar, pagar, chatear con proveedores de sus reservas, reseñar | Ver datos de otros turistas; publicar servicios |
| `proveedor` (agencia, guía, local) | Proveedor | Gestionar su perfil, servicios, tarifas y disponibilidad; confirmar reservas; ver sus ventas | Ver pedidos de otros; cambiar su propio estado de aprobación |
| `administrador` | Administración | Aprobar proveedores; gestionar catálogo, pagos, publicidad, contenido, reseñas | Crear otros administradores |
| `super_administrador` | Administración | Todo lo anterior; gestionar roles | — |

El registro público crea siempre `turista`. Un proveedor solicita su rol con datos y documentos, y queda `pendiente` hasta aprobación.

### 3.4 Tablas canónicas (destino)

| Dominio | Tablas | Notas |
|---|---|---|
| Identidad | `usuarios`, `roles`, `proveedores`, `proveedor_documentos`, `auditoria` | `proveedores.estado`: pendiente / aprobado / suspendido |
| Destinos | `destinos`, `destino_imagenes`, `categorias`, `destino_categoria` | `slug` único; `lat`, `lng` |
| Negocios | `negocios` | Reemplaza `locales_turisticos` y `lugares_locales` |
| Catálogo | `servicios`, `servicio_tarifas`, `servicio_disponibilidad` | El precio sale solo de `servicio_tarifas` vigente |
| Itinerarios | `itinerarios`, `itinerario_dias`, `itinerario_items`, `itinerario_eventos` | Ítem referencia `servicio_id` o `destino_id` |
| Pedidos | `pedidos`, `pedido_items` | Precio **congelado** en `pedido_items` al crear el pedido |
| Confirmaciones | `confirmaciones_servicio` | Reemplaza las dos tablas de confirmación |
| Pagos | `pagos`, `pago_eventos` | `idempotency_key` única |
| Chat | `conversaciones`, `conversacion_participantes`, `mensajes` | Participantes siempre `usuarios.id` |
| Reseñas | `resenas` | Solo para ítems completados; una por ítem |
| Publicidad | `publicidades`, `publicidad_eventos` | Una sola tabla con `ubicacion` |

**Reglas de integridad:** claves foráneas con `ON DELETE` explícito; sin borrado físico de pedidos ni pagos; `DECIMAL(10,2)` para montos, con moneda explícita; soft delete en destinos, negocios, servicios, pedidos y usuarios.

### 3.5 Estados del pedido

`borrador` → `pendiente_confirmacion` → `confirmado` → `pagado` → `en_curso` → `completado`. Desde cualquiera puede pasar a `cancelado` (con política) y, desde `pagado`, a `reembolsado`. Las transiciones se controlan en una sola clase; una transición inválida lanza excepción.

---

## 4. FASES Y TICKETS

Convención de IDs: `P` puerta, `C` contención, `H` hotfix, `D` descubrimiento, `F` fundaciones, `DS` diseño, `CMP` componentes, `UX` pantallas, `VI` e-Visa, `M` módulos, `PG` pagos, `I18N` idiomas, `Q` calidad, `O` operaciones.

### PUERTA P-0 · Antes de cualquier publicación en internet

No se publica nada en un dominio público hasta cumplir **todo** esto. Puedes trabajar en local sin cumplirlo.

- [ ] C-01 · Rotar la contraseña de la base de datos de producción y de cualquier usuario que la use.
- [ ] C-02 · Eliminar `if0_39858318_gq_turismo.sql` del árbol de trabajo y **reescribir el historial** con `git filter-repo` (o BFG) para quitar el archivo y las credenciales de todos los commits. Luego `git push --force` a una rama de respaldo primero y, tras aprobación humana, a `main`.
- [ ] C-03 · Hacer el repositorio privado. Revisar colaboradores y claves de despliegue.
- [ ] C-04 · Revisar `usuarios` con rol de administrador creados recientemente o con cambios de rol no explicados. Cambiar la contraseña de todos los administradores.
- [ ] C-05 · Rotar cualquier otro secreto que haya estado en el repositorio.

**Evidencia:** `git log --all -S "<la contraseña>"` sin resultados; el archivo `.sql` no responde por URL; informe escrito en `docs/seguridad/contencion.md`.

> Nota para el responsable: la contraseña `r8HgmKsHZRU` aparece en la auditoría y en el historial. Se considera comprometida.

### FASE 1 · Hotfixes del legado (semana 1)

Parches de contención en el código actual. Cada uno se verifica con el Anexo C antes de tocarse.

| Ticket | Hallazgo | Alcance | Criterios de aceptación |
|---|---|---|---|
| H-01 | Registro acepta `super_admin` | `api/auth.php`: ignorar `tipo_usuario` del POST; forzar `turista` | Un POST con `tipo_usuario=super_admin` crea un `turista` |
| H-02 | RCE por subida | Los cinco `admin/manage_*.php`: usar `FileUpload`. Crear `assets/img/.htaccess` que impida ejecutar PHP | Subir un `.php` renombrado a `.jpg` no ejecuta; un `.php` real es rechazado |
| H-03 | SQL concatenado | `api/itinerarios.php`, `api/destinos.php`, `api/itinerario_tracking.php`: consultas preparadas y `(int)` en IDs | Ninguna llamada `query()` contiene variables de entrada |
| H-04 | Precios del cliente | `api/pedidos.php`: ignorar `precio_unitario` y `precio_total` del body; calcular desde `servicio_tarifas` | Un pedido con precio alterado se guarda con el precio real |
| H-05 | Eliminación por GET | `admin/manage_users.php`: POST + CSRF + verificación de rol | Un GET a `?action=delete` no elimina nada |
| H-06 | Credenciales en código | `includes/db_connect.php` lee `.env`. Añadir `.env` a `.gitignore` | `db_connect.php` no contiene contraseñas |
| H-07 | Archivos de prueba en el webroot | Mover o eliminar `test_system*.php`, `verificar_sistema*.php`, `verify_system.php` | Ninguna de esas URLs responde 200 |
| H-08 | Sesión no aplicada | Incluir `session_security.php` en todos los puntos de entrada admin | Todo endpoint admin exige sesión y rol |

**Regresión mínima** (ejecutar tras cada H-*): registro y login de turista; crear y eliminar un destino con imagen; crear un pedido y comprobar total; enviar un mensaje de chat entre turista y proveedor.

### FASE 2 · Descubrimiento (semana 1 a 2)

| Ticket | Alcance | Entregable |
|---|---|---|
| D-01 | Inventario de todos los archivos PHP de `api/`, `admin/`, raíz e `includes/`: método, parámetros, tablas, uso | `docs/discovery/endpoints.csv` (estado: migrar / reemplazar / eliminar) |
| D-02 | Estructura de la base (`mysqldump --no-data`) y quién usa cada tabla | `docs/discovery/esquema-actual.sql`, `tablas.csv` |
| D-03 | Calidad de datos: conteos, huérfanos, `sender_id` inválidos, algoritmo de hash de contraseñas, imágenes faltantes | `docs/discovery/calidad-datos.md` |
| D-04 | Mapa de flujos actuales del turista, proveedor y admin | `docs/discovery/flujos-actuales.md` |
| D-05 | Validación del flujo con el responsable | `docs/decisiones/ADR-001-flujo-y-pagos.md` |
| D-06 | Decisiones de arquitectura (D-H, hosting) | `docs/decisiones/ADR-002-*.md` |
| D-07 | Inventario de medios: tamaño, referencias rotas | `docs/discovery/medios.md` |

**Crítico:** si D-03 encuentra hashes MD5, SHA1 o texto plano, esas cuentas se marcan para restablecer contraseña. **No se migran hashes inseguros.**

### FASE 3 · Fundaciones (semana 2 a 3)

| Ticket | Alcance | Criterios de aceptación |
|---|---|---|
| F-01 | Cargador de `.env` único (`app/Support/Env.php`). `.env.example` documentado. | Ninguna clase lee `getenv()` ni `$_ENV` directamente fuera del cargador |
| F-02 | Reemplazar `bin/test_runner.php` por una suite real (sección 5.5). Las pruebas usan una base de **pruebas separada**, definida en `.env.testing`. | La suite se niega a correr si el nombre de la base no termina en `_test` |
| F-03 | Autorización central: middleware `requerir_rol()` y matriz de permisos (sección 3.3). | Matriz de pruebas con al menos 30 casos (rol × acción) |
| F-04 | Servicio `FileUpload` completo: lista blanca, MIME con `finfo`, `getimagesize()`, nombre aleatorio con `random_bytes`, reencodificación a WebP, documentos privados fuera del webroot | Subir un `.php` disfrazado no deja un archivo ejecutable |
| F-05 | Cabecera y pie compartidos. Carga única de `gq-design-tokens.css` (Anexo B). Retirar las diez hojas móviles antiguas de todas las vistas. | `grep` de los nombres antiguos sin resultados en vistas |
| F-06 | Registro de fuentes oficiales `config/fuentes_oficiales.php` (sección 5.6 y Anexo A) | Ninguna URL oficial escrita a mano en vistas |
| F-07 | Logs estructurados y manejador de errores. Ningún error muestra trazas al usuario. | Un error de servidor muestra página genérica y registra el detalle |

### FASE 4 · Diseño y componentes (semana 2 a 4)

| Ticket | Alcance | Criterios de aceptación |
|---|---|---|
| DS-01 | Integrar tokens (Anexo B) | Ninguna vista usa color fijo; `grep -r "#[0-9A-Fa-f]\{3,6\}" vistas/` sin resultados fuera de tokens |
| DS-02 | Verificar contrastes de todos los pares de texto/fondo con una herramienta | Registro en `docs/diseno/contrastes.md`; todo AA |
| DS-03 | Autoalojar Fraunces e Inter en `assets/fonts/` | Ninguna petición a dominios de fuentes en la carga de página |
| DS-04 | Página `/styleguide` (solo entorno local) con todos los componentes y estados de la tabla 4.1 | Cada estado visible |
| CMP-01 | Botón, campo, tarjeta, chip, badge, alerta | Capturas por estado |
| CMP-02 | Tarjeta de servicio: precio en FCFA, equivalente en euros, cancelación, verificación | Formato de precio correcto para 0, 1 000 y 1 250 000 FCFA |
| CMP-03 | Estado de reserva con línea de progreso | Cada estado tiene texto e icono, no solo color |
| CMP-04 | Selector de fecha y personas con teclado | Se completa sin ratón |
| CMP-05 | Navegación inferior móvil (cinco accesos) y cabecera de escritorio | Acceso activo con `aria-current` |
| CMP-06 | Barra de estado sin conexión y modo ahorro de datos | Activos con `prefers-reduced-data` |

**Tabla 4.1 · Estados obligatorios**

| Componente | Estados |
|---|---|
| Botón | normal, hover, foco, activo, deshabilitado, cargando |
| Campo | normal, foco, error, deshabilitado, solo lectura |
| Tarjeta de servicio | normal, sin cupo, agotado, en oferta |
| Estado de reserva | solicitada, confirmada, pendiente de pago, pagada, en curso, completada, cancelada, reembolsada |
| Badge de confianza | verificado, pendiente, suspendido |
| Checkout | resumen, forma de pago, confirmación |

### FASE 5 · Pantallas (semana 4 a 6)

Cada pantalla: funciona en 360 px sin scroll horizontal, contraste AA, textos traducibles, estados vacío / cargando / error diseñados.

| Ticket | Pantalla | Contenido clave |
|---|---|---|
| UX-01 | Inicio | Búsqueda; accesos "Explorar Bioko", "Explorar Río Muni"; servicios destacados con precio en FCFA; franja de confianza (proveedores verificados, pago en efectivo o transferencia, soporte por WhatsApp); botón de e-Visa (VI-02) |
| UX-02 | Destino | Imagen, mejor época, cómo llegar; servicios cercanos con filtros; mapa bajo demanda; botón de e-Visa |
| UX-03 | Constructor de itinerario | Días en columnas (escritorio) o pestañas (móvil); subtotal por día; total con desglose; compartir y descargar PDF |
| UX-04 | Checkout | Resumen con fechas, personas y cancelación; forma de pago (efectivo, transferencia con código, tarjeta si aplica); total siempre visible |
| UX-05 | Mi reserva | Estado con progreso; próximo paso ("Falta comprobante de transferencia" / "Paga al guía el día de la salida"); chat y WhatsApp; botón de e-Visa |
| UX-06 | Panel de proveedor | Pendientes con "Aceptar" / "Rechazar" grandes; calendario de disponibilidad; registro de cobro |
| UX-07 | Confirmación de reserva | Código de reserva; comprobante descargable; botón de WhatsApp con el código prellenado; botón de e-Visa |

### FASE 6 · e-Visa: botones de redirección (semana 4, en paralelo)

Alcance: **solo botones** que llevan al portal oficial. Sin checklist, sin recolección de datos, sin cobro.

| Ticket | Alcance | Criterios de aceptación |
|---|---|---|
| VI-01 | Crear `config/fuentes_oficiales.php` (Anexo A) | Ninguna URL contiene `_gl`, `_ga`, `_gcl_au` ni `utm_`; cada fuente tiene `verificada_en` (null hasta VI-04) |
| VI-02 | Crear `includes/evisa.php` (Anexo A) | `boton_evisa($lang)` devuelve un enlace con `target="_blank"` y `rel="noopener noreferrer"`; si la fuente no es válida, devuelve cadena vacía |
| VI-03 | Colocar `boton_evisa()` en UX-01, UX-02, UX-05 y UX-07, y como enlace "Solicitar e-Visa" en la cabecera | Visible en las cuatro pantallas y en el menú |
| VI-04 | **Verificación manual** del enlace (ver 6.1) | Registro en `docs/diseno/verificacion-evisa.md` con fecha, persona, resultado y decisión sobre el enlace principal. Solo después se rellena `verificada_en` |
| VI-05 | Pruebas (Anexo A, `tests/evisa_test.php`) | Todas pasan con `php tests/evisa_test.php` |

**Aviso junto al botón (decisión del responsable):** el texto del botón deja claro que es un sitio externo oficial. No se añaden avisos largos, salvo que el responsable los pida.

### FASE 7 · Módulos núcleo (semana 4 a 9)

Cada módulo: migración reversible, modelo, servicio de dominio, autorización, pruebas, documentación breve en `docs/modulos/`.

| Ticket | Módulo | Criterios de aceptación clave |
|---|---|---|
| M-01 | Destinos | CRUD; slug único; galería ordenada; coordenadas; borrador/publicado; metadatos SEO y `sitemap.xml` |
| M-02 | Negocios | Un solo módulo que reemplaza `locales_turisticos` y `lugares_locales`; referencia por "punto de referencia" además de coordenadas |
| M-03 | Proveedores | Solicitud con datos y documentos privados; cola de aprobación; correo al aprobar o rechazar; registro en `auditoria` |
| M-04 | Servicios y tarifas | Tarifa vigente resuelta por una sola función; cambiar tarifa no altera pedidos existentes |
| M-05 | Disponibilidad | Cupo por fecha; bloqueo al crear pedido; liberación al cancelar o expirar; prueba de concurrencia (dos solicitudes por el último cupo, solo una gana) |
| M-06 | Itinerarios | Días y ítems ordenables; total calculado en servidor; enlace compartido de solo lectura con token aleatorio; sin datos personales expuestos |
| M-07 | Tracking del viaje | Estados `en_camino`, `llegado`, `completado`; historial en `itinerario_eventos`; reemplaza `api/itinerario_tracking.php` |
| M-08 | Pedidos | Carrito por usuario autenticado; precio congelado; máquina de estados (sección 3.5) |
| M-09 | Confirmación de proveedor | Aceptar o rechazar con motivo; rechazo libera cupo; sin respuesta en X horas se escala a administración |
| M-10 | Pagos (efectivo y transferencia) | Efectivo: el proveedor marca el cobro en su panel. Transferencia: instrucciones con código de referencia y carga de comprobante; el administrador verifica. **El monto se calcula en servidor; el cliente nunca envía un monto.** |
| M-11 | Tarjeta (opcional) | Interfaz `PaymentGateway` con webhooks firmados, `idempotency_key` y registro de cada evento. Solo si el proveedor la tiene habilitada. |
| M-12 | Chat | Conversación solo con pedido activo; participantes por `usuarios.id`; lectura; sondeo cada 5 s como base; sin ninguna consulta que compare identificadores de tablas distintas |
| M-13 | Reseñas | Solo ítems completados; una por ítem; moderación |
| M-14 | Publicidad | Una tabla; ubicación; vigencia; registro de impresiones y clics sin datos personales |
| M-15 | Administración | Recursos para aprobaciones, catálogo, pedidos, pagos, publicidad, reseñas, usuarios (cambio de rol solo a super admin) y `auditoria` (solo lectura). **Ninguna acción administrativa es un GET que modifica datos.** |
| M-16 | Paneles de rol | Proveedor: ventas, pedidos por confirmar, calendario, chats. Turista: viajes, reservas, pagos, reseñas pendientes. Prueba de acceso cruzado. |

**Dinero móvil:** no se implementa. Si D-G se cierra con un operador confirmado, se abre un ticket nuevo `PG-MM-01`.

### FASE 8 · Idiomas (semana 6)

| Ticket | Alcance | Criterios de aceptación |
|---|---|---|
| I18N-01 | Extraer todas las cadenas a `lang/es`, `lang/fr`, `lang/pt`, `lang/en` | Búsqueda de texto fijo en vistas sin resultados |
| I18N-02 | Prueba de expansión de texto (+30 %) | Ninguna pantalla rompe el diseño |
| I18N-03 | Revisión de traducciones por hablantes nativos | Aprobación firmada en `docs/i18n/aprobacion.md` |
| I18N-04 | Formatos locales: fechas `DD/MM/AAAA`; teléfono `+240`; precio `25 000 FCFA` (espacio como separador de miles, sin decimales) | Pruebas de formato |

### FASE 9 · Calidad, migración y lanzamiento (semana 9 a 12)

| Ticket | Alcance | Criterios de aceptación |
|---|---|---|
| MG-01 | Mapeo legado → canónico (anexo de tablas, completado con D-02) | Revisado y firmado |
| MG-02 | Scripts de migración idempotentes con `legacy_id` y tabla de correspondencias | Ejecutar dos veces no cambia conteos |
| MG-03 | Usuarios: `bcrypt` (`$2y$`) se migra; hashes inseguros requieren restablecimiento | Ningún usuario con hash inseguro entra sin restablecer |
| MG-04 | Imágenes: copiar, renombrar, WebP, correspondencia de rutas | Ninguna referencia rota |
| MG-05 | Reconciliación: conteos, sumas de pedidos y pagos, muestra de 50 registros | Diferencias de pedidos y pagos: cero |
| MG-06 | Tres ensayos completos en staging | Tres ensayos seguidos sin errores bloqueantes |
| Q-01 | E2E (Playwright): registro → itinerario → reserva → pago en efectivo → confirmación → chat → reseña | Pasa en Chrome, Firefox y Safari móvil emulado |
| Q-02 | Autorización: matriz rol × recurso × acción; acceso cruzado | Intentos no autorizados devuelven 403 o 404 |
| Q-03 | Seguridad: OWASP ZAP en staging; revisión de subidas, CSRF, XSS, IDOR; `composer audit` y `npm audit` | Cero hallazgos críticos o altos abiertos |
| Q-04 | Carga (k6): 200 usuarios navegando; 50 en checkout; 100 mensajes/s | p95 < 800 ms; sin 5xx en checkout |
| Q-05 | Accesibilidad: axe-core y revisión con teclado | Cero errores críticos |
| Q-06 | Rendimiento móvil: LCP < 2.5 s, CLS < 0.1 con 4G simulado; página de inicio < 300 KB sin imágenes diferidas | Medido y registrado |
| Q-07 | Usabilidad: 5 personas por perfil, móvil de gama media, 3G simulado | 80 % de tareas completadas sin ayuda |
| O-01 | Hosting de producción (D-D) | HTTPS válido; PHP 8.2+; MySQL 8; cron; copia de seguridad |
| O-02 | Respaldos diarios con retención de 30 días, copia fuera del servidor, prueba de restauración | Restauración verificada; RTO 2 h, RPO 15 min (objetivo) |
| O-03 | Monitoreo de disponibilidad y alertas | Caída simulada genera alerta en menos de 5 minutos |
| O-04 | Plan de corte: anunciar con 72 h; modo mantenimiento; respaldo final; migración final; reconciliación; cambio de DNS; redirecciones 301; prueba de humo | Si la reconciliación de pedidos o pagos no cuadra, **no se avanza** |

**Plan de reversión (O-04b):** si hay un error bloqueante en las primeras 48 h, se regresa al legado y se exportan los pedidos y pagos creados en la plataforma nueva para reconciliación manual.

---

## 5. FLUJO DE TRABAJO, DEFINICIÓN DE HECHO Y PROMPTS

### 5.1 Git

- `main` protegida. Se fusiona solo por Pull Request, con CI en verde y aprobación humana.
- Ramas: `fix/H-02-validacion-subida`, `feat/M-08-pedidos`, `feat/VI-02-boton-evisa`, `style/DS-01-tokens`.
- Commits en Conventional Commits: `feat(pedidos): congelar precio al crear pedido`.
- Un ticket, un PR. Si el PR supera unas 400 líneas sin contar migraciones o archivos generados, se divide.
- Nunca `push --force` a `main` ni borrar ramas ajenas.

### 5.2 Definición de Hecho (todo ticket)

1. Todos los criterios de aceptación están verificados con evidencia.
2. Las pruebas del proyecto pasan.
3. Hay una prueba que falla antes del cambio y pasa después (en hotfixes y reglas de negocio).
4. No hay secretos, contraseñas, tokens ni datos personales en código, logs ni pruebas.
5. Ninguna consulta concatena entrada del usuario.
6. Toda acción que modifica datos usa POST, CSRF y verificación de permisos.
7. Las migraciones son reversibles y se probaron con una base limpia.
8. Textos en archivos de idioma; colores y tamaños en tokens.
9. Documentación breve actualizada en `docs/modulos/`.
10. El PR describe qué se probó y cómo reproducirlo.

### 5.3 Plantilla de ticket

```markdown
## [ID] Título

**Fase:** N · **Prioridad:** P0 / P1 / P2 · **Depende de:** [IDs]

### Objetivo
Una frase.

### Alcance
- Archivos, tablas y rutas afectadas.

### Criterios de aceptación
- [ ] CA-1 …
- [ ] CA-2 …

### Pruebas
- Unitarias / feature / E2E: …

### Fuera de alcance
- Lo que no se toca aunque se detecte.
```

### 5.4 Plantilla de Pull Request

```markdown
## Ticket
[ID] — enlace

## Qué cambia
- …

## Cómo se probó
- Comandos y resultado (salida pegada).

## Criterios de aceptación
- [x] CA-1 (evidencia)

## Riesgos y reversión
- …

## Capturas (si hay cambios visuales)
- 360 px / 768 px / 1440 px
```

### 5.5 Suite de pruebas (reemplazo de `bin/test_runner.php`)

Requisitos mínimos:
- Base de datos de pruebas separada. Si el nombre no termina en `_test`, la suite no arranca.
- Cada prueba crea sus datos y los limpia al final, o usa transacciones con reversión.
- Cubre como mínimo: registro sin escalada de rol; subida maliciosa rechazada; precio ignorado del cliente; eliminación solo por POST con CSRF; acceso cruzado a pedidos y mensajes denegado; cupo no sobrevendido con dos solicitudes simultáneas; webhook duplicado sin doble cobro (si aplica).

### 5.6 Fuentes oficiales

Ningún enlace oficial se escribe en una vista. Todos viven en `config/fuentes_oficiales.php` (Anexo A) y se obtienen por ID con la función `boton_evisa()` o `fuente_oficial()`.

### 5.7 Prompts para Antigravity

**Arranque (una sola vez):**
```
Lee GQ-TURISMO_PLAN_MAESTRO_ANTIGRAVITY.md completo, incluida la sección 0.
Confirma en tus palabras las reglas de la sección 0.2 y la regla de identidad de la sección 3.2.
Luego ejecuta la sección 0.3 y guarda el resultado en docs/discovery/arranque.md.
No modifiques código todavía. Entrega el reporte de arranque.
```

**Hotfix H-*:**
```
Ticket [H-XX]. Antes de tocar código:
1. Localiza la línea exacta del hallazgo en el archivo indicado.
2. Reproduce el problema y muestra la salida.
3. Escribe una prueba que demuestre la vulnerabilidad.
4. Corrige con el mínimo cambio.
5. Muestra que la prueba ahora falla de forma controlada o no es explotable.
No refactorices nada fuera del alcance.
```

**Módulo M-* o pantalla UX-*:**
```
Ticket [ID]. Sigue la sección 5.2.
Usa los nombres de tablas de la sección 3.4 y los estados de la sección 3.5.
Primero escribe las pruebas de los criterios de aceptación.
Implementa en 360 px primero. Usa solo tokens. Textos a lang/.
Entrega el reporte de la sección 0.4 con capturas.
```

**Revisión de un PR (otro agente o persona):**
```
Revisa este PR contra la sección 5.2 y los criterios del ticket.
Busca: SQL concatenado; precios aceptados del cliente; acciones GET que modifican datos;
ausencia de verificación de permisos; secretos en el diff; rutas que aceptan rol como parámetro;
colores fijos fuera de tokens; textos sin traducir; URLs oficiales escritas a mano.
Responde con: aprobado, cambios requeridos (lista numerada) o bloqueado (motivo).
```

---

## 6. RIESGOS Y PREGUNTAS ABIERTAS

### 6.1 Verificación del enlace de e-Visa (VI-04)

Responsable: persona con acceso a un navegador normal, fuera del entorno de desarrollo.

1. Abrir `https://egonline.vfsevisa.com/gnq/en/on-boarding` y anotar: si carga, si redirige, qué dominio muestra al final, si el formulario corresponde a la solicitud de e-Visa de Guinea Ecuatorial.
2. Abrir `https://equatorialguinea-evisa.com/` y comparar.
3. Comprobar si existe la versión en español (`/gnq/es/on-boarding`) y si es el mismo trámite.
4. Anotar la tasa mostrada, sin incluirla en la plataforma.
5. Registrar el resultado y decidir cuál enlace es el principal. Si el principal cambia, actualizar `config/fuentes_oficiales.php` y registrar la fecha en `verificada_en`.

Hasta completar este paso, el botón muestra el enlace entregado y la plataforma no debe presentarlo como verificado.

### 6.2 Tabla de riesgos

| Riesgo | Prob. | Impacto | Mitigación |
|---|:---:|:---:|---|
| Credenciales comprometidas usadas por terceros | Alta (se asume) | Crítico | Puerta P-0; revisión de accesos; rotación |
| Pérdida de pedidos o pagos al migrar | Media | Crítico | Tres ensayos; reconciliación; legado en solo lectura 30 días |
| Usuarios migrados no pueden entrar | Media | Alto | Migrar hashes compatibles; restablecimiento guiado |
| Pérdida de posicionamiento | Media | Alto | Redirecciones 301; sitemap nuevo |
| Alcance crece sin control | Alta | Alto | Un ticket, un PR; "fuera de alcance" explícito |
| Cobros duplicados o no registrados | Media | Crítico | Idempotencia; registro de eventos; efectivo y transferencia como vía principal |
| Usuarios pagan a intermediarios o sitios falsos por el visado | Alta | Alto | Botón al portal oficial; la plataforma no cobra el trámite |
| El enlace oficial cambia | Media | Medio | Registro central con fecha; revisión periódica |
| Dependencia del agente sin revisión humana | Media | Alto | Nada se fusiona sin aprobación; Definición de Hecho |
| Datos caros o conexión inestable | Alta | Alto | Presupuesto de rendimiento; offline en itinerarios |
| Hosting insuficiente | Media | Medio | Decisión D-D antes de publicar |

### 6.3 Preguntas abiertas para el responsable

1. **D-G, dinero móvil:** ¿hay un operador o banco local que confirme cobros en línea? ¿Cuál?
2. **Público principal del lanzamiento:** turistas internacionales, diáspora y residentes, o negocios. Define el orden de las pantallas.
3. **Enlace de e-Visa:** ¿quieres que el botón use el enlace entregado o el dominio `equatorialguinea-evisa.com`? (Ver 1.3.)
4. **Hosting (D-D):** ¿VPS, PaaS o gestión por terceros?
5. **Logotipo:** ¿se mantiene o se rediseña?

---

## 7. CHECKLIST DE SALIDA

### 7.1 Para trabajar en local (cierre de cada fase)

- [ ] Reporte de arranque entregado (0.3)
- [ ] Todos los tickets de la fase con reporte (0.4)
- [ ] Suite de pruebas en verde con base de pruebas separada
- [ ] Ninguna URL de vista con dominio oficial escrito a mano
- [ ] Ningún color o tamaño fijo fuera de tokens
- [ ] Textos en archivos de idioma

### 7.2 Para publicar en internet (puerta P-0 y lanzamiento)

- [ ] C-01 a C-05 cerrados con evidencia
- [ ] VI-04 completado y registrado
- [ ] Q-03 sin hallazgos críticos ni altos abiertos
- [ ] MG-05 aprobado (si hay datos legados)
- [ ] Q-01 y Q-07 aprobados
- [ ] Respaldo restaurado con éxito (O-02)
- [ ] Monitoreo activo (O-03)
- [ ] Términos de uso, privacidad y cancelación publicados en las lenguas activas
- [ ] Decisiones D-A, D-B, D-C, D-D, D-F y D-H registradas en `docs/decisiones/`

---

## ANEXO A · CÓDIGO DE e-VISA (BOTONES DE REDIRECCIÓN)

Tres archivos. Copiarlos exactamente a las rutas indicadas.

### A.1 `config/fuentes_oficiales.php`

```php
<?php
/**
 * Registro central de enlaces oficiales del trámite de e-Visa.
 * - Las vistas NUNCA escriben estas URLs; usan boton_evisa() o fuente_oficial().
 * - Sin parámetros de seguimiento (?, _ga, _gl, utm_...).
 * - 'verificada_en' se rellena (AAAA-MM-DD) solo tras VI-04. Mientras sea null,
 *   el responsable debe saber que el enlace no está verificado.
 * - 'activo' => false oculta la fuente sin borrarla.
 */

return [

    // Enlace entregado por el responsable (portal de onboarding de VFS).
    'evisa_gnq' => [
        'nombre'        => 'Solicitud de e-Visa de Guinea Ecuatorial (portal oficial)',
        'tipo'          => 'oficial',
        'activo'        => true,
        'verificada_en' => null,
        'responsable'   => 'equipo-contenido',
        'urls' => [
            // La versión en español (/gnq/es/on-boarding) se activa solo tras VI-04.
            'es' => 'https://egonline.vfsevisa.com/gnq/en/on-boarding',
            'en' => 'https://egonline.vfsevisa.com/gnq/en/on-boarding',
            'fr' => 'https://egonline.vfsevisa.com/gnq/en/on-boarding',
            'pt' => 'https://egonline.vfsevisa.com/gnq/en/on-boarding',
        ],
    ],

    // Portal citado en el comunicado de lanzamiento (2023). Respaldo, inactivo
    // hasta comprobarlo en VI-04.
    'evisa_gnq_referencia' => [
        'nombre'        => 'Portal de e-Visa de Guinea Ecuatorial (referencia)',
        'tipo'          => 'oficial',
        'activo'        => false,
        'verificada_en' => null,
        'responsable'   => 'equipo-contenido',
        'urls' => [
            'es' => 'https://equatorialguinea-evisa.com/',
            'en' => 'https://equatorialguinea-evisa.com/',
            'fr' => 'https://equatorialguinea-evisa.com/',
            'pt' => 'https://equatorialguinea-evisa.com/',
        ],
    ],
];
```

### A.2 `includes/evisa.php`

```php
<?php
declare(strict_types=1);

/**
 * Botón que redirige al portal oficial de e-Visa.
 *
 * Uso:
 *   require_once __DIR__ . '/includes/evisa.php';
 *   echo boton_evisa($lang);
 *   echo boton_evisa($lang, 'btn btn--outline btn--block');
 */

const GQ_HOSTS_OFICIALES = [
    'egonline.vfsevisa.com',
    'equatorialguinea-evisa.com',
];

const GQ_LANGS = ['es', 'fr', 'pt', 'en'];

const GQ_TEXTOS_BOTON = [
    'es' => [
        'boton'   => 'Solicitar mi e-Visa en el sitio oficial',
        'externo' => 'sitio externo oficial, se abre en otra pestaña',
    ],
    'en' => [
        'boton'   => 'Apply for my e-Visa on the official site',
        'externo' => 'external official site, opens in a new tab',
    ],
    'fr' => [
        'boton'   => 'Demander mon e-Visa sur le site officiel',
        'externo' => 'site officiel externe, s’ouvre dans un nouvel onglet',
    ],
    'pt' => [
        'boton'   => 'Solicitar o meu e-Visa no site oficial',
        'externo' => 'site oficial externo, abre num novo separador',
    ],
];

/** Escapado HTML. Nombre propio para no chocar con otras funciones del proyecto. */
function gq_h(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Idioma válido o español por defecto. */
function gq_lang(?string $lang): string
{
    return in_array($lang, GQ_LANGS, true) ? $lang : 'es';
}

/**
 * URL oficial válida: https, host de la lista blanca, sin query ni fragmento.
 */
function url_oficial_valida(string $url): bool
{
    $p = parse_url($url);
    if ($p === false) {
        return false;
    }
    if (($p['scheme'] ?? '') !== 'https') {
        return false;
    }
    if (!in_array($p['host'] ?? '', GQ_HOSTS_OFICIALES, true)) {
        return false;
    }
    return !isset($p['query']) && !isset($p['fragment']);
}

/**
 * URL de una fuente oficial por ID, o null si no existe, está inactiva
 * o no pasa la validación.
 */
function fuente_oficial(string $id, string $lang = 'es'): ?string
{
    static $fuentes = null;
    if ($fuentes === null) {
        $fuentes = require dirname(__DIR__) . '/config/fuentes_oficiales.php';
    }

    if (!isset($fuentes[$id]) || empty($fuentes[$id]['activo'])) {
        return null;
    }

    $lang = gq_lang($lang);
    $url = $fuentes[$id]['urls'][$lang] ?? $fuentes[$id]['urls']['es'] ?? null;

    return ($url !== null && url_oficial_valida($url)) ? $url : null;
}

/**
 * Botón HTML. Si la fuente no es válida, devuelve cadena vacía
 * para no mostrar nunca un enlace roto o no oficial.
 */
function boton_evisa(?string $lang = 'es', string $clase = 'btn btn--primary btn--block'): string
{
    $lang = gq_lang($lang);
    $url = fuente_oficial('evisa_gnq', $lang);

    if ($url === null) {
        return '';
    }

    $textos = GQ_TEXTOS_BOTON[$lang];

    return '<a class="' . gq_h($clase) . '"'
         . ' href="' . gq_h($url) . '"'
         . ' target="_blank" rel="noopener noreferrer">'
         . gq_h($textos['boton'])
         . '<span class="sr-only"> (' . gq_h($textos['externo']) . ')</span>'
         . '</a>';
}
```

### A.3 `tests/evisa_test.php`

```php
<?php
declare(strict_types=1);

/**
 * Ejecutar: php tests/evisa_test.php
 */

require_once __DIR__ . '/../includes/evisa.php';

$fallos = 0;
$ok = 0;

function afirmar(bool $condicion, string $descripcion): void
{
    global $fallos, $ok;
    if ($condicion) {
        $ok++;
        echo "[OK]    {$descripcion}\n";
    } else {
        $fallos++;
        echo "[FALLO] {$descripcion}\n";
    }
}

$fuentes = require __DIR__ . '/../config/fuentes_oficiales.php';

foreach ($fuentes as $id => $fuente) {
    foreach ($fuente['urls'] as $lang => $url) {
        afirmar(
            !preg_match('/[?&](_gl|_ga|_gcl_au|utm_[a-z_]+)=|[?#]/i', $url),
            "[{$id}:{$lang}] sin parámetros de seguimiento"
        );
        afirmar(url_oficial_valida($url), "[{$id}:{$lang}] URL oficial válida");
    }
}

afirmar(!url_oficial_valida('http://egonline.vfsevisa.com/gnq'), 'rechaza http');
afirmar(!url_oficial_valida('https://sitio-falso.com/evisa'), 'rechaza dominio ajeno');
afirmar(!url_oficial_valida('https://egonline.vfsevisa.com/gnq?_ga=1'), 'rechaza parámetros de seguimiento');
afirmar(!url_oficial_valida('https://egonline.vfsevisa.com/gnq#x'), 'rechaza fragmento');
afirmar(fuente_oficial('evisa_gnq', 'es') !== null, 'evisa_gnq resuelve');
afirmar(fuente_oficial('evisa_gnq_referencia', 'es') === null, 'fuente inactiva no se publica');
afirmar(fuente_oficial('no_existe', 'es') === null, 'ID inexistente devuelve null');

$html = boton_evisa('en');
afirmar(str_contains($html, 'target="_blank"'), 'botón abre en pestaña nueva');
afirmar(str_contains($html, 'rel="noopener noreferrer"'), 'botón incluye rel seguro');
afirmar(str_contains($html, 'href="https://egonline.vfsevisa.com/'), 'botón apunta al dominio de la lista blanca');
afirmar(gq_h('<b>') === '&lt;b&gt;', 'gq_h escapa HTML');
afirmar(gq_lang('<img>') === 'es', 'idioma no válido cae a español');

foreach (['es', 'en', 'fr', 'pt'] as $lang) {
    afirmar(str_contains(boton_evisa($lang), 'href="'), "botón disponible en [{$lang}]");
}

echo "\nResultado: {$ok} correctas, {$fallos} fallidas.\n";
exit($fallos === 0 ? 0 : 1);
```

### A.4 Uso en las pantallas

```php
<?php require_once __DIR__ . '/includes/evisa.php'; ?>
<?= boton_evisa($lang) ?>
```

Lugares obligatorios: cabecera (enlace "Solicitar e-Visa"), UX-01, UX-02, UX-05 y UX-07.

---

## ANEXO B · TOKENS DE DISEÑO (`assets/css/gq-design-tokens.css`)

Cargar como **primera** hoja de estilos. Reemplaza las variables de cualquier hoja anterior. Valores de contraste medidos sobre blanco (WCAG 2.1 AA); repetir la medición en DS-02.

```css
/* ==========================================================================
   GQ-TURISMO · Design tokens y base de componentes (v1.0)
   Cargar primero. Reemplaza las hojas móviles antiguas.
   ========================================================================== */

@import url("https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap");

:root {
  /* Color: marca */
  --c-selva-700: #17613A;
  --c-selva-600: #1E7B4A;      /* primario · 5.3:1 sobre blanco */
  --c-selva-100: #E3F1E9;

  --c-atlantico-700: #084A71;
  --c-atlantico-600: #0B5C8C;  /* secundario · 7.2:1 */
  --c-atlantico-100: #E1EEF6;

  --c-terracota-600: #B2452F;  /* acento y errores · 5.5:1 */
  --c-terracota-100: #F8E4DE;

  --c-arena-50:  #FBF7F0;
  --c-arena-100: #F3E9D8;
  --c-arena-300: #D9C7A6;

  /* Color: neutros y estados */
  --c-carbon-900: #1F2421;
  --c-carbon-700: #4A524D;     /* 8.6:1 */
  --c-carbon-500: #6F7872;     /* solo texto no crítico · 4.6:1 */
  --c-blanco: #FFFFFF;

  --c-exito: var(--c-selva-600);
  --c-aviso: #8A5300;          /* texto · 6.3:1 */
  --c-aviso-100: #FBF0DC;
  --c-error: var(--c-terracota-600);
  --c-error-100: var(--c-terracota-100);
  --c-info: var(--c-atlantico-600);
  --c-info-100: var(--c-atlantico-100);

  /* Semántica (usar estas en componentes) */
  --bg-page: var(--c-arena-50);
  --bg-surface: var(--c-blanco);
  --bg-muted: var(--c-arena-100);
  --text-primary: var(--c-carbon-900);
  --text-secondary: var(--c-carbon-700);
  --text-muted: var(--c-carbon-500);
  --text-on-brand: var(--c-blanco);
  --border-default: var(--c-arena-300);
  --brand-primary: var(--c-selva-600);
  --brand-primary-hover: var(--c-selva-700);
  --brand-secondary: var(--c-atlantico-600);
  --brand-accent: var(--c-terracota-600);
  --focus-ring: var(--c-atlantico-600);

  /* Tipografía */
  --font-display: "Fraunces", Georgia, "Times New Roman", serif;
  --font-body: "Inter", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;

  --fs-xs: 0.8125rem;
  --fs-sm: 0.9375rem;
  --fs-md: 1rem;
  --fs-lg: 1.125rem;
  --fs-xl: 1.25rem;
  --fs-2xl: clamp(1.5rem, 1.2rem + 1.2vw, 1.875rem);
  --fs-3xl: clamp(1.875rem, 1.4rem + 2vw, 2.5rem);
  --fs-4xl: clamp(2.25rem, 1.6rem + 3vw, 3.25rem);

  --lh-tight: 1.2;
  --lh-normal: 1.5;
  --lh-relaxed: 1.65;

  --fw-regular: 400;
  --fw-medium: 500;
  --fw-semibold: 600;
  --fw-bold: 700;

  /* Espaciado (base 4 px) */
  --s-1: 0.25rem;
  --s-2: 0.5rem;
  --s-3: 0.75rem;
  --s-4: 1rem;
  --s-5: 1.25rem;
  --s-6: 1.5rem;
  --s-8: 2rem;
  --s-10: 2.5rem;
  --s-12: 3rem;
  --s-16: 4rem;

  /* Forma y profundidad */
  --r-sm: 6px;
  --r-md: 10px;
  --r-lg: 16px;
  --r-pill: 999px;

  --shadow-1: 0 1px 2px rgba(31, 36, 33, 0.06), 0 1px 1px rgba(31, 36, 33, 0.04);
  --shadow-2: 0 4px 12px rgba(31, 36, 33, 0.08);
  --shadow-3: 0 12px 28px rgba(31, 36, 33, 0.12);

  /* Interacción */
  --touch-min: 44px;
  --ease-out: cubic-bezier(0.2, 0.7, 0.2, 1);
  --dur-fast: 150ms;
  --dur-base: 220ms;

  /* Layout */
  --container-max: 1200px;
  --gutter: var(--s-4);
  --header-h: 64px;
  --bottom-nav-h: 64px;
  --safe-top: env(safe-area-inset-top, 0px);
  --safe-bottom: env(safe-area-inset-bottom, 0px);

  color-scheme: light;
}

*, *::before, *::after { box-sizing: border-box; }

html {
  -webkit-text-size-adjust: 100%;
  scroll-padding-top: calc(var(--header-h) + var(--safe-top));
}

body {
  margin: 0;
  font-family: var(--font-body);
  font-size: var(--fs-md);
  line-height: var(--lh-normal);
  color: var(--text-primary);
  background: var(--bg-page);
  overflow-x: hidden;
  -webkit-font-smoothing: antialiased;
}

img, svg, video { max-width: 100%; height: auto; display: block; }

h1, h2, h3, h4 {
  font-family: var(--font-display);
  line-height: var(--lh-tight);
  color: var(--text-primary);
  margin: 0 0 var(--s-3);
  font-weight: var(--fw-semibold);
}
h1 { font-size: var(--fs-4xl); }
h2 { font-size: var(--fs-3xl); }
h3 { font-size: var(--fs-2xl); }
h4 { font-size: var(--fs-xl); font-family: var(--font-body); font-weight: var(--fw-bold); }

p { margin: 0 0 var(--s-4); color: var(--text-secondary); max-width: 68ch; }

a { color: var(--brand-secondary); text-underline-offset: 3px; }
a:hover { color: var(--c-atlantico-700); }

:focus-visible {
  outline: 3px solid var(--focus-ring);
  outline-offset: 2px;
  border-radius: var(--r-sm);
}

@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
    scroll-behavior: auto !important;
  }
}

/* Layout */
.container {
  width: 100%;
  max-width: var(--container-max);
  margin-inline: auto;
  padding-inline: var(--gutter);
}
.stack > * + * { margin-top: var(--s-4); }
.cluster { display: flex; flex-wrap: wrap; gap: var(--s-2); align-items: center; }
.grid-cards { display: grid; gap: var(--s-4); grid-template-columns: 1fr; }
@media (min-width: 640px)  { .grid-cards { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .grid-cards { grid-template-columns: repeat(3, 1fr); } }

/* Botones */
.btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: var(--s-2);
  min-height: var(--touch-min);
  padding: var(--s-3) var(--s-5);
  border: 2px solid transparent;
  border-radius: var(--r-md);
  font: inherit;
  font-weight: var(--fw-semibold);
  text-decoration: none;
  cursor: pointer;
  transition: background var(--dur-fast) var(--ease-out), color var(--dur-fast) var(--ease-out),
              border-color var(--dur-fast) var(--ease-out), transform var(--dur-fast) var(--ease-out);
}
.btn:active { transform: translateY(1px); }
.btn:disabled, .btn[aria-disabled="true"] { opacity: 0.5; cursor: not-allowed; }

.btn--primary { background: var(--brand-primary); color: var(--text-on-brand); }
.btn--primary:hover { background: var(--brand-primary-hover); color: var(--text-on-brand); }

.btn--secondary { background: var(--brand-secondary); color: var(--text-on-brand); }
.btn--secondary:hover { background: var(--c-atlantico-700); color: var(--text-on-brand); }

.btn--outline { background: transparent; border-color: var(--brand-primary); color: var(--c-selva-700); }
.btn--outline:hover { background: var(--c-selva-100); }

.btn--ghost { background: transparent; color: var(--c-atlantico-600); }
.btn--ghost:hover { background: var(--c-atlantico-100); }

.btn--whatsapp { background: #128C4A; color: var(--c-blanco); }
.btn--whatsapp:hover { background: #0E6E3A; color: var(--c-blanco); }

.btn--block { width: 100%; }

/* Formularios */
.field { display: grid; gap: var(--s-2); margin-bottom: var(--s-4); }
.field label { font-weight: var(--fw-semibold); color: var(--text-primary); font-size: var(--fs-sm); }
.field__hint { font-size: var(--fs-xs); color: var(--text-muted); }
.field__error { font-size: var(--fs-sm); color: var(--c-error); font-weight: var(--fw-medium); }

.input, .select, .textarea {
  width: 100%;
  min-height: var(--touch-min);
  padding: var(--s-3) var(--s-4);
  font: inherit;
  color: var(--text-primary);
  background: var(--bg-surface);
  border: 1.5px solid var(--border-default);
  border-radius: var(--r-md);
}
.textarea { min-height: 120px; resize: vertical; }
.input:focus, .select:focus, .textarea:focus {
  outline: 3px solid var(--focus-ring);
  outline-offset: 0;
  border-color: var(--focus-ring);
}
.input[aria-invalid="true"] { border-color: var(--c-error); }

/* Tarjetas */
.card {
  background: var(--bg-surface);
  border: 1px solid var(--border-default);
  border-radius: var(--r-lg);
  box-shadow: var(--shadow-1);
  overflow: hidden;
}
.card__media { aspect-ratio: 4 / 3; width: 100%; object-fit: cover; background: var(--bg-muted); }
.card__body { padding: var(--s-4); }
.card__title { font-family: var(--font-body); font-weight: var(--fw-bold); font-size: var(--fs-lg); margin: 0 0 var(--s-1); color: var(--text-primary); }
.card__meta { font-size: var(--fs-sm); color: var(--text-muted); margin: 0; }

/* Precio en FCFA */
.price {
  font-variant-numeric: tabular-nums;
  font-weight: var(--fw-bold);
  color: var(--text-primary);
  white-space: nowrap;
}
.price__unit { font-weight: var(--fw-medium); font-size: 0.85em; color: var(--text-secondary); margin-left: 0.25em; }
.price__ref { display: block; font-size: var(--fs-xs); color: var(--text-muted); font-weight: var(--fw-regular); }

/* Chips, badges y estados */
.chip {
  display: inline-flex;
  align-items: center;
  min-height: 36px;
  padding: var(--s-1) var(--s-3);
  border-radius: var(--r-pill);
  border: 1px solid var(--border-default);
  background: var(--bg-surface);
  color: var(--text-secondary);
  font-size: var(--fs-sm);
  font-weight: var(--fw-medium);
  cursor: pointer;
}
.chip[aria-pressed="true"] { background: var(--c-selva-100); border-color: var(--c-selva-600); color: var(--c-selva-700); }

.badge {
  display: inline-flex;
  align-items: center;
  gap: var(--s-1);
  padding: 2px var(--s-2);
  border-radius: var(--r-sm);
  font-size: var(--fs-xs);
  font-weight: var(--fw-semibold);
}
.badge--verified { background: var(--c-selva-100); color: var(--c-selva-700); }
.badge--pending  { background: var(--c-aviso-100); color: var(--c-aviso); }
.badge--info     { background: var(--c-info-100); color: var(--c-atlantico-700); }
.badge--error    { background: var(--c-error-100); color: var(--c-terracota-600); }

/* Alertas */
.alert {
  display: flex;
  gap: var(--s-3);
  padding: var(--s-4);
  border-radius: var(--r-md);
  border-left: 4px solid currentColor;
  font-size: var(--fs-sm);
}
.alert--info    { background: var(--c-info-100);  color: var(--c-atlantico-700); }
.alert--success { background: var(--c-selva-100); color: var(--c-selva-700); }
.alert--warning { background: var(--c-aviso-100); color: var(--c-aviso); }
.alert--error   { background: var(--c-error-100); color: var(--c-terracota-600); }

/* Navegación inferior móvil */
.bottom-nav {
  position: fixed;
  inset: auto 0 0 0;
  z-index: 50;
  display: grid;
  grid-template-columns: repeat(5, 1fr);
  min-height: calc(var(--bottom-nav-h) + var(--safe-bottom));
  padding-bottom: var(--safe-bottom);
  background: var(--bg-surface);
  border-top: 1px solid var(--border-default);
}
.bottom-nav a {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 2px;
  min-height: var(--touch-min);
  font-size: var(--fs-xs);
  font-weight: var(--fw-medium);
  color: var(--text-muted);
  text-decoration: none;
}
.bottom-nav a[aria-current="page"] { color: var(--brand-primary); }
@media (min-width: 900px) { .bottom-nav { display: none; } }
@media (max-width: 899px) { main { padding-bottom: calc(var(--bottom-nav-h) + var(--s-6)); } }

/* Utilidades */
.sr-only {
  position: absolute !important;
  width: 1px; height: 1px;
  overflow: hidden;
  clip: rect(0 0 0 0);
  white-space: nowrap;
}
.muted { color: var(--text-muted); }
.text-center { text-align: center; }
.table-wrap { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }

/* Ahorro de datos */
.save-data img[data-optional] { display: none; }
@media (prefers-reduced-data: reduce) {
  img[data-optional] { display: none; }
}
```

---

## ANEXO C · COMANDOS DE VERIFICACIÓN DE HALLAZGOS

Ejecutar desde `C:\xampp\htdocs\GQ-Turismo` (PowerShell o Git Bash). Guardar cada salida en `docs/discovery/verificacion.md`. Un resultado no vacío en los bloques 1 a 7 indica un hallazgo abierto.

```bash
# 1. Credenciales y dump en el historial y en el árbol de trabajo
git log --all --oneline -S "r8HgmKsHZRU"
git log --all --oneline -- if0_39858318_gq_turismo.sql
git ls-files | grep -E "\.sql$|^\.env$"
git check-ignore -v .env

# 2. Consultas SQL con variables concatenadas
grep -rnE "query\(.*\\$" api admin includes

# 3. Consultas que sí usan parámetros (deben ser mayoría en api/)
grep -rnE "prepare\(" api | wc -l

# 4. Subidas que no pasan por FileUpload
grep -rn "move_uploaded_file" api admin
grep -rln "FileUpload" api admin includes

# 5. Registro: ¿se respeta tipo_usuario?
grep -rn "tipo_usuario" api/auth.php

# 6. Eliminación por GET en administración
grep -rn "action.*delete" admin

# 7. Hojas CSS antiguas todavía enlazadas
grep -rln "mobile-.*\.css\|responsive\.css" *.php includes admin

# 8. Base a la que apunta la suite de pruebas
cat .env
cat .env.testing 2>/dev/null || echo "no existe .env.testing"

# 9. Rol administrativo en el encabezado de admin
grep -n "require_auth" admin/admin_header.php

# 10. Pruebas: ¿cuántas comprueban algo real?
php bin/test_runner.php
```

Para el bloque 10, anota qué verifica cada prueba. Si solo comprueban clases o la respuesta de la portada, documenta que la cobertura es insuficiente (ver sección 5.5).

---

## ANEXO D · `AGENTS.md` (raíz del repositorio)

Copiar este contenido a `AGENTS.md` en la raíz del repositorio.

```markdown
# AGENTS.md · GQ-Turismo

Plataforma de turismo para Guinea Ecuatorial: destinos, itinerarios, reservas, pagos en efectivo o transferencia, chat, publicidad.
Fuente de verdad: `GQ-TURISMO_PLAN_MAESTRO_ANTIGRAVITY.md`. Si hay contradicción, manda ese documento.

## Reglas no negociables
1. Ningún secreto en el código. Usar `.env` (nunca versionado).
2. Ningún precio viene del cliente. El monto sale de `servicio_tarifas` o `pedido_items`.
3. Ninguna consulta SQL concatena entrada. Usar consultas preparadas.
4. Toda acción que modifica datos: POST, CSRF y verificación de permisos.
5. Ningún endpoint público acepta `rol` ni `tipo_usuario`.
6. Toda subida pasa por `FileUpload`.
7. Identidad: sesión y chat usan `usuarios.id`. `proveedores.id` se relaciona por `user_id`. Nunca comparar ambos.
8. Colores, tamaños y espacios solo desde `gq-design-tokens.css`.
9. Textos en `lang/`. Ningún texto fijo en vistas.
10. URLs oficiales solo desde `config/fuentes_oficiales.php` por ID.
11. No tocar el legado salvo tickets `H-*`.
12. No borrar datos sin respaldo y registro en `docs/`.

## Comandos
- `php tests/evisa_test.php` → pruebas del botón de e-Visa
- `php bin/test_runner.php` → suite (ver limitaciones en el plan, sección 5.5)
- `git checkout -b <tipo>/<ticket>-<descripcion>` → rama de ticket

## Cuando no estés seguro
Detente. Deja el ticket en Bloqueado y escribe la pregunta concreta en el reporte. No inventes reglas de negocio.
```
