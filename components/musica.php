<?php
/**
 * Vista de Música: "El Sonido de Nuestra Tierra"
 * Muestra secciones y piezas musicales administrables desde el panel de administración.
 */
require_once __DIR__ . '/../config/config.php';

try {
    $pdo = getDB();
    $secciones = $pdo->query('SELECT * FROM musica_secciones ORDER BY orden ASC')->fetchAll();

    if ($secciones) {
        $in = implode(',', array_map('intval', array_column($secciones, 'id')));
        $obras = $pdo->query("SELECT * FROM musica_obras WHERE seccion_id IN ($in) ORDER BY orden ASC")->fetchAll();
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

<section class="mus-section">
    <div class="mus-container">
        <div class="mus-texto">
            <div class="mus-tag">
                <img src="assets/musica.svg" alt="Música" class="icono">
                <span>MÚSICA</span>
            </div>
            <h1>EL SONIDO DE<br>NUESTRA TIERRA</h1>
            <p>
                Los ritmos, instrumentos y cantos que han tejido la identidad musical
                de Nicaragua: de la marimba al son nica, de los bailes tradicionales
                a nuestros compositores.
            </p>
        </div>

        <div class="mus-imagen">
            <span class="mus-marco-dorado"></span>
            <img src="assets/musica.svg" alt="Música tradicional" class="mus-emblema">
            <span class="mus-sombra"></span>
        </div>
    </div>
</section>

<?php foreach ($secciones as $idx => $seccion): ?>
<section class="musc-seccion" id="seccion-<?php echo $seccion['id']; ?>">
    <div class="musc-container">
        <div class="musc-cabecera">
            <span class="musc-num"><?php echo str_pad((string)($idx + 1), 2, '0', STR_PAD_LEFT); ?></span>
            <h2><?php echo htmlspecialchars($seccion['titulo']); ?></h2>
        </div>
        <?php if (!empty($seccion['descripcion'])): ?>
            <p class="musc-desc"><?php echo htmlspecialchars($seccion['descripcion']); ?></p>
        <?php endif; ?>

        <?php $lista = $obrasPorSeccion[$seccion['id']] ?? []; ?>
        <?php if ($lista): ?>
            <div class="musc-grid">
                <?php foreach ($lista as $obra): ?>
                    <a href="?page=musica_detalle&obra_id=<?php echo (int)$obra['id']; ?>" class="musc-card">
                        <div class="musc-img">
                            <?php if ($obra['imagen']): ?>
                                <img src="<?php echo htmlspecialchars($obra['imagen']); ?>" alt="<?php echo htmlspecialchars($obra['titulo']); ?>">
                            <?php else: ?>
                                <span class="musc-img-ph">Sin imagen</span>
                            <?php endif; ?>
                            <span class="musc-marco"></span>
                            <span class="musc-ver">Escuchar y ver →</span>
                        </div>
                        <div class="musc-card-body">
                            <h3><?php echo htmlspecialchars($obra['titulo']); ?></h3>
                            <div class="musc-meta">
                                <?php if (!empty($obra['autor'])): ?><span class="musc-label">Autor/a: <strong><?php echo htmlspecialchars($obra['autor']); ?></strong></span><?php endif; ?>
                                <?php if (!empty($obra['genero'])): ?><span class="musc-label">Género: <strong><?php echo htmlspecialchars($obra['genero']); ?></strong></span><?php endif; ?>
                            </div>
                            <?php if (!empty($obra['descripcion'])): ?>
                                <p class="musc-obra-desc"><?php echo htmlspecialchars($obra['descripcion']); ?></p>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="musc-vacio">Esta sección aún no tiene piezas.</p>
        <?php endif; ?>
    </div>
</section>
<?php endforeach; ?>

<style>
@font-face {
    font-family: 'Nikan Felthgothic';
    src: url('../fonts/Nikan-Felthgothic.otf') format('opentype');
}

.mus-section {
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

.mus-section::before {
    content: "";
    position: absolute;
    inset: 0;
    background-image:
        radial-gradient(circle at 20% 30%, rgba(120, 90, 40, 0.05) 0, transparent 40%),
        radial-gradient(circle at 80% 70%, rgba(120, 90, 40, 0.05) 0, transparent 40%),
        radial-gradient(circle at 50% 50%, rgba(120, 90, 40, 0.04) 0, transparent 50%);
    pointer-events: none;
}

.mus-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    gap: 40px;
    position: relative;
}

.mus-texto {
    max-width: 45%;
    padding-bottom: 120px;
}

.mus-tag {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #0a7a4b;
    font-size: 30px;
    letter-spacing: 4px;
    margin-bottom: 24px;
    text-shadow: 0 1px 0 rgba(255, 255, 255, 0.4);
}

.mus-tag .icono {
    width: 44px;
    height: 44px;
    object-fit: contain;
    filter: invert(1) brightness(0);
}

.mus-texto h1 {
    font-family: 'Rustica', serif;
    font-size: 82px;
    color: #C6372E;
    margin: 0 0 24px;
    line-height: 1.05;
    letter-spacing: 3px;
    text-shadow: 0 2px 2px rgba(90, 60, 20, 0.25);
}

.mus-texto p {
    font-size: 22px;
    color: #4a3b22;
    line-height: 1.7;
    margin-bottom: 30px;
    max-width: 560px;
}

.mus-imagen {
    position: relative;
    text-align: center;
    align-self: flex-end;
    transform: translateY(20px);
    padding: 24px;
}

.mus-marco-dorado {
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

.mus-emblema {
    position: relative;
    z-index: 2;
    width: 460px;
    max-width: 40vw;
    filter: drop-shadow(0 10px 16px rgba(90, 60, 20, 0.25));
}

.mus-sombra {
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

/* ===== SECCIONES DE MÚSICA ===== */
.musc-seccion {
    background:
        linear-gradient(135deg, #f6efe3 0%, #efe4d0 45%, #e7d8bd 100%);
    padding: 90px 80px;
    font-family: 'Montserrat', sans-serif;
    position: relative;
    z-index: 2;
    border-top: 3px solid #c9a94f;
}

.musc-seccion:nth-child(even) {
    background:
        linear-gradient(135deg, #efe8d8 0%, #e5d6b8 60%, #dcc9a3 100%);
}

.musc-container {
    max-width: 1200px;
    margin: 0 auto;
}

.musc-cabecera {
    display: flex;
    align-items: center;
    gap: 18px;
    margin-bottom: 14px;
}

.musc-num {
    font-family: 'Nikan Felthgothic', 'Felthgothic', serif;
    font-size: 42px;
    color: #c9a94f;
    text-shadow: 0 1px 0 rgba(255,255,255,0.4);
}

.musc-cabecera h2 {
    font-family: 'Rustica', serif;
    font-size: 40px;
    color: #0a7a4b;
    letter-spacing: 1px;
    text-shadow: 0 2px 2px rgba(90, 60, 20, 0.2);
}

.musc-desc {
    font-size: 18px;
    color: #4a3b22;
    max-width: 760px;
    line-height: 1.6;
    margin-bottom: 32px;
}

.musc-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 28px;
}

.musc-card {
    background: rgba(255, 255, 255, 0.55);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(90, 60, 20, 0.15);
    display: flex;
    flex-direction: column;
    backdrop-filter: blur(2px);
    text-decoration: none;
    color: inherit;
    transition: transform 0.18s, box-shadow 0.18s;
}
.musc-card:hover { transform: translateY(-4px); box-shadow: 0 14px 30px rgba(90, 60, 20, 0.25); }

.musc-img {
    position: relative;
    aspect-ratio: 4 / 3;
    background: #fff8e8;
    overflow: hidden;
}

.musc-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    position: relative;
    z-index: 1;
}

.musc-img-ph {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #b9a06a;
    font-size: 14px;
    font-style: italic;
    z-index: 1;
}

.musc-marco {
    position: absolute;
    inset: 0;
    border: 2px solid rgba(201, 169, 79, 0.6);
    box-shadow: 0 0 0 4px rgba(201, 169, 79, 0.15) inset;
    pointer-events: none;
    z-index: 2;
}

.musc-ver {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 3;
    background: linear-gradient(to top, rgba(12, 12, 12, 0.75), transparent);
    color: #fff;
    font-size: 13px;
    font-weight: 700;
    letter-spacing: 0.5px;
    padding: 30px 18px 12px;
    text-align: center;
    opacity: 0;
    transform: translateY(8px);
    transition: opacity 0.2s, transform 0.2s;
}
.musc-card:hover .musc-ver { opacity: 1; transform: translateY(0); }

.musc-card-body {
    padding: 18px 20px 22px;
    color: #4a3b22;
}

.musc-card-body h3 {
    font-family: 'Rustica', serif;
    font-size: 24px;
    color: #0a7a4b;
    margin-bottom: 8px;
}

.musc-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 6px 16px;
    font-size: 13px;
    color: #6a5a38;
    margin-bottom: 8px;
}

.musc-label strong {
    font-weight: 700;
    color: #3d3d20;
}

.musc-obra-desc {
    font-size: 14px;
    line-height: 1.5;
    color: #4a3b22;
    text-align: justify;
}

.musc-vacio {
    color: #8a7a58;
    font-style: italic;
    font-size: 16px;
}
</style>