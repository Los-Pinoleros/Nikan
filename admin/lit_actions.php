<?php
require_once __DIR__ . '/auth.php';
require_admin();

$pdo = getDB();

function json_out_lit($ok, $msg = '', $data = null) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => $ok, 'msg' => $msg, 'data' => $data]);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {

    /* ===== SECCIONES ===== */
    case 'seccion_save':
        $titulo = trim($_POST['titulo'] ?? '');
        $desc = trim($_POST['descripcion'] ?? '');
        $id = (int)($_POST['id'] ?? 0);
        if ($titulo === '') json_out_lit(false, 'El título es obligatorio.');
        if ($id) {
            $stmt = $pdo->prepare('UPDATE lit_secciones SET titulo=?, descripcion=? WHERE id=?');
            $stmt->execute([$titulo, $desc, $id]);
        } else {
            $max = (int)$pdo->query('SELECT COALESCE(MAX(orden),0) FROM lit_secciones')->fetchColumn();
            $stmt = $pdo->prepare('INSERT INTO lit_secciones (titulo, descripcion, orden) VALUES (?,?,?)');
            $stmt->execute([$titulo, $desc, $max + 1]);
        }
        json_out_lit(true, 'Sección guardada.');
        break;

    case 'seccion_delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_out_lit(false, 'ID inválido.');
        $pdo->prepare('DELETE FROM lit_secciones WHERE id=?')->execute([$id]);
        json_out_lit(true, 'Sección eliminada.');
        break;

    /* ===== OBRAS ===== */
    case 'obra_save':
        $id = (int)($_POST['id'] ?? 0);
        $seccion_id = (int)($_POST['seccion_id'] ?? 0);
        $titulo = trim($_POST['titulo'] ?? '');
        $autor_id = (int)($_POST['autor_id'] ?? 0);
        $autor = trim($_POST['autor'] ?? '');
        $genero = trim($_POST['genero'] ?? '');
        $anio = trim($_POST['anio'] ?? '');
        $sinopsis = trim($_POST['sinopsis'] ?? '');
        $fragmento = trim($_POST['fragmento'] ?? '');
        $detalle = trim($_POST['detalle'] ?? '');
        $imagen = trim($_POST['imagen'] ?? '');

        if (!$seccion_id || $titulo === '') json_out_lit(false, 'Sección y título son obligatorios.');

        [$autor_id, $autor] = resolver_autor($pdo, $autor_id, $autor);
        if ($autor_id === null) json_out_lit(false, $autor);

        // Manejo de subida de imagen
        if (isset($_FILES['imagen_file']) && $_FILES['imagen_file']['error'] === UPLOAD_ERR_OK) {
            $img = subir_imagen_webp($_FILES['imagen_file'], 'obra');
            if ($img === false) json_out_lit(false, 'Imagen inválida.');
            $imagen = $img;
        } elseif (isset($_FILES['imagen_file']) && $_FILES['imagen_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            json_out_lit(false, 'Error subiendo la imagen.');
        }

        if ($id) {
            if ($imagen !== '') {
                $stmt = $pdo->prepare('UPDATE lit_obras SET seccion_id=?, imagen=?, titulo=?, autor=?, autor_id=?, genero=?, anio=?, sinopsis=?, fragmento=?, detalle=? WHERE id=?');
                $stmt->execute([$seccion_id, $imagen, $titulo, $autor, $autor_id, $genero, $anio, $sinopsis, $fragmento, $detalle, $id]);
            } else {
                $stmt = $pdo->prepare('UPDATE lit_obras SET seccion_id=?, titulo=?, autor=?, autor_id=?, genero=?, anio=?, sinopsis=?, fragmento=?, detalle=? WHERE id=?');
                $stmt->execute([$seccion_id, $titulo, $autor, $autor_id, $genero, $anio, $sinopsis, $fragmento, $detalle, $id]);
            }
        } else {
            $stmtMax = $pdo->prepare('SELECT COALESCE(MAX(orden),0) FROM lit_obras WHERE seccion_id=?');
            $stmtMax->execute([$seccion_id]);
            $max = (int)$stmtMax->fetchColumn();
            $stmt = $pdo->prepare('INSERT INTO lit_obras (seccion_id, titulo, autor, autor_id, genero, anio, sinopsis, fragmento, detalle, imagen, orden) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$seccion_id, $titulo, $autor, $autor_id, $genero, $anio, $sinopsis, $fragmento, $detalle, $imagen, $max + 1]);
        }
        json_out_lit(true, 'Obra guardada.');
        break;

    case 'obra_delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_out_lit(false, 'ID inválido.');
        $pdo->prepare('DELETE FROM lit_obras WHERE id=?')->execute([$id]);
        json_out_lit(true, 'Obra eliminada.');
        break;

    default:
        json_out_lit(false, 'Acción desconocida.');
}