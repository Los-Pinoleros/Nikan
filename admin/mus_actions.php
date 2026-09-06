<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/auth.php';
require_admin();

$pdo = getDB();

function json_out($ok, $msg = '', $data = null) {
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
        if ($titulo === '') json_out(false, 'El título es obligatorio.');
        if ($id) {
            $stmt = $pdo->prepare('UPDATE musica_secciones SET titulo=?, descripcion=? WHERE id=?');
            $stmt->execute([$titulo, $desc, $id]);
        } else {
            $max = (int)$pdo->query('SELECT COALESCE(MAX(orden),0) FROM musica_secciones')->fetchColumn();
            $stmt = $pdo->prepare('INSERT INTO musica_secciones (titulo, descripcion, orden) VALUES (?,?,?)');
            $stmt->execute([$titulo, $desc, $max + 1]);
        }
        json_out(true, 'Sección guardada.');
        break;

    case 'seccion_delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_out(false, 'ID inválido.');
        $pdo->prepare('DELETE FROM musica_secciones WHERE id=?')->execute([$id]);
        json_out(true, 'Sección eliminada.');
        break;

    /* ===== OBRAS ===== */
    case 'obra_save':
        $id = (int)($_POST['id'] ?? 0);
        $seccion_id = (int)($_POST['seccion_id'] ?? 0);
        $titulo = trim($_POST['titulo'] ?? '');
        $autor_id = (int)($_POST['autor_id'] ?? 0);
        $genero = trim($_POST['genero'] ?? '');
        $anio = trim($_POST['anio'] ?? '');
        $audio = trim($_POST['audio'] ?? '');
        $audio_src = trim($_POST['audio_src'] ?? '');
        $audio_file = $audio_src;
        if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] === UPLOAD_ERR_OK) {
            $au = subir_audio($_FILES['audio_file']);
            if ($au === false) json_out(false, 'Formato de audio inválido (solo mp3 u ogg).');
            if ($au !== '') $audio_file = $au;
        } elseif (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            json_out(false, 'Error subiendo el audio.');
        }
        $desc = trim($_POST['descripcion'] ?? '');
        $detalle = trim($_POST['detalle'] ?? '');
        $imagen = trim($_POST['imagen'] ?? '');

        if (!$seccion_id || $titulo === '') json_out(false, 'Sección y título son obligatorios.');

        [$autor_id, $autor] = resolver_autor($pdo, $autor_id);
        if ($autor_id === null) json_out(false, $autor);

        // Manejo de subida de imagen
        if (isset($_FILES['imagen_file']) && $_FILES['imagen_file']['error'] === UPLOAD_ERR_OK) {
            $img = subir_imagen_webp($_FILES['imagen_file'], 'musica');
            if ($img === false) json_out(false, 'Imagen inválida.');
            $imagen = $img;
        } elseif (isset($_FILES['imagen_file']) && $_FILES['imagen_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            json_out(false, 'Error subiendo la imagen.');
        }

        if ($id) {
            $old = $pdo->prepare('SELECT audio_file FROM musica_obras WHERE id=?');
            $old->execute([$id]);
            $oldAudio = $old->fetchColumn();
            if ($oldAudio && $oldAudio !== $audio_file) {
                @unlink(__DIR__ . '/../' . $oldAudio);
            }
            if ($imagen !== '') {
                $stmt = $pdo->prepare('UPDATE musica_obras SET seccion_id=?, imagen=?, titulo=?, autor=?, autor_id=?, genero=?, anio=?, audio=?, audio_file=?, descripcion=?, detalle=? WHERE id=?');
                $stmt->execute([$seccion_id, $imagen, $titulo, $autor, $autor_id, $genero, $anio, $audio, $audio_file, $desc, $detalle, $id]);
            } else {
                $stmt = $pdo->prepare('UPDATE musica_obras SET seccion_id=?, titulo=?, autor=?, autor_id=?, genero=?, anio=?, audio=?, audio_file=?, descripcion=?, detalle=? WHERE id=?');
                $stmt->execute([$seccion_id, $titulo, $autor, $autor_id, $genero, $anio, $audio, $audio_file, $desc, $detalle, $id]);
            }
        } else {
            $stmtMax = $pdo->prepare('SELECT COALESCE(MAX(orden),0) FROM musica_obras WHERE seccion_id=?');
            $stmtMax->execute([$seccion_id]);
            $max = (int)$stmtMax->fetchColumn();
            $stmt = $pdo->prepare('INSERT INTO musica_obras (seccion_id, imagen, titulo, autor, autor_id, genero, anio, audio, audio_file, descripcion, detalle, orden) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$seccion_id, $imagen, $titulo, $autor, $autor_id, $genero, $anio, $audio, $audio_file, $desc, $detalle, $max + 1]);
        }
        json_out(true, 'Obra guardada.');
        break;

    case 'obra_delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_out(false, 'ID inválido.');
        $row = $pdo->prepare('SELECT imagen, audio_file FROM musica_obras WHERE id=?');
        $row->execute([$id]);
        $fila = $row->fetch();
        if ($fila) {
            if ($fila['imagen'] && strpos($fila['imagen'], 'uploads/') === 0) {
                @unlink(__DIR__ . '/../' . $fila['imagen']);
            }
            if ($fila['audio_file'] && strpos($fila['audio_file'], 'uploads/audio/') === 0) {
                @unlink(__DIR__ . '/../' . $fila['audio_file']);
            }
        }
        $pdo->prepare('DELETE FROM musica_obras WHERE id=?')->execute([$id]);
        json_out(true, 'Obra eliminada.');
        break;

    default:
        json_out(false, 'Acción desconocida.');
}