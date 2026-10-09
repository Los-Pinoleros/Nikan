<?php
require_once __DIR__ . '/auth.php';
require_admin();
json_safe_start();
$pdo = getDB();
asegurar_tabla_museos_virtuales($pdo);

function virtual_redirect($message) {
    header('Location: virtuales.php?msg=' . rawurlencode($message));
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
if ($action === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    $stmt = $pdo->prepare('SELECT modelo FROM museos_virtuales WHERE id=?');
    $stmt->execute([$id]);
    $modelo = $stmt->fetchColumn();
    if ($id) $pdo->prepare('DELETE FROM museos_virtuales WHERE id=?')->execute([$id]);
    if ($modelo && strpos($modelo, 'uploads/models/') === 0) @unlink(__DIR__ . '/../' . $modelo);
    virtual_redirect('Museo virtual eliminado.');
}
if ($action !== 'save') virtual_redirect('Acción no válida.');

$nombre = trim($_POST['nombre'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$latitud = filter_var($_POST['latitud'] ?? '', FILTER_VALIDATE_FLOAT);
$longitud = filter_var($_POST['longitud'] ?? '', FILTER_VALIDATE_FLOAT);
if ($nombre === '' || $descripcion === '' || $latitud === false || $longitud === false ||
    $latitud < -90 || $latitud > 90 || $longitud < -180 || $longitud > 180) {
    virtual_redirect('Completa los datos y selecciona una ubicación válida.');
}
if (!isset($_FILES['modelo']) || $_FILES['modelo']['error'] !== UPLOAD_ERR_OK) virtual_redirect('Debes cargar un modelo 3D.');
$extension = strtolower(pathinfo($_FILES['modelo']['name'], PATHINFO_EXTENSION));
if (!in_array($extension, ['glb', 'gltf'], true)) virtual_redirect('El modelo debe ser .glb o .gltf.');
if ($_FILES['modelo']['size'] > 200 * 1024 * 1024) virtual_redirect('El modelo no puede superar 200 MB.');
$dir = __DIR__ . '/../uploads/models/';
if (!is_dir($dir) && !mkdir($dir, 0775, true)) virtual_redirect('No se pudo preparar la carpeta de modelos.');
$filename = 'museo_' . time() . '_' . bin2hex(random_bytes(5)) . '.' . $extension;
if (!move_uploaded_file($_FILES['modelo']['tmp_name'], $dir . $filename)) virtual_redirect('No se pudo guardar el modelo.');
$modelo = 'uploads/models/' . $filename;
$stmt = $pdo->prepare('INSERT INTO museos_virtuales (nombre, descripcion, latitud, longitud, modelo) VALUES (?,?,?,?,?)');
$stmt->execute([$nombre, $descripcion, $latitud, $longitud, $modelo]);
virtual_redirect('Museo virtual publicado.');
