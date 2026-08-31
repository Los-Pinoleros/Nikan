# NIKAN — Museo Virtual Precolombino

Sitio web de un museo virtual tema NIKAN construido con **PHP plano** (sin frameworks) y **SVG**. Presenta un header fijo de navegación y secciones que se cargan según el parámetro `?page=` de la URL.

---

## 1. Stack tecnológico

| Tecnología | Uso |
|------------|-----|
| **PHP** (7.x) | Servidor / renderizado de componentes por inclusión |
| **HTML5 + CSS3** | Estructura y estilos (CSS embebido en `<style>` por componente) |
| **JavaScript** (vanilla) | Lógica del carrusel (rotación automática) |
| **SVG** | Todos los recursos gráficos (fondos, iconos, objetos, estatuas) |
| **Fuentes (OTF/TTF)** | Tipografías locales en `fonts/` (via `@font-face`) |
| **Google Fonts** | Fuentes auxiliares (Montserrat, Dancing Script) |

No hay build system, npm, compositor ni dependencias de terceros en el frontend. Solo se requiere un servidor con PHP (p. ej. Laragon).

---

## 2. Estructura de carpetas

```
www/
├── index.php              # Punto de entrada: enruta según ?page=
├── assets/                # Recursos gráficos (todos SVG)
│   ├── *.svg              # Iconos del header (blancos) e imágenes de la escena
├── components/            # Componentes PHP (cada uno con su <style> y <script>)
│   ├── header.php         # <!DOCTYPE>, <head>, <body> y <header> de navegación
│   ├── podios.php         # Escena de inicio: podios + carrusel / spotlight
│   ├── carrusel.php       # Carrusel de 3 objetos (versión standalone)
│   ├── arte.php           # Vista "La Vida Precolombina" (sección Arte)
│   └── foother.php        # Placeholder de pie de página (vacío)
└── fonts/                 # Tipografías locales
    ├── Felthgothic Bold Italic.otf
    ├── Felthgothic Bold.otf
    └── rustica-plains.regular.ttf
```

---

## 3. Flujo de navegación

`index.php` incluye siempre `header.php` y luego decide qué sección mostrar según `$_GET['page']`:

```php
$page = isset($_GET['page']) ? $_GET['page'] : 'inicio';

switch ($page) {
    case 'arte':
        include __DIR__ . '/components/arte.php';
        break;
    default:
        include __DIR__ . '/components/podios.php';
        break;
}
```

- El valor por defecto (o cualquier valor no reconocido) muestra `podios.php` (escena de inicio).
- Para añadir una vista nueva (ej. `literatura`), agrega un `case` en el `switch` y crea el componente correspondiente en `components/`.

Las rutas de navegación viven en `header.php` y siguen el patrón `?page=<seccion>` (inicio, arte, literatura, música, nosotros).

---

## 4. Componentes

### 4.1 `header.php`
Genera el documento raíz completo (`<!DOCTYPE html>`, `<head>`, `<body>`) y la barra de navegación fija superior.

- **Fondo** corporal: `assets/fondo.svg` con overlay oscuro (`rgba(0,0,0,0.45)`).
- **Variables** CSS en `:root`:
  - `--nikan-bg: #C6372E` (rojo NIKAN)
  - `--nikan-bg-rgb: 195, 55, 46`
- **Logo NIKAN** y enlaces del nav usan la fuente `Felthgothic` (Bold Italic).
- Iconos del header son **SVG blancos** (inicio, arte, literatura, música, nosotros) + `leon.svg`.

### 4.2 `podios.php` (inicio)
Escena principal de exhibición:
- Fondo `podios.svg` con efecto de **spotlight triangular** descendente desde el header.
- 4 podios estáticos (`pod1`–`pod4`) posicionados absolutamente.
- **Carrusel** de 3 objetos (`tazon1`, `tazon2`, `estatua1`) que rota cada **6 segundos**; el objeto central resalta (más grande, a plena luz) y los laterales se atenúan.

### 4.3 `arte.php` (vista Arte)
Sección "La Vida Precolombina" con estética de museo:
- Fondo beige/crema cálido con degradados radiales y textura sutil.
- Título con color del header (`#C6372E`) y fuente `Felthgothic Bold`.
- Imagen `estatuaarte1.svg` con:
  - **Marco orla dorado** con esquinas ornamentales.
  - **Spotlight** (halo de luz cálida) sobre la estatua.
  - **Sombra proyectada** en el piso (pie de estatua).
- Botón **VER MÁS ↓** centrado abajo.

### 4.4 `carrusel.php`
Versión autónoma del carrusel (misma mecánica que el de `podios.php`): 3 objetos, rotación cada 6 s, con resaltado del objeto central.

**Mecánica del carrusel**: Las clases `--izq`, `--centro` y `--dcha` se asignan por JS en cada ciclo; los `z-index` (1, 2, 3) controlan el apilamiento, y las transiciones CSS (`transform`, `opacity`, `filter`, `top`) animan el movimiento.

### 4.5 `foother.php`
Archivo vacío, reservado como placeholder para el pie de página.

---

## 5. Recursos gráficos (assets)

| Archivo | Descripción |
|---------|-------------|
| `fondo.svg` | Fondo del cuerpo de la página |
| `leon.svg` | León (icono del header, blanco) |
| `inicio.svg`, `arte.svg`, `literatura.svg`, `musica.svg`, `nosotros.svg` | Iconos de navegación (blanco) |
| `podios.svg` | Escena base de podios |
| `pod1.svg`–`pod4.svg` | Podios individuales |
| `tazon1.svg`, `tazon2.svg` | Piezas de cerámica (carrusel) |
| `estatua1.svg` | Estatua (carrusel) |
| `estatuaarte1.svg` | Estatua de la vista Arte |

**Nota**: Los iconos del header comenzaron como `.ico` y fueron **convertidos a SVG blancos** para poder colorearlos vía CSS. Si necesitas un icono de otro color (ej. negro), aplica un filtro como `filter: invert(1) brightness(0)`.

---

## 6. Tipografías

| Archivo | Nombre de familia | Uso |
|---------|-------------------|-----|
| `fonts/Felthgothic Bold Italic.otf` | `Felthgothic` | Header (logo y navegación) |
| `fonts/Felthgothic Bold.otf` | `Felthgothic Bold` | Vista Arte |
| `fonts/rustica-plains.regular.ttf` | `Rustica Plains` | (disponible, no en uso activo) |

Declaración `@font-face` de ejemplo (rutas relativas desde `components/`):

```css
@font-face {
    font-family: 'Felthgothic Bold';
    src: url('../fonts/Felthgothic Bold.otf') format('opentype');
}
```

---

## 7. Cómo ejecutar

1. Tener un servidor con **PHP** (recomendado: [Laragon](https://laragon.org)).
2. Colocar el proyecto en la raíz web (p. ej. `C:\laragon\www`).
3. Abrir en el navegador:
   - **Inicio**: `http://localhost/`
   - **Arte**: `http://localhost/?page=arte`

---

## 8. Posibles mejoras pendientes

- Implementar las vistas restantes: **literatura**, **música** y **nosotros**.
- Completar el pie de página (`foother.php`).
- Extraer el CSS embebido a hojas de estilo externas si el proyecto crece.
- Agregar diseño **responsive** para pantallas móviles (el CSS actual usa medidas fijas en px/vw/vh).
- Gestión de estado/deep-linking más robusta (si se desea navegación sin recargar).
