<?php
require_once __DIR__ . '/../config/config.php';

$autorId = (int)($_GET['autor_id'] ?? 0);
$autor = null;
$obras = [];
$obrasPorTipo = [];

try {
    if ($autorId > 0) {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT * FROM autores WHERE id = ?');
        $stmt->execute([$autorId]);
        $autor = $stmt->fetch();

        if ($autor) {
            $areas = [
                ['tabla' => 'arte_obras', 'tipo' => 'Arte', 'pagina' => 'arte_detalle'],
                ['tabla' => 'lit_obras', 'tipo' => 'Literatura', 'pagina' => 'lit_detalle'],
                ['tabla' => 'poe_obras', 'tipo' => 'Poesía', 'pagina' => 'poe_detalle'],
                ['tabla' => 'musica_obras', 'tipo' => 'Música', 'pagina' => 'musica_detalle'],
            ];
            foreach ($areas as $area) {
                $obraStmt = $pdo->prepare("SELECT id, titulo FROM {$area['tabla']} WHERE autor_id = ? ORDER BY titulo ASC");
                $obraStmt->execute([$autorId]);
                $areaObras = $obraStmt->fetchAll();
                $obrasPorTipo[$area['tipo']] = count($areaObras);
                foreach ($areaObras as $obra) {
                    $obras[] = [
                        'titulo' => $obra['titulo'],
                        'tipo' => $area['tipo'],
                        'url' => '?page=' . $area['pagina'] . '&obra_id=' . (int)$obra['id'],
                    ];
                }
            }
        }
    }
} catch (Exception $e) {
    error_log('[NIKAN] Error en ficha de autor: ' . $e->getMessage());
}
?>

<main class="autor-ficha">
    <?php if (!$autor): ?>
        <section class="autor-ficha__empty">
            <h1>Autor no encontrado</h1>
            <a href="?page=autores">Volver a autores</a>
        </section>
    <?php else: ?>
        <section class="autor-ficha__hero">
            <a class="autor-ficha__back" href="?page=autores">← Todos los autores</a>
            <div class="autor-ficha__portrait">
                <?php if (!empty($autor['retrato'])): ?>
                    <img src="<?php echo htmlspecialchars($autor['retrato']); ?>" alt="Retrato de <?php echo htmlspecialchars($autor['nombre']); ?>">
                <?php else: ?>
                    <span><?php echo htmlspecialchars(mb_substr($autor['nombre'], 0, 1)); ?></span>
                <?php endif; ?>
            </div>
            <div class="autor-ficha__content">
                <span class="autor-ficha__tag">Ficha de autor</span>
                <h1><?php echo htmlspecialchars($autor['nombre']); ?></h1>
                <?php if (!empty($autor['lugar']) || !empty($autor['nacimiento']) || !empty($autor['fallecimiento']) || !empty($autor['epoca'])): ?>
                    <p class="autor-ficha__meta">
                        <?php echo !empty($autor['lugar']) ? htmlspecialchars($autor['lugar']) : ''; ?>
                        <?php if (!empty($autor['nacimiento']) || !empty($autor['fallecimiento'])): ?>
                            · <?php echo !empty($autor['nacimiento']) ? htmlspecialchars($autor['nacimiento']) : '¿?'; ?><?php if (!empty($autor['fallecimiento'])): ?>–<?php echo htmlspecialchars($autor['fallecimiento']); ?><?php endif; ?>
                        <?php endif; ?>
                        <?php if (!empty($autor['epoca'])): ?> · <?php echo htmlspecialchars($autor['epoca']); ?><?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
        </section>
        <section class="autor-ficha__information">
            <div class="autor-ficha__texts">
                <?php if (!empty($autor['bio'])): ?>
                    <article class="autor-ficha__text">
                        <h2>Biografía</h2>
                        <p><?php echo nl2br(htmlspecialchars($autor['bio'], ENT_QUOTES, 'UTF-8')); ?></p>
                    </article>
                <?php endif; ?>
                <?php if (!empty($autor['trayectoria'])): ?>
                    <article class="autor-ficha__text">
                        <h2>Trayectoria</h2>
                        <p><?php echo nl2br(htmlspecialchars($autor['trayectoria'], ENT_QUOTES, 'UTF-8')); ?></p>
                    </article>
                <?php endif; ?>
                <?php if (!empty($autor['estilo_aportes'])): ?>
                    <article class="autor-ficha__text">
                        <h2>Estilo y aportes</h2>
                        <p><?php echo nl2br(htmlspecialchars($autor['estilo_aportes'], ENT_QUOTES, 'UTF-8')); ?></p>
                    </article>
                <?php endif; ?>
            </div>
            <?php if (!empty($autor['obra_representativa'])): ?>
                <figure class="autor-ficha__representative">
                    <img src="<?php echo htmlspecialchars($autor['obra_representativa'], ENT_QUOTES, 'UTF-8'); ?>" alt="Obra representativa de <?php echo htmlspecialchars($autor['nombre'], ENT_QUOTES, 'UTF-8'); ?>">
                    <figcaption>Obra representativa</figcaption>
                </figure>
            <?php endif; ?>
        </section>
        <section class="autor-ficha__works">
            <div class="autor-ficha__works-heading">
                <div>
                    <span class="autor-ficha__tag">Producción registrada</span>
                    <h2>Obras vinculadas</h2>
                </div>
                <?php if ($obrasPorTipo): ?>
                    <div class="autor-ficha__counts">
                        <?php foreach ($obrasPorTipo as $tipo => $cantidad): ?>
                            <?php if ($cantidad > 0): ?><span><?php echo htmlspecialchars($tipo); ?> <strong><?php echo $cantidad; ?></strong></span><?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <?php if ($obras): ?>
                <div class="autor-ficha__grid">
                    <?php foreach ($obras as $obra): ?>
                        <a href="<?php echo htmlspecialchars($obra['url']); ?>" class="autor-ficha__work">
                            <span><?php echo htmlspecialchars($obra['tipo']); ?></span>
                            <strong><?php echo htmlspecialchars($obra['titulo']); ?></strong>
                            <small>Ver ficha →</small>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>Este autor aún no tiene obras vinculadas.</p>
            <?php endif; ?>
        </section>
    <?php endif; ?>
</main>

<style>
.autor-ficha { position: relative; z-index: 2; min-height: 100vh; padding: 150px 8vw 80px; background: linear-gradient(135deg, #f7efe2, #e8d7bd); color: #38251f; }
.autor-ficha__hero { max-width: 1050px; margin: 0 auto; display: grid; grid-template-columns: 260px 1fr; gap: 55px; align-items: center; }
.autor-ficha__back { grid-column: 1 / -1; color: #c6372e; font-weight: 800; text-decoration: none; }
.autor-ficha__portrait { width: 260px; height: 320px; border-radius: 130px 130px 18px 18px; overflow: hidden; background: #c6372e; display: grid; place-items: center; color: #fff; font: 800 100px 'Alegreya', serif; box-shadow: 0 18px 35px rgba(56, 37, 31, .25); }
.autor-ficha__portrait img { width: 100%; height: 100%; object-fit: cover; }
.autor-ficha__tag { color: #c6372e; text-transform: uppercase; letter-spacing: 2px; font-size: 12px; font-weight: 800; }
.autor-ficha h1 { margin: 10px 0; font: 800 clamp(38px, 6vw, 76px) 'Alegreya', serif; }
.autor-ficha__meta { color: #76675e; font-weight: 600; }
.autor-ficha__information { max-width: 1050px; margin: 55px auto 0; display: grid; grid-template-columns: minmax(0, 1fr) 280px; gap: 35px; align-items: start; }
.autor-ficha__texts { display: grid; gap: 24px; }
.autor-ficha__text { padding: 25px 28px; border-left: 4px solid #c6372e; background: rgba(255,255,255,.62); border-radius: 0 12px 12px 0; }
.autor-ficha__text h2 { margin-bottom: 10px; font: 800 28px 'Alegreya', serif; }
.autor-ficha__text p { line-height: 1.8; white-space: normal; }
.autor-ficha__representative { margin: 0; background: rgba(255,255,255,.72); border-radius: 12px; overflow: hidden; box-shadow: 0 12px 24px rgba(56,37,31,.12); }
.autor-ficha__representative img { display: block; width: 100%; max-height: 340px; object-fit: cover; }
.autor-ficha__representative figcaption { padding: 13px 16px; color: #76675e; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }
.autor-ficha__works { max-width: 1050px; margin: 75px auto 0; }
.autor-ficha__works-heading { display: flex; justify-content: space-between; align-items: end; gap: 20px; margin-bottom: 20px; }
.autor-ficha__works h2 { margin-top: 6px; font: 800 32px 'Alegreya', serif; }
.autor-ficha__counts { display: flex; flex-wrap: wrap; justify-content: end; gap: 8px; }
.autor-ficha__counts span { padding: 8px 11px; border-radius: 20px; background: rgba(255,255,255,.7); color: #76675e; font-size: 11px; font-weight: 700; }
.autor-ficha__counts strong { color: #c6372e; margin-left: 4px; }
.autor-ficha__grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; }
.autor-ficha__work { display: flex; flex-direction: column; gap: 7px; padding: 20px; border-radius: 12px; background: rgba(255,255,255,.72); color: #38251f; text-decoration: none; transition: transform .25s, box-shadow .25s; }
.autor-ficha__work:hover { transform: translateY(-5px); box-shadow: 0 12px 24px rgba(56,37,31,.16); }
.autor-ficha__work span { color: #c6372e; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }
.autor-ficha__work small { color: #76675e; margin-top: 8px; }
.autor-ficha__empty { max-width: 900px; margin: auto; text-align: center; }
.autor-ficha__empty a { color: #c6372e; }
@media (max-width: 700px) {
    .autor-ficha { padding: 125px 24px 60px; }
    .autor-ficha__hero { display: block; }
    .autor-ficha__portrait { margin: 30px auto; width: 210px; height: 250px; }
    .autor-ficha__information { display: block; }
    .autor-ficha__representative { margin-top: 25px; }
    .autor-ficha__works-heading { display: block; }
    .autor-ficha__counts { justify-content: start; margin-top: 16px; }
}
</style>
