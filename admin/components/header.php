<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600;800&family=Montserrat:wght@500;600;800&display=swap" rel="stylesheet">
    <style>
        @font-face {
            font-family: 'Felthgothic';
            src: url('../../fonts/Felthgothic Bold Italic.otf') format('opentype');
            font-weight: bold;
            font-style: italic;
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
            background-image: url('../../assets/fondo.svg');
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
            font: oblique bold 120% 'Felthgothic', cursive;
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
        .header__menu-backdrop { display: none; }
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
        .admin-feedback {
            position: fixed;
            inset: 0;
            z-index: 500;
            display: grid;
            place-items: center;
            padding: 20px;
            background: rgba(0,0,0,.58);
            opacity: 0;
            visibility: hidden;
            transition: opacity .25s ease, visibility .25s ease;
        }
        .admin-feedback.open { opacity: 1; visibility: visible; }
        .admin-feedback__dialog {
            width: min(420px, 100%);
            padding: 28px 26px 24px;
            border: 1px solid rgba(3,212,55,.75);
            border-radius: 14px;
            color: #fff;
            background: #30201c;
            box-shadow: 0 20px 55px rgba(0,0,0,.42);
            text-align: center;
            transform: translateY(12px) scale(.97);
            transition: transform .25s ease;
        }
        .admin-feedback--delete .admin-feedback__dialog { border-color: rgba(255,224,138,.75); }
        .admin-feedback--error .admin-feedback__dialog { border-color: rgba(255,99,99,.78); }
        .admin-feedback.open .admin-feedback__dialog { transform: translateY(0) scale(1); }
        .admin-feedback__icon { color: #03d437; font-size: 34px; line-height: 1; }
        .admin-feedback__title { margin: 12px 0 8px; font: oblique bold 26px 'Felthgothic', cursive; }
        .admin-feedback__message { color: rgba(255,255,255,.78); font-size: 13px; line-height: 1.5; }
        .admin-feedback__close {
            margin-top: 20px;
            padding: 10px 24px;
            border: 0;
            border-radius: 7px;
            color: #172318;
            background: #03d437;
            font: 800 11px 'Montserrat', sans-serif;
            letter-spacing: 1px;
            text-transform: uppercase;
            cursor: pointer;
        }
        @media (max-width: 900px) {
            .header { padding: 20px 24px 20px 76px; min-height: 78px; }
            .header__logo { font-size: 38px; }
        }
        @media (max-width: 680px) {
            .header {
                padding: 16px 18px 16px 76px;
                min-height: 70px;
                -webkit-backdrop-filter: none !important;
                backdrop-filter: none !important;
            }
            .header__logo { font-size: 32px; }
            .header__leon { right: 18px; height: 44px; }
            .header__menu-button {
                display: block;
                position: fixed;
                top: 13px;
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
                width: min(84vw, 330px);
                max-width: none;
                margin: 0;
                padding: 88px 18px 24px;
                background: #30201c;
                overflow-y: auto;
                transform: translateX(-105%);
                box-shadow: 12px 0 30px rgba(0,0,0,.3);
                transition: transform .45s cubic-bezier(.16,1,.3,1);
                z-index: 101;
            }
            .header__nav.open { transform: translateX(0); }
            .header__nav .header__link {
                justify-content: flex-start;
                width: 100%;
                padding: 13px 8px;
                font-size: 17px;
                border-bottom: 1px solid rgba(255,255,255,.12);
            }
            .header__nav .header__link img { width: 34px; height: 34px; }
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
        }
    </style>
</head>
<body>
    <header class="header">
        <button type="button" class="header__menu-button" id="mobileMenuButton" aria-label="Abrir menú" aria-expanded="false">
            <span></span><span></span><span></span>
        </button>
        <a href="../index.php" class="header__logo">NIKAN</a>
        <button type="button" class="header__leon-btn" onclick="document.getElementById('userMenu').classList.toggle('open')" style="background:none;border:none;padding:0;">
            <img src="../../assets/leon-verde.svg" alt="León" class="header__leon">
        </button>
        <div id="userMenu" class="header__user">
            <div class="header__user-name"><?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?></div>
            <div class="header__user-role"><?php echo $_SESSION['role'] ?? ''; ?></div>
            <a class="header__user-link" href="../index.php">Ver sitio</a>
            <a class="header__user-link" href="../auth/logout.php">Cerrar sesión</a>
        </div>
        <nav class="header__nav">
            <a href="dashboard.php" class="header__link"><img src="../../assets/inicio.svg" alt="Dashboard">Dashboard</a>
            <a href="autores.php" class="header__link"><img src="../../assets/nosotros.svg" alt="Autores">Autores</a>
            <a href="arte.php" class="header__link"><img src="../../assets/arte.svg" alt="Arte">Arte</a>
            <a href="literatura.php" class="header__link"><img src="../../assets/literatura.svg" alt="Literatura">Literatura</a>
            <a href="musica.php" class="header__link"><img src="../../assets/musica.svg" alt="Música">Música</a>
            <a href="virtuales.php" class="header__link"><img src="../../assets/vr.svg" alt="Museos virtuales">Museos virtuales</a>
            <a href="tienda.php" class="header__link"><img src="../../assets/nosotros.svg" alt="Tienda Cultural">Tienda Cultural</a>
            <a href="#" class="header__link"><img src="../../assets/nosotros.svg" alt="Usuarios">Usuarios</a>
        </nav>
        <div class="header__menu-backdrop" id="mobileMenuBackdrop"></div>
    </header>
    <div class="admin-feedback" id="adminFeedback" role="alertdialog" aria-modal="true" aria-labelledby="adminFeedbackTitle">
        <div class="admin-feedback__dialog">
            <div class="admin-feedback__icon" id="adminFeedbackIcon">✓</div>
            <h2 class="admin-feedback__title" id="adminFeedbackTitle">Operación completada</h2>
            <p class="admin-feedback__message" id="adminFeedbackMessage"></p>
            <button class="admin-feedback__close" type="button" id="adminFeedbackClose">Continuar</button>
        </div>
    </div>
    <script>
    window.adminNotify = function (message, type, callback) {
        var modal = document.getElementById('adminFeedback');
        if (!modal) {
            if (callback) callback();
            return;
        }
        var title = document.getElementById('adminFeedbackTitle');
        var icon = document.getElementById('adminFeedbackIcon');
        document.getElementById('adminFeedbackMessage').textContent = message || 'La operación se completó correctamente.';
        title.textContent = type === 'error' ? 'No se pudo completar' : (type === 'delete' ? 'Elemento eliminado' : 'Cambios guardados');
        icon.textContent = type === 'error' ? '!' : (type === 'delete' ? '×' : '✓');
        icon.style.color = type === 'error' ? '#ff8c76' : (type === 'delete' ? '#ffe08a' : '#03d437');
        modal.classList.remove('admin-feedback--delete', 'admin-feedback--error');
        if (type === 'delete') modal.classList.add('admin-feedback--delete');
        if (type === 'error') modal.classList.add('admin-feedback--error');
        modal.classList.add('open');
        var close = function () {
            modal.classList.remove('open');
            document.getElementById('adminFeedbackClose').removeEventListener('click', close);
            if (callback) callback();
        };
        document.getElementById('adminFeedbackClose').addEventListener('click', close);
        modal.addEventListener('click', function (event) {
            if (event.target === modal) close();
        }, { once: true });
    };
    (function () {
        var button = document.getElementById('mobileMenuButton');
        var nav = document.querySelector('.header__nav');
        var backdrop = document.getElementById('mobileMenuBackdrop');
        function toggleMenu(open) {
            nav.classList.toggle('open', open);
            button.classList.toggle('open', open);
            backdrop.classList.toggle('open', open);
            button.setAttribute('aria-expanded', open ? 'true' : 'false');
            button.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
        }
        button.addEventListener('click', function () { toggleMenu(!nav.classList.contains('open')); });
        backdrop.addEventListener('click', function () { toggleMenu(false); });
        nav.addEventListener('click', function (event) {
            if (event.target.closest('a')) toggleMenu(false);
        });
    })();
    document.addEventListener('click', function (e) {
        var menu = document.getElementById('userMenu');
        if (menu && !e.target.closest('.header__leon-btn') && !e.target.closest('.header__user')) {
            menu.classList.remove('open');
        }
    });
    </script>
