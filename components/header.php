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
    'poesia' => 'Literatura · NIKAN',
    'autores' => 'Autores · NIKAN',
    'autor_detalle' => 'Ficha de autor · NIKAN',
    'musica' => 'Música · NIKAN',
    'musica_detalle' => 'Pieza Musical · NIKAN',
    'nosotros' => 'Tienda Cultural · NIKAN',
    'tienda' => 'Tienda Cultural · NIKAN',
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
    <link rel="stylesheet" href="assets/nikan-anim.css?v=20261010">
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
        .header__menu-button {
            display: none;
            position: absolute;
            left: 18px;
            top: 50%;
            z-index: 210;
            width: 44px;
            height: 44px;
            padding: 10px;
            border: 0;
            background: transparent;
            cursor: pointer;
        }
        .header__menu-button span {
            display: block;
            height: 2px;
            margin: 5px 0;
            background: #fff;
            transition: transform .3s ease, opacity .2s ease;
        }
        .header__menu-button.open span:first-child { transform: translateY(7px) rotate(45deg); }
        .header__menu-button.open span:nth-child(2) { opacity: 0; }
        .header__menu-button.open span:last-child { transform: translateY(-7px) rotate(-45deg); }
        .header__menu-backdrop {
            display: none;
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
            border: 0;
            border-radius: 0;
            background: transparent;
            cursor: pointer;
            display: grid;
            place-items: center;
            transition: transform 0.25s ease, opacity 0.25s ease;
        }
        .header__search-button:hover,
        .header__search.open .header__search-button {
            transform: scale(1.08);
            opacity: .82;
        }
        .header__search-button img { width: 34px; height: 34px; object-fit: contain; }
        .header__search-input {
            width: 0;
            min-width: 0;
            height: 42px;
            margin-left: 8px;
            padding: 0;
            border: 0;
            border-radius: 8px;
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
        .header__dropdown {
            position: relative;
        }
        .header__dropdown-button {
            border: 0;
            background: transparent;
            cursor: pointer;
        }
        .header__dropdown-button::after {
            content: "";
            width: 7px;
            height: 7px;
            margin: -4px 0 0 2px;
            border-right: 2px solid currentColor;
            border-bottom: 2px solid currentColor;
            transform: rotate(45deg);
            transition: transform .2s ease;
        }
        .header__dropdown.open .header__dropdown-button::after {
            transform: rotate(225deg);
            margin-top: 4px;
        }
        .header__dropdown-menu {
            position: absolute;
            top: calc(100% + 18px);
            left: 50%;
            min-width: 190px;
            padding: 8px;
            border: 1px solid rgba(198, 55, 46, .28);
            background: #fff;
            box-shadow: 0 14px 30px rgba(0, 0, 0, .22);
            opacity: 0;
            visibility: hidden;
            transform: translate(-50%, -8px);
            transition: opacity .2s ease, transform .2s ease, visibility .2s;
            z-index: 160;
        }
        .header__dropdown.open .header__dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translate(-50%, 0);
        }
        .header__dropdown-link {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 11px 14px;
            color: #C6372E;
            font: oblique bold 16px 'Felthgothic', cursive;
            text-decoration: none;
            text-transform: uppercase;
            transition: color .2s ease, background .2s ease, padding-left .2s ease;
        }
        .header__dropdown-link:hover {
            padding-left: 19px;
            color: #a72e27;
            background: rgba(198, 55, 46, .08);
        }
        .header__dropdown-link img {
            display: none;
        }
        @media (max-width: 900px) {
            .header { padding: 20px 24px; }
            .header__logo { font-size: 38px; }
            .header__search { left: 180px; }
            .header__nav { gap: 14px; }
            .header__link { font-size: 15px; gap: 3px; }
            .header__link img { width: 30px; height: 30px; }
            .header__dropdown-link { font-size: 14px; }
            .header.search-open .header__nav { transform: translateX(100px); }
        }
        @media (max-width: 680px) {
            .header {
                min-height: 76px;
                padding: 16px 18px 16px 76px;
                -webkit-backdrop-filter: none !important;
                backdrop-filter: none !important;
            }
            .header__logo { font-size: 32px; }
            .header__leon { right: 18px; height: 44px; }
            .header__search { display: none; }
            .header__menu-button {
                display: block;
                position: fixed;
                top: 16px;
                transform: none;
            }
            .header__nav {
                display: flex;
                flex-direction: column;
                align-items: stretch;
                justify-content: flex-start;
                gap: 0;
                position: fixed;
                top: 0;
                bottom: 0;
                left: 0;
                width: min(82vw, 320px);
                max-width: none;
                margin: 0;
                padding: 96px 18px 24px;
                background: #30201c;
                overflow-y: auto;
                transform: translateX(-105%);
                opacity: 1;
                box-shadow: 12px 0 30px rgba(0,0,0,.3);
                transition: transform .45s cubic-bezier(.16,1,.3,1);
                z-index: 101;
            }
            .header__nav.open { transform: translateX(0); }
            .header__nav .header__link {
                justify-content: flex-start;
                width: 100%;
                padding: 13px 8px;
                font-size: 18px;
                border-bottom: 1px solid rgba(255,255,255,.12);
            }
            .header__nav .header__link img { width: 34px; height: 34px; }
            .header__dropdown-menu {
                position: static;
                min-width: 0;
                padding: 4px 0 4px 18px;
                border: 0;
                background: #fff;
                box-shadow: none;
                transform: none;
            }
            .header__dropdown.open .header__dropdown-menu { transform: none; }
            .header__dropdown-link {
                color: #C6372E;
                font-size: 15px;
            }
            .header__menu-backdrop {
                display: block;
                position: fixed;
                inset: 0;
                z-index: 90;
                background: rgba(0,0,0,.48);
                opacity: 0;
                visibility: hidden;
                transition: opacity .3s ease, visibility .3s ease;
            }
            .header__menu-backdrop.open { opacity: 1; visibility: visible; }
            .header__search-results { left: -114px; }
        }
    </style>
</head>
<body>
    <header class="header">
        <button type="button" class="header__menu-button" id="mobileMenuButton" aria-label="Abrir menú" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
        <a href="/" class="header__logo">NIKAN</a>
        <div class="header__search" id="siteSearch">
            <button type="button" class="header__search-button" id="siteSearchButton" aria-label="Buscar obras o autores" aria-expanded="false">
                <img src="assets/lipa.svg" alt="" aria-hidden="true">
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
            <div class="header__dropdown" id="museumDropdown">
                <button type="button" class="header__link header__dropdown-button" id="museumDropdownButton" aria-expanded="false" aria-haspopup="true">
                    <img src="assets/arte.svg" alt="">Biblioteca
                </button>
                <div class="header__dropdown-menu" id="museumDropdownMenu">
                    <a href="?page=arte" class="header__dropdown-link"><img src="assets/arte.svg" alt="">Arte</a>
                    <a href="?page=literatura" class="header__dropdown-link"><img src="assets/literatura.svg" alt="">Literatura</a>
                    <a href="?page=musica" class="header__dropdown-link"><img src="assets/musica.svg" alt="">Música</a>
                </div>
            </div>
            <a href="?page=tienda" class="header__link"><img src="assets/nosotros.svg" alt="Tienda Cultural">Tienda Cultural</a>
        </nav>
        <div class="header__menu-backdrop" id="mobileMenuBackdrop"></div>
    </header>
    <script>
    (function () {
        var header = document.querySelector('.header');
        var search = document.getElementById('siteSearch');
        var button = document.getElementById('siteSearchButton');
        var input = document.getElementById('siteSearchInput');
        var results = document.getElementById('siteSearchResults');
        var museumDropdown = document.getElementById('museumDropdown');
        var museumDropdownButton = document.getElementById('museumDropdownButton');
        var timer;
        var mobileMenuButton = document.getElementById('mobileMenuButton');
        var mobileMenuBackdrop = document.getElementById('mobileMenuBackdrop');
        var mobileNav = document.querySelector('.header__nav');

        function toggleMobileMenu(open) {
            mobileNav.classList.toggle('open', open);
            mobileMenuButton.classList.toggle('open', open);
            mobileMenuBackdrop.classList.toggle('open', open);
            mobileMenuButton.setAttribute('aria-expanded', open ? 'true' : 'false');
            mobileMenuButton.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
        }
        mobileMenuButton.addEventListener('click', function () {
            toggleMobileMenu(!mobileNav.classList.contains('open'));
        });
        mobileMenuBackdrop.addEventListener('click', function () { toggleMobileMenu(false); });

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
        museumDropdownButton.addEventListener('click', function (event) {
            event.preventDefault();
            event.stopPropagation();
            var isOpen = museumDropdown.classList.toggle('open');
            museumDropdownButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
        document.addEventListener('click', function (e) {
            var menu = document.getElementById('userMenu');
            if (menu && !e.target.closest('.header__leon-btn') && !e.target.closest('.header__user')) {
                menu.classList.remove('open');
            }
            if (!e.target.closest('.header__search')) {
                results.classList.remove('open');
            }
            if (!e.target.closest('.header__dropdown')) {
                museumDropdown.classList.remove('open');
                museumDropdownButton.setAttribute('aria-expanded', 'false');
            }
            if (e.target.closest('.header__nav a')) toggleMobileMenu(false);
        });
    })();
    </script>
