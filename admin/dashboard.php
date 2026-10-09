<?php
require_once __DIR__ . '/auth.php';
require_admin();

$pdo = getDB();

$usuarios  = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
$admins    = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='admin'")->fetchColumn();
$miembros  = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$autores   = (int) $pdo->query('SELECT COUNT(*) FROM autores')->fetchColumn();
$obrasTotal = (int) $pdo->query('SELECT (SELECT COUNT(*) FROM arte_obras) + (SELECT COUNT(*) FROM lit_obras) + (SELECT COUNT(*) FROM poe_obras) + (SELECT COUNT(*) FROM musica_obras)')->fetchColumn();

$porArea = [];
foreach (['Arte' => 'arte', 'Literatura' => 'lit', 'Poesía' => 'poe', 'Música' => 'musica'] as $nombre => $prefijo) {
    $obras  = (int) $pdo->query("SELECT COUNT(*) FROM {$prefijo}_obras")->fetchColumn();
    $secciones = (int) $pdo->query("SELECT COUNT(*) FROM {$prefijo}_secciones")->fetchColumn();
    $porArea[$nombre] = ['obras' => $obras, 'secciones' => $secciones];
}
$maxObrasArea = max(array_column($porArea, 'obras'));

$topAutores = $pdo->query("
    SELECT a.nombre,
        (SELECT COUNT(*) FROM arte_obras  x WHERE x.autor_id = a.id OR x.autor = a.nombre) +
        (SELECT COUNT(*) FROM lit_obras   y WHERE y.autor_id = a.id OR y.autor = a.nombre) +
        (SELECT COUNT(*) FROM poe_obras   z WHERE z.autor_id = a.id OR z.autor = a.nombre) +
        (SELECT COUNT(*) FROM musica_obras w WHERE w.autor_id = a.id OR w.autor = a.nombre) AS total
    FROM autores a
    ORDER BY total DESC
")->fetchAll();
$maxObrasAutor = (int) (max(array_column($topAutores, 'total')) ?: 1);

$seccionesResumen = [];
foreach (['Arte' => 'arte', 'Literatura' => 'lit', 'Poesía' => 'poe', 'Música' => 'musica'] as $nombre => $prefijo) {
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
    UNION ALL SELECT 'Música', titulo, created_at FROM musica_obras
    ORDER BY created_at DESC
")->fetchAll();

$usuariosLista = $pdo->query('SELECT username, email, role, created_at FROM users ORDER BY role ASC, username ASC')->fetchAll();

include __DIR__ . '/components/header.php';
?>

<div class="dash">
    <header class="dash-head">
        <div>
            <span class="dash-eyebrow">PANEL DE CONTROL</span>
            <h1 class="admin-title">Panel de Administración</h1>
            <p class="admin-sub">Bienvenido al área de gestión de NIKAN.</p>
        </div>
        <span class="dash-count"><?php echo $obrasTotal; ?> obras · <?php echo $usuarios; ?> usuarios</span>
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
            <div class="kpi__sub"><?php echo $porArea['Arte']['obras']; ?> arte · <?php echo $porArea['Literatura']['obras']; ?> lit. · <?php echo $porArea['Poesía']['obras']; ?> poesía · <?php echo $porArea['Música']['obras']; ?> música</div>
        </div>
        <div class="kpi kpi--dark">
            <div class="kpi__num"><?php echo array_sum(array_column($porArea, 'secciones')); ?></div>
            <div class="kpi__label">Secciones</div>
            <div class="kpi__sub"><?php echo $porArea['Arte']['secciones']; ?> + <?php echo $porArea['Literatura']['secciones']; ?> + <?php echo $porArea['Poesía']['secciones']; ?> + <?php echo $porArea['Música']['secciones']; ?> por área</div>
        </div>
    </section>

    <section class="row">
        <div class="card">
            <h2 class="card__title"><img class="card__fav" src="../assets/leon.svg" alt="NIKAN"> Distribución por área</h2>
            <?php foreach ($porArea as $nombre => $d): ?>
                <div class="bar">
                    <div class="bar__top">
                        <span class="bar__name"><?php echo $nombre; ?></span>
                        <span class="bar__val"><?php echo $d['obras']; ?> obras · <?php echo $d['secciones']; ?> secciones</span>
                    </div>
                    <div class="bar__track"><div class="bar__fill <?php
                        $nombreL = strtolower($nombre);
                        if ($nombreL === 'arte') echo 'bar__fill--red';
                        elseif ($nombreL === 'literatura') echo 'bar__fill--gold';
                        elseif ($nombreL === 'poesía' || $nombreL === 'poesia') echo 'bar__fill--green';
                        else echo 'bar__fill--blue';
                    ?>" style="width: <?php echo round($d['obras'] / $maxObrasArea * 100); ?>%"></div></div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <h2 class="card__title"><img class="card__fav" src="../assets/leon.svg" alt="NIKAN"> Autores con más obras</h2>
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
            <h2 class="card__title"><img class="card__fav" src="../assets/leon.svg" alt="NIKAN"> Colecciones de arte</h2>
            <?php foreach ($seccionesResumen['Arte'] as $s): ?>
                <div class="pill"><span><?php echo htmlspecialchars($s['titulo']); ?></span><span class="pill__n"><?php echo $s['c']; ?></span></div>
            <?php endforeach; ?>
        </div>
        <div class="card">
            <h2 class="card__title"><img class="card__fav" src="../assets/leon.svg" alt="NIKAN"> Colecciones de literatura</h2>
            <?php foreach ($seccionesResumen['Literatura'] as $s): ?>
                <div class="pill"><span><?php echo htmlspecialchars($s['titulo']); ?></span><span class="pill__n"><?php echo $s['c']; ?></span></div>
            <?php endforeach; ?>
        </div>
        <div class="card">
            <h2 class="card__title"><img class="card__fav" src="../assets/leon.svg" alt="NIKAN"> Colecciones de poesía</h2>
            <?php foreach ($seccionesResumen['Poesía'] as $s): ?>
                <div class="pill"><span><?php echo htmlspecialchars($s['titulo']); ?></span><span class="pill__n"><?php echo $s['c']; ?></span></div>
            <?php endforeach; ?>
        </div>
        <div class="card">
            <h2 class="card__title"><img class="card__fav" src="../assets/leon.svg" alt="NIKAN"> Colecciones de música</h2>
            <?php foreach ($seccionesResumen['Música'] as $s): ?>
                <div class="pill"><span><?php echo htmlspecialchars($s['titulo']); ?></span><span class="pill__n"><?php echo $s['c']; ?></span></div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="row">
        <div class="card card--wide">
            <h2 class="card__title"><img class="card__fav" src="../assets/leon.svg" alt="NIKAN"> Actividad reciente</h2>
            <?php if ($recientes): ?>
                <ul class="feed pag" data-per="5" id="pagFeed">
                    <?php foreach ($recientes as $r): ?>
                        <li>
                            <span class="feed__dot <?php
                                if ($r['area'] === 'Arte') echo 'feed__dot--red';
                                elseif ($r['area'] === 'Literatura') echo 'feed__dot--gold';
                                elseif ($r['area'] === 'Poesía') echo 'feed__dot--green';
                                else echo 'feed__dot--blue';
                            ?>"></span>
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
            <h2 class="card__title"><img class="card__fav" src="../assets/leon.svg" alt="NIKAN"> Usuarios del sistema</h2>
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
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 24px;
    }
    .dash-eyebrow { color: #03d437; font-size: 10px; font-weight: 800; letter-spacing: 3px; }
    .dash-head .admin-title {
        margin: 4px 0;
        color: #fff;
        font: oblique bold 100% 'Felthgothic', cursive;
        font-size: 40px;
    }
    .dash-head .admin-sub {
        margin: 0;
        color: #fff;
        font-size: 14px;
        opacity: .9;
    }
    .dash-count {
        flex-shrink: 0;
        padding: 12px 16px;
        border-radius: 10px;
        background: #0f9c60;
        color: #fff;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .4px;
        box-shadow: 0 8px 18px rgba(15,156,96,.28);
    }

    /* Tarjetas KPI: panel editorial crema con acento de color. */
    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 18px;
        margin-bottom: 22px;
    }
    .kpi {
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.16);
        border-radius: 6px;
        padding: 20px 22px 18px;
        background: #f5eee3;
        color: #38251f;
        box-shadow: 0 18px 45px rgba(0,0,0,.3);
    }
    .kpi::before {
        content: "";
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: var(--kpi-accent, #c6372e);
    }
    .kpi__num { font: oblique bold 100% 'Felthgothic', cursive; font-size: 42px; line-height: 1; color: var(--kpi-accent, #c6372e); }
    .kpi__label { font-size: 11px; text-transform: uppercase; letter-spacing: 2px; margin: 8px 0 5px; font-weight: 800; color: #594238; }
    .kpi__sub { font-size: 11.5px; line-height: 1.5; color: #927e6b; }
    .kpi--red { --kpi-accent: #c6372e; }
    .kpi--gold { --kpi-accent: #b8912f; }
    .kpi--green { --kpi-accent: #397d27; }
    .kpi--dark { --kpi-accent: #38251f; }

    .row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 18px;
        margin-bottom: 18px;
    }
    .card {
        padding: 0 24px 22px;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.16);
        border-radius: 6px;
        background: #f5eee3;
        box-shadow: 0 18px 45px rgba(0,0,0,.3);
        color: #38251f;
    }
    .card__title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin: 0 -24px 18px;
        padding: 15px 24px;
        background: #c6372e;
        color: #fff;
        font: oblique bold 100% 'Felthgothic', cursive;
        font-size: 21px;
        letter-spacing: .3px;
    }
    .card__fav { width: 24px; height: 24px; object-fit: contain; filter: brightness(0) invert(1); opacity: 0.95; flex-shrink: 0; }
    .card__empty { font-size: 13px; color: #765c4b; }
    .card--wide { grid-column: span 2; }

    .bar { margin-bottom: 14px; }
    .bar:last-child { margin-bottom: 0; }
    .bar__top { display: flex; justify-content: space-between; gap: 10px; font-size: 13px; margin-bottom: 6px; }
    .bar__name { font-weight: 700; color: #38251f; }
    .bar__val { color: #765c4b; font-size: 12px; }
    .bar__track { height: 8px; border-radius: 999px; background: #e3d6c1; overflow: hidden; }
    .bar__fill { height: 100%; border-radius: 999px; transition: width 0.4s ease; }
    .bar__fill--red { background: #c6372e; }
    .bar__fill--gold { background: #b8912f; }
    .bar__fill--green { background: #397d27; }
    .bar__fill--blue { background: #2a6fdb; }

    .pill {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        background: #fffaf3;
        border: 1px solid #d7c5aa;
        border-radius: 4px;
        padding: 9px 13px;
        font-size: 13px;
        margin-bottom: 8px;
        color: #38251f;
    }
    .pill:last-child { margin-bottom: 0; }
    .pill__n {
        background: #c6372e;
        border-radius: 999px;
        color: #fff;
        font-weight: 800;
        font-size: 11px;
        padding: 2px 10px;
    }

    .feed { list-style: none; }
    .feed li { display: flex; align-items: center; gap: 10px; padding: 9px 0; border-bottom: 1px solid #e2d5c0; font-size: 13px; color: #38251f; }
    .feed li:last-child { border-bottom: none; }
    .feed__dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .feed__dot--red { background: #c6372e; }
    .feed__dot--gold { background: #b8912f; }
    .feed__dot--green { background: #397d27; }
    .feed__dot--blue { background: #2a6fdb; }
    .feed__area { font-weight: 800; font-size: 11px; letter-spacing: 1px; text-transform: uppercase; color: #765c4b; min-width: 74px; }
    .feed__titulo { flex: 1; min-width: 0; font-weight: 600; }
    .feed__fecha { color: #927e6b; font-size: 12px; white-space: nowrap; }

    .user-item { display: flex; align-items: center; gap: 10px; padding: 9px 0; border-bottom: 1px solid #e2d5c0; }
    .user-item:last-child { border-bottom: none; }
    .user-item__avatar {
        width: 36px; height: 36px; border-radius: 50%;
        background: #c6372e;
        color: #fff; font-weight: 800;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .user-item__main { flex: 1; min-width: 0; display: flex; flex-direction: column; }
    .user-item__name { font-weight: 700; font-size: 13px; color: #38251f; }
    .user-item__mail { font-size: 11px; color: #927e6b; }
    .user-item__role { font-size: 10px; text-transform: uppercase; letter-spacing: 1px; font-weight: 800; border-radius: 999px; padding: 3px 10px; }
    .user-item__role--admin { background: #c6372e; color: #fff; }
    .user-item__role--user { background: #b8912f; color: #fff; }

    .pag-nav {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-top: 16px;
        padding-top: 14px;
        border-top: 1px solid #e2d5c0;
    }
    .pag-btn {
        border: 1px solid #d7c5aa;
        background: #fffaf3;
        color: #594238;
        border-radius: 4px;
        padding: 7px 14px;
        font-family: 'Montserrat', sans-serif;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .5px;
        text-transform: uppercase;
        cursor: pointer;
        transition: background .2s, border-color .2s;
    }
    .pag-btn:hover:not(:disabled) { background: #f0e4d1; border-color: #bca78c; }
    .pag-btn:disabled { opacity: 0.4; cursor: default; }
    .pag-info { font-size: 12px; color: #765c4b; }

    @media (max-width: 760px) {
        .dash-head { flex-direction: column; align-items: flex-start; }
        .dash-head .admin-title { font-size: 32px; }
        .card--wide { grid-column: span 1; }
        .card { padding: 0 18px 20px; }
        .card__title { margin: 0 -18px 16px; padding: 14px 18px; font-size: 19px; }
        .feed li { flex-wrap: wrap; }
        .feed__fecha { width: 100%; padding-left: 18px; }
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