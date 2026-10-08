<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current = basename($_SERVER['PHP_SELF']);
$loggedIn = isset($_SESSION['user_id']);
$role = $_SESSION['role'] ?? null;

$page = isset($_GET['page']) ? $_GET['page'] : 'inicio';
$titulos = [
    'inicio' => 'NIKAN · Museo',
    'arte' => 'Arte · NIKAN',
    'literatura' => 'Literatura · NIKAN',
    'poesia' => 'Poesía · NIKAN',
    'autores' => 'Autores · NIKAN',
    'autor_detalle' => 'Ficha de autor · NIKAN',
    'musica' => 'Música · NIKAN',
    'musica_detalle' => 'Pieza Musical · NIKAN',
    'nosotros' => 'Nosotros · NIKAN',
    'vr' => 'Museo en VR · NIKAN',
    'arte_detalle' => 'Obra de Arte · NIKAN',
    'lit_detalle' => 'Obra Literaria · NIKAN',
    'poe_detalle' => 'Poema · NIKAN',
];
$pageTitle = $titulos[$page] ?? 'NIKAN · Museo';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Alegreya:ital,wght@0,500;0,700;0,800;1,500&family=Dancing+Script:wght@600;800&family=Montserrat:wght@500;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/nikan-anim.css">
    <style>
        @font-face {
            font-family: 'Felthgothic';
            src: url('../fonts/Felthgothic Bold Italic.otf') format('opentype');
            font-weight: bold;
            font-style: italic;
        }
        @font-face {
            font-family: 'Nikan Felthgothic';
            src: url('../fonts/Felthgothic Bold.ttf') format('opentype');
        }
        @font-face {
            font-family: 'Rustica';
            src: url('../fonts/rustica-plains.regular.ttf') format('truetype');
        }
        :root {
            --nikan-bg: #C6372E;
            --nikan-bg-rgb: 195, 55, 46;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Montserrat', sans-serif;
            -webkit-font-smoothing: antialiased;
            margin: 0;
            min-height: 100vh;
            width: 100%;
            background-image: url('assets/fondo.svg');
            background-position: center center;
            background-repeat: no-repeat;
            background-size: cover;
            position: relative;
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.45);
            z-index: 1;
            pointer-events: none;
        }
        .header {
            background: rgb(var(--nikan-bg-rgb));
            display: flex;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            padding: 28px 48px;
        }
        .header__logo {
            font-family: 'Rustica', serif;
            font-size: 48px;
            letter-spacing: 2px;
            color: #fff;
            text-decoration: none;
            position: relative;
            z-index: 2;
        }
        .header__leon {
            position: absolute;
            right: 48px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 2;
            height: 56px;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .header__leon:hover {
            opacity: 0.85;
        }
        .header__user {
            position: absolute;
            right: 30px;
            top: calc(100% + 10px);
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
            padding: 14px 18px;
            min-width: 220px;
            z-index: 150;
            display: none;
        }
        .header__user.open {
            display: block;
        }
        .header__user-name {
            font: oblique bold 100% 'Felthgothic', cursive;
            font-size: 20px;
            color: var(--nikan-bg);
        }
        .header__user-role {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #666;
            margin: 2px 0 12px;
        }
        .header__user-link {
            display: block;
            padding: 8px 0;
            color: #333;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            border-top: 1px solid #eee;
        }
        .header__user-link:hover {
            color: var(--nikan-bg);
        }
        .header__nav {
            display: flex;
            justify-content: center;
            gap: 32px;
            position: absolute;
            left: 0;
            right: 0;
            margin: 0 auto;
            width: max-content;
            max-width: 100%;
            transition: transform 0.45s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.3s ease;
        }
        .header__search {
            position: absolute;
            left: 220px;
            top: 50%;
            transform: translateY(-50%);
            z-index: 4;
            display: flex;
            align-items: center;
            width: 48px;
            height: 48px;
            transition: width 0.45s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .header__search.open { width: min(340px, calc(100vw - 250px)); }
        .header__search-button {
            width: 48px;
            height: 48px;
            flex: 0 0 48px;
            border: 2px solid rgba(255, 255, 255, 0.75);
            border-radius: 50%;
            background: transparent;
            color: #fff;
            cursor: pointer;
            display: grid;
            place-items: center;
            transition: background 0.25s ease, transform 0.25s ease;
        }
        .header__search-button:hover,
        .header__search.open .header__search-button {
            background: rgba(255, 255, 255, 0.16);
            transform: scale(1.08);
        }
        .header__search-button svg { width: 22px; height: 22px; }
        .header__search-input {
            width: 0;
            min-width: 0;
            height: 42px;
            margin-left: 8px;
            padding: 0;
            border: 0;
            border-radius: 21px;
            outline: 0;
            color: #38251f;
            background: #fff;
            font: 600 14px 'Montserrat', sans-serif;
            opacity: 0;
            transition: width 0.45s ease, opacity 0.25s ease, padding 0.45s ease;
        }
        .header__search.open .header__search-input {
            width: calc(100% - 56px);
            padding: 0 16px;
            opacity: 1;
        }
        .header__search-results {
            position: absolute;
            top: calc(100% + 10px);
            left: 0;
            width: min(390px, calc(100vw - 32px));
            max-height: 390px;
            overflow-y: auto;
            padding: 8px;
            border-radius: 12px;
            background: #fff;
            box-shadow: 0 14px 34px rgba(0, 0, 0, 0.28);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-8px);
            transition: opacity 0.25s ease, transform 0.25s ease, visibility 0.25s;
        }
        .header__search-results.open {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .header__search-result {
            display: block;
            padding: 10px 12px;
            border-radius: 8px;
            color: #38251f;
            text-decoration: none;
            transition: background 0.2s ease, transform 0.2s ease;
        }
        .header__search-result:hover { background: #f5e8d8; transform: translateX(4px); }
        .header__search-result-type {
            display: block;
            color: var(--nikan-bg);
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .header__search-result-title { display: block; font-size: 14px; font-weight: 800; }
        .header__search-result-author { display: block; color: #76675e; font-size: 12px; margin-top: 2px; }
        .header__search-empty { padding: 14px 12px; color: #76675e; font-size: 13px; }
        .header.search-open .header__nav {
            transform: translateX(150px);
            opacity: 0.35;
            pointer-events: none;
        }
        .header__link {
            font: oblique bold 120% 'Felthgothic', cursive;
            font-size: 20px;
            color: #fff;
            text-decoration: none;
            text-transform: uppercase;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .header__link img {
            width: 44px;
            height: 44px;
            object-fit: contain;
        }
        .header__link:hover {
            opacity: 0.8;
        }
        .header__link--vr {
            color: #ffe08a;
        }
        .header__link--vr img {
            width: 42px;
            height: 42px;
        }
        @media (max-width: 900px) {
            .header { padding: 20px 24px; }
            .header__logo { font-size: 38px; }
            .header__search { left: 180px; }
            .header__nav { gap: 14px; }
            .header__link { font-size: 15px; gap: 3px; }
            .header__link img { width: 30px; height: 30px; }
            .header.search-open .header__nav { transform: translateX(100px); }
        }
        @media (max-width: 680px) {
            .header { padding: 16px 18px; }
            .header__logo { font-size: 32px; }
            .header__leon { right: 18px; height: 44px; }
            .header__search { left: 132px; }
            .header__search.open { width: calc(100vw - 150px); }
            .header__nav { display: none; }
            .header__search-results { left: -114px; }
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="/" class="header__logo">NIKAN</a>
        <div class="header__search" id="siteSearch">
            <button type="button" class="header__search-button" id="siteSearchButton" aria-label="Buscar obras o autores" aria-expanded="false">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" stroke-linecap="round" aria-hidden="true">
                    <circle cx="10.8" cy="10.8" r="6.8"></circle>
                    <path d="m16 16 5 5"></path>
                </svg>
            </button>
            <input type="search" class="header__search-input" id="siteSearchInput" placeholder="Buscar obra o autor..." autocomplete="off" aria-label="Buscar obra o autor">
            <div class="header__search-results" id="siteSearchResults" role="listbox"></div>
        </div>
        <?php if ($loggedIn): ?>
            <button type="button" class="header__leon-btn" onclick="document.getElementById('userMenu').classList.toggle('open')" style="background:none;border:none;padding:0;">
                <img src="assets/leon-verde.svg" alt="León" class="header__leon">
            </button>
            <div id="userMenu" class="header__user">
                <div class="header__user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                <div class="header__user-role"><?php echo $role; ?></div>
                <?php if ($role === 'admin'): ?>
                    <a class="header__user-link" href="admin/dashboard.php">Panel admin</a>
                <?php endif; ?>
                <a class="header__user-link" href="auth/logout.php">Cerrar sesión</a>
            </div>
        <?php else: ?>
            <a href="auth/login.php" aria-label="Iniciar sesión">
                <img src="assets/leon.svg" alt="León" class="header__leon">
            </a>
        <?php endif; ?>
        <nav class="header__nav">
            <a href="?page=inicio" class="header__link"><img src="assets/inicio.svg" alt="Inicio">Inicio</a>
            <a href="?page=arte" class="header__link"><img src="assets/arte.svg" alt="Arte">Arte</a>
            <a href="?page=literatura" class="header__link"><img src="assets/literatura.svg" alt="Literatura">Literatura</a>
            <a href="?page=poesia" class="header__link"><img src="assets/literatura.svg" alt="Poesía">Poesía</a>
            <a href="?page=musica" class="header__link"><img src="assets/musica.svg" alt="Música">Música</a>
            <a href="?page=nosotros" class="header__link"><img src="assets/nosotros.svg" alt="Nosotros">Nosotros</a>
            <a href="?page=vr" class="header__link header__link--vr"><img src="assets/vr.svg" alt="VR">VR</a>
        </nav>
    </header>
    <script>
    (function () {
        var header = document.querySelector('.header');
        var search = document.getElementById('siteSearch');
        var button = document.getElementById('siteSearchButton');
        var input = document.getElementById('siteSearchInput');
        var results = document.getElementById('siteSearchResults');
        var timer;

        function showResults(items, message) {
            if (!items.length) {
                results.innerHTML = '<div class="header__search-empty">' + message + '</div>';
            } else {
                results.innerHTML = items.map(function (item) {
                    return '<a class="header__search-result" href="' + item.url + '">' +
                        '<span class="header__search-result-type">' + item.type + '</span>' +
                        '<span class="header__search-result-title">' + item.title + '</span>' +
                        (item.author ? '<span class="header__search-result-author">' + item.author + '</span>' : '') +
                        '</a>';
                }).join('');
            }
            results.classList.add('open');
        }

        button.addEventListener('click', function () {
            var isOpen = search.classList.toggle('open');
            header.classList.toggle('search-open', isOpen);
            button.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            if (isOpen) input.focus();
            else results.classList.remove('open');
        });
        input.addEventListener('input', function () {
            var query = input.value.trim();
            clearTimeout(timer);
            if (query.length < 2) {
                results.classList.remove('open');
                return;
            }
            showResults([], 'Buscando...');
            timer = setTimeout(function () {
                fetch('buscar.php?q=' + encodeURIComponent(query), { headers: { 'Accept': 'application/json' } })
                    .then(function (response) {
                        if (!response.ok) throw new Error('search_failed');
                        return response.json();
                    })
                    .then(function (data) {
                        showResults(data.ok ? data.results : [], data.message || 'No se encontraron coincidencias.');
                    })
                    .catch(function () {
                        showResults([], 'No se pudo realizar la búsqueda.');
                    });
            }, 220);
        });
        document.addEventListener('click', function (e) {
            var menu = document.getElementById('userMenu');
            if (menu && !e.target.closest('.header__leon-btn') && !e.target.closest('.header__user')) {
                menu.classList.remove('open');
            }
            if (!e.target.closest('.header__search')) {
                results.classList.remove('open');
            }
        });
    })();
    </script>
