<?php
/**
 * Carrusel de 3 objetos. El objeto central resalta (más grande, a plena luz);
 * los laterales se ven más pequeños y atenuados. Cambia cada 6 segundos.
 */
?>

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

<style>
.carrusel {
    position: relative;
    width: 100%;
    min-height: 55vh;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.carrusel__slide {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 26vw;
    height: 52vh;
    transform: translate(-50%, -50%) scale(0.6);
    transition: transform 1s ease, opacity 1s ease, filter 1s ease;
    opacity: 0;
    filter: brightness(0.55);
    pointer-events: none;
}

.carrusel__slide img {
    display: block;
    width: 100%;
    height: 100%;
    object-fit: contain;
    filter: drop-shadow(0 20px 28px rgba(0, 0, 0, 0.35));
}

/* ---------- Posiciones del "escenario" ---------- */
.carrusel__slide--izq {
    transform: translate(-115%, -50%) scale(0.68);
    opacity: 0.55;
    filter: brightness(0.55);
}
.carrusel__slide--centro {
    transform: translate(-50%, -50%) scale(1);
    opacity: 1;
    filter: brightness(1);
    z-index: 3;
}
.carrusel__slide--dcha {
    transform: translate(15%, -50%) scale(0.68);
    opacity: 0.55;
    filter: brightness(0.55);
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
        // orden de apilamiento
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
