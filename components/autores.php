<?php
/**
 * Vista de Autores
 * Lista de autores con ficha detallada y obras vinculadas (Arte/Literatura/Poesía).
 */
require_once __DIR__ . '/../config/config.php';

function aut_area_label($area) {
    return ['arte' => 'Arte', 'lit' => 'Literatura', 'poe' => 'Poesía'][$area] ?? $area;
}

try {
    $pdo = getDB();
    $autores = $pdo->query('SELECT * FROM autores ORDER BY nacimiento ASC, nombre ASC')->fetchAll();

    $autoresData = [];
    $obrasPorAutor = [];
    foreach ($autores as $a) {
        $autoresData[$a['id']] = $a;
        $obrasPorAutor[$a['id']] = [];
    }

    if ($autores) {
        // Obras vinculadas por área
        $areas = [
            'arte' => 'arte_obras',
            'lit'  => 'lit_obras',
            'poe'  => 'poe_obras',
        ];
        foreach ($areas as $area => $tabla) {
            foreach ($pdo->query("SELECT autor_id, titulo FROM $tabla WHERE autor_id IS NOT NULL") as $o) {
                $obrasPorAutor[$o['autor_id']][] = ['area' => $area, 'titulo' => $o['titulo']];
            }
        }
    }
} catch (Exception $e) {
    $autores = [];
    $autoresData = [];
    $obrasPorAutor = [];
}
?>

<section class="aut-section">
    <div class="aut-container">
        <div class="aut-texto">
            <div class="aut-tag">
                <img src="assets/nosotros.svg" alt="Autores" class="aut-icono">
                <span>AUTORES</span>
            </div>
            <h1>LAS VOCES<br>DETRÁS DE LA OBRA</h1>
            <p>
                Conoce a los creadores: su vida, su trayectoria, su estilo y las obras
                que los unen al arte, la literatura y la poesía de Nicaragua.
            </p>
        </div>
        <div class="aut-imagen">
            <div class="aut-marco-deco"></div>
            <img src="assets/nosotros.svg" alt="Autores" class="aut-emblema">
            <span class="aut-sombras"></span>
        </div>
    </div>
</section>

<section class="aut-seccion">
    <div class="aut-inner">
        <?php if ($autores): ?>
            <div class="aut-grid">
                <?php foreach ($autores as $a): ?>
                    <button type="button" class="aut-card" onclick="abrirAutor(<?php echo (int)$a['id']; ?>)">
                        <div class="aut-retrato">
                            <?php if ($a['retrato']): ?>
                                <img src="<?php echo htmlspecialchars($a['retrato']); ?>" alt="Retrato de <?php echo htmlspecialchars($a['nombre']); ?>">
                            <?php else: ?>
                                <span class="aut-retrato-ph"><?php echo mb_substr($a['nombre'], 0, 1); ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="aut-card-info">
                            <strong class="aut-card-nombre"><?php echo htmlspecialchars($a['nombre']); ?></strong>
                            <span class="aut-card-sub">
                                <?php if (!empty($a['lugar'])): ?><?php echo htmlspecialchars($a['lugar']); ?><?php endif; ?>
                                <?php if (!empty($a['nacimiento'])): ?> · <?php echo htmlspecialchars($a['nacimiento']); ?><?php if (!empty($a['fallecimiento'])): ?>–<?php echo htmlspecialchars($a['fallecimiento']); ?><?php endif; ?><?php endif; ?>
                            </span>
                            <span class="aut-card-count"><?php echo count($obrasPorAutor[$a['id']]); ?> obra(s) vinculada(s) · Ver ficha →</span>
                        </div>
                    </button>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="aut-vacio">Aún no hay autores registrados.</p>
        <?php endif; ?>
    </div>
</section>

<!-- Ficha modal del autor -->
<?php foreach ($autores as $a): $id = (int)$a['id']; ?>
<div id="autModal<?php echo $id; ?>" class="aut-modal" style="display:none;">
    <div class="aut-modal-box">
        <button class="aut-modal-close" onclick="cerrarAutor(<?php echo $id; ?>)">×</button>
        <div class="aut-ficha-head">
            <div class="aut-ficha-retrato">
                <?php if ($a['retrato']): ?>
                    <img src="<?php echo htmlspecialchars($a['retrato']); ?>" alt="Retrato de <?php echo htmlspecialchars($a['nombre']); ?>">
                <?php else: ?>
                    <span class="aut-retrato-ph"><?php echo mb_substr($a['nombre'], 0, 1); ?></span>
                <?php endif; ?>
            </div>
            <div class="aut-ficha-head-info">
                <h2><?php echo htmlspecialchars($a['nombre']); ?></h2>
                <?php if (!empty($a['lugar']) || !empty($a['nacimiento']) || !empty($a['fallecimiento'])): ?>
                    <p class="aut-ficha-datos">
                        <?php echo htmlspecialchars($a['lugar']); ?>
                        <?php if (!empty($a['nacimiento'])): echo ' · ' . htmlspecialchars($a['nacimiento']); if (!empty($a['fallecimiento'])) echo '–' . htmlspecialchars($a['fallecimiento']); endif; ?>
                    </p>
                <?php endif; ?>
                <?php if (!empty($a['epoca'])): ?><span class="aut-ficha-epoca"><?php echo htmlspecialchars($a['epoca']); ?></span><?php endif; ?>
            </div>
        </div>

        <?php if (!empty($a['bio'])): ?>
            <div class="aut-ficha-bloque">
                <h3>Biografía breve</h3>
                <p><?php echo nl2br(htmlspecialchars($a['bio'])); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($a['trayectoria'])): ?>
            <div class="aut-ficha-bloque">
                <h3>Trayectoria</h3>
                <p><?php echo nl2br(htmlspecialchars($a['trayectoria'])); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($a['estilo_aportes'])): ?>
            <div class="aut-ficha-bloque">
                <h3>Estilo y aportes</h3>
                <p><?php echo nl2br(htmlspecialchars($a['estilo_aportes'])); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($a['nacimiento']) || !empty($a['fallecimiento'])): ?>
            <div class="aut-ficha-bloque">
                <h3>Línea de tiempo</h3>
                <div class="aut-timeline">
                    <?php if (!empty($a['nacimiento'])): ?>
                        <div class="aut-tl-item">
                            <span class="aut-tl-año"><?php echo htmlspecialchars($a['nacimiento']); ?></span>
                            <span class="aut-tl-texto">Nacimiento<?php if (!empty($a['lugar'])): ?> — <?php echo htmlspecialchars($a['lugar']); ?><?php endif; ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($a['fallecimiento'])): ?>
                        <div class="aut-tl-item">
                            <span class="aut-tl-año"><?php echo htmlspecialchars($a['fallecimiento']); ?></span>
                            <span class="aut-tl-texto">Fallecimiento</span>
                        </div>
                    <?php else: ?>
                        <div class="aut-tl-item">
                            <span class="aut-tl-año">Act.</span>
                            <span class="aut-tl-texto">Autor con obra vigente</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="aut-ficha-bloque">
            <h3>Obras principales</h3>
            <?php $obras = $obrasPorAutor[$id] ?? []; ?>
            <?php if ($obras): ?>
                <ul class="aut-obras">
                    <?php foreach ($obras as $o): ?>
                        <li><span class="aut-area aut-area--<?php echo $o['area']; ?>"><?php echo aut_area_label($o['area']); ?></span> <?php echo htmlspecialchars($o['titulo']); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p class="aut-vacio">Sin obras vinculadas por el momento.</p>
            <?php endif; ?>
        </div>

        <?php if (!empty($a['obra_representativa'])): ?>
            <div class="aut-ficha-bloque">
                <h3>Obra representativa</h3>
                <img src="<?php echo htmlspecialchars($a['obra_representativa']); ?>" alt="Obra representativa de <?php echo htmlspecialchars($a['nombre']); ?>" class="aut-obra-img">
            </div>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>

<style>
@font-face {
    font-family: 'Nikan Felthgothic';
    src: url('../fonts/Nikan-Felthgothic.otf') format('opentype');
}

.aut-section {
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

.aut-section::before {
    content: "";
    position: absolute;
    inset: 0;
    background-image:
        radial-gradient(circle at 20% 30%, rgba(120, 90, 40, 0.05) 0, transparent 40%),
        radial-gradient(circle at 80% 70%, rgba(120, 90, 40, 0.05) 0, transparent 40%),
        radial-gradient(circle at 50% 50%, rgba(120, 90, 40, 0.04) 0, transparent 50%);
    pointer-events: none;
}

.aut-container {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    gap: 40px;
    position: relative;
}

.aut-texto {
    max-width: 45%;
    padding-bottom: 120px;
}

.aut-tag {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #0a7a4b;
    font-size: 30px;
    letter-spacing: 4px;
    margin-bottom: 24px;
    text-shadow: 0 1px 0 rgba(255, 255, 255, 0.4);
}

.aut-tag .aut-icono {
    width: 44px;
    height: 44px;
    object-fit: contain;
    filter: invert(1) brightness(0);
}

.aut-texto h1 {
    font-family: 'Rustica', serif;
    font-size: 72px;
    color: #C6372E;
    margin: 0 0 24px;
    line-height: 1.05;
    letter-spacing: 3px;
    text-shadow: 0 2px 2px rgba(90, 60, 20, 0.25);
}

.aut-texto p {
    font-size: 22px;
    color: #4a3b22;
    line-height: 1.7;
    margin-bottom: 30px;
    max-width: 560px;
}

.aut-imagen {
    position: relative;
    text-align: center;
    align-self: flex-end;
    margin-bottom: -80px;
    transform: translateY(-60px);
    padding: 24px;
}

.aut-marco-deco {
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

.aut-marco-deco::before,
.aut-marco-deco::after {
    content: "";
    position: absolute;
    width: 40px;
    height: 40px;
    border: 3px solid #8a6a2f;
}
.aut-marco-deco::before {
    top: -3px;
    left: -3px;
    border-right: none;
    border-bottom: none;
}
.aut-marco-deco::after {
    top: -3px;
    right: -3px;
    border-left: none;
    border-bottom: none;
}

.aut-emblema {
    width: 340px;
    height: auto;
    display: block;
    position: relative;
    z-index: 2;
}

.aut-sombras {
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

/* ===== LISTA DE AUTORES ===== */
.aut-seccion {
    background:
        linear-gradient(135deg, #f6efe3 0%, #efe4d0 45%, #e7d8bd 100%);
    padding: 90px 80px;
    font-family: 'Montserrat', sans-serif;
    position: relative;
    z-index: 2;
    border-top: 3px solid #c9a94f;
}

.aut-inner {
    max-width: 1200px;
    margin: 0 auto;
}

.aut-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 22px;
}

.aut-card {
    background: rgba(255, 255, 255, 0.6);
    border: 2px solid rgba(201, 169, 79, 0.4);
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 8px 20px rgba(90, 60, 20, 0.15);
    backdrop-filter: blur(2px);
    text-align: left;
    cursor: pointer;
    font-family: 'Montserrat', sans-serif;
    color: #4a3b22;
    display: flex;
    gap: 16px;
    align-items: center;
    transition: transform 0.15s, box-shadow 0.15s;
}
.aut-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 28px rgba(90, 60, 20, 0.22);
}

.aut-retrato {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    overflow: hidden;
    background: #f0e5c8;
    flex-shrink: 0;
    border: 2px solid #c9a94f;
    display: flex;
    align-items: center;
    justify-content: center;
}
.aut-retrato img { width: 100%; height: 100%; object-fit: cover; }
.aut-retrato-ph {
    font-family: 'Nikan Felthgothic', 'Felthgothic', serif;
    font-size: 32px;
    color: #C6372E;
}

.aut-card-info { flex: 1; min-width: 0; }
.aut-card-nombre {
    display: block;
    font-family: 'Rustica', serif;
    font-size: 22px;
    color: #C6372E;
}
.aut-card-sub { display: block; font-size: 13px; color: #6a5a38; margin-top: 2px; }
.aut-card-count { display: block; font-size: 12px; color: #0a7a4b; margin-top: 6px; font-weight: 600; }

.aut-vacio { color: #8a7a58; font-style: italic; font-size: 16px; }

/* ===== FICHA MODAL ===== */
.aut-modal {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.65);
    z-index: 300;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.aut-modal-box {
    background:
        linear-gradient(160deg, #f9f3e4 0%, #efe4d0 100%);
    border-radius: 16px;
    width: 100%;
    max-width: 700px;
    max-height: 90vh;
    overflow-y: auto;
    padding: 34px;
    position: relative;
    color: #4a3b22;
    border: 2px solid #c9a94f;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
    font-family: 'Montserrat', sans-serif;
}
.aut-modal-close {
    position: absolute;
    top: 14px;
    right: 18px;
    background: none;
    border: none;
    font-size: 34px;
    color: #C6372E;
    cursor: pointer;
    line-height: 1;
}
.aut-ficha-head {
    display: flex;
    gap: 20px;
    align-items: center;
    margin-bottom: 22px;
}
.aut-ficha-retrato {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    overflow: hidden;
    background: #f0e5c8;
    flex-shrink: 0;
    border: 3px solid #c9a94f;
    display: flex;
    align-items: center;
    justify-content: center;
}
.aut-ficha-retrato img { width: 100%; height: 100%; object-fit: cover; }
.aut-ficha-head h2 {
    font-family: 'Rustica', serif;
    font-size: 34px;
    color: #C6372E;
    margin: 0 0 4px;
}
.aut-ficha-datos { font-size: 14px; color: #6a5a38; }
.aut-ficha-epoca {
    display: inline-block;
    margin-top: 6px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #fff;
    background: #0a7a4b;
    padding: 3px 10px;
    border-radius: 20px;
}
.aut-ficha-bloque {
    margin-bottom: 20px;
    padding-top: 16px;
    border-top: 1px dashed rgba(120, 90, 40, 0.3);
}
.aut-ficha-bloque h3 {
    font-family: 'Rustica', serif;
    font-size: 20px;
    color: #C6372E;
    margin-bottom: 8px;
}
.aut-ficha-bloque p { font-size: 14px; line-height: 1.6; }

.aut-timeline {
    display: flex;
    flex-direction: column;
    gap: 10px;
    border-left: 3px solid #c9a94f;
    padding-left: 18px;
}
.aut-tl-item { display: flex; gap: 12px; align-items: baseline; }
.aut-tl-año { font-weight: 800; color: #C6372E; min-width: 46px; }
.aut-tl-texto { font-size: 14px; }

.aut-obras { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
.aut-obras li {
    font-size: 14px;
    padding: 8px 12px;
    background: rgba(201, 169, 79, 0.14);
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.aut-area {
    font-size: 10px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #fff;
    padding: 2px 8px;
    border-radius: 12px;
}
.aut-area--arte { background: #C6372E; }
.aut-area--lit { background: #2a6fdb; }
.aut-area--poe { background: #0a7a4b; }

.aut-obra-img {
    width: 100%;
    max-height: 260px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid #c9a94f;
    margin-top: 8px;
}
</style>

<script>
function abrirAutor(id) { document.getElementById('autModal' + id).style.display = 'flex'; }
function cerrarAutor(id) { document.getElementById('autModal' + id).style.display = 'none'; }
document.addEventListener('click', function (e) {
    document.querySelectorAll('.aut-modal').forEach(m => {
        if (m.style.display === 'flex' && !e.target.closest('.aut-modal-box')) {
            m.style.display = 'none';
        }
    });
});
</script>