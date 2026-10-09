<?php
require_once __DIR__ . '/auth.php';
require_admin();
$pdo = getDB();
asegurar_tabla_museos_virtuales($pdo);
$museos = $pdo->query('SELECT * FROM museos_virtuales ORDER BY nombre ASC')->fetchAll();
include __DIR__ . '/components/header.php';
?>
<div class="admin-body">
    <div class="virtual-head">
        <div>
            <span class="virtual-eyebrow">GESTIÓN DE EXPERIENCIAS</span>
            <h1 class="admin-title">Museos virtuales</h1>
            <p class="admin-sub">Publica nuevos recorridos 3D y ubícalos en el mapa cultural de NIKAN.</p>
        </div>
        <div class="virtual-head__badge"><strong><?php echo count($museos); ?></strong><span>publicados</span></div>
    </div>
    <?php if (!empty($_GET['msg'])): ?>
        <div class="virtual-alert"><?php echo htmlspecialchars($_GET['msg'], ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>
    <div class="virtual-layout">
        <section class="admin-panel">
            <h2 class="admin-h2"><span class="section-mark">+</span> Registrar museo</h2>
            <p class="panel-intro">Completa la información que verá el visitante al seleccionar el punto en el mapa.</p>
            <form method="post" action="virtuales_actions.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="save">
                <label class="admin-label" for="nombre">Nombre</label>
                <input class="admin-input" id="nombre" name="nombre" required maxlength="150">
                <label class="admin-label" for="descripcion">Descripción</label>
                <textarea class="admin-input" id="descripcion" name="descripcion" required></textarea>
                <div class="coords">
                    <div><label class="admin-label" for="latitud">Latitud</label><input class="admin-input" id="latitud" name="latitud" required readonly></div>
                    <div><label class="admin-label" for="longitud">Longitud</label><input class="admin-input" id="longitud" name="longitud" required readonly></div>
                </div>
                <label class="admin-label" for="modelo">Modelo 3D (.glb o .gltf)</label>
                <input class="admin-input" id="modelo" type="file" name="modelo" accept=".glb,.gltf,model/gltf-binary,model/gltf+json" required>
                <button class="btn btn-green virtual-submit" type="submit">Publicar museo virtual <span>→</span></button>
            </form>
        </section>
        <section class="admin-panel">
            <h2 class="admin-h2"><span class="section-mark section-mark--gold">⌖</span> Ubicación del museo</h2>
            <p class="panel-intro">Haz clic en el mapa o mueve el marcador para elegir el punto exacto.</p>
            <div id="adminMuseumMap"></div>
            <div class="coordinates-readout"><span>LAT <b id="latReadout">12.1328000</b></span><span>LNG <b id="lngReadout">-86.2504000</b></span></div>
        </section>
    </div>
    <section class="admin-panel museum-list">
        <div class="list-head"><h2 class="admin-h2"><span class="section-mark section-mark--red">N</span> Museos publicados</h2><span class="list-count"><?php echo count($museos); ?> registros</span></div>
        <?php foreach ($museos as $museo): ?>
            <div class="museum-row">
                <div class="museum-row__identity"><span class="museum-index"><?php echo str_pad((string)$museo['id'], 2, '0', STR_PAD_LEFT); ?></span><div><strong><?php echo htmlspecialchars($museo['nombre']); ?></strong><span><?php echo htmlspecialchars($museo['descripcion']); ?></span></div></div>
                <div class="museum-row__meta"><span><?php echo number_format((float)$museo['latitud'], 4); ?>, <?php echo number_format((float)$museo['longitud'], 4); ?></span><a class="btn btn-small btn-red" href="virtuales_actions.php?action=delete&id=<?php echo (int)$museo['id']; ?>" onclick="return confirm('¿Eliminar este museo virtual?')">Eliminar</a></div>
            </div>
        <?php endforeach; ?>
        <?php if (!$museos): ?><p class="map-help">Todavía no hay museos virtuales publicados.</p><?php endif; ?>
    </section>
</div>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {
    var map = L.map('adminMuseumMap').setView([12.8654, -85.2072], 7);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
    var marker;
    function setPoint(lat, lng) {
        document.getElementById('latitud').value = lat.toFixed(7);
        document.getElementById('longitud').value = lng.toFixed(7);
        document.getElementById('latReadout').textContent = lat.toFixed(7);
        document.getElementById('lngReadout').textContent = lng.toFixed(7);
        if (!marker) marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        else marker.setLatLng([lat, lng]);
        marker.on('dragend', function () { var p = marker.getLatLng(); setPoint(p.lat, p.lng); });
    }
    map.on('click', function (event) { setPoint(event.latlng.lat, event.latlng.lng); });
    setPoint(12.1328, -86.2504);
})();
</script>
<style>
.admin-body { position:relative; z-index:2; max-width:1180px; margin:0 auto; padding:126px 24px 70px; color:#fff; }
.virtual-head { display:flex; align-items:flex-end; justify-content:space-between; gap:24px; margin-bottom:24px; }
.virtual-eyebrow { display:block; margin-bottom:8px; color:#03d437; font-size:10px; font-weight:800; letter-spacing:3px; }
.admin-title { margin:0 0 5px; font:oblique bold 40px 'Felthgothic', cursive; }
.admin-sub { margin:0; color:rgba(255,255,255,.72); font-size:13px; }
.virtual-head__badge { min-width:126px; padding:15px 20px; border-radius:14px; background:linear-gradient(135deg,#0a7a4b,#0f9c60); box-shadow:0 10px 30px rgba(0,0,0,.35); text-align:center; }
.virtual-head__badge strong,.virtual-head__badge span { display:block; }
.virtual-head__badge strong { font:oblique bold 31px 'Felthgothic',cursive; line-height:1; }
.virtual-head__badge span { margin-top:5px; font-size:10px; letter-spacing:1.5px; text-transform:uppercase; opacity:.85; }
.virtual-alert { margin-bottom:18px; padding:12px 16px; border-left:4px solid #03d437; border-radius:8px; background:rgba(15,156,96,.22); font-size:13px; }
.virtual-layout { display:grid; grid-template-columns:minmax(300px,.82fr) minmax(420px,1.18fr); gap:16px; }
.admin-panel { padding:22px; border-radius:14px; background:rgba(20,20,20,.75); box-shadow:0 10px 30px rgba(0,0,0,.35); }
.admin-h2 { display:flex; align-items:center; gap:10px; margin:0 0 8px; font:oblique bold 24px 'Felthgothic',cursive; }
.section-mark { display:grid; place-items:center; width:28px; height:28px; border-radius:8px; color:#fff; background:#0f9c60; font:800 19px Montserrat,sans-serif; }
.section-mark--gold { background:#b8912f; }.section-mark--red { background:#c6372e; }
.panel-intro { margin:0 0 18px; color:rgba(255,255,255,.65); font-size:12px; line-height:1.5; }
.admin-label { display:block; margin:14px 0 6px; color:rgba(255,255,255,.8); font-size:11px; font-weight:700; letter-spacing:1px; text-transform:uppercase; }
.admin-input { width:100%; padding:11px 13px; border:1px solid rgba(255,255,255,.22); border-radius:8px; outline:none; background:rgba(255,255,255,.1); color:#fff; font:13px Montserrat,sans-serif; }
.admin-input:focus { border-color:#03d437; box-shadow:0 0 0 2px rgba(3,212,55,.14); }
textarea.admin-input { min-height:105px; resize:vertical; }
.admin-input[type=file] { padding:8px; }.admin-input[type=file]::file-selector-button { margin-right:8px; padding:6px 10px; border:0; border-radius:6px; background:#0f9c60; color:#fff; cursor:pointer; }
.coords { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
.btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; border:0; border-radius:8px; padding:10px 15px; color:#fff; font:700 12px Montserrat,sans-serif; text-decoration:none; cursor:pointer; }
.btn-green { background:#2e8b57; }.btn-green:hover { background:#37a86a; }.btn-red { background:#c0392b; }.btn-red:hover { background:#d54838; }
.virtual-submit { width:100%; margin-top:20px; padding:13px; letter-spacing:.3px; }.virtual-submit span { font-size:18px; line-height:10px; }
#adminMuseumMap { height:404px; overflow:hidden; border:2px solid #0f9c60; border-radius:12px; box-shadow:inset 0 0 0 1px rgba(255,255,255,.15); }
.coordinates-readout { display:flex; gap:9px; margin-top:12px; }.coordinates-readout span { flex:1; padding:9px 10px; border-radius:7px; background:rgba(255,255,255,.08); color:rgba(255,255,255,.55); font-size:9px; letter-spacing:1px; }.coordinates-readout b { display:block; margin-top:4px; color:#03d437; font-size:11px; letter-spacing:0; }
.museum-list { margin-top:16px; }.list-head { display:flex; align-items:center; justify-content:space-between; gap:15px; }.list-head .admin-h2 { margin-bottom:16px; }.list-count { padding:5px 10px; border-radius:999px; background:rgba(255,255,255,.1); color:rgba(255,255,255,.65); font-size:10px; }
.museum-row { display:flex; align-items:center; justify-content:space-between; gap:20px; padding:14px 0; border-top:1px solid rgba(255,255,255,.1); }.museum-row__identity,.museum-row__meta { display:flex; align-items:center; gap:13px; }.museum-row__identity { min-width:0; }.museum-index { display:grid; place-items:center; width:36px; height:36px; flex-shrink:0; border-radius:50%; background:#c9a94f; color:#282019; font-weight:800; font-size:11px; }.museum-row strong,.museum-row span { display:block; }.museum-row strong { font-size:14px; }.museum-row__identity span:not(.museum-index) { max-width:650px; margin-top:5px; overflow:hidden; color:rgba(255,255,255,.62); font-size:11px; text-overflow:ellipsis; white-space:nowrap; }.museum-row__meta > span { color:rgba(255,255,255,.5); font-size:10px; white-space:nowrap; }.btn-small { padding:6px 10px; font-size:11px; }
@media(max-width:800px){.virtual-head{align-items:flex-start;flex-direction:column}.virtual-head__badge{align-self:stretch}.virtual-layout{grid-template-columns:1fr}.museum-row{align-items:flex-start;flex-direction:column}.museum-row__meta{width:100%;justify-content:space-between}.museum-row__identity{width:100%}}
</style>
