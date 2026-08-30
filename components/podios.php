<?php
/**
 * Escena de exhibición: podios.svg como fondo y carrusel de 3 objetos
 * (tazon1, tazon2, estatua1) sobre los podios centrales.
 * El objeto central resalta. Cambia cada 6 segundos.
 */
?>

<div class="museo">
    <img src="assets/podios.svg" alt="Exhibición de podios" class="museo__img">

    <img src="assets/pod1.svg" alt="Pod 1" class="museo__pod1">
    <img src="assets/pod2.svg" alt="Pod 2" class="museo__pod2">
    <img src="assets/pod3.svg" alt="Pod 3" class="museo__pod3">
    <img src="assets/pod4.svg" alt="Pod 4" class="museo__pod4">

    <div class="carrusel" id="carrusel">
        <div class="carrusel__slide carrusel__slide--1">
            <img src="assets/tazon1.svg" alt="Tazón 1">
        </div>
        <div class="carrusel__slide carrusel__slide--2">
            <img src="assets/tazon2.svg" alt="Tazón 2">
        </div>
        <div class="carrusel__slide carrusel__slide--3">
            <img src="assets/estatua1.svg" alt="Estatua">
        </div>
    </div>
</div>

<style>
.museo {
    position: relative;
    z-index: 2;
    width: 100%;
    min-height: 100vh;
    overflow: hidden;
}

/* Efecto de luz spotlight triangular que baja desde el header */
.museo::before {
    content: "";
    position: absolute;
    top: 0;
    left: 10%;
    width: 80%;
    height: 75%;
    background: linear-gradient(
        to bottom,
        rgba(255, 255, 255, 0.95) 0%,
        rgba(255, 235, 200, 0.35) 35%,
        rgba(255, 255, 255, 0.05) 70%,
        rgba(255, 255, 255, 0) 100%
    );
    /* triángulo invertido: punta arriba, base abajo (base ancha) */
    clip-path: polygon(50% 0%, 28% 100%, 72% 100%);
    /* degrada (suaviza) los bordes laterales del triángulo */
    -webkit-mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);
    mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);
    filter: blur(2px);
    pointer-events: none;
    z-index: 20;
}

.museo__img {
    display: block;
    width: 100%;
    height: 100vh;
    object-fit: cover;
}

/* Pod1 estático a la derecha del carrusel (posicion pequeña, ajustable) */
.museo__pod1 {
    position: absolute;
    right: calc(6% - 20px);
    top: 40%;
    width: 220px;
    height: auto;
    object-fit: contain;
    transform: translateY(-50%);
    z-index: 5;
    filter: drop-shadow(0 10px 14px rgba(0, 0, 0, 0.35));
}

/* Pod2 estático debajo de pod1 (posicion pequeña, ajustable) */
.museo__pod2 {
    position: absolute;
    right: calc(5% - 35px);
    top: calc(74% + 60px);
    width: 200px;
    height: auto;
    object-fit: contain;
    transform: translateY(-50%);
    z-index: 5;
    filter: drop-shadow(0 10px 14px rgba(0, 0, 0, 0.35));
}

/* Pod3 estático a la izquierda del carrusel, a la altura de pod1 (ajustable) */
.museo__pod3 {
    position: absolute;
    left: calc(6% + 15px);
    top: calc(38% - 10px);
    width: 180px;
    height: auto;
    object-fit: contain;
    transform: translateY(-50%);
    z-index: 5;
    filter: drop-shadow(0 10px 14px rgba(0, 0, 0, 0.35));
}

/* Pod4 estático a la izquierda del carrusel, a la misma altura que pod2 (ajustable) */
.museo__pod4 {
    position: absolute;
    left: calc(6% - 60px);
    top: calc(74% + 60px);
    width: 200px;
    height: auto;
    object-fit: contain;
    transform: translateY(-50%);
    z-index: 5;
    filter: drop-shadow(0 10px 14px rgba(0, 0, 0, 0.35));
}

/* ================= CARRUSEL sobre los podios ================= */
.carrusel {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    overflow: hidden;
}

.carrusel__slide {
    position: absolute;
    top: 66%;           /* posición base */
    left: 50%;
    width: 36vw;
    height: 60vh;
    transform: translate(-50%, -50%) scale(0.6);
    transition: transform 1s ease, opacity 1s ease, filter 1s ease, top 1s ease;
    opacity: 0;
    filter: brightness(0.55);
    pointer-events: none;
}

.carrusel__slide img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: contain;
}

/* sombra proyectada bajo el objeto */
.carrusel__slide::after {
    content: "";
    position: absolute;
    bottom: 4%;
    left: 50%;
    width: 100%;
    height: 10%;
    transform: translateX(-50%);
    background: radial-gradient(ellipse at center, rgba(6, 3, 1, 0.85), rgba(6, 3, 1, 0));
    filter: blur(11px);
}

.carrusel__slide--izq {
    top: 64%;           /* laterales bajan un poco */
    transform: translate(-115%, -50%) scale(0.62);
    opacity: 0.85;
    filter: brightness(0.7);
}
.carrusel__slide--centro {
    top: 66%;           /* centro sube un poco */
    transform: translate(-50%, -50%) scale(1.25);
    opacity: 1;
    filter: brightness(1);
    z-index: 3;
}
.carrusel__slide--dcha {
    top: 64%;           /* laterales bajan un poco */
    transform: translate(15%, -50%) scale(0.62);
    opacity: 0.85;
    filter: brightness(0.7);
}
</style>

<script>
(function () {
    var slides = Array.prototype.slice.call(
        document.querySelectorAll('#carrusel .carrusel__slide')
    );
    if (slides.length < 3) return;
    var actual = 0;

    function render() {
        var prev = (actual + slides.length - 1) % slides.length;
        var next = (actual + 1) % slides.length;
        slides.forEach(function (s) { s.className = 'carrusel__slide'; });
        slides[prev].classList.add('carrusel__slide--izq');
        slides[actual].classList.add('carrusel__slide--centro');
        slides[next].classList.add('carrusel__slide--dcha');
        slides[prev].style.zIndex = 1;
        slides[next].style.zIndex = 2;
        slides[actual].style.zIndex = 3;
    }

    render();
    setInterval(function () {
        actual = (actual + 1) % slides.length;
        render();
    }, 6000);
})();
</script>
