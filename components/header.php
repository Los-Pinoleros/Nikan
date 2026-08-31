<?php
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
            src: url('../fonts/Felthgothic Bold Italic.otf') format('opentype');
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
        <img src="assets/leon.svg" alt="León" class="header__leon">
        <nav class="header__nav">
            <a href="?page=inicio" class="header__link"><img src="assets/inicio.svg" alt="Inicio">Inicio</a>
            <a href="?page=arte" class="header__link"><img src="assets/arte.svg" alt="Arte">Arte</a>
            <a href="?page=literatura" class="header__link"><img src="assets/literatura.svg" alt="Literatura">Literatura</a>
            <a href="?page=musica" class="header__link"><img src="assets/musica.svg" alt="Música">Música</a>
            <a href="?page=nosotros" class="header__link"><img src="assets/nosotros.svg" alt="Nosotros">Nosotros</a>
        </nav>
    </header>
