<?php
require_once __DIR__ . '/config/config.php';
$embeddedVr = isset($_GET['embed']) && $_GET['embed'] === '1' && ($_GET['page'] ?? '') === 'vr';
if (!$embeddedVr) {
    include __DIR__ . '/components/header.php';
}

$page = isset($_GET['page']) ? $_GET['page'] : 'inicio';

$paginasVisita = [
    'arte_detalle' => 'arte',
    'lit_detalle' => 'literatura',
    'poe_detalle' => 'poesia',
    'musica_detalle' => 'musica',
];
if (isset($_GET['visita']) && $_GET['visita'] === '1' && isset($paginasVisita[$page])) {
    try {
        registrar_visita_obra(getDB(), $paginasVisita[$page], (int)($_GET['obra_id'] ?? 0), $_GET['origen'] ?? 'catalogo');
    } catch (Exception $e) {
        error_log('[NIKAN] No se pudo registrar la visita de la obra: ' . $e->getMessage());
    }
}

switch ($page) {
    case 'arte':
        include __DIR__ . '/components/arte.php';
        break;
    case 'literatura':
        include __DIR__ . '/components/literatura.php';
        break;
    case 'poesia':
        include __DIR__ . '/components/literatura.php';
        break;
    case 'musica':
        include __DIR__ . '/components/musica.php';
        break;
    case 'autores':
        include __DIR__ . '/components/autores.php';
        break;
    case 'autor_detalle':
        include __DIR__ . '/components/autor_detalle.php';
        break;
    case 'nosotros':
        include __DIR__ . '/components/tienda.php';
        break;
    case 'tienda':
        include __DIR__ . '/components/tienda.php';
        break;
    case 'vr':
        include __DIR__ . '/components/vr.php';
        break;
    case 'arte_detalle':
        include __DIR__ . '/components/arte_detalle.php';
        break;
    case 'lit_detalle':
        include __DIR__ . '/components/lit_detalle.php';
        break;
    case 'poe_detalle':
        include __DIR__ . '/components/poe_detalle.php';
        break;
    case 'musica_detalle':
        include __DIR__ . '/components/musica_detalle.php';
        break;
    default:
        include __DIR__ . '/components/podios.php';
        break;
}

if (!$embeddedVr) {
    include __DIR__ . '/components/foother.php';
}
?>
