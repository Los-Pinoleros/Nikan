<?php
/**
 * Portada: escena de podios y ranking de las tres obras más consultadas.
 */
require_once __DIR__ . '/../config/config.php';

$obrasDestacadas = [];
try {
    $pdo = getDB();
    asegurar_tabla_obra_visitas($pdo);
    $obrasDestacadas = $pdo->query("
        SELECT catalogo.area, catalogo.obra_id, catalogo.titulo, catalogo.imagen,
               catalogo.pagina, COUNT(visitas.id) AS visitas
        FROM (
            SELECT 'Arte' AS area, id AS obra_id, titulo, imagen, 'arte_detalle' AS pagina
            FROM arte_obras
            UNION ALL
            SELECT 'Literatura', id, titulo, NULL, 'lit_detalle'
            FROM lit_obras
            UNION ALL
            SELECT 'Música', id, titulo, imagen, 'musica_detalle'
            FROM musica_obras
        ) AS catalogo
        LEFT JOIN obra_visitas AS visitas
            ON visitas.area = CASE
                WHEN catalogo.pagina = 'arte_detalle' THEN 'arte'
                WHEN catalogo.pagina = 'lit_detalle' THEN 'literatura'
                ELSE 'musica'
            END
            AND visitas.obra_id = catalogo.obra_id
        GROUP BY catalogo.area, catalogo.obra_id, catalogo.titulo, catalogo.imagen, catalogo.pagina
        ORDER BY visitas DESC, catalogo.titulo ASC
        LIMIT 5
    ")->fetchAll();
} catch (Exception $e) {
    error_log('[NIKAN] No se pudo cargar el ranking de obras: ' . $e->getMessage());
    try {
        $pdo = getDB();
        $obrasDestacadas = $pdo->query("
            SELECT 'Arte' AS area, id AS obra_id, titulo, imagen, 'arte_detalle' AS pagina, 0 AS visitas
            FROM arte_obras
            UNION ALL
            SELECT 'Literatura', id, titulo, NULL, 'lit_detalle', 0
            FROM lit_obras
            UNION ALL
            SELECT 'Música', id, titulo, imagen, 'musica_detalle', 0
            FROM musica_obras
            ORDER BY titulo ASC
            LIMIT 5
        ")->fetchAll();
    } catch (Exception $fallbackError) {
        error_log('[NIKAN] Tampoco se pudo cargar obras de portada: ' . $fallbackError->getMessage());
    }
}
?>

<div class="museo">
    <img src="assets/podios.svg" alt="Exhibición de podios" class="museo__img">
    <img src="assets/pod1.svg" alt="Pod 1" class="museo__pod1">
    <img src="assets/pod2.svg" alt="Pod 2" class="museo__pod2">
    <img src="assets/pod3.svg" alt="Pod 3" class="museo__pod3">
    <img src="assets/pod4.svg" alt="Pod 4" class="museo__pod4">

    <div class="obras-top" aria-label="Obras más consultadas">
        <div class="obras-top__heading">
            <span>COLECCIÓN DESTACADA</span>
            <h1>Las más visitadas</h1>
            <i></i>
        </div>
        <?php if ($obrasDestacadas): ?>
            <div class="obras-top__carousel">
                <button class="obras-top__control obras-top__control--prev" type="button" aria-label="Obra anterior">‹</button>
                <div class="obras-top__track" id="obrasTopTrack">
                <?php foreach ($obrasDestacadas as $posicion => $obra): ?>
                    <a class="obra-top"
                       href="?page=<?php echo htmlspecialchars($obra['pagina'], ENT_QUOTES, 'UTF-8'); ?>&obra_id=<?php echo (int)$obra['obra_id']; ?>&visita=1">
                        <span class="obra-top__rank">0<?php echo $posicion + 1; ?></span>
                        <div class="obra-top__image">
                            <?php if (!empty($obra['imagen'])): ?>
                                <img src="<?php echo htmlspecialchars($obra['imagen'], ENT_QUOTES, 'UTF-8'); ?>"
                                     alt="<?php echo htmlspecialchars($obra['titulo'], ENT_QUOTES, 'UTF-8'); ?>"
                                     onerror="this.onerror=null;this.src='assets/literaturas.svg';">
                            <?php else: ?>
                                <img src="assets/literaturas.svg" alt="">
                            <?php endif; ?>
                        </div>
                        <div class="obra-top__info">
                            <span class="obra-top__area"><?php echo htmlspecialchars($obra['area'], ENT_QUOTES, 'UTF-8'); ?></span>
                            <h2><?php echo htmlspecialchars($obra['titulo'], ENT_QUOTES, 'UTF-8'); ?></h2>
                            <span class="obra-top__visitas"><?php echo number_format((int)$obra['visitas'], 0, ',', '.'); ?> visitas</span>
                        </div>
                        <span class="obra-top__arrow">↗</span>
                    </a>
                <?php endforeach; ?>
                </div>
                <button class="obras-top__control obras-top__control--next" type="button" aria-label="Obra siguiente">›</button>
            </div>
        <?php else: ?>
            <div class="obras-top__empty">Las obras más consultadas aparecerán aquí cuando comiencen las visitas.</div>
        <?php endif; ?>
    </div>
</div>

<section class="museos-virtuales" aria-labelledby="museosVirtualesTitle">
    <div class="museos-virtuales__intro">
        <span>RUTA CULTURAL</span>
        <h2 id="museosVirtualesTitle">Museos virtuales</h2>
        <p>Explora los espacios culturales de Nicaragua y descubre nuevas rutas para conocer nuestro patrimonio.</p>
    </div>
    <div class="museos-virtuales__map-wrap">
        <div id="museosMap" class="museos-virtuales__map" aria-label="Mapa interactivo de museos virtuales en Nicaragua"></div>
        <aside class="museos-virtuales__legend">
            <span class="museos-virtuales__pin">N</span>
            <strong>Explora Nicaragua</strong>
            <small>Selecciona un punto del mapa</small>
        </aside>
    </div>
</section>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<style>
.museo {
    position: relative;
    z-index: 2;
    width: 100%;
    min-height: 100vh;
    overflow: hidden;
}
.museo::before {
    content: "";
    position: absolute;
    top: 0;
    left: 10%;
    width: 80%;
    height: 75%;
    background: linear-gradient(to bottom, rgba(255,255,255,.95), rgba(255,235,200,.35) 35%, rgba(255,255,255,.05) 70%, transparent);
    clip-path: polygon(50% 0%, 28% 100%, 72% 100%);
    -webkit-mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);
    mask-image: linear-gradient(to right, transparent, black 12%, black 88%, transparent);
    filter: blur(2px);
    pointer-events: none;
    z-index: 20;
}
.museo__img { display: block; width: 100%; height: 100vh; object-fit: cover; }
.museo__pod1, .museo__pod2, .museo__pod3, .museo__pod4 {
    position: absolute;
    height: auto;
    object-fit: contain;
    transform: translateY(-50%);
    z-index: 5;
    filter: drop-shadow(0 10px 14px rgba(0,0,0,.35));
}
.museo__pod1 { right: calc(6% - 20px); top: 40%; width: 220px; }
.museo__pod2 { right: calc(5% - 35px); top: calc(74% + 60px); width: 200px; }
.museo__pod3 { left: calc(6% + 15px); top: calc(38% - 10px); width: 180px; }
.museo__pod4 { left: calc(6% - 60px); top: calc(74% + 60px); width: 200px; }

.obras-top {
    position: absolute;
    top: clamp(124px, 18vh, 180px);
    left: 50%;
    width: min(960px, 72vw);
    transform: translateX(-50%);
    z-index: 25;
    color: #fff;
    font-family: 'Montserrat', sans-serif;
}
.obras-top__heading { margin-bottom: 18px; text-align: center; }
.obras-top__heading span {
    color: #03d437;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 3px;
}
.obras-top__heading h1 {
    margin: 5px 0 8px;
    color: #fff2cd;
    font: 800 clamp(27px, 3vw, 44px)/1 'Nikan Felthgothic', 'Felthgothic', serif;
    letter-spacing: 1px;
}
.obras-top__heading i { display: block; width: 70px; height: 2px; margin: 0 auto; background: #03d437; }
.obras-top__carousel { position: relative; overflow: hidden; }
.obras-top__track {
    --card-width: min(280px, 28vw);
    display: flex;
    gap: 16px;
    overflow-x: auto;
    scroll-behavior: smooth;
    scrollbar-width: none;
    padding: 28px calc((100% - var(--card-width)) / 2) 32px;
}
.obras-top__track::-webkit-scrollbar { display: none; }
.obra-top {
    position: relative;
    display: flex;
    flex-direction: column;
    flex: 0 0 var(--card-width);
    min-height: 315px;
    overflow: hidden;
    border-radius: 28px;
    border: 1px solid rgba(255,224,138,.45);
    color: #fff;
    background: linear-gradient(160deg, rgba(48,32,26,.94), rgba(25,21,19,.96));
    text-decoration: none;
    box-shadow: 0 16px 35px rgba(0,0,0,.32);
    filter: blur(3px);
    opacity: .48;
    transform: scale(.86);
    transition: filter .45s ease, opacity .45s ease, transform .45s ease, border-color .35s ease, box-shadow .35s ease;
}
.obra-top.is-active {
    z-index: 1;
    filter: none;
    opacity: 1;
    transform: scale(1.04);
    border-color: #03d437;
    box-shadow: 0 22px 45px rgba(0,0,0,.48);
}
.obra-top:first-child { border-color: rgba(255,224,138,.45); transform: scale(.86); }
.obra-top.is-active:first-child { border-color: #03d437; transform: scale(1.04); }
.obra-top:hover { z-index: 2; border-color: #03d437; transform: translateY(-16px); box-shadow: 0 22px 45px rgba(0,0,0,.48); }
.obras-top__control {
    position: absolute; top: 50%; z-index: 3; width: 42px; height: 42px;
    border: 1px solid #03d437; border-radius: 50%; color: #fff; background: rgba(25,21,19,.92);
    font-size: 30px; line-height: 1; cursor: pointer; transform: translateY(-50%);
    transition: color .2s, background .2s, transform .2s;
}
.obras-top__control:hover { color: #1d251c; background: #03d437; transform: translateY(-50%) scale(1.08); }
.obras-top__control--prev { left: -58px; }
.obras-top__control--next { right: -58px; }
.obra-top__rank { position: absolute; top: 14px; left: 15px; z-index: 2; color: #ffe08a; font: 800 22px 'Alegreya', serif; }
.obra-top__image {
    height: 170px;
    padding: 24px 32px 10px;
    border-radius: 27px 27px 0 0;
    background: radial-gradient(circle, rgba(255,230,174,.22), transparent 65%);
}
.obra-top__image img { display: block; width: 100%; height: 100%; object-fit: contain; filter: drop-shadow(0 12px 10px rgba(0,0,0,.45)); }
.obra-top__info { flex: 1; padding: 15px 17px 17px; border-top: 1px solid rgba(255,255,255,.13); }
.obra-top__area { color: #03d437; font-size: 9px; font-weight: 800; letter-spacing: 2px; text-transform: uppercase; }
.obra-top h2 { margin: 7px 0 12px; color: #fff2cd; font: 800 clamp(18px,2vw,25px)/1.05 'Alegreya', serif; }
.obra-top__visitas { color: rgba(255,255,255,.65); font-size: 10px; letter-spacing: 1px; }
.obra-top__arrow { position: absolute; right: 15px; bottom: 12px; color: #03d437; font-size: 20px; }
.obras-top__empty { padding: 24px; border: 1px solid rgba(255,224,138,.35); color: rgba(255,255,255,.75); background: rgba(25,21,19,.82); text-align: center; }
.museos-virtuales {
    position: relative; z-index: 2; padding: 90px 7vw 110px;
    color: #fff; background: #201a18; font-family: 'Montserrat', sans-serif;
}
.museos-virtuales__intro { max-width: 760px; margin: 0 auto 28px; text-align: center; }
.museos-virtuales__intro > span { color: #03d437; font-size: 10px; font-weight: 800; letter-spacing: 3px; }
.museos-virtuales__intro h2 { margin: 8px 0 12px; color: #fff2cd; font: 800 clamp(32px, 5vw, 62px)/1 'Nikan Felthgothic', serif; }
.museos-virtuales__intro p { margin: 0 auto; max-width: 560px; color: rgba(255,255,255,.72); line-height: 1.7; }
.museos-virtuales__map-wrap { position: relative; max-width: 1180px; height: 520px; margin: 0 auto; border: 2px solid #03d437; box-shadow: 0 24px 60px rgba(0,0,0,.38); }
.museos-virtuales__map { width: 100%; height: 100%; background: #ded8c9; }
.museos-virtuales__legend { position: absolute; left: 22px; bottom: 22px; z-index: 500; display: grid; grid-template-columns: 42px 1fr; column-gap: 10px; padding: 13px 16px; color: #fff; background: rgba(31,23,20,.92); border-left: 3px solid #03d437; pointer-events: none; }
.museos-virtuales__pin { grid-row: span 2; display: grid; place-items: center; width: 36px; height: 36px; color: #1c241c; background: #03d437; font: 800 21px 'Nikan Felthgothic', serif; }
.museos-virtuales__legend strong { align-self: end; font-size: 12px; letter-spacing: 1px; }
.museos-virtuales__legend small { align-self: start; margin-top: 3px; color: #ffe08a; font-size: 10px; }
.leaflet-popup-content-wrapper, .leaflet-popup-tip { background: #30231e; color: #fff; }
.leaflet-popup-content strong { color: #03d437; }
@media (max-width: 900px) {
    .obras-top { width: 84vw; }
    .obra-top { min-height: 270px; }
    .obras-top__track { --card-width: min(250px, 28vw); }
    .obra-top__image { height: 135px; padding: 20px; }
    .obras-top__control--prev { left: -20px; }
    .obras-top__control--next { right: -20px; }
}
@media (max-width: 680px) {
    .obras-top { top: 13%; width: 88vw; }
    .obra-top { flex-basis: 100%; min-height: 0; flex-direction: row; transform: scale(.92); }
    .obra-top.is-active { transform: scale(1); }
    .obras-top__track { --card-width: 100%; padding: 20px 0 25px; }
    .obra-top:hover { transform: scale(1); }
    .obra-top__image { width: 100px; height: 105px; flex: 0 0 100px; padding: 12px; border-radius: 27px 0 0 27px; }
    .obra-top__info { padding: 17px 12px; }
    .obra-top h2 { margin: 5px 0 7px; font-size: 20px; }
    .obra-top__rank { top: 8px; left: 8px; font-size: 17px; }
    .obras-top__control { width: 34px; height: 34px; font-size: 24px; }
    .obras-top__control--prev { left: -12px; }
    .obras-top__control--next { right: -12px; }
    .museos-virtuales { padding: 65px 18px 80px; }
    .museos-virtuales__map-wrap { height: 430px; }
}
</style>
<script>
(function () {
    var track = document.getElementById('obrasTopTrack');
    if (track) {
        var originalCards = Array.prototype.slice.call(track.querySelectorAll('.obra-top'));
        var originalCount = originalCards.length;
        originalCards.forEach(function (card) {
            track.appendChild(card.cloneNode(true));
        });
        var cards = track.querySelectorAll('.obra-top');
        var index = 0;
        var step = function () { return cards[0] ? cards[0].getBoundingClientRect().width + 16 : 0; };
        var activate = function () {
            cards.forEach(function (card, cardIndex) {
                card.classList.toggle('is-active', cardIndex === index);
            });
            track.scrollTo({ left: index * step(), behavior: 'smooth' });
        };
        var go = function (direction) {
            if (!originalCount) return;
            if (direction > 0) {
                index += 1;
                activate();
                if (index === originalCount) {
                    setTimeout(function () {
                        index = 0;
                        cards.forEach(function (card) { card.classList.remove('is-active'); });
                        track.scrollTo({ left: 0, behavior: 'auto' });
                        cards[0].classList.add('is-active');
                    }, 480);
                }
                return;
            }
            index = index > 0 ? index - 1 : originalCount - 1;
            track.scrollTo({ left: index * step(), behavior: 'auto' });
            cards.forEach(function (card, cardIndex) {
                card.classList.toggle('is-active', cardIndex === index);
            });
        };
        activate();
        document.querySelector('.obras-top__control--prev').addEventListener('click', function () { go(-1); });
        document.querySelector('.obras-top__control--next').addEventListener('click', function () { go(1); });
        setInterval(function () { go(1); }, 3000);
    }

    var mapElement = document.getElementById('museosMap');
    if (!mapElement || typeof L === 'undefined') return;
    var map = L.map(mapElement, { scrollWheelZoom: false }).setView([12.8654, -85.2072], 7);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    var locations = [
        ['Museo Managua', 12.1328, -86.2504, 'Recorrido virtual principal de NIKAN.'],
        ['León cultural', 12.4379, -86.8780, 'Arte, historia y patrimonio colonial.'],
        ['Granada histórica', 11.9344, -85.9560, 'Arquitectura y memoria de Nicaragua.'],
        ['Caribe nicaragüense', 12.0069, -83.7635, 'Tradiciones y expresiones del Caribe.']
    ];
    locations.forEach(function (place) {
        L.marker([place[1], place[2]]).addTo(map)
            .bindPopup('<strong>' + place[0] + '</strong><br>' + place[3]);
    });
})();
</script>
