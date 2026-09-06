<?php
/**
 * Detalle de una pieza musical.
 * Se accede via ?page=musica_detalle&obra_id=ID
 * Muestra la obra completa + audio + bloque del autor + otras obras del mismo autor.
 */
require_once __DIR__ . '/../config/config.php';

$obra_id = (int)($_GET['obra_id'] ?? 0);

function mus_aut_area_label($area) {
    return ['arte' => 'Arte', 'lit' => 'Literatura', 'poe' => 'Poesía', 'mus' => 'Música'][$area] ?? $area;
}

try {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM musica_obras WHERE id=?');
    $stmt->execute([$obra_id]);
    $obra = $stmt->fetch();

    if (!$obra) {
        echo '<div class="det-vacio"><a href="index.php?page=musica">← Volver a Música</a><p>No se encontró la obra.</p></div>
        <style>.det-vacio{position:relative;z-index:2;padding:160px 40px;text-align:center;font-family:Montserrat,sans-serif;color:#4a3b22}.det-vacio a{color:#0a7a4b;font-weight:700;text-decoration:none}.det-vacio p{font-size:18px;margin-top:12px;color:#8a7a58}</style>';
        return;
    }

    $sec = $pdo->prepare('SELECT titulo FROM musica_secciones WHERE id=?');
    $sec->execute([$obra['seccion_id']]);
    $seccion_titulo = $sec->fetchColumn();

    $autor = null;
    if (!empty($obra['autor_id'])) {
        $st = $pdo->prepare('SELECT * FROM autores WHERE id=?');
        $st->execute([$obra['autor_id']]);
        $autor = $st->fetch();
    }

    $otras_obras = [];
    if ($autor) {
        $areas = ['arte' => 'arte_obras', 'lit' => 'lit_obras', 'poe' => 'poe_obras', 'mus' => 'musica_obras'];
        foreach ($areas as $area => $tabla) {
            $excluir = $area === 'mus' ? " AND id<>?" : '';
            $q = "SELECT id, titulo, '$area' AS area FROM $tabla WHERE autor_id=?" . $excluir . " ORDER BY orden ASC";
            $params = [$obra['autor_id']];
            if ($area === 'mus') $params[] = $obra_id;
            $stmtArea = $pdo->prepare($q);
            $stmtArea->execute($params);
            foreach ($stmtArea as $r) {
                $otras_obras[] = $r;
            }
        }
    }
} catch (Exception $e) {
    $obra = null;
}
?>

<?php if (!$obra) return; ?>

<?php
$youtube_id = '';
if (!empty($obra['audio']) && !preg_match('~^uploads/~', $obra['audio'])) {
    if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([A-Za-z0-9_-]{6,})~', $obra['audio'], $m)) {
        $youtube_id = $m[1];
    }
}
$audio_src = '';
if (!empty($obra['audio_file']) && file_exists(__DIR__ . '/../' . $obra['audio_file'])) {
    $audio_src = $obra['audio_file'];
} elseif (!empty($obra['audio'])) {
    $audio_src = $obra['audio'];
}
?>

<section class="det-section">
    <div class="det-container">

        <a href="?page=musica" class="det-back">← Volver a Música</a>

        <div class="det-hero">
            <div class="det-imagen">
                <div class="det-marco">
                    <?php if ($obra['imagen']): ?>
                        <img src="<?php echo htmlspecialchars($obra['imagen']); ?>" alt="<?php echo htmlspecialchars($obra['titulo']); ?>">
                    <?php else: ?>
                        <span class="det-img-ph">Sin imagen</span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="det-info">
                <?php if ($seccion_titulo): ?>
                    <span class="det-tag"><?php echo htmlspecialchars($seccion_titulo); ?></span>
                <?php endif; ?>
                <h1><?php echo htmlspecialchars($obra['titulo']); ?></h1>

                <dl class="det-ficha">
                    <?php if ($autor): ?>
                        <div class="det-afil">
                            <dt>Autor/a</dt>
                            <dd>
                                <a href="?page=autores" class="det-autor"><?php echo htmlspecialchars($autor['nombre']); ?></a>
                            </dd>
                        </div>
                    <?php elseif (!empty($obra['autor'])): ?>
                        <div class="det-afil">
                            <dt>Autor/a</dt>
                            <dd><?php echo htmlspecialchars($obra['autor']); ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($obra['genero'])): ?>
                        <div class="det-afil"><dt>Género</dt><dd><?php echo htmlspecialchars($obra['genero']); ?></dd></div>
                    <?php endif; ?>
                    <?php if (!empty($obra['anio'])): ?>
                        <div class="det-afil"><dt>Año</dt><dd><?php echo htmlspecialchars($obra['anio']); ?></dd></div>
                    <?php endif; ?>
                    <?php if (!empty($obra['seccion_id'])): ?>
                        <div class="det-afil"><dt>Colección</dt><dd><?php echo htmlspecialchars($seccion_titulo ?: '-'); ?></dd></div>
                    <?php endif; ?>
                </dl>

                <?php if (!empty($obra['descripcion'])): ?>
                    <p class="det-desc"><?php echo nl2br(htmlspecialchars($obra['descripcion'])); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($audio_src)): ?>
            <div class="det-bloque">
                <h2>Escucha esta pieza</h2>
                <?php if ($youtube_id): ?>
                    <div class="det-player">
                        <iframe
                            src="https://www.youtube.com/embed/<?php echo $youtube_id; ?>"
                            title="Reproductor de <?php echo htmlspecialchars($obra['titulo']); ?>"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen></iframe>
                    </div>
                <?php else: ?>
                    <div class="det-reproducir" data-src="<?php echo htmlspecialchars($audio_src); ?>" data-titulo="<?php echo htmlspecialchars($obra['titulo']); ?>">
                        <button type="button" class="det-reproducir-btn" aria-label="Reproducir"><span>▶</span></button>
                        <div class="det-reproducir-info">
                            <span class="det-reproducir-estado">Reproducir</span>
                            <span class="det-reproducir-titulo"><?php echo htmlspecialchars($obra['titulo']); ?></span>
                        </div>
                        <audio preload="none" src="<?php echo htmlspecialchars($audio_src); ?>"></audio>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($obra['detalle'])): ?>
            <div class="det-bloque">
                <h2>Detalles de la pieza</h2>
                <div class="det-cuerpo"><?php echo nl2br(htmlspecialchars($obra['detalle'])); ?></div>
            </div>
        <?php endif; ?>

        <?php if ($autor): ?>
            <div class="det-bloque">
                <h2>Sobre el autor</h2>
                <div class="det-autor-box">
                    <div class="det-autor-retrato">
                        <?php if ($autor['retrato']): ?>
                            <img src="<?php echo htmlspecialchars($autor['retrato']); ?>" alt="Retrato de <?php echo htmlspecialchars($autor['nombre']); ?>">
                        <?php else: ?>
                            <span class="det-autor-ph"><?php echo mb_substr($autor['nombre'], 0, 1); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="det-autor-cuerpo">
                        <h3><?php echo htmlspecialchars($autor['nombre']); ?></h3>
                        <?php if (!empty($autor['lugar']) || !empty($autor['nacimiento'])): ?>
                            <p class="det-autor-datos">
                                <?php echo htmlspecialchars($autor['lugar']); ?>
                                <?php if (!empty($autor['nacimiento'])): ?> · <?php echo htmlspecialchars($autor['nacimiento']); ?><?php if (!empty($autor['fallecimiento'])): ?>–<?php echo htmlspecialchars($autor['fallecimiento']); ?><?php endif; ?><?php endif; ?>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($autor['bio'])): ?>
                            <p class="det-autor-bio"><?php echo nl2br(htmlspecialchars($autor['bio'])); ?></p>
                        <?php endif; ?>
                        <a href="?page=autores" class="det-autor-link">Ver ficha completa del autor →</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($otras_obras): ?>
            <div class="det-bloque">
                <h2>Otras obras de este autor</h2>
                <div class="det-otras">
                    <?php foreach ($otras_obras as $o): ?>
                        <?php
                        $href = $o['area'] === 'mus'
                            ? '?page=musica_detalle&obra_id=' . (int)$o['id']
                            : ($o['area'] === 'arte'
                                ? '?page=arte_detalle&obra_id=' . (int)$o['id']
                                : ($o['area'] === 'lit'
                                    ? '?page=lit_detalle&obra_id=' . (int)$o['id']
                                    : '?page=poe_detalle&obra_id=' . (int)$o['id']));
                        ?>
                        <a class="det-mini" href="<?php echo $href; ?>">
                            <div class="det-mini-img det-mini-img--txt">
                                <span class="det-mini-area"><?php echo mus_aut_area_label($o['area']); ?></span>
                            </div>
                            <span class="det-mini-titulo"><?php echo htmlspecialchars($o['titulo']); ?></span>
                            <span class="det-mini-area-label"><?php echo mus_aut_area_label($o['area']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</section>

<style>
@font-face {
    font-family: 'Nikan Felthgothic';
    src: url('../fonts/Felthgothic Bold.ttf') format('opentype');
}

.det-section {
    background:
        radial-gradient(ellipse at 25% 40%, rgba(255, 244, 222, 0.9), transparent 60%),
        radial-gradient(ellipse at 75% 45%, rgba(255, 244, 222, 0.7), transparent 55%),
        linear-gradient(135deg, #f6efe3 0%, #efe4d0 45%, #e7d8bd 100%);
    min-height: 100vh;
    font-family: 'Montserrat', sans-serif;
    padding: 130px 40px 80px;
    position: relative;
    z-index: 2;
}

.det-container {
    max-width: 1000px;
    margin: 0 auto;
}

.det-back {
    display: inline-block;
    color: #0a7a4b;
    font-weight: 700;
    text-decoration: none;
    margin-bottom: 28px;
    font-size: 15px;
}
.det-back:hover { text-decoration: none; }

.det-hero {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    align-items: start;
}

.det-imagen {}
.det-marco {
    position: relative;
    background: rgba(255, 252, 244, 0.5);
    border: 2px solid #c9a94f;
    border-radius: 6px;
    box-shadow: 0 0 0 6px rgba(201, 169, 79, 0.15), 0 15px 35px rgba(60, 35, 10, 0.25);
    padding: 16px;
}
.det-marco img {
    width: 100%;
    max-height: 520px;
    object-fit: contain;
    display: block;
}
.det-img-ph {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 320px;
    color: #b9a06a;
    font-style: italic;
}

.det-info {}
.det-tag {
    display: inline-block;
    color: #fff;
    background: #0a7a4b;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 4px 12px;
    border-radius: 20px;
    margin-bottom: 14px;
}
.det-info h1 {
    font-family: 'Nikan Felthgothic', 'Felthgothic', serif;
    font-size: 46px;
    color: #C6372E;
    line-height: 1.1;
    margin: 0 0 20px;
    text-shadow: 0 2px 2px rgba(90, 60, 20, 0.2);
}

.det-ficha {
    margin: 0 0 20px;
}
.det-afil {
    display: flex;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px dashed rgba(120, 90, 40, 0.3);
    font-size: 15px;
}
.det-afil dt { font-weight: 700; color: #0a7a4b; min-width: 90px; }
.det-afil dd { margin: 0; color: #4a3b22; font-weight: 600; }
.det-autor { color: #C6372E; font-weight: 700; text-decoration: none; }
.det-autor:hover { text-decoration: underline; }

.det-desc {
    font-size: 16px;
    line-height: 1.7;
    color: #4a3b22;
}

.det-bloque {
    margin-top: 44px;
    padding-top: 34px;
    border-top: 2px solid #c9a94f;
}
.det-bloque h2 {
    font-family: 'Nikan Felthgothic', 'Felthgothic', serif;
    font-size: 30px;
    color: #C6372E;
    margin: 0 0 18px;
}
.det-cuerpo {
    font-size: 16px;
    line-height: 1.8;
    color: #4a3b22;
    max-width: 780px;
}

/* Reproductor */
.det-player {
    position: relative;
    width: 100%;
    max-width: 720px;
    aspect-ratio: 16 / 9;
    border-radius: 12px;
    overflow: hidden;
    border: 2px solid #c9a94f;
    background: #000;
}
.det-player iframe {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
}
.det-reproducir {
    position: relative;
    display: flex;
    align-items: center;
    gap: 18px;
    width: 100%;
    box-sizing: border-box;
    background: rgba(255, 252, 244, 0.65);
    border: 2px solid #c9a94f;
    border-radius: 12px;
    padding: 22px;
    box-shadow: 0 10px 26px rgba(90, 60, 20, 0.18);
}
.det-reproducir.playing {
    animation: det-tarjeta-pulso 1.6s ease-in-out infinite;
}
@keyframes det-tarjeta-pulso {
    0%, 100% { transform: scale(1); box-shadow: 0 10px 26px rgba(90, 60, 20, 0.18); }
    50% { transform: scale(1.012); box-shadow: 0 12px 34px rgba(201, 169, 79, 0.4); }
}
.det-reproducir-btn {
    position: relative;
    width: 64px;
    height: 64px;
    flex-shrink: 0;
    border-radius: 50%;
    border: 3px solid #C6372E;
    background: #C6372E;
    color: #fff;
    font-size: 22px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.15s, background 0.15s;
}
.det-reproducir-btn:hover { transform: scale(1.06); }
.det-reproducir-btn.playing::after {
    content: "";
    position: absolute;
    inset: -8px;
    border: 2px solid rgba(201, 169, 79, 0.6);
    border-radius: 50%;
    animation: det-pulso 1.4s ease-out infinite;
}
@keyframes det-pulso {
    from { transform: scale(0.9); opacity: 1; }
    to   { transform: scale(1.35); opacity: 0; }
}
.det-reproducir-info { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.det-reproducir-estado {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 2px;
    color: #0a7a4b;
}
.det-reproducir-titulo {
    font-family: 'Nikan Felthgothic', 'Felthgothic', serif;
    font-size: 22px;
    color: #4a3b22;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.det-reproducir audio { display: none; }

/* Autor */
.det-autor-box {
    display: flex;
    gap: 22px;
    background: rgba(255, 255, 255, 0.5);
    border: 2px solid rgba(201, 169, 79, 0.4);
    border-radius: 12px;
    padding: 22px;
    align-items: flex-start;
}
.det-autor-retrato {
    width: 96px;
    height: 96px;
    border-radius: 50%;
    overflow: hidden;
    background: #f0e5c8;
    border: 3px solid #c9a94f;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
}
.det-autor-retrato img { width: 100%; height: 100%; object-fit: cover; }
.det-autor-ph { font-family: 'Nikan Felthgothic', 'Felthgothic', serif; font-size: 40px; color: #C6372E; }
.det-autor-cuerpo h3 { font-family: 'Nikan Felthgothic', 'Felthgothic', serif; font-size: 26px; color: #C6372E; margin: 0 0 4px; }
.det-autor-datos { font-size: 14px; color: #6a5a38; margin: 0 0 10px; }
.det-autor-bio { font-size: 15px; line-height: 1.6; color: #4a3b22; margin: 0 0 12px; }
.det-autor-link { color: #0a7a4b; font-weight: 700; font-size: 14px; text-decoration: none; }
.det-autor-link:hover { text-decoration: none; }

/* Otras obras */
.det-otras {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 18px;
}
.det-mini {
    display: flex;
    flex-direction: column;
    background: rgba(255, 255, 255, 0.5);
    border: 2px solid rgba(201, 169, 79, 0.4);
    border-radius: 10px;
    overflow: hidden;
    text-decoration: none;
    transition: transform 0.15s, box-shadow 0.15s;
}
.det-mini:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(60, 35, 10, 0.2); }
.det-mini-img { aspect-ratio: 4/3; background: #fff8e8; overflow: hidden; }
.det-mini-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
.det-mini-img--txt { display: flex; align-items: center; justify-content: center; }
.det-mini-area { font-family: 'Montserrat', sans-serif; font-size: 22px; color: #b9a06a; }
.det-mini-titulo { padding: 10px 12px 2px; font-size: 14px; font-weight: 700; color: #4a3b22; }
.det-mini-area-label { padding: 0 12px 12px; font-size: 11px; color: #0a7a4b; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }

@media (max-width: 760px) {
    .det-hero { grid-template-columns: 1fr; }
    .det-section { padding: 120px 20px 60px; }
}
</style>

<script>
(function () {
    document.querySelectorAll('.det-reproducir').forEach(function (wrap) {
        var audio = wrap.querySelector('audio');
        var btn = wrap.querySelector('.det-reproducir-btn');
        var estado = wrap.querySelector('.det-reproducir-estado');
        if (!audio || !btn) return;

        function playing() {
            wrap.classList.add('playing');
            btn.classList.add('playing');
            btn.innerHTML = '<span>❚❚</span>';
            estado.textContent = 'Reproduciendo';
        }
        function paused() {
            wrap.classList.remove('playing');
            btn.classList.remove('playing');
            btn.innerHTML = '<span>▶</span>';
            estado.textContent = 'Reproducir';
        }
        btn.addEventListener('click', function () {
            if (audio.paused) {
                var p = audio.play();
                if (p && p.catch) p.catch(function () {});
            } else {
                audio.pause();
            }
        });
        audio.addEventListener('play', playing);
        audio.addEventListener('pause', paused);
        audio.addEventListener('ended', paused);
        audio.addEventListener('error', function () {
            estado.textContent = 'Error al reproducir';
            paused();
        });
    });
})();
</script>