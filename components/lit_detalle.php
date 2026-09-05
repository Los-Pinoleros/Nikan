<?php
/**
 * Detalle de una obra de literatura.
 * Se accede via ?page=lit_detalle&obra_id=ID
 */
require_once __DIR__ . '/../config/config.php';

$obra_id = (int)($_GET['obra_id'] ?? 0);

function lit_area_label($area) {
    return ['arte' => 'Arte', 'lit' => 'Literatura', 'poe' => 'Poesía'][$area] ?? $area;
}

try {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM lit_obras WHERE id=?');
    $stmt->execute([$obra_id]);
    $obra = $stmt->fetch();

    if (!$obra) {
        echo '<div class="det-vacio"><a href="index.php?page=literatura">← Volver a Literatura</a><p>No se encontró la obra.</p></div>
        <style>.det-vacio{position:relative;z-index:2;padding:160px 40px;text-align:center;font-family:Montserrat,sans-serif;color:#4a3b22}.det-vacio a{color:#C6372E;font-weight:700;text-decoration:none}.det-vacio p{font-size:18px;margin-top:12px;color:#8a7a58}</style>';
        return;
    }

    $sec = $pdo->prepare('SELECT titulo FROM lit_secciones WHERE id=?');
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
        $areas = ['arte' => 'arte_obras', 'lit' => 'lit_obras', 'poe' => 'poe_obras'];
        foreach ($areas as $area => $tabla) {
            $imgCol = $area === 'lit' ? 'NULL' : 'imagen';
            $excluir = $area === 'lit' ? " AND id<>?" : '';
            $q = "SELECT id, titulo, $imgCol AS imagen, '{$area}' AS area FROM $tabla WHERE autor_id=?" . $excluir . " ORDER BY orden ASC";
            $params = [$obra['autor_id']];
            if ($area === 'lit') $params[] = $obra_id;
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

<section class="detl-section">
    <div class="detl-container">

        <a href="?page=literatura" class="detl-back">← Volver a Literatura</a>

        <div class="detl-hero">
            <div class="detl-imagen">
                <div class="detl-marco">
                    <?php if (!empty($obra['imagen'])): ?>
                        <img src="<?php echo htmlspecialchars($obra['imagen']); ?>" alt="<?php echo htmlspecialchars($obra['titulo']); ?>">
                    <?php else: ?>
                        <div class="detl-portada-txt">
                            <span class="detl-portada-letra"><?php echo mb_substr($obra['titulo'], 0, 1); ?></span>
                            <span class="detl-portada-titulo"><?php echo htmlspecialchars($obra['titulo']); ?></span>
                            <?php if (!empty($obra['autor'])): ?>
                                <span class="detl-portada-autor"><?php echo htmlspecialchars($obra['autor']); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="detl-info">
                <?php if ($seccion_titulo): ?>
                    <span class="detl-tag"><?php echo htmlspecialchars($seccion_titulo); ?></span>
                <?php endif; ?>
                <h1><?php echo htmlspecialchars($obra['titulo']); ?></h1>

                <dl class="detl-ficha">
                    <?php if ($autor): ?>
                        <div class="detl-afil">
                            <dt>Autor/a</dt>
                            <dd><a href="?page=autores" class="detl-autor"><?php echo htmlspecialchars($autor['nombre']); ?></a></dd>
                        </div>
                    <?php elseif (!empty($obra['autor'])): ?>
                        <div class="detl-afil"><dt>Autor/a</dt><dd><?php echo htmlspecialchars($obra['autor']); ?></dd></div>
                    <?php endif; ?>
                    <?php if (!empty($obra['genero'])): ?>
                        <div class="detl-afil"><dt>Género</dt><dd><?php echo htmlspecialchars($obra['genero']); ?></dd></div>
                    <?php endif; ?>
                    <?php if (!empty($obra['anio'])): ?>
                        <div class="detl-afil"><dt>Año</dt><dd><?php echo htmlspecialchars($obra['anio']); ?></dd></div>
                    <?php endif; ?>
                </dl>

                <?php if (!empty($obra['sinopsis'])): ?>
                    <p class="detl-sinopsis"><?php echo nl2br(htmlspecialchars($obra['sinopsis'])); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($obra['detalle'])): ?>
            <div class="detl-bloque">
                <h2>Detalles de la obra</h2>
                <div class="detl-cuerpo"><?php echo nl2br(htmlspecialchars($obra['detalle'])); ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($obra['fragmento'])): ?>
            <div class="detl-bloque">
                <h2>Fragmento</h2>
                <blockquote class="detl-fragmento">«<?php echo nl2br(htmlspecialchars($obra['fragmento'])); ?>»</blockquote>
            </div>
        <?php endif; ?>

        <?php if ($autor): ?>
            <div class="detl-bloque">
                <h2>Sobre el autor</h2>
                <div class="detl-autor-box">
                    <div class="detl-autor-retrato">
                        <?php if ($autor['retrato']): ?>
                            <img src="<?php echo htmlspecialchars($autor['retrato']); ?>" alt="Retrato de <?php echo htmlspecialchars($autor['nombre']); ?>">
                        <?php else: ?>
                            <span class="detl-autor-ph"><?php echo mb_substr($autor['nombre'], 0, 1); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="detl-autor-cuerpo">
                        <h3><?php echo htmlspecialchars($autor['nombre']); ?></h3>
                        <?php if (!empty($autor['lugar']) || !empty($autor['nacimiento'])): ?>
                            <p class="detl-autor-datos">
                                <?php echo htmlspecialchars($autor['lugar']); ?>
                                <?php if (!empty($autor['nacimiento'])): ?> · <?php echo htmlspecialchars($autor['nacimiento']); ?><?php if (!empty($autor['fallecimiento'])): ?>–<?php echo htmlspecialchars($autor['fallecimiento']); ?><?php endif; ?><?php endif; ?>
                            </p>
                        <?php endif; ?>
                        <?php if (!empty($autor['bio'])): ?>
                            <p class="detl-autor-bio"><?php echo nl2br(htmlspecialchars($autor['bio'])); ?></p>
                        <?php endif; ?>
                        <a href="?page=autores" class="detl-autor-link">Ver ficha completa del autor →</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($otras_obras): ?>
            <div class="detl-bloque">
                <h2>Otras obras de este autor</h2>
                <div class="detl-otras">
                    <?php foreach ($otras_obras as $o): ?>
                        <?php
                        $href = $o['area'] === 'arte'
                            ? '?page=arte_detalle&obra_id=' . (int)$o['id']
                            : ($o['area'] === 'lit'
                                ? '?page=lit_detalle&obra_id=' . (int)$o['id']
                                : '?page=poe_detalle&obra_id=' . (int)$o['id']);
                        ?>
                        <a class="detl-mini" href="<?php echo $href; ?>">
                            <?php if (($o['area'] === 'arte' || $o['area'] === 'poe') && !empty($o['imagen'])): ?>
                                <div class="detl-mini-img">
                                    <img src="<?php echo htmlspecialchars($o['imagen']); ?>" alt="<?php echo htmlspecialchars($o['titulo']); ?>">
                                </div>
                            <?php else: ?>
                                <div class="detl-mini-img detl-mini-img--txt">
                                    <span class="detl-mini-area"><?php echo lit_area_label($o['area']); ?></span>
                                </div>
                            <?php endif; ?>
                            <span class="detl-mini-titulo"><?php echo htmlspecialchars($o['titulo']); ?></span>
                            <span class="detl-mini-area-label"><?php echo lit_area_label($o['area']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</section>

<style>
@font-face {
    font-family: 'Felthgothic Bold';
    src: url('../fonts/Felthgothic Bold.otf') format('opentype');
}

.detl-section {
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

.detl-container { max-width: 1000px; margin: 0 auto; }

.detl-back {
    display: inline-block;
    color: #0a7a4b;
    font-weight: 700;
    text-decoration: none;
    margin-bottom: 28px;
    font-size: 15px;
}
.detl-back:hover { text-decoration: underline; }

.detl-hero {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 1fr;
    gap: 40px;
    align-items: start;
}

.detl-marco {
    position: relative;
    background: rgba(255, 252, 244, 0.5);
    border: 2px solid #c9a94f;
    border-radius: 6px;
    box-shadow: 0 0 0 6px rgba(201, 169, 79, 0.15), 0 15px 35px rgba(60, 35, 10, 0.25);
    padding: 16px;
    min-height: 340px;
}
.detl-marco img {
    width: 100%;
    max-height: 520px;
    object-fit: contain;
    display: block;
}

.detl-portada-txt {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 320px;
    text-align: center;
    padding: 20px;
}
.detl-portada-letra {
    font-family: 'Felthgothic Bold', 'Felthgothic', serif;
    font-size: 90px;
    color: #C6372E;
    line-height: 1;
}
.detl-portada-titulo {
    font-family: 'Felthgothic Bold', 'Felthgothic', serif;
    font-size: 22px;
    color: #5a4720;
    margin-top: 6px;
}
.detl-portada-autor {
    font-size: 14px;
    font-style: italic;
    color: #8a7a58;
    margin-top: 4px;
}

.detl-info {}
.detl-tag {
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
.detl-info h1 {
    font-family: 'Felthgothic Bold', 'Felthgothic', serif;
    font-size: 46px;
    color: #C6372E;
    line-height: 1.1;
    margin: 0 0 20px;
    text-shadow: 0 2px 2px rgba(90, 60, 20, 0.2);
}

.detl-ficha { margin: 0 0 20px; }
.detl-afil {
    display: flex;
    gap: 10px;
    padding: 8px 0;
    border-bottom: 1px dashed rgba(120, 90, 40, 0.3);
    font-size: 15px;
}
.detl-afil dt { font-weight: 700; color: #0a7a4b; min-width: 90px; }
.detl-afil dd { margin: 0; color: #4a3b22; font-weight: 600; }
.detl-autor { color: #C6372E; font-weight: 700; text-decoration: none; }
.detl-autor:hover { text-decoration: underline; }

.detl-sinopsis {
    font-size: 16px;
    line-height: 1.7;
    color: #4a3b22;
}

.detl-bloque {
    margin-top: 44px;
    padding-top: 34px;
    border-top: 2px solid #c9a94f;
}
.detl-bloque h2 {
    font-family: 'Felthgothic Bold', 'Felthgothic', serif;
    font-size: 30px;
    color: #C6372E;
    margin: 0 0 18px;
}
.detl-cuerpo {
    font-size: 16px;
    line-height: 1.8;
    color: #4a3b22;
    max-width: 780px;
}

.detl-fragmento {
    margin: 0;
    padding: 22px 26px;
    background: rgba(201, 169, 79, 0.14);
    border-left: 4px solid #c9a94f;
    font-family: 'Felthgothic Bold', 'Felthgothic', serif;
    font-size: 20px;
    line-height: 1.6;
    color: #5a4720;
    font-style: italic;
}

/* Autor */
.detl-autor-box {
    display: flex;
    gap: 22px;
    background: rgba(255, 255, 255, 0.5);
    border: 2px solid rgba(201, 169, 79, 0.4);
    border-radius: 12px;
    padding: 22px;
    align-items: flex-start;
}
.detl-autor-retrato {
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
.detl-autor-retrato img { width: 100%; height: 100%; object-fit: cover; }
.detl-autor-ph { font-family: 'Felthgothic Bold', 'Felthgothic', serif; font-size: 40px; color: #C6372E; }
.detl-autor-cuerpo h3 { font-family: 'Felthgothic Bold', 'Felthgothic', serif; font-size: 26px; color: #C6372E; margin: 0 0 4px; }
.detl-autor-datos { font-size: 14px; color: #6a5a38; margin: 0 0 10px; }
.detl-autor-bio { font-size: 15px; line-height: 1.6; color: #4a3b22; margin: 0 0 12px; }
.detl-autor-link { color: #0a7a4b; font-weight: 700; font-size: 14px; text-decoration: none; }
.detl-autor-link:hover { text-decoration: underline; }

/* Otras obras */
.detl-otras {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 18px;
}
.detl-mini {
    display: flex;
    flex-direction: column;
    background: rgba(255, 255, 255, 0.5);
    border: 2px solid rgba(201, 169, 79, 0.4);
    border-radius: 10px;
    overflow: hidden;
    text-decoration: none;
    transition: transform 0.15s, box-shadow 0.15s;
}
.detl-mini:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(60, 35, 10, 0.2); }
.detl-mini-img { aspect-ratio: 4/3; background: #fff8e8; overflow: hidden; }
.detl-mini-img img { width: 100%; height: 100%; object-fit: cover; display: block; }
.detl-mini-img--txt { display: flex; align-items: center; justify-content: center; }
.detl-mini-area { font-family: 'Felthgothic Bold', 'Felthgothic', serif; font-size: 22px; color: #b9a06a; }
.detl-mini-titulo { padding: 10px 12px 2px; font-size: 14px; font-weight: 700; color: #4a3b22; }
.detl-mini-area-label { padding: 0 12px 12px; font-size: 11px; color: #0a7a4b; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }

@media (max-width: 760px) {
    .detl-hero { grid-template-columns: 1fr; }
    .detl-section { padding: 120px 20px 60px; }
}
</style>