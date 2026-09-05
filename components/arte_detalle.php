<?php
/**
 * Detalle de una obra de arte.
 * Se accede via ?page=arte_detalle&obra_id=ID
 * Muestra la obra completa + bloque del autor + otras obras del mismo autor.
 */
require_once __DIR__ . '/../config/config.php';

$obra_id = (int)($_GET['obra_id'] ?? 0);

function arte_aut_area_label($area) {
    return ['arte' => 'Arte', 'lit' => 'Literatura', 'poe' => 'Poesía'][$area] ?? $area;
}

try {
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM arte_obras WHERE id=?');
    $stmt->execute([$obra_id]);
    $obra = $stmt->fetch();

    if (!$obra) {
        echo '<div class="det-vacio"><a href="index.php?page=arte">← Volver a Arte</a><p>No se encontró la obra.</p></div>
        <style>.det-vacio{position:relative;z-index:2;padding:160px 40px;text-align:center;font-family:Montserrat,sans-serif;color:#4a3b22}.det-vacio a{color:#C6372E;font-weight:700;text-decoration:none}.det-vacio p{font-size:18px;margin-top:12px;color:#8a7a58}</style>';
        return;
    }

    // Sección a la que pertenece
    $sec = $pdo->prepare('SELECT titulo FROM arte_secciones WHERE id=?');
    $sec->execute([$obra['seccion_id']]);
    $seccion_titulo = $sec->fetchColumn();

    // Autor
    $autor = null;
    if (!empty($obra['autor_id'])) {
        $st = $pdo->prepare('SELECT * FROM autores WHERE id=?');
        $st->execute([$obra['autor_id']]);
        $autor = $st->fetch();
    }

    // Otras obras de este autor (todas las áreas)
    $otras_obras = [];
    if ($autor) {
        $areas = ['arte' => 'arte_obras', 'lit' => 'lit_obras', 'poe' => 'poe_obras'];
        foreach ($areas as $area => $tabla) {
            $imgCol = $area === 'lit' ? 'NULL' : 'imagen';
            $excluir = $area === 'arte' ? " AND id<>?" : '';
            $q = "SELECT id, titulo, $imgCol AS imagen, '{$area}' AS area FROM $tabla WHERE autor_id=?" . $excluir . " ORDER BY orden ASC";
            $params = [$obra['autor_id']];
            if ($area === 'arte') $params[] = $obra_id;
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

<section class="det-section">
    <div class="det-container">

        <a href="?page=arte" class="det-back">← Volver a Arte</a>

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
                    <?php if (!empty($obra['anio'])): ?>
                        <div class="det-afil"><dt>Año</dt><dd><?php echo htmlspecialchars($obra['anio']); ?></dd></div>
                    <?php endif; ?>
                    <?php if (!empty($obra['tecnica'])): ?>
                        <div class="det-afil"><dt>Técnica</dt><dd><?php echo htmlspecialchars($obra['tecnica']); ?></dd></div>
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

        <?php if (!empty($obra['detalle'])): ?>
            <div class="det-bloque">
                <h2>Detalles de la obra</h2>
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
                        $href = $o['area'] === 'arte'
                            ? '?page=arte_detalle&obra_id=' . (int)$o['id']
                            : ($o['area'] === 'lit'
                                ? '?page=lit_detalle&obra_id=' . (int)$o['id']
                                : '?page=poe_detalle&obra_id=' . (int)$o['id']);
                        ?>
                        <a class="det-mini" href="<?php echo $href; ?>">
                            <?php if ($o['area'] === 'arte' && !empty($o['imagen'])): ?>
                                <div class="det-mini-img">
                                    <img src="<?php echo htmlspecialchars($o['imagen']); ?>" alt="<?php echo htmlspecialchars($o['titulo']); ?>">
                                </div>
                            <?php else: ?>
                                <div class="det-mini-img det-mini-img--txt">
                                    <span class="det-mini-area"><?php echo arte_aut_area_label($o['area']); ?></span>
                                </div>
                            <?php endif; ?>
                            <span class="det-mini-titulo"><?php echo htmlspecialchars($o['titulo']); ?></span>
                            <span class="det-mini-area-label"><?php echo arte_aut_area_label($o['area']); ?></span>
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
.det-back:hover { text-decoration: underline; }

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
    font-family: 'Felthgothic Bold', 'Felthgothic', serif;
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
    font-family: 'Felthgothic Bold', 'Felthgothic', serif;
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
.det-autor-ph { font-family: 'Felthgothic Bold', 'Felthgothic', serif; font-size: 40px; color: #C6372E; }
.det-autor-cuerpo h3 { font-family: 'Felthgothic Bold', 'Felthgothic', serif; font-size: 26px; color: #C6372E; margin: 0 0 4px; }
.det-autor-datos { font-size: 14px; color: #6a5a38; margin: 0 0 10px; }
.det-autor-bio { font-size: 15px; line-height: 1.6; color: #4a3b22; margin: 0 0 12px; }
.det-autor-link { color: #0a7a4b; font-weight: 700; font-size: 14px; text-decoration: none; }
.det-autor-link:hover { text-decoration: underline; }

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
.det-mini-area { font-family: 'Felthgothic Bold', 'Felthgothic', serif; font-size: 22px; color: #b9a06a; }
.det-mini-titulo { padding: 10px 12px 2px; font-size: 14px; font-weight: 700; color: #4a3b22; }
.det-mini-area-label { padding: 0 12px 12px; font-size: 11px; color: #0a7a4b; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }

@media (max-width: 760px) {
    .det-hero { grid-template-columns: 1fr; }
    .det-section { padding: 120px 20px 60px; }
}
</style>