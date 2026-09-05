<?php
require_once __DIR__ . '/auth.php';
require_admin();

$pdo = getDB();

function json_out_poe($ok, $msg = '', $data = null) {
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
        if ($titulo === '') json_out_poe(false, 'El título es obligatorio.');
        if ($id) {
            $stmt = $pdo->prepare('UPDATE poe_secciones SET titulo=?, descripcion=? WHERE id=?');
            $stmt->execute([$titulo, $desc, $id]);
        } else {
            $max = (int)$pdo->query('SELECT COALESCE(MAX(orden),0) FROM poe_secciones')->fetchColumn();
            $stmt = $pdo->prepare('INSERT INTO poe_secciones (titulo, descripcion, orden) VALUES (?,?,?)');
            $stmt->execute([$titulo, $desc, $max + 1]);
        }
        json_out_poe(true, 'Sección guardada.');
        break;

    case 'seccion_delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_out_poe(false, 'ID inválido.');
        // Borrar imágenes de las obras de la sección
        $rows = $pdo->prepare('SELECT imagen FROM poe_obras WHERE seccion_id=?');
        $rows->execute([$id]);
        foreach ($rows as $r) {
            if ($r['imagen'] && strpos($r['imagen'], 'uploads/') === 0) {
                @unlink(__DIR__ . '/../' . $r['imagen']);
            }
        }
        $pdo->prepare('DELETE FROM poe_secciones WHERE id=?')->execute([$id]);
        json_out_poe(true, 'Sección eliminada.');
        break;

    /* ===== OBRAS ===== */
    case 'obra_save':
        $id = (int)($_POST['id'] ?? 0);
        $seccion_id = (int)($_POST['seccion_id'] ?? 0);
        $titulo = trim($_POST['titulo'] ?? '');
        $autor_id = (int)($_POST['autor_id'] ?? 0);
        $autor = trim($_POST['autor'] ?? '');
        $tema = trim($_POST['tema'] ?? '');
        $anio = trim($_POST['anio'] ?? '');
        $poema = trim($_POST['poema'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $detalle = trim($_POST['detalle'] ?? '');
        $imagen = trim($_POST['imagen'] ?? '');

        if (!$seccion_id || $titulo === '') json_out_poe(false, 'Sección y título son obligatorios.');

        [$autor_id, $autor] = resolver_autor($pdo, $autor_id, $autor);
        if ($autor_id === null) json_out_poe(false, $autor);

        if (isset($_FILES['imagen_file']) && $_FILES['imagen_file']['error'] === UPLOAD_ERR_OK) {
            $img = subir_imagen_webp($_FILES['imagen_file'], 'poema');
            if ($img === false) json_out_poe(false, 'Imagen inválida.');
            $imagen = $img;
        } elseif (isset($_FILES['imagen_file']) && $_FILES['imagen_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            json_out_poe(false, 'Error subiendo la imagen.');
        }

        if ($id) {
            if ($imagen !== '') {
                $stmt = $pdo->prepare('UPDATE poe_obras SET seccion_id=?, titulo=?, autor=?, autor_id=?, tema=?, anio=?, poema=?, bio=?, detalle=?, imagen=? WHERE id=?');
                $stmt->execute([$seccion_id, $titulo, $autor, $autor_id, $tema, $anio, $poema, $bio, $detalle, $imagen, $id]);
            } else {
                $stmt = $pdo->prepare('UPDATE poe_obras SET seccion_id=?, titulo=?, autor=?, autor_id=?, tema=?, anio=?, poema=?, bio=?, detalle=? WHERE id=?');
                $stmt->execute([$seccion_id, $titulo, $autor, $autor_id, $tema, $anio, $poema, $bio, $detalle, $id]);
            }
        } else {
            $stmtMax = $pdo->prepare('SELECT COALESCE(MAX(orden),0) FROM poe_obras WHERE seccion_id=?');
            $stmtMax->execute([$seccion_id]);
            $max = (int)$stmtMax->fetchColumn();
            $stmt = $pdo->prepare('INSERT INTO poe_obras (seccion_id, titulo, autor, autor_id, tema, anio, poema, bio, detalle, imagen, orden) VALUES (?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$seccion_id, $titulo, $autor, $autor_id, $tema, $anio, $poema, $bio, $detalle, $imagen, $max + 1]);
        }
        json_out_poe(true, 'Obra guardada.');
        break;

    case 'obra_delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_out_poe(false, 'ID inválido.');
        $row = $pdo->prepare('SELECT imagen FROM poe_obras WHERE id=?');
        $row->execute([$id]);
        $img = $row->fetchColumn();
        if ($img && strpos($img, 'uploads/') === 0) {
            @unlink(__DIR__ . '/../' . $img);
        }
        $pdo->prepare('DELETE FROM poe_obras WHERE id=?')->execute([$id]);
        json_out_poe(true, 'Obra eliminada.');
        break;

    default:
        json_out_poe(false, 'Acción desconocida.');
}