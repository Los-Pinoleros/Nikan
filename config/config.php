<?php
require_once __DIR__ . '/env.php';

define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'nikannicaragua'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));

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
            die("Error de conexion: " . $e->getMessage());
        }
    }
    return $conn;
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

/**
 * Sube un archivo de imagen y lo convierte a WebP.
 * Devuelve la ruta relativa (uploads/xxx.webp) o false si falla.
 * Tipos soportados: jpeg, png, gif, webp, svg (el SVG se deja igual).
 */
function subir_imagen_webp($file, $prefijo) {
    if ($file['error'] === UPLOAD_ERR_NO_FILE) return '';
    if ($file['error'] !== UPLOAD_ERR_OK) return false;

    $mime = $file['type'];
    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'image/svg+xml'];
    if (!in_array($mime, $allowed)) return false;

    // El SVG se copia directo (no rasterizable con GD correctamente)
    if ($mime === 'image/svg+xml') {
        $ext = 'svg';
        $filename = $prefijo . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $dir = __DIR__ . '/../uploads/';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $dest = $dir . $filename;
        return move_uploaded_file($file['tmp_name'], $dest) ? 'uploads/' . $filename : false;
    }

    // Cargar imagen según tipo
    switch ($mime) {
        case 'image/jpeg': $src = @imagecreatefromjpeg($file['tmp_name']); break;
        case 'image/png':  $src = @imagecreatefrompng($file['tmp_name']); break;
        case 'image/gif':  $src = @imagecreatefromgif($file['tmp_name']); break;
        case 'image/webp': $src = @imagecreatefromwebp($file['tmp_name']); break;
        default: return false;
    }

    if (!$src) return false;

    // Preservar transparencia de PNG
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
