<?php
/**
 * Vista de Poesía
 * Muestra secciones y obras administrables desde el panel de administración.
 */
require_once __DIR__ . '/../config/config.php';

function poe_tema_color($tema) {
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

try {
    $pdo = getDB();
    $secciones = $pdo->query('SELECT * FROM poe_secciones ORDER BY orden ASC')->fetchAll();

    if ($secciones) {
        $in = implode(',', array_map('intval', array_column($secciones, 'id')));
        $obras = $pdo->query("SELECT * FROM poe_obras WHERE seccion_id IN ($in) ORDER BY orden ASC")->fetchAll();
        $obrasPorSeccion = [];
        foreach ($obras as $o) {
            $obrasPorSeccion[$o['seccion_id']][] = $o;
        }
    } else {
        $obrasPorSeccion = [];
    }
} catch (Exception $e) {
    $secciones = [];
    $obrasPorSeccion = [];
}
?>

<section class="poe-section">
    <div class="poe-container">
        <div class="poe-texto">
            <div class="poe-tag">
                <img src="assets/literatura.svg" alt="Poesía" class="poe-icono">
                <span>POESÍA</span>
            </div>
            <h1>VERSOS<br>DE NICARAGUA</h1>
            <p>
                De la pluma de Rubén Darío a nuestras voces contemporáneas:
                poemas, poetas y antologías que celebran la palabra en verso.
            </p>
        </div>

        <div class="poe-imagen">
            <div class="poe-marco-deco"></div>
            <img src="assets/literatura.svg" alt="Poesía" class="poe-emblema">
            <span class="poe-sombras"></span>
        </div>
    </div>
</section>

<?php foreach ($secciones as $idx => $seccion): ?>
<section class="poe-seccion" id="seccion-<?php echo $seccion['id']; ?>">
    <div class="poe-inner">
        <div class="poe-cabecera">
            <span class="poe-num"><?php echo str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT); ?></span>
            <h2><?php echo htmlspecialchars($seccion['titulo']); ?></h2>
        </div>
        <?php if (!empty($seccion['descripcion'])): ?>
            <p class="poe-desc"><?php echo htmlspecialchars($seccion['descripcion']); ?></p>
        <?php endif; ?>

        <?php $lista = $obrasPorSeccion[$seccion['id']] ?? []; ?>
        <?php if ($lista): ?>
            <div class="poe-grid">
                <?php foreach ($lista as $obra): ?>
                    <a href="?page=poe_detalle&obra_id=<?php echo (int)$obra['id']; ?>" class="poe-card">
                        <?php if (!empty($obra['imagen'])): ?>
                            <div class="poe-img">
                                <img src="<?php echo htmlspecialchars($obra['imagen']); ?>" alt="<?php echo htmlspecialchars($obra['titulo']); ?>">
                            </div>
                        <?php endif; ?>
                        <div class="poe-card-body">
                            <h3><?php echo htmlspecialchars($obra['titulo']); ?></h3>
                            <div class="poe-autor">
                                <?php if (!empty($obra['autor'])): ?>
                                    <span class="poe-autor-nombre"><?php echo htmlspecialchars($obra['autor']); ?></span>
                                <?php endif; ?>
                                <div class="poe-meta">
                                    <?php if (!empty($obra['tema'])): ?>
                                        <span class="poe-tema" style="background: <?php echo poe_tema_color($obra['tema']); ?>;"><?php echo htmlspecialchars($obra['tema']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($obra['anio'])): ?>
                                        <span class="poe-anio"><?php echo htmlspecialchars($obra['anio']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php if (!empty($obra['poema'])): ?>
                                <div class="poe-poema"><?php echo nl2br(htmlspecialchars($obra['poema'])); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($obra['bio'])): ?>
                                <div class="poe-bio"><strong>Ficha del autor:</strong> <?php echo htmlspecialchars($obra['bio']); ?></div>
                            <?php endif; ?>
                            <span class="poe-ver">Leer ficha →</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="poe-vacio">Esta sección aún no tiene obras.</p>
        <?php endif; ?>
    </div>
</section>
<?php endforeach; ?>

<style>
@font-face {
    font-family: 'Nikan Felthgothic';
    src: url('../fonts/Felthgothic Bold.ttf') format('opentype');
}

.poe-section {
    background:
        radial-gradient(ellipse at 25% 40%, rgba(255, 244, 222, 0.9), transparent 60%),
        radial-gradient(ellipse at 75% 45%, rgba(255, 244, 222, 0.7), transparent 55%),
        linear-gradient(135deg, #f6efe3 0%, #efe4d0 45%, #e7d8bd 100%);
    padding: 80px 80px 0;
    font-family: 'Montserrat', sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    position: relative;
    z-index: 2;
    overflow: hidden;
}

.poe-section::before {
    content: "";
    position: absolute;
    inset: 0;
    background-image:
        radial-gradient(circle at 20% 30%, rgba(120, 90, 40, 0.05) 0, transparent 40%),
        radial-gradient(circle at 80% 70%, rgba(120, 90, 40, 0.05) 0, transparent 40%),
        radial-gradient(circle at 50% 50%, rgba(120, 90, 40, 0.04) 0, transparent 50%);
    pointer-events: none;
}

.poe-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    gap: 40px;
    position: relative;
}

.poe-texto {
    max-width: 45%;
    padding-bottom: 120px;
}

.poe-tag {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #0a7a4b;
    font-size: 30px;
    letter-spacing: 4px;
    margin-bottom: 24px;
    text-shadow: 0 1px 0 rgba(255, 255, 255, 0.4);
}

.poe-tag .poe-icono {
    width: 44px;
    height: 44px;
    object-fit: contain;
    filter: invert(1) brightness(0);
}

.poe-texto h1 {
    font-family: 'Nikan Felthgothic', 'Felthgothic', serif;
    font-size: 78px;
    color: #C6372E;
    margin: 0 0 24px;
    line-height: 1.05;
    letter-spacing: 3px;
    text-shadow: 0 2px 2px rgba(90, 60, 20, 0.25);
}

.poe-texto p {
    font-size: 22px;
    color: #4a3b22;
    line-height: 1.7;
    margin-bottom: 30px;
    max-width: 560px;
}

.poe-imagen {
    position: relative;
    text-align: center;
    align-self: flex-end;
    margin-bottom: -80px;
    transform: translateY(-60px);
    padding: 24px;
}

.poe-marco-deco {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 66%;
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

.poe-marco-deco::before,
.poe-marco-deco::after {
    content: "";
    position: absolute;
    width: 40px;
    height: 40px;
    border: 3px solid #8a6a2f;
}
.poe-marco-deco::before {
    top: -3px;
    left: -3px;
    border-right: none;
    border-bottom: none;
}
.poe-marco-deco::after {
    top: -3px;
    right: -3px;
    border-left: none;
    border-bottom: none;
}

.poe-emblema {
    width: 340px;
    height: auto;
    display: block;
    position: relative;
    z-index: 2;
}

.poe-sombras {
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

/* ===== SECCIONES DE POESÍA ===== */
.poe-seccion {
    background:
        linear-gradient(135deg, #f6efe3 0%, #efe4d0 45%, #e7d8bd 100%);
    padding: 90px 80px;
    font-family: 'Montserrat', sans-serif;
    position: relative;
    z-index: 2;
    border-top: 3px solid #c9a94f;
}

.poe-seccion:nth-child(even) {
    background:
        linear-gradient(135deg, #efe8d8 0%, #e5d6b8 60%, #dcc9a3 100%);
}

.poe-inner {
    max-width: 1200px;
    margin: 0 auto;
}

.poe-cabecera {
    display: flex;
    align-items: center;
    gap: 18px;
    margin-bottom: 14px;
}

.poe-num {
    font-family: 'Nikan Felthgothic', 'Felthgothic', serif;
    font-size: 42px;
    color: #c9a94f;
    text-shadow: 0 1px 0 rgba(255,255,255,0.4);
}

.poe-cabecera h2 {
    font-family: 'Nikan Felthgothic', 'Felthgothic', serif;
    font-size: 40px;
    color: #C6372E;
    letter-spacing: 1px;
    text-shadow: 0 2px 2px rgba(90, 60, 20, 0.2);
}

.poe-desc {
    font-family: 'Rustica', serif;
    font-size: 18px;
    color: #4a3b22;
    max-width: 760px;
    line-height: 1.6;
    margin-bottom: 32px;
}

.poe-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
    gap: 28px;
}

.poe-card {
    background: rgba(255, 255, 255, 0.6);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(90, 60, 20, 0.15);
    display: flex;
    flex-direction: column;
    backdrop-filter: blur(2px);
    border-top: 3px solid #c9a94f;
    color: inherit;
    text-decoration: none;
    transition: transform 0.18s, box-shadow 0.18s;
}
.poe-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 30px rgba(90, 60, 20, 0.25);
}
.poe-ver {
    margin-top: 12px;
    color: #0a7a4b;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.poe-img {
    aspect-ratio: 16 / 9;
    background: #fff8e8;
    overflow: hidden;
}

.poe-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.poe-card-body {
    padding: 22px 22px 24px;
    color: #4a3b22;
}

.poe-card-body h3 {
    font-family: 'Nikan Felthgothic', 'Felthgothic', serif;
    font-size: 24px;
    color: #C6372E;
    margin-bottom: 4px;
}

.poe-autor-nombre {
    display: block;
    font-size: 14px;
    font-style: italic;
    color: #0a7a4b;
    margin-bottom: 8px;
}

.poe-meta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    padding-bottom: 12px;
    border-bottom: 1px dashed rgba(120, 90, 40, 0.3);
    margin-bottom: 12px;
}

.poe-tema {
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    padding: 3px 10px;
    border-radius: 20px;
}

.poe-anio {
    font-size: 13px;
    color: #6a5a38;
}

.poe-poema {
    font-family: 'Nikan Felthgothic', 'Felthgothic', serif;
    font-size: 16px;
    line-height: 1.7;
    color: #4a3b22;
    white-space: normal;
    margin-bottom: 14px;
    padding-left: 14px;
    border-left: 3px solid #c9a94f;
}

.poe-bio {
    font-size: 13px;
    line-height: 1.55;
    color: #6a5a38;
    background: rgba(201, 169, 79, 0.12);
    border-radius: 8px;
    padding: 10px 12px;
}

.poe-bio strong {
    color: #5a4720;
    font-weight: 700;
}

.poe-vacio {
    color: #8a7a58;
    font-style: italic;
    font-size: 16px;
}
</style>