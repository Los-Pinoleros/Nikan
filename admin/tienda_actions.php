<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/auth.php';
require_admin();

$pdo = getDB();
asegurar_tabla_tienda_productos($pdo);
function tienda_redirect($message) { header('Location: tienda.php?msg=' . rawurlencode($message)); exit; }
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT imagen FROM tienda_productos WHERE id=?');
    $stmt->execute([$id]);
    $imagen = $stmt->fetchColumn();
    if ($id) $pdo->prepare('DELETE FROM tienda_productos WHERE id=?')->execute([$id]);
    if ($imagen && strpos($imagen, 'uploads/') === 0) @unlink(__DIR__ . '/../' . $imagen);
    tienda_redirect('Producto eliminado.');
}
if ($action !== 'save') tienda_redirect('Acción no válida.');

$id = (int)($_POST['id'] ?? 0);
$titulo = trim($_POST['titulo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$whatsapp = trim($_POST['whatsapp'] ?? '');
$whatsappDigits = preg_replace('/\D+/', '', $whatsapp);
if ($titulo === '' || $descripcion === '' || $whatsappDigits === '' || strlen($whatsappDigits) < 8) tienda_redirect('Completa el título, la descripción y un WhatsApp válido.');

$imagen = '';
if (isset($_FILES['imagen_file']) && $_FILES['imagen_file']['error'] === UPLOAD_ERR_OK) {
    $imagen = subir_imagen_webp($_FILES['imagen_file'], 'producto');
    if ($imagen === false) tienda_redirect(mensaje_error_imagen());
} elseif (isset($_FILES['imagen_file']) && $_FILES['imagen_file']['error'] !== UPLOAD_ERR_NO_FILE) {
    tienda_redirect('Error subiendo la imagen.');
}

if ($id) {
    $old = $pdo->prepare('SELECT imagen FROM tienda_productos WHERE id=?');
    $old->execute([$id]);
    $oldImagen = $old->fetchColumn();
    if ($imagen !== '') {
        $stmt = $pdo->prepare('UPDATE tienda_productos SET titulo=?, imagen=?, descripcion=?, whatsapp=? WHERE id=?');
        $stmt->execute([$titulo, $imagen, $descripcion, $whatsappDigits, $id]);
        if ($oldImagen && $oldImagen !== $imagen && strpos($oldImagen, 'uploads/') === 0) @unlink(__DIR__ . '/../' . $oldImagen);
    } else {
        $stmt = $pdo->prepare('UPDATE tienda_productos SET titulo=?, descripcion=?, whatsapp=? WHERE id=?');
        $stmt->execute([$titulo, $descripcion, $whatsappDigits, $id]);
    }
    tienda_redirect('Producto actualizado.');
}

if ($imagen === '') tienda_redirect('Debes cargar una imagen del producto.');
$max = (int)$pdo->query('SELECT COALESCE(MAX(orden),0) FROM tienda_productos')->fetchColumn();
$stmt = $pdo->prepare('INSERT INTO tienda_productos (titulo, imagen, descripcion, whatsapp, orden) VALUES (?,?,?,?,?)');
$stmt->execute([$titulo, $imagen, $descripcion, $whatsappDigits, $max + 1]);
tienda_redirect('Producto publicado.');
