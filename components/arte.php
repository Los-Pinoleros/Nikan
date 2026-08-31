<?php
/**
 * Vista de Arte: "La Vida Precolombina"
 * Diseño centralizado: texto a la izquierda, imagen a la derecha.
 * Vibra de museo: spotlight, marco dorado, plaquita, fondo cálido, sombra.
 */
?>

<section class="arte-section">
    <div class="arte-container">

        <!-- LADO IZQUIERDO -->
        <div class="arte-texto">
            <div class="arte-tag">
                <img src="assets/arte.svg" alt="Arte" class="icono">
                <span>ARTE</span>
            </div>

            <h1>LA VIDA<br>PRECOLOMBINA</h1>

            <p>
                Sala “La Vida Precolombina” refleja la llegada del hombre a nuestro territorio
                hasta el desarrollo de la técnica cerámica en el país, la cual está íntimamente
                ligada a la producción agrícola.
            </p>
        </div>

        <!-- LADO DERECHO -->
        <div class="arte-imagen">
            <span class="spotlight"></span>
            <span class="marco-dorado"></span>
            <img src="assets/estatuaarte1.svg" alt="Estatua precolombina">
            <span class="pie-sombra"></span>
        </div>

    </div>

    <a href="#" class="ver-mas">VER MÁS ↓</a>
</section>

<style>
@font-face {
    font-family: 'Felthgothic Bold';
    src: url('../fonts/Felthgothic Bold.otf') format('opentype');
}

.arte-section {
    background:
        radial-gradient(ellipse at 25% 40%, rgba(255, 244, 222, 0.9), transparent 60%),
        radial-gradient(ellipse at 75% 45%, rgba(255, 244, 222, 0.7), transparent 55%),
        linear-gradient(135deg, #f6efe3 0%, #efe4d0 45%, #e7d8bd 100%);
    padding: 80px 80px 0;
    font-family: 'Felthgothic Bold', 'Felthgothic', 'Arial', sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    position: relative;
    z-index: 2;
    overflow: hidden;
}

/* Textura sutil de grano de pared antigua */
.arte-section::before {
    content: "";
    position: absolute;
    inset: 0;
    background-image:
        radial-gradient(circle at 20% 30%, rgba(120, 90, 40, 0.05) 0, transparent 40%),
        radial-gradient(circle at 80% 70%, rgba(120, 90, 40, 0.05) 0, transparent 40%),
        radial-gradient(circle at 50% 50%, rgba(120, 90, 40, 0.04) 0, transparent 50%);
    pointer-events: none;
}

.arte-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    gap: 40px;
    position: relative;
}

/* TEXTO */
.arte-texto {
    max-width: 45%;
    padding-bottom: 120px;
}

.arte-tag {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #0a7a4b;
    font-size: 30px;
    letter-spacing: 4px;
    margin-bottom: 24px;
    text-shadow: 0 1px 0 rgba(255, 255, 255, 0.4);
}

.arte-tag .icono {
    width: 44px;
    height: 44px;
    object-fit: contain;
    filter: invert(1) brightness(0);
}

.arte-texto h1 {
    font-size: 82px;
    color: #C6372E;
    margin: 0 0 24px;
    line-height: 1.05;
    letter-spacing: 3px;
    text-shadow: 0 2px 2px rgba(90, 60, 20, 0.25);
}

.arte-texto p {
    font-size: 22px;
    color: #4a3b22;
    line-height: 1.7;
    margin-bottom: 30px;
    max-width: 560px;
}

/* Plaquita / etiqueta tipo museo */
.ver-mas {
    color: #0a7a4b;
    text-decoration: none;
    font-weight: bold;
    font-size: 18px;
    letter-spacing: 2px;
    position: absolute;
    bottom: 50px;
    left: 50%;
    transform: translateX(-50%);
    z-index: 5;
    text-shadow: 0 1px 0 rgba(255, 255, 255, 0.5);
}

/* IMAGEN */
.arte-imagen {
    position: relative;
    text-align: center;
    align-self: flex-end;
    margin-bottom: -140px;
    transform: translateY(-60px);
    padding: 24px;
}

/* Marco decorativo dorado detrás de la estatua */
.marco-dorado {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 76%;
    height: 88%;
    transform: translate(-50%, -52%);
    border: 2px solid #c9a94f;
    border-radius: 4px;
    box-shadow:
        0 0 0 6px rgba(201, 169, 79, 0.15),
        0 0 0 1px #8a6a2f inset;
    background: rgba(255, 252, 244, 0.25);
    pointer-events: none;
    z-index: 0;
}

/* Esquinas ornamentales del marco */
.marco-dorado::before,
.marco-dorado::after {
    content: "";
    position: absolute;
    width: 40px;
    height: 40px;
    border: 3px solid #8a6a2f;
}
.marco-dorado::before {
    top: -3px;
    left: -3px;
    border-right: none;
    border-bottom: none;
}
.marco-dorado::after {
    top: -3px;
    right: -3px;
    border-left: none;
    border-bottom: none;
}

.arte-imagen img {
    width: 560px;
    height: auto;
    display: block;
    position: relative;
    z-index: 2;
}

/* Efecto spotlight: halo de luz cálida sobre la estatua */
.spotlight {
    position: absolute;
    top: 22%;
    left: 50%;
    width: 90%;
    height: 75%;
    transform: translateX(-50%);
    background: radial-gradient(ellipse at 50% 30%, rgba(255, 250, 230, 0.85), rgba(255, 244, 210, 0.25) 55%, transparent 75%);
    filter: blur(12px);
    pointer-events: none;
    z-index: 1;
    mix-blend-mode: screen;
}

/* Sombra proyectada en el piso (pie de estatua) */
.pie-sombra {
    position: absolute;
    bottom: 8%;
    left: 50%;
    width: 70%;
    height: 8%;
    transform: translateX(-50%);
    background: radial-gradient(ellipse at center, rgba(60, 35, 10, 0.5), transparent 70%);
    filter: blur(12px);
    pointer-events: none;
    z-index: 3;
}
</style>
