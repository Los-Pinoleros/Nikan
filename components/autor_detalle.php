<?php
require_once __DIR__ . '/../config/config.php';

$autorId = (int)($_GET['autor_id'] ?? 0);
$autor = null;
$obras = [];

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
                foreach ($obraStmt->fetchAll() as $obra) {
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
                <?php if (!empty($autor['lugar']) || !empty($autor['nacimiento'])): ?>
                    <p class="autor-ficha__meta">
                        <?php echo !empty($autor['lugar']) ? htmlspecialchars($autor['lugar']) : ''; ?>
                        <?php if (!empty($autor['nacimiento'])): ?> · <?php echo htmlspecialchars($autor['nacimiento']); ?><?php endif; ?>
                        <?php if (!empty($autor['fallecimiento'])): ?>–<?php echo htmlspecialchars($autor['fallecimiento']); ?><?php endif; ?>
                    </p>
                <?php endif; ?>
                <?php if (!empty($autor['bio'])): ?>
                    <p class="autor-ficha__bio"><?php echo nl2br(htmlspecialchars($autor['bio'])); ?></p>
                <?php endif; ?>
            </div>
        </section>
        <section class="autor-ficha__works">
            <h2>Obras vinculadas</h2>
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
.autor-ficha__bio { margin-top: 25px; max-width: 650px; line-height: 1.8; }
.autor-ficha__works { max-width: 1050px; margin: 75px auto 0; }
.autor-ficha__works h2 { margin-bottom: 20px; font: 800 32px 'Alegreya', serif; }
.autor-ficha__grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; }
.autor-ficha__work { display: flex; flex-direction: column; gap: 7px; padding: 20px; border-radius: 12px; background: rgba(255,255,255,.72); color: #38251f; text-decoration: none; transition: transform .25s, box-shadow .25s; }
.autor-ficha__work:hover { transform: translateY(-5px); box-shadow: 0 12px 24px rgba(56,37,31,.16); }
.autor-ficha__work span { color: #c6372e; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }
.autor-ficha__work small { color: #76675e; margin-top: 8px; }
.autor-ficha__empty { max-width: 900px; margin: auto; text-align: center; }
.autor-ficha__empty a { color: #c6372e; }
@media (max-width: 700px) { .autor-ficha { padding: 125px 24px 60px; } .autor-ficha__hero { display: block; } .autor-ficha__portrait { margin: 30px auto; width: 210px; height: 250px; } }
</style>
