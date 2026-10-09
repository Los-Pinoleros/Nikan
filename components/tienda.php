<?php
require_once __DIR__ . '/../config/config.php';

$productos = [];
try {
    $pdo = getDB();
    asegurar_tabla_tienda_productos($pdo);
    $productos = $pdo->query('SELECT * FROM tienda_productos ORDER BY orden ASC, creado_en DESC')->fetchAll();
} catch (Exception $e) {
    error_log('[NIKAN] No se pudo cargar la tienda cultural: ' . $e->getMessage());
}

function tienda_whatsapp_url($numero, $producto = '') {
    $numero = preg_replace('/\D+/', '', (string)$numero);
    $mensaje = 'Hola, vengo de NIKAN. Saludos, estoy interesado en ' . trim((string)$producto) . '.';
    return $numero !== '' ? 'https://wa.me/' . $numero . '?text=' . rawurlencode($mensaje) : '#';
}
?>
<main class="tienda-page">
    <section class="tienda-hero">
        <div class="tienda-hero__copy">
            <span class="tienda-kicker">NIKAN PRESENTA</span>
            <h1>TIENDA<br>CULTURAL</h1>
            <p>Descubre productos que llevan la identidad y el talento nicaragüense a cada rincón.</p>
        </div>
        <div class="tienda-hero__mark" aria-hidden="true">N</div>
    </section>
    <section class="tienda-catalogo" aria-labelledby="tiendaTitulo">
        <div class="tienda-heading">
            <span>PRODUCTOS CON IDENTIDAD</span>
            <h2 id="tiendaTitulo">Encuentra algo especial</h2>
        </div>
        <?php if ($productos): ?>
            <div class="tienda-grid">
                <?php foreach ($productos as $producto): ?>
                    <article class="tienda-card">
                        <div class="tienda-card__image">
                            <img src="<?php echo htmlspecialchars($producto['imagen'], ENT_QUOTES, 'UTF-8'); ?>"
                                 alt="<?php echo htmlspecialchars($producto['titulo'], ENT_QUOTES, 'UTF-8'); ?>"
                                 loading="lazy"
                                 onerror="this.onerror=null;this.src='assets/fondo.svg';">
                        </div>
                        <div class="tienda-card__body">
                            <h3><?php echo htmlspecialchars($producto['titulo'], ENT_QUOTES, 'UTF-8'); ?></h3>
                            <p><?php echo nl2br(htmlspecialchars($producto['descripcion'], ENT_QUOTES, 'UTF-8')); ?></p>
                            <a class="tienda-card__contact" href="<?php echo htmlspecialchars(tienda_whatsapp_url($producto['whatsapp'], $producto['titulo']), ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer">
                                CONTACTAR <span aria-hidden="true">↗</span>
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="tienda-empty">Próximamente encontrarás aquí productos culturales de Nicaragua.</div>
        <?php endif; ?>
    </section>
</main>
<style>
.tienda-page { position:relative; z-index:2; color:#43261e; background:#f4ede1; }
.tienda-hero { display:flex; align-items:center; justify-content:space-between; gap:40px; min-height:560px; padding:150px 10vw 90px; overflow:hidden; background:linear-gradient(135deg,#f5eee3,#e5d4b6); }
.tienda-hero__copy { max-width:640px; }
.tienda-kicker,.tienda-heading > span { color:#c6372e; font-size:11px; font-weight:800; letter-spacing:3px; }
.tienda-hero h1 { margin:16px 0 20px; color:#397d27; font:400 clamp(58px,9vw,125px)/.82 Rustica,serif; letter-spacing:2px; }
.tienda-hero p { max-width:540px; font:400 clamp(18px,2vw,27px)/1.4 'Nikan Felthgothic',sans-serif; }
.tienda-hero__mark { display:grid; place-items:center; width:270px; height:270px; flex:0 0 270px; color:#f4ede1; border:2px solid #c6372e; background:#c6372e; font:400 190px/.8 Rustica,serif; transform:rotate(8deg); }
.tienda-catalogo { padding:85px 7vw 110px; background:#fffaf2; }
.tienda-heading { max-width:850px; margin:0 auto 38px; text-align:center; }
.tienda-heading h2 { margin-top:9px; color:#43261e; font:400 clamp(34px,5vw,65px)/1 'Nikan Felthgothic',sans-serif; }
.tienda-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:24px; max-width:1200px; margin:auto; }
.tienda-card { overflow:hidden; background:#f1e5d2; box-shadow:0 12px 28px rgba(67,38,30,.13); }
.tienda-card__image { height:270px; background:#e2d1b5; }
.tienda-card__image img { display:block; width:100%; height:100%; object-fit:cover; }
.tienda-card__body { padding:22px; }
.tienda-card h3 { margin:0 0 10px; color:#397d27; font:400 27px/1.05 Rustica,serif; text-transform:uppercase; }
.tienda-card p { min-height:66px; margin:0; color:#654b3d; font-size:14px; line-height:1.6; }
.tienda-card__contact { display:flex; justify-content:space-between; align-items:center; margin-top:20px; padding:12px 14px; color:#fff; background:#0a7a4b; font-size:11px; font-weight:800; letter-spacing:1.5px; text-decoration:none; }
.tienda-card__contact:hover { background:#c6372e; }
.tienda-empty { max-width:700px; margin:auto; padding:35px; border:1px solid #d8c5a6; color:#765c4b; text-align:center; }
@media (max-width:760px) {
    .tienda-hero { display:block; min-height:auto; padding:125px 24px 55px; text-align:center; }
    .tienda-hero__copy { margin:auto; }
    .tienda-hero h1 { font-size:clamp(57px,17vw,90px); }
    .tienda-hero p { margin:auto; font-size:17px; }
    .tienda-hero__mark { width:150px; height:150px; margin:42px auto 0; font-size:105px; }
    .tienda-catalogo { padding:60px 18px 75px; }
    .tienda-grid { grid-template-columns:1fr; }
}
</style>
