<?php
/**
 * Portada: escena de podios y ranking de las tres obras más consultadas.
 */
require_once __DIR__ . '/../config/config.php';

$obrasDestacadas = [];
$museosVirtuales = [];
try {
    $pdo = getDB();
    asegurar_tabla_obra_visitas($pdo);
    asegurar_tabla_museos_virtuales($pdo);
    $museosVirtuales = $pdo->query('SELECT id, nombre, descripcion, latitud, longitud FROM museos_virtuales ORDER BY nombre ASC')->fetchAll();
    $obrasDestacadas = $pdo->query("
        SELECT catalogo.area, catalogo.obra_id, catalogo.titulo, catalogo.imagen,
               catalogo.autor, catalogo.descripcion, catalogo.pagina, COUNT(visitas.id) AS visitas
        FROM (
            SELECT 'Arte' AS area, id AS obra_id, titulo, imagen, autor,
                   COALESCE(NULLIF(descripcion, ''), detalle) AS descripcion, 'arte_detalle' AS pagina
            FROM arte_obras
            UNION ALL
            SELECT 'Literatura', id, titulo, NULL, autor,
                   COALESCE(NULLIF(sinopsis, ''), fragmento, detalle), 'lit_detalle'
            FROM lit_obras
            UNION ALL
            SELECT 'Música', id, titulo, imagen, autor,
                   COALESCE(NULLIF(descripcion, ''), detalle), 'musica_detalle'
            FROM musica_obras
        ) AS catalogo
        LEFT JOIN obra_visitas AS visitas
            ON visitas.area = CASE
                WHEN catalogo.pagina = 'arte_detalle' THEN 'arte'
                WHEN catalogo.pagina = 'lit_detalle' THEN 'literatura'
                ELSE 'musica'
            END
            AND visitas.obra_id = catalogo.obra_id
        GROUP BY catalogo.area, catalogo.obra_id, catalogo.titulo, catalogo.imagen,
                 catalogo.autor, catalogo.descripcion, catalogo.pagina
        ORDER BY visitas DESC, catalogo.titulo ASC
        LIMIT 3
    ")->fetchAll();
} catch (Exception $e) {
    error_log('[NIKAN] No se pudo cargar el ranking de obras: ' . $e->getMessage());
    try {
        $pdo = getDB();
        $obrasDestacadas = $pdo->query("
            SELECT 'Arte' AS area, id AS obra_id, titulo, imagen, autor, descripcion, 'arte_detalle' AS pagina, 0 AS visitas
            FROM arte_obras
            UNION ALL
            SELECT 'Literatura', id, titulo, NULL, autor, COALESCE(NULLIF(sinopsis, ''), fragmento, detalle), 'lit_detalle', 0
            FROM lit_obras
            UNION ALL
            SELECT 'Música', id, titulo, imagen, autor, descripcion, 'musica_detalle', 0
            FROM musica_obras
            ORDER BY titulo ASC
            LIMIT 3
        ")->fetchAll();
    } catch (Exception $fallbackError) {
        error_log('[NIKAN] Tampoco se pudo cargar obras de portada: ' . $fallbackError->getMessage());
    }
}
?>

<section class="inicio-bienvenida" aria-labelledby="bienvenidaTitulo">
    <div class="inicio-bienvenida__contenido">
        <span class="inicio-bienvenida__eyebrow">REDESCUBRE NICARAGUA</span>
        <h1 id="bienvenidaTitulo">BIENVENIDO</h1>
        <p>Nikán es una plataforma web que reúne diferentes expresiones de la cultura nicaragüense y las convierte en una experiencia accesible e interactiva.</p>
        <a class="inicio-bienvenida__cta" href="#coleccion-destacada">EXPLORAR LA COLECCIÓN <span aria-hidden="true">↗</span></a>
    </div>
    <div class="inicio-bienvenida__visual" aria-hidden="true">
        <video class="inicio-bienvenida__tigre"
               autoplay
               muted
               playsinline
               preload="auto"
               poster="assets/leon-verde.svg"
               aria-label="Animación de un tigre">
            <source src="assets/tigre.webm" type="video/webm">
            <source src="assets/tigre.mp4" type="video/mp4">
        </video>
        <span class="inicio-bienvenida__sello">CULTURA<br>NICARAGÜENSE</span>
    </div>
</section>

<div class="museo">
    <img src="assets/podios.svg" alt="Exhibición de podios" class="museo__img">
    <img src="assets/pod1.svg" alt="Pod 1" class="museo__pod1">
    <img src="assets/pod2.svg" alt="Pod 2" class="museo__pod2">
    <img src="assets/pod3.svg" alt="Pod 3" class="museo__pod3">
    <img src="assets/pod4.svg" alt="Pod 4" class="museo__pod4">

    <div class="obras-top" aria-label="Obras más consultadas">
        <div class="obras-top__heading" id="coleccion-destacada">
            <span>TOP 3</span>
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
                            <?php if (!empty($obra['autor'])): ?>
                                <p class="obra-top__author">Autor: <?php echo htmlspecialchars($obra['autor'], ENT_QUOTES, 'UTF-8'); ?></p>
                            <?php endif; ?>
                            <p class="obra-top__description"><?php echo htmlspecialchars($obra['descripcion'] ?? 'Descubre esta obra de la colección NIKAN.', ENT_QUOTES, 'UTF-8'); ?></p>
                            <div class="obra-top__stats">
                                <span class="obra-top__visitas">◉ <?php echo number_format((int)$obra['visitas'], 0, ',', '.'); ?> vistas</span>
                                <span class="obra-top__more">VER MÁS ↗</span>
                            </div>
                        </div>
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
.inicio-bienvenida {
    position: relative;
    z-index: 2;
    display: grid;
    grid-template-columns: minmax(0, .95fr) minmax(340px, 1.05fr);
    align-items: center;
    gap: clamp(30px, 7vw, 110px);
    min-height: min(860px, 100vh);
    padding: 150px 9vw 90px;
    overflow: hidden;
    color: #43261e;
    background: #f5f0e7;
}
.inicio-bienvenida::before {
    content: "";
    position: absolute;
    inset: 0;
    opacity: .25;
    pointer-events: none;
    background-image: radial-gradient(rgba(75, 43, 28, .2) .7px, transparent .7px);
    background-size: 5px 5px;
    mix-blend-mode: multiply;
}
.inicio-bienvenida__contenido,
.inicio-bienvenida__visual {
    position: relative;
    z-index: 1;
}
.inicio-bienvenida__contenido { max-width: 560px; }
.inicio-bienvenida__eyebrow {
    display: block;
    margin: 0 0 18px;
    color: #00a4ab;
    font: 500 clamp(20px, 2.3vw, 34px)/1.1 'Nikan Felthgothic', sans-serif;
    letter-spacing: .5px;
}
.inicio-bienvenida h1 {
    margin: 0 0 18px;
    color: #397d27;
    font: 400 clamp(66px, 9vw, 132px)/.84 'Rustica', serif;
    letter-spacing: 2px;
}
.inicio-bienvenida p {
    max-width: 510px;
    margin: 0;
    font: 400 clamp(18px, 1.8vw, 27px)/1.28 'Nikan Felthgothic', sans-serif;
}
.inicio-bienvenida__cta {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    margin-top: 30px;
    padding: 12px 18px;
    color: #f5f0e7;
    background: #c6372e;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: 1.5px;
    text-decoration: none;
    transition: background .2s ease, transform .2s ease;
}
.inicio-bienvenida__cta:hover,
.inicio-bienvenida__cta:focus-visible {
    background: #397d27;
    transform: translateY(-2px);
}
.inicio-bienvenida__visual {
    min-height: 350px;
    border-bottom: 1px solid rgba(67, 38, 30, .35);
}
.inicio-bienvenida__tigre {
    position: absolute;
    right: 4%;
    top: 50%;
    width: min(92%, 600px);
    max-height: 310px;
    object-fit: contain;
    opacity: .72;
    filter: sepia(1) saturate(.45) hue-rotate(315deg) brightness(.42);
    transform: translateY(-50%);
}
.inicio-bienvenida__sello {
    position: absolute;
    right: 0;
    bottom: 24px;
    color: #397d27;
    font: 400 12px/1.2 'Rustica', serif;
    letter-spacing: 2px;
    text-align: right;
}

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
    box-shadow: none;
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
    box-shadow: none;
}
.obra-top:first-child { border-color: rgba(255,224,138,.45); transform: scale(.86); }
.obra-top.is-active:first-child { border-color: #03d437; transform: scale(1.04); }
.obra-top:hover { z-index: 2; border-color: #03d437; transform: translateY(-16px); box-shadow: none; }
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
.museos-virtuales__intro > span { color: #fff; font-size: 10px; font-weight: 800; letter-spacing: 3px; }
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
.leaflet-popup-content { margin: 16px 18px; min-width: 190px; font-family: 'Montserrat', sans-serif; line-height: 1.5; }
.leaflet-popup-content strong { display: block; margin-bottom: 5px; font-size: 14px; letter-spacing: .3px; }
.museum-popup-description { display: block; margin-bottom: 13px; color: rgba(255,255,255,.72); font-size: 11px; line-height: 1.45; }
.museum-vr-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 10px 14px;
    border: 1px solid #03d437;
    border-radius: 7px;
    color: #172318 !important;
    background: #03d437;
    box-shadow: 0 5px 14px rgba(3,212,55,.24);
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1.2px;
    text-align: center;
    text-decoration: none;
    text-transform: uppercase;
    transition: color .2s ease, background .2s ease, transform .2s ease, box-shadow .2s ease;
}
.museum-vr-link::before { content: '◉'; font-size: 13px; line-height: 1; }
.museum-vr-link::after { content: '↗'; font-size: 15px; line-height: 1; }
.museum-vr-link:hover, .museum-vr-link:focus-visible {
    color: #fff !important;
    background: #0f9c60;
    box-shadow: 0 7px 18px rgba(3,212,55,.35);
    outline: none;
    transform: translateY(-2px);
}
.museum-vr-link:active { transform: translateY(0); }
.museum-vr-overlay {
    position: fixed;
    inset: 0;
    z-index: 1000;
    width: 100vw;
    height: 100vh;
    overflow: hidden;
    background: #211a18;
}
.museum-vr-overlay:fullscreen { width: 100vw; height: 100vh; }
.nikan-vr-open { overflow: hidden !important; }
.museum-vr-overlay iframe {
    display: block;
    width: 100vw;
    height: 100vh;
    border: 0;
    overflow: hidden;
}
@media (max-width: 900px) {
    .inicio-bienvenida { padding-right: 6vw; padding-left: 6vw; gap: 35px; }
    .inicio-bienvenida h1 { font-size: clamp(58px, 10vw, 100px); }
    .obras-top { width: 84vw; }
    .obra-top { min-height: 270px; }
    .obras-top__track { --card-width: min(250px, 28vw); }
    .obra-top__image { height: 135px; padding: 20px; }
    .obras-top__control--prev { left: -20px; }
    .obras-top__control--next { right: -20px; }
}
@media (max-width: 680px) {
    body { overflow-x: hidden; }
    .inicio-bienvenida {
        display: block;
        min-height: 760px;
        padding: 130px 24px 46px;
        text-align: center;
    }

    /* Composición editorial del podio: una pieza central y dos laterales. */
    .museo {
        min-height: 680px;
        background: #382218;
    }
    .museo__img {
        height: 680px;
        object-position: center;
        filter: sepia(.35) saturate(.9) brightness(.72);
    }
    .museo::after {
        content: "";
        position: absolute;
        inset: 0;
        z-index: 3;
        pointer-events: none;
        background: linear-gradient(90deg, rgba(29,13,8,.5), transparent 28%, transparent 72%, rgba(29,13,8,.5)),
                    linear-gradient(0deg, rgba(19,9,6,.68), transparent 32%);
    }
    .obras-top {
        top: 58px;
        width: min(1180px, 90vw);
    }
    .obras-top__heading {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-bottom: 4px;
        text-align: left;
    }
    .obras-top__heading span {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        color: #fff;
        font: 400 clamp(28px, 3vw, 47px)/1 'Nikan Felthgothic', sans-serif;
        letter-spacing: 0;
        white-space: nowrap;
    }
    .obras-top__heading span img {
        width: 54px;
        height: 54px;
        padding: 5px;
        object-fit: contain;
        border: 1px solid rgba(255,255,255,.85);
        filter: grayscale(1) brightness(0) invert(1);
    }
    .obras-top__heading h1 {
        position: absolute;
        width: 1px;
        height: 1px;
        overflow: hidden;
        clip: rect(0 0 0 0);
    }
    .obras-top__heading i {
        flex: 1;
        width: auto;
        height: 1px;
        margin: 0;
        background: rgba(255,255,255,.85);
    }
    .obras-top__carousel { overflow: visible; }
    .obras-top__track {
        --card-width: 100%;
        padding: 22px 0 0;
        gap: 0;
        overflow: hidden;
    }
    .obra-top {
        flex-basis: 100%;
        min-height: 470px;
        grid-template-columns: minmax(360px, 1fr) minmax(330px, .75fr);
    }
    .obra-top__image {
        height: 420px;
        padding: 22px 90px;
    }
    .obra-top__info {
        max-width: 510px;
        padding-right: 25px;
    }
    .obra-top h2 {
        font-size: clamp(37px, 5vw, 68px);
        text-transform: uppercase;
    }
    .obras-top__control { display: none; }
    .obras-top .obra-top.is-active { border-color: #fff; }
    .obras-top .obras-top__heading i { background: #fff; }
    .obras-top .obra-top__area,
    .obras-top .obra-top__more { color: #fff !important; }

    @media (max-width: 680px) {
        .museo, .museo__img { min-height: 700px; height: 700px; }
        .obras-top { top: 52px; width: 88vw; }
        .obras-top__heading span { font-size: 28px; }
        .obras-top__heading span img { width: 40px; height: 40px; }
        .obras-top__track { padding-top: 12px; }
        .obra-top { min-height: 510px; }
        .obra-top__image { height: 235px; flex-basis: 235px; padding: 25px 55px 15px; }
        .obra-top__info { max-width: none; padding: 18px 22px 22px; }
        .obra-top h2 { font-size: 33px; }
    }
    .inicio-bienvenida__contenido { max-width: 620px; margin: 0 auto; }
    .inicio-bienvenida__eyebrow { font-size: 19px; }
    .inicio-bienvenida h1 { font-size: clamp(58px, 18vw, 88px); }
    .inicio-bienvenida p { margin: 0 auto; font-size: 17px; line-height: 1.45; }
    .inicio-bienvenida__cta { margin-top: 24px; }
    .inicio-bienvenida__visual {
        min-height: 270px;
        margin: 28px auto 0;
        max-width: 520px;
    }
    .inicio-bienvenida__tigre { width: 90%; right: 5%; max-height: 220px; }
    .inicio-bienvenida__sello { bottom: 12px; right: 5px; font-size: 10px; }
    .museo { min-height: 820px; }
    .museo__img { height: 820px; object-position: center top; }
    .museo__pod1, .museo__pod2, .museo__pod3, .museo__pod4 { display: none; }
    .museo__pod1 { right: -36px; top: 34%; width: 135px; }
    .museo__pod2 { right: -30px; top: 74%; width: 125px; }
    .museo__pod3 { left: -32px; top: 34%; width: 120px; }
    .museo__pod4 { left: -28px; top: 74%; width: 125px; }
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
    .obras-top__control { display: none; }
    .museos-virtuales { padding: 65px 18px 80px; }
    .museos-virtuales__map-wrap { height: 430px; }
    .museos-virtuales__legend { left: 12px; right: 12px; bottom: 12px; }
    .museos-virtuales__map-wrap { max-width: 100%; }
}

@media (max-width: 420px) {
    .inicio-bienvenida { padding-right: 17px; padding-left: 17px; }
    .inicio-bienvenida h1 { font-size: 56px; }
    .inicio-bienvenida p { font-size: 15px; }
    .inicio-bienvenida__visual { min-height: 225px; }
}

/* Presentación editorial de una obra destacada por turno. */
.obras-top__track { --card-width: 100%; gap: 18px; padding: 18px 0 28px; }
.obra-top {
    flex-basis: 100%;
    min-height: 430px;
    display: grid;
    grid-template-columns: minmax(360px, 1.15fr) minmax(320px, .85fr);
    align-items: center;
    border: 0;
    border-radius: 0;
    background: transparent;
    box-shadow: none;
}
.obra-top:not(.is-active) { opacity: .14; filter: blur(7px); pointer-events: none; }
.obra-top.is-active, .obra-top:first-child, .obra-top.is-active:first-child { transform: scale(1); }
.obra-top__rank { top: 22px; left: 25px; font-size: 30px; }
.obra-top__image {
    width: 100%;
    height: 390px;
    padding: 22px 76px;
    border-radius: 0;
    background: radial-gradient(ellipse at center, rgba(255,230,174,.3), transparent 64%);
}
.obra-top__image img { filter: drop-shadow(0 24px 22px rgba(0,0,0,.56)); }
.obra-top__info {
    align-self: center;
    display: flex;
    flex-direction: column;
    justify-content: center;
    max-width: 480px;
    padding: 20px 45px 20px 28px;
    border-top: 0;
    border-left: 1px solid rgba(255,255,255,.25);
}
.obra-top__area { letter-spacing: 3px; }
.obra-top h2 { margin: 11px 0 5px; font-size: clamp(32px, 4vw, 60px); line-height: .95; }
.obra-top__author { margin: 0 0 19px; color: #ffe08a; font: 500 clamp(17px, 2vw, 25px)/1.2 'Alegreya', serif; }
.obra-top__description { display: -webkit-box; margin: 0; overflow: hidden; color: rgba(255,255,255,.83); font-size: clamp(13px, 1.2vw, 17px); line-height: 1.55; -webkit-box-orient: vertical; -webkit-line-clamp: 5; }
.obra-top__stats { display: flex; align-items: center; gap: 22px; margin-top: 25px; }
.obra-top__visitas { color: #fff2cd; font-size: 12px; letter-spacing: 1px; }
.obra-top__more { color: #fff; font-size: 10px; font-weight: 800; letter-spacing: 1px; }
@media (max-width: 680px) {
    .obra-top { min-height: 390px; display: flex; flex-direction: column; }
    .obra-top__image { width: 100%; height: 190px; flex: 0 0 190px; padding: 25px 60px 15px; border-radius: 24px 24px 0 0; }
    .obra-top__info { align-self: auto; width: 100%; padding: 18px 22px 22px; border-left: 0; border-top: 1px solid rgba(255,255,255,.16); }
    .obra-top h2 { font-size: 32px; }
    .obra-top__author { margin-bottom: 10px; }
    .obra-top__description { -webkit-line-clamp: 3; }
    .obra-top__stats { margin-top: 13px; }
}
</style>
<script>
(function () {
    var tigre = document.querySelector('.inicio-bienvenida__tigre');
    if (tigre) {
        tigre.addEventListener('ended', function () {
            tigre.pause();
            tigre.currentTime = tigre.duration;
        });
    }
})();

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
    var locations = <?php echo json_encode($museosVirtuales, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    function escapeHtml(value) {
        return String(value).replace(/[&<>"']/g, function (character) {
            return {'&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#039;'}[character];
        });
    }
    locations.forEach(function (place) {
        var popup = '<strong>' + escapeHtml(place.nombre) + '</strong>' +
            '<span class="museum-popup-description">' + escapeHtml(place.descripcion) + '</span>' +
            '<a href="#" class="museum-vr-link" data-museum-id="' + encodeURIComponent(place.id) + '">Explorar en VR</a>';
        L.marker([Number(place.latitud), Number(place.longitud)]).addTo(map).bindPopup(popup);
    });
    map.on('popupopen', function (event) {
        var link = event.popup.getElement().querySelector('.museum-vr-link');
        if (!link) return;
        link.addEventListener('click', function (clickEvent) {
            clickEvent.preventDefault();
            abrirMuseoVR(link.dataset.museumId);
        });
    });

    function abrirMuseoVR(museoId) {
        var overlay = document.createElement('div');
        overlay.className = 'museum-vr-overlay';
        overlay.innerHTML = '<iframe tabindex="0" title="Recorrido virtual" allow="fullscreen" src="index.php?page=vr&museo_id=' +
            encodeURIComponent(museoId) + '&embed=1"></iframe>';
        document.body.appendChild(overlay);
        document.body.classList.add('nikan-vr-open');
        var frame = overlay.querySelector('iframe');
        frame.addEventListener('load', function () {
            frame.focus();
            if (frame.contentWindow) frame.contentWindow.focus();
        });
        if (overlay.requestFullscreen) {
            overlay.requestFullscreen().catch(function () {
                frame.focus();
            });
        }
    }
    window.addEventListener('message', function (event) {
        if (event.origin !== window.location.origin || !event.data || event.data.type !== 'nikan-vr-exit') return;
        var overlay = document.querySelector('.museum-vr-overlay');
        if (overlay) overlay.remove();
        document.body.classList.remove('nikan-vr-open');
        window.location.href = 'index.php';
    });
    document.addEventListener('fullscreenchange', function () {
        if (document.fullscreenElement || !document.querySelector('.museum-vr-overlay')) return;
        document.querySelector('.museum-vr-overlay').remove();
        document.body.classList.remove('nikan-vr-open');
        window.location.href = 'index.php';
    });
})();
</script>
