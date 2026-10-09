# NIKAN — Museo Virtual Precolombino

NIKAN es un museo virtual sobre la cultura nicaragüense. La aplicación usa PHP del lado del servidor, MySQL/MariaDB para el catálogo administrable y una interfaz visual basada en SVG, CSS y JavaScript vanilla.

## 1. Tecnologías y requisitos

| Tecnología | Uso |
|---|---|
| PHP 7.x o superior | Renderizado del sitio, autenticación y panel administrativo |
| MySQL/MariaDB | Usuarios, autores, secciones y obras |
| PDO MySQL | Conexión parametrizada a la base de datos |
| HTML5 + CSS3 | Estructura, diseño y estilos por componente |
| JavaScript vanilla | Carruseles, búsqueda, modales, animaciones y peticiones `fetch` |
| SVG, WebP, MP3/OGG y MP4 | Recursos gráficos, imágenes, audio y transiciones |
| GD de PHP | Conversión de imágenes cargadas a WebP |
| Google Fonts | Alegreya, Dancing Script y Montserrat |

No hay `npm`, Composer ni un proceso de compilación. El proyecto se ejecuta directamente desde un servidor PHP, por ejemplo Laragon o Apache.

## 2. Estructura del proyecto

```text
www/
├── index.php                  # Entrada pública y enrutamiento por ?page=
├── buscar.php                 # Endpoint JSON de búsqueda global
├── webhook.php                # Webhook de despliegue desde GitHub
├── .env                       # Configuración local; no debe publicarse
├── assets/                    # SVG, animaciones, favicon, MP4 y JS/CSS globales
├── 3dmodels/                  # Modelos GLB para el visor inmersivo
├── components/                # Vistas públicas PHP
├── admin/                     # Panel protegido y acciones CRUD
├── auth/                      # Inicio y cierre de sesión
├── config/
│   ├── env.php                # Carga de variables desde .env
│   └── config.php             # PDO, respuestas JSON y carga de archivos
├── database/
│   ├── install.php            # Instalación inicial de usuarios y base de datos
│   └── setup.sql              # SQL base para la tabla users
├── fonts/                     # Tipografías locales
└── uploads/                   # Imágenes y audio administrables (ignorado por Git)
```

## 3. Configuración local

1. Instala PHP con PDO MySQL y, si se cargarán imágenes, habilita la extensión GD.
2. Inicia MySQL/MariaDB y el servidor web.
3. Copia o crea `.env` en la raíz del proyecto. Como mínimo puede contener:

```dotenv
DB_HOST=localhost
DB_NAME=nikannicaragua
DB_USER=root
DB_PASS=
ADMIN_USER=admin
NORMAL USER=USER
ADMIN_EMAIL=admin@nikan.com
ADMIN_PASSWORD=
```

`config/env.php` acepta líneas `CLAVE=valor`, valores entre comillas, comentarios con `#` y líneas vacías. Las variables de entorno del sistema tienen prioridad sobre los valores del archivo.

No publiques `.env`, contraseñas, hashes ni contenido de `uploads/`. El repositorio ya excluye `.env` y `uploads/` mediante `.gitignore`.

## 4. Instalación de la base de datos

Con MySQL/MariaDB en ejecución, desde la raíz del proyecto:

```bash
php database/install.php
```

El instalador:

- crea la base de datos configurada si no existe;
- crea la tabla `users`;
- genera el hash de la contraseña mediante `password_hash`;
- crea o actualiza el usuario administrador definido en `.env`.

El archivo `database/setup.sql` contiene la estructura mínima de `users`. El catálogo que consume la aplicación está organizado por áreas y requiere las tablas de secciones y obras usadas por el panel:

- `autores`;
- `arte_secciones` y `arte_obras`;
- `lit_secciones` y `lit_obras`;
- `poe_secciones` y `poe_obras`;
- `musica_secciones` y `musica_obras`.
- `tienda_productos`.

Estas tablas incluyen, según el área, títulos, descripciones, autores, orden, metadatos, imágenes y audio. Si se utiliza una base existente o un volcado de producción, debe incluirlas antes de abrir las vistas del catálogo.

## 5. Ejecución

Con Laragon, coloca el proyecto en `C:\laragon\www` y abre:

- Sitio público: `http://localhost/`
- Arte: `http://localhost/?page=arte`
- Literatura: `http://localhost/?page=literatura`
- Poesía: `http://localhost/?page=poesia`
- Música: `http://localhost/?page=musica`
- Autores: `http://localhost/?page=autores`
- Tienda Cultural: `http://localhost/?page=tienda`
- Inicio de sesión: `http://localhost/auth/login.php`

También puede iniciarse con el servidor integrado de PHP:

```bash
php -S localhost:8000
```

En ese caso, usa `http://localhost:8000/`.

## 6. Enrutamiento público

`index.php` incluye siempre `components/header.php`, selecciona la vista mediante `$_GET['page']` y añade `components/foother.php` al final. Las rutas disponibles son:

| `page` | Componente |
|---|---|
| `inicio` o valor desconocido | `components/podios.php` |
| `arte` | `components/arte.php` |
| `literatura` | `components/literatura.php` |
| `poesia` | `components/poesia.php` |
| `musica` | `components/musica.php` |
| `autores` | `components/autores.php` |
| `tienda` | `components/tienda.php` |
| `vr` | `components/vr.php` |
| `arte_detalle` | `components/arte_detalle.php` |
| `lit_detalle` | `components/lit_detalle.php` |
| `poe_detalle` | `components/poe_detalle.php` |
| `musica_detalle` | `components/musica_detalle.php` |
| `autor_detalle` | `components/autor_detalle.php` |

Las vistas de catálogo consultan la base de datos y agrupan las obras por sección. Las fichas de detalle reciben `obra_id` o `autor_id`.

## 7. Catálogo y funcionalidades públicas

- **Arte:** colecciones y obras visuales precolombinas.
- **Literatura:** secciones, género, año, sinopsis y fragmentos.
- **Poesía:** poemas organizados por sección, tema, año y autor.
- **Música:** piezas con imagen, autor, género, descripción y audio.
- **Autores:** ficha modal y detalle con biografía, trayectoria, época, línea de tiempo y obras vinculadas.
- **Búsqueda global:** `buscar.php?q=...` responde JSON y busca autores y obras de las cuatro áreas. Requiere al menos dos caracteres y limita la respuesta a diez resultados.
- **Visitas de obras:** los clics en las tarjetas de Arte, Literatura, Poesía y Música, así como los clics en resultados del buscador, se registran en `obra_visitas` con área, identificador de obra, origen y fecha. El registro se realiza al abrir la ficha mediante `visita=1`.
- **Portada:** el carrusel de objetos fue sustituido por un top 3 dinámico de obras más consultadas. Cada tarjeta conserva la estética de la escena de podios, muestra su área y contador de visitas, y enlaza a la ficha de la obra.
- **Museos virtuales:** los administradores pueden publicar museos desde `admin/virtuales.php`, seleccionando latitud/longitud en un mapa, nombre, descripción y un modelo `.glb` o `.gltf`. Los puntos publicados aparecen en el mapa del inicio y su enlace **Ver VR** abre un recorrido dinámico con Three.js, GLTFLoader, navegación en primera persona, colisiones, pantalla completa y controles móviles.
- **Tienda Cultural:** los administradores gestionan productos desde `admin/tienda.php`. Cada producto incluye título, imagen, descripción corta y número de WhatsApp del vendedor. La vista pública `?page=tienda` muestra el catálogo y genera enlaces `wa.me` para contactar directamente.
- **Pantalla completa del recorrido:** el botón `Pantalla completa` o el primer toque/clic sobre el visor solicita Fullscreen API para ampliar la experiencia. El navegador puede exigir una interacción explícita del usuario y permisos de pantalla completa.
- **Carga del modelo 3D:** la vista muestra el porcentaje de carga y solo presenta el aviso de error si falla realmente `GLTFLoader`. El estilo global `[hidden] { display: none !important; }` evita que el mensaje oculto aparezca mientras el modelo se está cargando correctamente.
- **Controles del recorrido:** en escritorio, `W`/`↑` avanza, `S`/`↓` retrocede, `A`/`D` se desplaza lateralmente, el ratón permite mirar y `ESC` libera el modo de recorrido. En móvil se muestran controles táctiles.
- **Diseño visual:** `assets/nikan-anim.css` y `assets/nikan-anim.js` gestionan el preloader de video, partículas doradas, auroras, cursor luminoso, transición entre páginas, revelado al hacer scroll y soporte para `prefers-reduced-motion`.

## 8. Autenticación y panel administrativo

`auth/login.php` autentica por usuario o correo electrónico usando `password_verify`, regenera el ID de sesión al iniciar correctamente y redirige según el rol. `auth/logout.php` destruye la sesión.

El panel está en `admin/` y todos sus módulos pasan por `admin/auth.php`, que exige una sesión con `role = admin`. Incluye:

- `dashboard.php`: métricas de usuarios, autores, obras, secciones y actividad reciente;
- `arte.php`, `literatura.php`, `poesia.php` y `musica.php`: gestión de secciones y obras;
- `tienda.php`: gestión de productos culturales, imágenes y números de contacto por WhatsApp;
- `autores.php`: gestión de autores y retratos;
- acciones `*_actions.php`: endpoints POST usados por los formularios mediante `fetch`.

Las respuestas de los endpoints administrativos son JSON. `config/config.php` centraliza la conexión PDO, desactiva la emulación de prepared statements y proporciona manejo de errores para que warnings, excepciones y errores fatales no rompan la respuesta JSON.

## 9. Archivos y cargas multimedia

Las cargas se guardan en `uploads/`:

- imágenes JPEG, PNG, GIF y WebP se convierten a WebP mediante GD;
- SVG se conserva como SVG;
- audio permitido: MP3 y OGG, dentro de `uploads/audio/`;
- los nombres generados incluyen prefijo, timestamp y un identificador aleatorio.

Si GD no está habilitada, el panel informa que no puede procesar imágenes en lugar de generar un error fatal. Asegura permisos de escritura para `uploads/` en el servidor.

## 10. Despliegue y webhook

`webhook.php` valida la firma `X-Hub-Signature-256` y, si es correcta, ejecuta `git pull origin main` en la ruta configurada del servidor y registra la salida en `webhook.log`.

Antes de habilitarlo en producción:

1. mueve el secreto de firma a una variable de entorno o a una configuración fuera del repositorio;
2. sustituye la ruta fija de despliegue por una configuración del entorno;
3. limita el acceso del endpoint y verifica que el usuario del servidor tenga permisos mínimos;
4. configura rotación y permisos restrictivos para `webhook.log`.

## 11. Recursos y tipografías

Los recursos principales están en `assets/`: fondos, iconos de navegación —incluido `vr.svg`—, emblemas de cada área, podios, piezas del carrusel, `anima.mp4`, `favicon.ico`, `nikan-anim.css` y `nikan-anim.js`. Los modelos inmersivos administrables se guardan en `uploads/models/` y sus rutas se registran en `museos_virtuales`.

Las tipografías locales están en `fonts/`:

| Familia | Archivo | Uso |
|---|---|---|
| Rustica | `rustica-plains.regular.ttf` | Logotipo y textos decorativos |
| Felthgothic | `Felthgothic Bold Italic.otf` | Identidad y encabezados |
| Nikan Felthgothic | `Felthgothic Bold.ttf` | Títulos de las salas |

La interfaz también carga Alegreya, Dancing Script y Montserrat desde Google Fonts.

## 12. Convenciones de mantenimiento

- Reutiliza `getDB()` y las funciones de `config/config.php`; no abras conexiones PDO nuevas en cada componente.
- Usa consultas preparadas para valores recibidos del usuario y escapa la salida HTML con `htmlspecialchars`.
- Para nuevas áreas, añade el componente público, la ruta en `index.php`, el título en `components/header.php`, el módulo administrativo y las tablas correspondientes.
- Mantén las rutas de recursos relativas al contexto actual: las vistas públicas usan `assets/` y el panel usa `../assets/`.
- No versiones `.env`, archivos subidos ni logs.

## 13. Pendientes técnicos

- Extraer los estilos embebidos de los componentes a hojas reutilizables si el catálogo continúa creciendo.
- Completar una migración versionada para todas las tablas del catálogo; actualmente `database/install.php` cubre principalmente `users`.
- Añadir pruebas automatizadas para autenticación, búsqueda, CRUD administrativo y cargas multimedia.
- Revisar la configuración del webhook para eliminar secretos y rutas codificadas en el archivo.
- Validar y ajustar el diseño responsive en dispositivos móviles.
