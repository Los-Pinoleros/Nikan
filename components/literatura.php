<?php
/**
 * Vista de Literatura
 * Muestra secciones y obras administrables desde el panel de administración.
 */
require_once __DIR__ . '/../config/config.php';

try {
    $pdo = getDB();
    $secciones = $pdo->query('SELECT * FROM lit_secciones ORDER BY orden ASC')->fetchAll();

    if ($secciones) {
        $in = implode(',', array_map('intval', array_column($secciones, 'id')));
        $obras = $pdo->query("SELECT * FROM lit_obras WHERE seccion_id IN ($in) ORDER BY orden ASC")->fetchAll();
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

<section class="lite-section">
    <div class="lite-container">
        <div class="lite-texto">
            <div class="lite-tag">
                <img src="assets/literatura.svg" alt="Literatura" class="lite-icono">
                <span>LITERATURA</span>
            </div>
            <h1>LA PALABRA<br>NICARAGÜENSE</h1>
            <p>
                Un recorrido por la palabra escrita y oral del país: la novela, el cuento,
                el teatro, las leyendas y los mitos que alimentan nuestra identidad cultural.
            </p>
        </div>

        <div class="lite-imagen">
            <div class="lite-marco-deco"></div>
            <img src="assets/literaturas.svg" alt="Literatura" class="lite-emblema">
            <span class="lite-sombras"></span>
        </div>
    </div>
</section>

<?php foreach ($secciones as $idx => $seccion): ?>
<section class="lite-seccion" id="seccion-<?php echo $seccion['id']; ?>">
    <div class="lite-inner">
        <div class="lite-cabecera">
            <span class="lite-num"><?php echo str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT); ?></span>
            <h2><?php echo htmlspecialchars($seccion['titulo']); ?></h2>
        </div>
        <?php if (!empty($seccion['descripcion'])): ?>
            <p class="lite-desc"><?php echo htmlspecialchars($seccion['descripcion']); ?></p>
        <?php endif; ?>

        <?php $lista = $obrasPorSeccion[$seccion['id']] ?? []; ?>
        <?php if ($lista): ?>
            <div class="lite-grid">
                <?php foreach ($lista as $obra): ?>
                    <a href="?page=lit_detalle&obra_id=<?php echo (int)$obra['id']; ?>" class="lite-card">
                        <div class="lite-card-head">
                            <h3><?php echo htmlspecialchars($obra['titulo']); ?></h3>
                            <?php if (!empty($obra['autor'])): ?>
                                <span class="lite-autor"><?php echo htmlspecialchars($obra['autor']); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="lite-meta">
                            <?php if (!empty($obra['genero'])): ?><span class="lite-label">Género: <strong><?php echo htmlspecialchars($obra['genero']); ?></strong></span><?php endif; ?>
                            <?php if (!empty($obra['anio'])): ?><span class="lite-label">Año: <strong><?php echo htmlspecialchars($obra['anio']); ?></strong></span><?php endif; ?>
                        </div>
                        <?php if (!empty($obra['sinopsis'])): ?>
                            <p class="lite-sinopsis"><?php echo htmlspecialchars($obra['sinopsis']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($obra['fragmento'])): ?>
                            <blockquote class="lite-cita">«<?php echo htmlspecialchars($obra['fragmento']); ?>»</blockquote>
                        <?php endif; ?>
                        <span class="lite-ver">Ver ficha →</span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="lite-vacio">Esta sección aún no tiene obras.</p>
        <?php endif; ?>
    </div>
</section>
<?php endforeach; ?>

<style>
@font-face {
    font-family: 'Nikan Felthgothic';
    src: url('../fonts/Felthgothic Bold.ttf') format('opentype');
}

.lite-section {
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

.lite-section::before {
    content: "";
    position: absolute;
    inset: 0;
    background-image:
        radial-gradient(circle at 20% 30%, rgba(120, 90, 40, 0.05) 0, transparent 40%),
        radial-gradient(circle at 80% 70%, rgba(120, 90, 40, 0.05) 0, transparent 40%),
        radial-gradient(circle at 50% 50%, rgba(120, 90, 40, 0.04) 0, transparent 50%);
    pointer-events: none;
}

.lite-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    gap: 40px;
    position: relative;
}

.lite-texto {
    max-width: 45%;
    padding-bottom: 120px;
}

.lite-tag {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #0a7a4b;
    font-size: 30px;
    letter-spacing: 4px;
    margin-bottom: 24px;
    text-shadow: 0 1px 0 rgba(255, 255, 255, 0.4);
}

.lite-tag .lite-icono {
    width: 44px;
    height: 44px;
    object-fit: contain;
    filter: invert(1) brightness(0);
}

.lite-texto h1 {
    font-family: 'Nikan Felthgothic', 'Felthgothic', serif;
    font-size: 78px;
    color: #C6372E;
    margin: 0 0 24px;
    line-height: 1.05;
    letter-spacing: 3px;
    text-shadow: 0 2px 2px rgba(90, 60, 20, 0.25);
}

.lite-texto p {
    font-family: 'Rustica', serif;
    font-size: 22px;
    color: #4a3b22;
    line-height: 1.7;
    margin-bottom: 30px;
    max-width: 560px;
}

/* Emblema literario decorativo */
.lite-imagen {
    position: relative;
    text-align: center;
    align-self: flex-end;
    margin-bottom: -140px;
    transform: translateY(-60px);
    padding: 24px;
}

.lite-marco-deco {
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

.lite-marco-deco::before,
.lite-marco-deco::after {
    content: "";
    position: absolute;
    width: 40px;
    height: 40px;
    border: 3px solid #8a6a2f;
}
.lite-marco-deco::before {
    top: -3px;
    left: -3px;
    border-right: none;
    border-bottom: none;
}
.lite-marco-deco::after {
    top: -3px;
    right: -3px;
    border-left: none;
    border-bottom: none;
}

.lite-emblema {
    width: 560px;
    height: auto;
    display: block;
    position: relative;
    z-index: 2;
}

.lite-sombras {
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

/* ===== SECCIONES DE LITERATURA ===== */
.lite-seccion {
    background:
        linear-gradient(135deg, #f6efe3 0%, #efe4d0 45%, #e7d8bd 100%);
    padding: 90px 80px;
    font-family: 'Montserrat', sans-serif;
    position: relative;
    z-index: 2;
    border-top: 3px solid #c9a94f;
}

.lite-seccion:nth-child(even) {
    background:
        linear-gradient(135deg, #efe8d8 0%, #e5d6b8 60%, #dcc9a3 100%);
}

.lite-inner {
    max-width: 1200px;
    margin: 0 auto;
}

.lite-cabecera {
    display: flex;
    align-items: center;
    gap: 18px;
    margin-bottom: 14px;
}

.lite-num {
    font-family: 'Nikan Felthgothic', 'Felthgothic', serif;
    font-size: 42px;
    color: #c9a94f;
    text-shadow: 0 1px 0 rgba(255,255,255,0.4);
}

.lite-cabecera h2 {
    font-family: 'Nikan Felthgothic', 'Felthgothic', serif;
    font-size: 40px;
    color: #C6372E;
    letter-spacing: 1px;
    text-shadow: 0 2px 2px rgba(90, 60, 20, 0.2);
}

.lite-desc {
    font-family: 'Rustica', serif;
    font-size: 18px;
    color: #4a3b22;
    max-width: 760px;
    line-height: 1.6;
    margin-bottom: 32px;
}

.lite-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 28px;
}

.lite-card {
    background: rgba(255, 255, 255, 0.55);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(90, 60, 20, 0.15);
    display: flex;
    flex-direction: column;
    backdrop-filter: blur(2px);
    border-top: 3px solid #c9a94f;
    padding: 22px 22px 24px;
    color: #4a3b22;
    text-decoration: none;
    transition: transform 0.18s, box-shadow 0.18s;
    position: relative;
}
.lite-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 30px rgba(90, 60, 20, 0.25);
}
.lite-ver {
    margin-top: 14px;
    color: #0a7a4b;
    font-weight: 700;
    font-size: 13px;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.lite-card-head h3 {
    font-family: 'Nikan Felthgothic', 'Felthgothic', serif;
    font-size: 24px;
    color: #C6372E;
    margin-bottom: 2px;
}

.lite-autor {
    display: block;
    font-size: 14px;
    font-style: italic;
    color: #0a7a4b;
    margin-bottom: 10px;
}

.lite-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 6px 16px;
    font-size: 13px;
    color: #6a5a38;
    padding-top: 10px;
    border-top: 1px dashed rgba(120, 90, 40, 0.3);
    margin-bottom: 10px;
}

.lite-label strong {
    font-weight: 700;
    color: #3d3d20;
}

.lite-sinopsis {
    font-size: 14px;
    line-height: 1.55;
    color: #4a3b22;
    margin-bottom: 12px;
}

.lite-cita {
    margin: 0;
    padding: 12px 14px;
    background: rgba(201, 169, 79, 0.14);
    border-left: 3px solid #c9a94f;
    font-family: 'Nikan Felthgothic', 'Felthgothic', serif;
    font-size: 16px;
    line-height: 1.5;
    color: #5a4720;
    font-style: italic;
}

.lite-vacio {
    color: #8a7a58;
    font-style: italic;
    font-size: 16px;
}
</style>