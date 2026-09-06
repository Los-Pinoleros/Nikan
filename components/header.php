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
    'musica' => 'Música · NIKAN',
    'musica_detalle' => 'Pieza Musical · NIKAN',
    'nosotros' => 'Nosotros · NIKAN',
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
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600;800&family=Montserrat:wght@500;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/nikan-anim.css">
    <style>
        @font-face {
            font-family: 'Felthgothic';
            src: url('../fonts/Felthgothic Bold Italic.otf') format('opentype');
            font-weight: bold;
            font-style: italic;
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
    </style>
</head>
<body>
    <header class="header">
        <a href="/" class="header__logo">NIKAN</a>
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
        </nav>
    </header>
    <script>
    document.addEventListener('click', function (e) {
        var menu = document.getElementById('userMenu');
        if (menu && !e.target.closest('.header__leon-btn') && !e.target.closest('.header__user')) {
            menu.classList.remove('open');
        }
    });
    </script>
