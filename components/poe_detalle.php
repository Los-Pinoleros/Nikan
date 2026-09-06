<?php
/**
 * Detalle de una obra de poesía.
 * Se accede via ?page=poe_detalle&obra_id=ID
 */
require_once __DIR__ . '/../config/config.php';

$obra_id = (int)($_GET['obra_id'] ?? 0);

function poe_area_label($area) {
    return ['arte' => 'Arte', 'lit' => 'Literatura', 'poe' => 'Poesía', 'mus' => 'Música'][$area] ?? $area;
}

function poe_det_tema_color($tema) {
    switch ($tema) {
        case 'amor':      return '#C6372E';
        case 'patria':    return '#2a6fdb';
        case 'naturaleza':return '#0a7a4b';
        case 'vida':      return '#c98a2e';
        case 'muerte':    return '#5a4a35';
        case 'fe':        return '#7a4a9e';
        default:          return '#8a7a58';
    }
}

$tema_labels = [
    'amor' => 'Amor', 'patria' => 'Patria', 'naturaleza' => 'Naturaleza',
    'vida' => 'Vida', 'muerte' => 'Muerte', 'fe' => 'Fe', 'otro' => 'Otro',
];

try {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM poe_obras WHERE id=?');
    $stmt->execute([$obra_id]);
    $obra = $stmt->fetch();

    if (!$obra) {
        echo '<div class="det-vacio"><a href="index.php?page=poesia">← Volver a Poesía</a><p>No se encontró la obra.</p></div>
        <style>.det-vacio{position:relative;z-index:2;padding:160px 40px;text-align:center;font-family:Montserrat,sans-serif;color:#4a3b22}.det-vacio a{color:#C6372E;font-weight:700;text-decoration:none}.det-vacio p{font-size:18px;margin-top:12px;color:#8a7a58}</style>';
        return;
    }

    $sec = $pdo->prepare('SELECT titulo FROM poe_secciones WHERE id=?');
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
            $imgCol = $area === 'lit' ? 'NULL' : 'imagen';
            $excluir = $area === 'poe' ? " AND id<>?" : '';
            $q = "SELECT id, titulo, $imgCol AS imagen, '{$area}' AS area FROM $tabla WHERE autor_id=?" . $excluir . " ORDER BY orden ASC";
            $params = [$obra['autor_id']];
            if ($area === 'poe') $params[] = $obra_id;
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

<section class="detp-section">
    <div class="detp-container">

        <a href="?page=poesia" class="detp-back">← Volver a Poesía</a>

        <div class="detp-hero">
            <div class="detp-imagen">
                <div class="detp-marco">
                    <?php if (!empty($obra['imagen'])): ?>
                        <img src="<?php echo htmlspecialchars($obra['imagen']); ?>" alt="<?php echo htmlspecialchars($obra['titulo']); ?>">
                    <?php else: ?>
                        <div class="detp-marco-txt">
                            <span class="detp-marco-letra"><?php echo mb_substr($obra['titulo'], 0, 1); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="detp-info">
                <?php if ($seccion_titulo): ?>
                    <span class="detp-tag"><?php echo htmlspecialchars($seccion_titulo); ?></span>
                <?php endif; ?>
                <h1 aria-label="<?php echo htmlspecialchars($obra['titulo'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo preg_replace('/\s+/u', '<span class="detp-titulo-espacio" aria-hidden="true"></span>', trim(htmlspecialchars($obra['titulo'], ENT_QUOTES, 'UTF-8'))); ?></h1>

                <dl class="detp-ficha">
                    <?php if ($autor): ?>
                        <div class="detp-afil">
                            <dt>Autor/a</dt>
                            <dd><a href="?page=autores" class="detp-autor"><?php echo htmlspecialchars($autor['nombre']); ?></a></dd>
                        </div>
                    <?php elseif (!empty($obra['autor'])): ?>
                        <div class="detp-afil"><dt>Autor/a</dt><dd><?php echo htmlspecialchars($obra['autor']); ?></dd></div>
                    <?php endif; ?>
                    <?php if (!empty($obra['tema'])): ?>
                        <div class="detp-afil"><dt>Tema</dt><dd><span class="detp-tema" style="background: <?php echo poe_det_tema_color($obra['tema']); ?>;"><?php echo htmlspecialchars(($obra['tema'])); ?></span></dd></div>
                    <?php endif; ?>
                    <?php if (!empty($obra['anio'])): ?>
                        <div class="detp-afil"><dt>Año</dt><dd><?php echo htmlspecialchars($obra['anio']); ?></dd></div>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <?php if (!empty($obra['poema'])): ?>
            <div class="detp-bloque">
                <h2>El poema</h2>
                <div class="detp-poema"><?php echo nl2br(htmlspecialchars($obra['poema'])); ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($obra['detalle'])): ?>
            <div class="detp-bloque">
                <h2>Detalles de la obra</h2>
                <div class="detp-cuerpo"><?php echo nl2br(htmlspecialchars($obra['detalle'])); ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($obra['bio'])): ?>
            <div class="detp-bloque">
                <h2>Ficha del autor</h2>
                <div class="detp-cuerpo"><?php echo nl2br(htmlspecialchars($obra['bio'])); ?></div>
            </div>
        <?php endif; ?>

        <?php if ($autor): ?>
            <div class="detp-bloque">
                <h2>Sobre el autor</h2>
                <div class="detp-autor-box">
                    <div class="detp-autor-retrato">
                        <?php if ($autor['retrato']): ?>
                            <img src="<?php echo htmlspecialchars($autor['retrato']); ?>" alt="Retrato de <?php echo htmlspecialchars($autor['nombre']); ?>">
                        <?php else: ?>
                            <span class="detp-autor-ph"><?php echo mb_substr($autor['nombre'], 0, 1); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="detp-autor-cuerpo">
                        <h3><?php echo htmlspecialchars($autor['nombre']); ?></h3>
                        <?php if (!empty($autor['lugar']) || !empty($autor['nacimiento'])): ?>
                            <p class="detp-autor-datos">
                                <?php echo htmlspecialchars($autor['lugar']); ?>
                                <?php if (!empty($autor['nacimiento'])): ?> · <?php echo htmlspecialchars($autor['nacimiento']); ?><?php if (!empty($autor['fallecimiento'])): ?>–<?php echo htmlspecialchars($autor['fallecimiento']); ?><?php endif; ?><?php endif; ?>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($autor['bio'])): ?>
                            <p class="detp-autor-bio"><?php echo nl2br(htmlspecialchars($autor['bio'])); ?></p>
                        <?php endif; ?>
                        <a href="?page=autores" class="detp-autor-link">Ver ficha completa del autor →</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($otras_obras): ?>
            <div class="detp-bloque">
                <h2>Otras obras de este autor</h2>
                <div class="detp-otras">
                    <?php foreach ($otras_obras as $o): ?>
                        <?php
                        $href = $o['area'] === 'arte'
                            ? '?page=arte_detalle&obra_id=' . (int)$o['id']
                            : ($o['area'] === 'lit'
                                ? '?page=lit_detalle&obra_id=' . (int)$o['id']
                                : ($o['area'] === 'poe'
                                    ? '?page=poe_detalle&obra_id=' . (int)$o['id']
                                    : '?page=musica_detalle&obra_id=' . (int)$o['id']));
                        ?>
                        <a class="detp-mini" href="<?php echo $href; ?>">
                            <?php if (($o['area'] === 'arte' || $o['area'] === 'poe') && !empty($o['imagen'])): ?>
                                <div class="detp-mini-img">
                                    <img src="<?php echo htmlspecialchars($o['imagen']); ?>" alt="<?php echo htmlspecialchars($o['titulo']); ?>">
                                </div>
                            <?php else: ?>
                                <div class="detp-mini-img detp-mini-img--txt">
                                    <span class="detp-mini-area"><?php echo poe_area_label($o['area']); ?></span>
                                </div>
                            <?php endif; ?>
                            <span class="detp-mini-titulo"><?php echo htmlspecialchars($o['titulo']); ?></span>
                            <span class="detp-mini-area-label"><?php echo poe_area_label($o['area']); ?></span>
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

.detp-section {
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

.detp-container { max-width: 1000px; margin: 0 auto; }

.detp-back {
    display: inline-block;
    color: #0a7a4b;
    font-weight: 700;
    text-decoration: none;
    margin-bottom: 28px;
    font-size: 15px;
}
.detp-back:hover { text-decoration: none; }

.detp-hero {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 1fr;
    gap: 40px;
    align-items: start;
}

.detp-marco {
    position: relative;
    background: rgba(255, 252, 244, 0.5);
    border: 2px solid #c9a94f;
    border-radius: 6px;
    box-shadow: 0 0 0 6px rgba(201, 169, 79, 0.15), 0 15px 35px rgba(60, 35, 10, 0.25);
    padding: 16px;
    min-height: 300px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.detp-marco img {
    width: 100%;
    max-height: 400px;
    object-fit: contain;
    display: block;
}

.detp-marco-txt {
    display: flex;
    align-items: center;
    justify-content: center;
}
.detp-marco-letra {
    font-family: 'Nikan Felthgothic', 'Felthgothic', serif;
    font-size: 120px;
    color: #C6372E;
    line-height: 1;
}

.detp-info {}
.detp-tag {
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
.detp-info h1 {
    font-family: 'Nikan Felthgothic', 'Felthgothic', serif;
    font-size: 46px;
    color: #C6372E;
    line-height: 1.1;
    margin: 0 0 20px;
    text-shadow: 0 2px 2px rgba(90, 60, 20, 0.2);
}
.detp-titulo-espacio { display: inline-block; width: 0.28em; }

.detp-ficha { margin: 0 0 20px; }
.detp-afil {
    display: flex;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px dashed rgba(120, 90, 40, 0.3);
    font-size: 15px;
    align-items: center;
}
.detp-afil dt { font-weight: 700; color: #0a7a4b; min-width: 90px; }
.detp-afil dd { margin: 0; color: #4a3b22; font-weight: 600; }
.detp-autor { color: #C6372E; font-weight: 700; text-decoration: none; }
.detp-autor:hover { text-decoration: underline; }

.detp-tema {
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 3px 10px;
    border-radius: 20px;
}

.detp-bloque {
    margin-top: 44px;
    padding-top: 34px;
    border-top: 2px solid #c9a94f;
}
.detp-bloque h2 {
    font-family: 'Nikan Felthgothic', 'Felthgothic', serif;
    font-size: 30px;
    color: #C6372E;
    margin: 0 0 18px;
}
.detp-cuerpo {
    font-size: 16px;
    line-height: 1.8;
    color: #4a3b22;
    max-width: 780px;
}

.detp-poema {
    font-family: 'Nikan Felthgothic', 'Felthgothic', serif;
    font-size: 20px;
    line-height: 2;
    color: #4a3b22;
    max-width: 700px;
    padding-left: 22px;
    border-left: 4px solid #c9a94f;
    font-style: italic;
}

/* Autor */
.detp-autor-box {
    display: flex;
    gap: 22px;
    background: rgba(255, 255, 255, 0.5);
    border: 2px solid rgba(201, 169, 79, 0.4);
    border-radius: 12px;
    padding: 22px;
    align-items: flex-start;
}
.detp-autor-retrato {
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
.detp-autor-retrato img { width: 100%; height: 100%; object-fit: cover; }
.detp-autor-ph { font-family: 'Nikan Felthgothic', 'Felthgothic', serif; font-size: 40px; color: #C6372E; }
.detp-autor-cuerpo h3 { font-family: 'Nikan Felthgothic', 'Felthgothic', serif; font-size: 26px; color: #C6372E; margin: 0 0 4px; }
.detp-autor-datos { font-size: 14px; color: #6a5a38; margin: 0 0 10px; }
.detp-autor-bio { font-size: 15px; line-height: 1.6; color: #4a3b22; margin: 0 0 12px; }
.detp-autor-link { color: #0a7a4b; font-weight: 700; font-size: 14px; text-decoration: none; }
.detp-autor-link:hover { text-decoration: none; }

/* Otras obras */
.detp-otras {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 18px;
}
.detp-mini {
    display: flex;
    flex-direction: column;
    background: rgba(255, 255, 255, 0.5);
    border: 2px solid rgba(201, 169, 79, 0.4);
    border-radius: 10px;
    overflow: hidden;
    text-decoration: none;
    transition: transform 0.15s, box-shadow 0.15s;
}
.detp-mini:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(60, 35, 10, 0.2); }
.detp-mini-img { aspect-ratio: 4/3; background: #fff8e8; overflow: hidden; }
.detp-mini-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
.detp-mini-img--txt { display: flex; align-items: center; justify-content: center; }
.detp-mini-area { font-family: 'Montserrat', sans-serif; font-size: 22px; color: #b9a06a; }
.detp-mini-titulo { padding: 10px 12px 2px; font-size: 14px; font-weight: 700; color: #4a3b22; }
.detp-mini-area-label { padding: 0 12px 12px; font-size: 11px; color: #0a7a4b; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }

@media (max-width: 760px) {
    .detp-hero { grid-template-columns: 1fr; }
    .detp-section { padding: 120px 20px 60px; }
}
</style>