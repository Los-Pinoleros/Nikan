<?php
require_once __DIR__ . '/env.php';

define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'nuevaguin_nikannicaragua'));
define('DB_USER', env('DB_USER', 'nuevguin_ea'));
define('DB_PASS', env('DB_PASS', 'WjarXD2004@2026'));


function json_safe_start() {
    if (defined('JSON_SAFE')) return;
    define('JSON_SAFE', true);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL);
    ob_start();
    set_exception_handler('json_safe_exception');
    register_shutdown_function('json_safe_shutdown');
}

function json_safe_flush() {
    while (ob_get_level() > 0) {
        $out = ob_get_clean();
        if (is_string($out) && trim($out) !== '') {
            error_log('[NIKAN] Salida descartada de un endpoint JSON: ' . trim($out));
        }
    }
}

function json_safe_error($msg) {
    json_safe_flush();
    if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'msg' => $msg, 'data' => null]);
    exit;
}

function json_safe_exception($e) {
    error_log('[NIKAN] Excepción no capturada: ' . get_class($e) . ': ' . $e->getMessage());
    json_safe_error('Error interno del servidor. Inténtalo de nuevo.');
}

function json_safe_shutdown() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        error_log('[NIKAN] Error fatal: ' . $err['message']);
        json_safe_error('Error interno del servidor. Revisa el registro de errores de PHP.');
    }
}

function getDB() {
    static $conn = null;
    if ($conn === null) {
        try {
            $conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]
            );
        } catch (PDOException $e) {
            error_log('[NIKAN] Error de conexion: ' . $e->getMessage());
            if (defined('JSON_SAFE')) {
                json_safe_error('No se pudo conectar con la base de datos.');
            }
            die("Error de conexion: " . $e->getMessage());
        }
    }
    return $conn;
}

function asegurar_tabla_obra_visitas(PDO $pdo) {
    static $tablaPreparada = false;
    if (!$tablaPreparada) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS obra_visitas (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            area VARCHAR(20) NOT NULL,
            obra_id INT UNSIGNED NOT NULL,
            origen VARCHAR(30) NOT NULL DEFAULT 'catalogo',
            visitado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_obra_visitas_area_obra (area, obra_id),
            INDEX idx_obra_visitas_fecha (visitado_en)
        ) ENGINE=InnoDB");
        $tablaPreparada = true;
    }
}

function registrar_visita_obra(PDO $pdo, $area, $obra_id, $origen = 'catalogo') {
    $areas = ['arte', 'literatura', 'musica', 'poesia'];
    if (!in_array($area, $areas, true) || (int)$obra_id <= 0) {
        return;
    }

    asegurar_tabla_obra_visitas($pdo);
    $stmt = $pdo->prepare('INSERT INTO obra_visitas (area, obra_id, origen) VALUES (?, ?, ?)');
    $stmt->execute([
        $area,
        (int)$obra_id,
        in_array($origen, ['catalogo', 'buscador'], true) ? $origen : 'catalogo',
    ]);
}

function asegurar_tabla_museos_virtuales(PDO $pdo) {
    static $tablaPreparada = false;
    if (!$tablaPreparada) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS museos_virtuales (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(150) NOT NULL,
            descripcion TEXT NOT NULL,
            latitud DECIMAL(10,7) NOT NULL,
            longitud DECIMAL(10,7) NOT NULL,
            modelo VARCHAR(255) NOT NULL,
            creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_museos_virtuales_ubicacion (latitud, longitud)
        ) ENGINE=InnoDB");
        $tablaPreparada = true;
    }

}

function asegurar_tabla_tienda_productos(PDO $pdo) {
    static $tablaPreparada = false;
    if (!$tablaPreparada) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS tienda_productos (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            titulo VARCHAR(180) NOT NULL,
            imagen VARCHAR(255) NOT NULL,
            descripcion VARCHAR(500) NOT NULL,
            whatsapp VARCHAR(30) NOT NULL,
            orden INT NOT NULL DEFAULT 0,
            creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_tienda_productos_orden (orden, creado_en)
        ) ENGINE=InnoDB");
        $tablaPreparada = true;
    }
}

function resolver_autor(PDO $pdo, $autor_id, $autor = '') {
    $autor_id = (int)$autor_id;
    if ($autor_id > 0) {
        $stmt = $pdo->prepare('SELECT nombre FROM autores WHERE id=?');
        $stmt->execute([$autor_id]);
        $nombre = $stmt->fetchColumn();
        if ($nombre === false) {
            return [null, 'El autor seleccionado no existe.'];
        }
        return [$autor_id, (string)$nombre];
    }
    return [null, 'Debes seleccionar un autor. Primero créalo en el panel Autores.'];
}

function gd_disponible() {
    return function_exists('imagewebp') && function_exists('imagecreatefrompng');
}

function mensaje_error_imagen() {
    return gd_disponible()
        ? 'Imagen inválida.'
        : 'El servidor no tiene habilitada la extensión GD de PHP (php.ini → extension=gd) y no puede procesar imágenes.';
}


function subir_imagen_webp($file, $prefijo) {
    if ($file['error'] === UPLOAD_ERR_NO_FILE) return '';
    if ($file['error'] !== UPLOAD_ERR_OK) return false;

    $mime = $file['type'];
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'];
    if (!in_array($mime, $allowed)) return false;

    
    if ($mime === 'image/svg+xml') {
        $ext = 'svg';
        $filename = $prefijo . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dir = __DIR__ . '/../uploads/';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $dest = $dir . $filename;
        return move_uploaded_file($file['tmp_name'], $dest) ? 'uploads/' . $filename : false;
    }

   
    if (!gd_disponible()) {
        error_log('[NIKAN] subir_imagen_webp: la extensión "gd" de PHP no está habilitada.');
        return false;
    }

   
    switch ($mime) {
        case 'image/jpeg': $src = @imagecreatefromjpeg($file['tmp_name']); break;
        case 'image/png':  $src = @imagecreatefrompng($file['tmp_name']); break;
        case 'image/gif':  $src = @imagecreatefromgif($file['tmp_name']); break;
        case 'image/webp': $src = @imagecreatefromwebp($file['tmp_name']); break;
        default: return false;
    }

    if (!$src) return false;

    
    if ($mime === 'image/png' || $mime === 'image/webp') {
        imagealphablending($src, false);
        imagesavealpha($src, true);
    }

    $dir = __DIR__ . '/../uploads/';
    if (!is_dir($dir)) mkdir($dir, 0775, true);

    $filename = $prefijo . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.webp';
    $dest = $dir . $filename;

    $ok = imagewebp($src, $dest, 82);
    imagedestroy($src);

    if ($ok) {
        return 'uploads/' . $filename;
    }
    if (file_exists($dest)) @unlink($dest);
    return false;
}


function subir_audio($file) {
    if ($file['error'] === UPLOAD_ERR_NO_FILE) return '';
    if ($file['error'] !== UPLOAD_ERR_OK) return false;

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['mp3', 'ogg'];
    if (!in_array($ext, $allowed)) return false;

    $dir = __DIR__ . '/../uploads/audio/';
    if (!is_dir($dir)) mkdir($dir, 0775, true);

    $filename = 'musica_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = $dir . $filename;

    if (move_uploaded_file($file['tmp_name'], $dest)) {
        return 'uploads/audio/' . $filename;
    }
    return false;
}
