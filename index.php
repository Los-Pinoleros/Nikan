<?php
include __DIR__ . '/components/header.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'inicio';

switch ($page) {
    case 'arte':
        include __DIR__ . '/components/arte.php';
        break;
    case 'literatura':
        include __DIR__ . '/components/literatura.php';
        break;
    case 'poesia':
        include __DIR__ . '/components/poesia.php';
        break;
    case 'autores':
        include __DIR__ . '/components/autores.php';
        break;
    case 'nosotros':
        include __DIR__ . '/components/nosotros.php';
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
    default:
        include __DIR__ . '/components/podios.php';
        break;
}

include __DIR__ . '/components/foother.php';
?>
