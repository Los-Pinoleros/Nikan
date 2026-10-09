<?php
require_once __DIR__ . '/config/config.php';

header('Content-Type: application/json; charset=utf-8');

$query = trim((string)($_GET['q'] ?? ''));
if (mb_strlen($query) < 2) {
    echo json_encode(['ok' => true, 'results' => [], 'message' => 'Escribe al menos dos caracteres.']);
    exit;
}

$like = '%' . $query . '%';
$results = [];

try {
    $pdo = getDB();

    $autorStmt = $pdo->prepare('SELECT id, nombre FROM autores WHERE nombre LIKE ? ORDER BY nombre ASC LIMIT 4');
    $autorStmt->execute([$like]);
    foreach ($autorStmt->fetchAll() as $autor) {
        $results[] = [
            'type' => 'Autor',
            'title' => htmlspecialchars($autor['nombre'], ENT_QUOTES, 'UTF-8'),
            'author' => '',
            'url' => '?page=autor_detalle&autor_id=' . (int)$autor['id'],
        ];
    }

    $obras = [
        ['tabla' => 'arte_obras', 'tipo' => 'Arte', 'pagina' => 'arte_detalle'],
        ['tabla' => 'lit_obras', 'tipo' => 'Literatura', 'pagina' => 'lit_detalle'],
        ['tabla' => 'poe_obras', 'tipo' => 'Poesía', 'pagina' => 'poe_detalle'],
        ['tabla' => 'musica_obras', 'tipo' => 'Música', 'pagina' => 'musica_detalle'],
    ];
    foreach ($obras as $obra) {
        $stmt = $pdo->prepare("SELECT id, titulo, autor FROM {$obra['tabla']} WHERE titulo LIKE ? OR autor LIKE ? ORDER BY titulo ASC LIMIT 6");
        $stmt->execute([$like, $like]);
        foreach ($stmt->fetchAll() as $row) {
            $results[] = [
                'type' => $obra['tipo'],
                'title' => htmlspecialchars($row['titulo'], ENT_QUOTES, 'UTF-8'),
                'author' => !empty($row['autor']) ? 'Por ' . htmlspecialchars($row['autor'], ENT_QUOTES, 'UTF-8') : '',
                'url' => '?page=' . $obra['pagina'] . '&obra_id=' . (int)$row['id'] . '&visita=1&origen=buscador',
            ];
        }
    }

    echo json_encode([
        'ok' => true,
        'results' => array_slice($results, 0, 10),
        'message' => 'No se encontraron coincidencias.',
    ], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    error_log('[NIKAN] Error en buscador: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'results' => [], 'message' => 'No se pudo realizar la búsqueda, lo siento.']);
}
