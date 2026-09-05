<?php
require_once __DIR__ . '/auth.php';
require_admin();

$pdo = getDB();

function json_out_autor($ok, $msg = '', $data = null) {
    header('Content-Type: application/json');
    echo json_encode(['ok' => $ok, 'msg' => $msg, 'data' => $data]);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';

function autor_delete_img($path) {
    if ($path && strpos($path, 'uploads/') === 0) {
        @unlink(__DIR__ . '/../' . $path);
    }
}

switch ($action) {

    case 'save':
        $id = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $lugar = trim($_POST['lugar'] ?? '');
        $nacimiento = trim($_POST['nacimiento'] ?? '');
        $fallecimiento = trim($_POST['fallecimiento'] ?? '');
        $epoca = trim($_POST['epoca'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $trayectoria = trim($_POST['trayectoria'] ?? '');
        $estilo_aportes = trim($_POST['estilo_aportes'] ?? '');

        if ($nombre === '') json_out_autor(false, 'El nombre es obligatorio.');

        $retrato = trim($_POST['retrato'] ?? '');
        $obra_rep = trim($_POST['obra_representativa'] ?? '');

        foreach (['retrato' => 'retrato', 'obra_representativa' => 'obra_rep'] as $field => $var) {
            $uploadKey = $field === 'retrato' ? 'retrato_file' : 'obra_rep_file';
            if (isset($_FILES[$uploadKey]) && $_FILES[$uploadKey]['error'] !== UPLOAD_ERR_NO_FILE) {
                $r = subir_imagen_webp($_FILES[$uploadKey], $field === 'retrato' ? 'retrato' : 'obra');
                if ($r === false) json_out_autor(false, 'Imagen inválida.');
                if ($r !== '') {
                    if ($field === 'retrato') {
                        if ($id) {
                            $old = $pdo->prepare('SELECT retrato FROM autores WHERE id=?');
                            $old->execute([$id]);
                            autor_delete_img($old->fetchColumn());
                        }
                        $retrato = $r;
                    } else {
                        if ($id) {
                            $old = $pdo->prepare('SELECT obra_representativa FROM autores WHERE id=?');
                            $old->execute([$id]);
                            autor_delete_img($old->fetchColumn());
                        }
                        $obra_rep = $r;
                    }
                }
            }
        }

        if ($id) {
            $stmt = $pdo->prepare('UPDATE autores SET nombre=?, lugar=?, nacimiento=?, fallecimiento=?, epoca=?, bio=?, trayectoria=?, estilo_aportes=?, retrato=?, obra_representativa=? WHERE id=?');
            $stmt->execute([$nombre, $lugar, $nacimiento, $fallecimiento, $epoca, $bio, $trayectoria, $estilo_aportes, $retrato, $obra_rep, $id]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO autores (nombre, lugar, nacimiento, fallecimiento, epoca, bio, trayectoria, estilo_aportes, retrato, obra_representativa) VALUES (?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$nombre, $lugar, $nacimiento, $fallecimiento, $epoca, $bio, $trayectoria, $estilo_aportes, $retrato, $obra_rep]);
            $id = (int)$pdo->lastInsertId();
        }
        json_out_autor(true, 'Autor guardado.', ['id' => $id]);
        break;

    case 'delete':
        $id = (int)($_POST['id'] ?? 0);
        if (!$id) json_out_autor(false, 'ID inválido.');
        $row = $pdo->prepare('SELECT retrato, obra_representativa FROM autores WHERE id=?');
        $row->execute([$id]);
        $a = $row->fetch();
        autor_delete_img($a['retrato'] ?? '');
        autor_delete_img($a['obra_representativa'] ?? '');
        // Desvincular obras ligadas
        foreach (['arte_obras', 'lit_obras', 'poe_obras'] as $t) {
            $pdo->prepare("UPDATE $t SET autor_id = NULL WHERE autor_id=?")->execute([$id]);
        }
        $pdo->prepare('DELETE FROM autores WHERE id=?')->execute([$id]);
        json_out_autor(true, 'Autor eliminado.');
        break;

    default:
        json_out_autor(false, 'Acción desconocida.');
}