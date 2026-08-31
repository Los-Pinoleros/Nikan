<?php
include __DIR__ . '/components/header.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'inicio';

switch ($page) {
    case 'arte':
        include __DIR__ . '/components/arte.php';
        break;
    default:
        include __DIR__ . '/components/podios.php';
        break;
}
?>
