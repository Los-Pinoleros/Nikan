<?php
require_once __DIR__ . '/auth.php';
require_admin();

$pdo = getDB();

$usuarios  = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$admins    = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
$miembros  = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$autores   = (int) $pdo->query('SELECT COUNT(*) FROM autores')->fetchColumn();
$obrasTotal = (int) $pdo->query('SELECT (SELECT COUNT(*) FROM arte_obras) + (SELECT COUNT(*) FROM lit_obras) + (SELECT COUNT(*) FROM poe_obras)')->fetchColumn();

$porArea = [];
foreach (['Arte' => 'arte', 'Literatura' => 'lit', 'Poesía' => 'poe'] as $nombre => $prefijo) {
    $obras  = (int) $pdo->query("SELECT COUNT(*) FROM {$prefijo}_obras")->fetchColumn();
    $secciones = (int) $pdo->query("SELECT COUNT(*) FROM {$prefijo}_secciones")->fetchColumn();
    $porArea[$nombre] = ['obras' => $obras, 'secciones' => $secciones];
}
$maxObrasArea = max(array_column($porArea, 'obras'));

$topAutores = $pdo->query("
    SELECT a.nombre,
        (SELECT COUNT(*) FROM arte_obras  x WHERE x.autor_id = a.id OR x.autor = a.nombre) +
        (SELECT COUNT(*) FROM lit_obras   y WHERE y.autor_id = a.id OR y.autor = a.nombre) +
        (SELECT COUNT(*) FROM poe_obras   z WHERE z.autor_id = a.id OR z.autor = a.nombre) AS total
    FROM autores a
    ORDER BY total DESC
")->fetchAll();
$maxObrasAutor = (int) (max(array_column($topAutores, 'total')) ?: 1);

$seccionesResumen = [];
foreach (['Arte' => 'arte', 'Literatura' => 'lit', 'Poesía' => 'poe'] as $nombre => $prefijo) {
    $rows = $pdo->query("
        SELECT s.titulo, COUNT(o.id) AS c
        FROM {$prefijo}_secciones s
        LEFT JOIN {$prefijo}_obras o ON o.seccion_id = s.id
        GROUP BY s.id, s.titulo
        ORDER BY c DESC, s.id ASC
        LIMIT 5
    ")->fetchAll();
    $seccionesResumen[$nombre] = $rows;
}

$recientes = $pdo->query("
    SELECT 'Arte' AS area, titulo, created_at FROM arte_obras
    UNION ALL SELECT 'Literatura', titulo, created_at FROM lit_obras
    UNION ALL SELECT 'Poesía', titulo, created_at FROM poe_obras
    ORDER BY created_at DESC
")->fetchAll();

$usuariosLista = $pdo->query('SELECT username, email, role, created_at FROM users ORDER BY role ASC, username ASC')->fetchAll();

include __DIR__ . '/components/header.php';
?>

<div class="dash">
    <header class="dash-head">
        <div>
            <h1 class="admin-title">Panel de Administración</h1>
            <p class="admin-sub">Bienvenido al área de gestión de NIKAN.</p>
        </div>
        </header>

    <section class="kpi-grid">
        <div class="kpi kpi--red">
            <div class="kpi__num"><?php echo $usuarios; ?></div>
            <div class="kpi__label">Usuarios</div>
            <div class="kpi__sub"><?php echo $admins; ?> admin · <?php echo $miembros; ?> miembro</div>
        </div>
        <div class="kpi kpi--gold">
            <div class="kpi__num"><?php echo $autores; ?></div>
            <div class="kpi__label">Autores</div>
            <div class="kpi__sub">Registrados en NIKAN</div>
        </div>
        <div class="kpi kpi--green">
            <div class="kpi__num"><?php echo $obrasTotal; ?></div>
            <div class="kpi__label">Obras</div>
            <div class="kpi__sub"><?php echo $porArea['Arte']['obras']; ?> arte · <?php echo $porArea['Literatura']['obras']; ?> lit. · <?php echo $porArea['Poesía']['obras']; ?> poesía</div>
        </div>
        <div class="kpi kpi--dark">
            <div class="kpi__num"><?php echo array_sum(array_column($porArea, 'secciones')); ?></div>
            <div class="kpi__label">Secciones</div>
            <div class="kpi__sub"><?php echo $porArea['Arte']['secciones']; ?> + <?php echo $porArea['Literatura']['secciones']; ?> + <?php echo $porArea['Poesía']['secciones']; ?> por área</div>
        </div>
    </section>

    <section class="row">
        <div class="card">
            <h2 class="card__title">Distribución por área</h2>
            <?php foreach ($porArea as $nombre => $d): ?>
                <div class="bar">
                    <div class="bar__top">
                        <span class="bar__name"><?php echo $nombre; ?></span>
                        <span class="bar__val"><?php echo $d['obras']; ?> obras · <?php echo $d['secciones']; ?> secciones</span>
                    </div>
                    <div class="bar__track"><div class="bar__fill bar__fill--<?php echo strtolower($nombre) === 'arte' ? 'red' : (strtolower($nombre) === 'literatura' ? 'gold' : 'green'); ?>" style="width: <?php echo round($d['obras'] / $maxObrasArea * 100); ?>%"></div></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <h2 class="card__title">Autores con más obras</h2>
            <div class="pag" data-per="5" id="pagAutores">
            <?php foreach ($topAutores as $a): ?>
                <div class="bar">
                    <div class="bar__top">
                        <span class="bar__name"><?php echo htmlspecialchars($a['nombre']); ?></span>
                        <span class="bar__val"><?php echo $a['total']; ?> obra<?php echo $a['total'] == 1 ? '' : 's'; ?></span>
                    </div>
                    <div class="bar__track"><div class="bar__fill bar__fill--red" style="width: <?php echo round($a['total'] / $maxObrasAutor * 100); ?>%"></div></div>
                </div>
            <?php endforeach; ?>
            </div>
            <div class="pag-nav" data-target="pagAutores">
                <button type="button" class="pag-btn pag-prev">‹ Anterior</button>
                <span class="pag-info">Página 1 de 1</span>
                <button type="button" class="pag-btn pag-next">Siguiente ›</button>
            </div>
        </div>
    </section>

    <section class="row">
        <div class="card">
            <h2 class="card__title">Colecciones de arte</h2>
            <?php foreach ($seccionesResumen['Arte'] as $s): ?>
                <div class="pill"><span><?php echo htmlspecialchars($s['titulo']); ?></span><span class="pill__n"><?php echo $s['c']; ?></span></div>
            <?php endforeach; ?>
        </div>
        <div class="card">
            <h2 class="card__title">Colecciones de literatura</h2>
            <?php foreach ($seccionesResumen['Literatura'] as $s): ?>
                <div class="pill"><span><?php echo htmlspecialchars($s['titulo']); ?></span><span class="pill__n"><?php echo $s['c']; ?></span></div>
            <?php endforeach; ?>
        </div>
        <div class="card">
            <h2 class="card__title">Colecciones de poesía</h2>
            <?php foreach ($seccionesResumen['Poesía'] as $s): ?>
                <div class="pill"><span><?php echo htmlspecialchars($s['titulo']); ?></span><span class="pill__n"><?php echo $s['c']; ?></span></div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="row">
        <div class="card card--wide">
            <h2 class="card__title">Actividad reciente</h2>
            <?php if ($recientes): ?>
                <ul class="feed pag" data-per="5" id="pagFeed">
                    <?php foreach ($recientes as $r): ?>
                        <li>
                            <span class="feed__dot feed__dot--<?php echo $r['area'] === 'Arte' ? 'red' : ($r['area'] === 'Literatura' ? 'gold' : 'green'); ?>"></span>
                            <span class="feed__area"><?php echo $r['area']; ?></span>
                            <span class="feed__titulo"><?php echo htmlspecialchars($r['titulo']); ?></span>
                            <span class="feed__fecha"><?php echo date('d/m/Y H:i', strtotime($r['created_at'])); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="pag-nav" data-target="pagFeed">
                    <button type="button" class="pag-btn pag-prev">‹ Anterior</button>
                    <span class="pag-info">Página 1 de 1</span>
                    <button type="button" class="pag-btn pag-next">Siguiente ›</button>
                </div>
            <?php else: ?>
                <p class="card__empty">Sin obras registradas todavía.</p>
            <?php endif; ?>
        </div>
        <div class="card">
            <h2 class="card__title">Usuarios del sistema</h2>
            <?php foreach ($usuariosLista as $u): ?>
                <div class="user-item">
                    <div class="user-item__avatar"><?php echo strtoupper(substr($u['username'], 0, 1)); ?></div>
                    <div class="user-item__main">
                        <span class="user-item__name"><?php echo htmlspecialchars($u['username']); ?></span>
                        <span class="user-item__mail"><?php echo htmlspecialchars($u['email']); ?></span>
                    </div>
                    <span class="user-item__role user-item__role--<?php echo $u['role']; ?>"><?php echo htmlspecialchars($u['role']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<style>
    .dash {
        position: relative;
        z-index: 2;
        max-width: 1200px;
        margin: 0 auto;
        padding: 120px 24px 60px;
    }
    .dash-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 24px;
    }
    .dash-user {
        background: rgba(20, 20, 20, 0.75);
        border-radius: 14px;
        padding: 14px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
        color: #fff;
    }
    .dash-user img { width: 46px; height: 46px; }
    .dash-user__name { font-weight: 800; font-size: 16px; }
    .dash-user__role { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.85; }

    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .kpi {
        border-radius: 14px;
        padding: 20px 22px;
        color: #fff;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
    }
    .kpi__num { font: oblique bold 100% 'Felthgothic', cursive; font-size: 42px; line-height: 1; }
    .kpi__label { font-size: 12px; text-transform: uppercase; letter-spacing: 1.5px; margin: 6px 0 4px; font-weight: 700; }
    .kpi__sub { font-size: 12px; opacity: 0.85; }
    .kpi--red { background: linear-gradient(135deg, #C6372E, #e04538); }
    .kpi--gold { background: linear-gradient(135deg, #b8912f, #c9a94f); }
    .kpi--green { background: linear-gradient(135deg, #0a7a4b, #0f9c60); }
    .kpi--dark { background: linear-gradient(135deg, #2a2a2a, #454545); }

    .row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 16px;
        margin-bottom: 16px;
    }
    .card {
        background: rgba(20, 20, 20, 0.75);
        border-radius: 14px;
        padding: 20px 22px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.35);
        color: #fff;
    }
    .card__title { font: oblique bold 100% 'Felthgothic', cursive; font-size: 22px; margin-bottom: 16px; }
    .card__empty { font-size: 13px; opacity: 0.8; }
    .card--wide { grid-column: span 2; }

    .bar { margin-bottom: 14px; }
    .bar:last-child { margin-bottom: 0; }
    .bar__top { display: flex; justify-content: space-between; gap: 10px; font-size: 13px; margin-bottom: 6px; }
    .bar__name { font-weight: 700; }
    .bar__val { opacity: 0.85; }
    .bar__track { height: 8px; border-radius: 999px; background: rgba(255, 255, 255, 0.15); overflow: hidden; }
    .bar__fill { height: 100%; border-radius: 999px; transition: width 0.4s ease; }
    .bar__fill--red { background: #C6372E; }
    .bar__fill--gold { background: #c9a94f; }
    .bar__fill--green { background: #0f9c60; }

    .pill {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 13px;
        margin-bottom: 8px;
    }
    .pill:last-child { margin-bottom: 0; }
    .pill__n {
        background: rgb(var(--nikan-bg-rgb));
        border-radius: 999px;
        color: #fff;
        font-weight: 800;
        font-size: 12px;
        padding: 2px 10px;
    }

    .feed { list-style: none; }
    .feed li { display: flex; align-items: center; gap: 10px; padding: 7px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.08); font-size: 13px; }
    .feed li:last-child { border-bottom: none; }
    .feed__dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .feed__dot--red { background: #C6372E; }
    .feed__dot--gold { background: #c9a94f; }
    .feed__dot--green { background: #0f9c60; }
    .feed__area { font-weight: 700; opacity: 0.85; min-width: 74px; }
    .feed__titulo { flex: 1; min-width: 0; }
    .feed__fecha { opacity: 0.6; font-size: 12px; }

    .user-item { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid rgba(255, 255, 255, 0.08); }
    .user-item:last-child { border-bottom: none; }
    .user-item__avatar {
        width: 36px; height: 36px; border-radius: 50%;
        background: rgb(var(--nikan-bg-rgb));
        color: #fff; font-weight: 800;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .user-item__main { flex: 1; min-width: 0; display: flex; flex-direction: column; }
    .user-item__name { font-weight: 700; font-size: 13px; }
    .user-item__mail { font-size: 11px; opacity: 0.6; }
    .user-item__role { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; font-weight: 800; border-radius: 999px; padding: 3px 10px; }
    .user-item__role--admin { background: #C6372E; color: #fff; }
    .user-item__role--user { background: #c9a94f; color: #fff; }

    .pag-nav {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-top: 16px;
    }
    .pag-btn {
        border: 1px solid rgba(255,255,255,0.3);
        background: rgba(255,255,255,0.12);
        color: #fff;
        border-radius: 8px;
        padding: 7px 14px;
        font-family: 'Montserrat', sans-serif;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        transition: background .2s;
    }
    .pag-btn:hover:not(:disabled) { background: rgba(255,255,255,0.25); }
    .pag-btn:disabled { opacity: 0.4; cursor: default; }
    .pag-info { font-size: 12px; opacity: 0.85; }

    @media (max-width: 760px) {
        .dash-head { flex-direction: column; }
        .card--wide { grid-column: span 1; }
    }
</style>

<script>
    document.querySelectorAll('.pag').forEach(function (list) {
        var per = parseInt(list.getAttribute('data-per') || 5, 10);
        var items = Array.prototype.slice.call(list.children);
        var pages = Math.ceil(items.length / per);
        var nav = document.querySelector('.pag-nav[data-target="' + list.id + '"]');
        if (!nav) return;
        var info = nav.querySelector('.pag-info');
        var prev = nav.querySelector('.pag-prev');
        var next = nav.querySelector('.pag-next');
        var page = 0;

        if (pages <= 1) { nav.style.display = 'none'; return; }

        function render() {
            items.forEach(function (it, i) {
                it.style.display = (i >= page * per && i < page * per + per) ? '' : 'none';
            });
            info.textContent = 'Página ' + (page + 1) + ' de ' + pages;
            prev.disabled = page === 0;
            next.disabled = page === pages - 1;
        }

        prev.addEventListener('click', function () { if (page > 0) { page--; render(); } });
        next.addEventListener('click', function () { if (page < pages - 1) { page++; render(); } });
        render();
    });
</script>

</body>
</html>