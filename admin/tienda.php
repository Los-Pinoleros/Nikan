<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/auth.php';
require_admin();
$pdo = getDB();
asegurar_tabla_tienda_productos($pdo);
$productos = $pdo->query('SELECT * FROM tienda_productos ORDER BY orden ASC, creado_en DESC')->fetchAll();
include __DIR__ . '/components/header.php';
?>
<div class="admin-body tienda-admin">
    <div class="admin-head-row">
        <div><span class="admin-eyebrow">GESTIÓN DE PRODUCTOS</span><h1 class="admin-title">Tienda Cultural</h1><p class="admin-sub">Publica productos y conecta a cada vendedor directamente por WhatsApp.</p></div>
        <span class="admin-count"><?php echo count($productos); ?> productos</span>
    </div>
    <?php if (!empty($_GET['msg'])): ?><div class="admin-alert"><?php echo htmlspecialchars($_GET['msg'], ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
    <section class="admin-panel tienda-form-panel">
        <div class="tienda-form-heading">
            <div>
                <span class="tienda-form-kicker">CATÁLOGO CULTURAL</span>
                <h2 class="admin-h2">Nuevo producto</h2>
            </div>
            <span class="tienda-form-mark">+</span>
        </div>
        <p class="tienda-form-intro">Agrega una pieza, proyecto o producto cultural para que los visitantes puedan conocerlo y contactar directamente a su vendedor.</p>
        <form method="post" action="tienda_actions.php" enctype="multipart/form-data" class="tienda-form">
            <input type="hidden" name="action" value="save">
            <div class="tienda-field"><label class="admin-label" for="tiendaTitulo">Título</label><input id="tiendaTitulo" class="admin-input" name="titulo" required maxlength="180" placeholder="Nombre del producto"></div>
            <div class="tienda-field"><label class="admin-label" for="tiendaWhatsapp">WhatsApp del vendedor</label><input id="tiendaWhatsapp" class="admin-input" name="whatsapp" required maxlength="30" placeholder="50588888888"><small class="tienda-help">Incluye el código de país, sin espacios ni símbolos.</small></div>
            <div class="tienda-field tienda-field--wide"><label class="admin-label" for="tiendaDescripcion">Descripción corta</label><textarea id="tiendaDescripcion" class="admin-input" name="descripcion" required maxlength="500" placeholder="Cuenta brevemente qué hace especial a este producto"></textarea><small class="tienda-help">Máximo 500 caracteres.</small></div>
            <div class="tienda-field tienda-field--wide"><label class="admin-label" for="tiendaImagen">Imagen del producto</label><div class="tienda-file"><input id="tiendaImagen" class="admin-input" type="file" name="imagen_file" accept="image/*" required><span>JPG, PNG, WEBP o GIF</span></div></div>
            <button class="btn btn-green tienda-submit" type="submit"><span>Publicar producto</span><b>→</b></button>
        </form>
    </section>
    <section class="admin-panel tienda-list">
        <h2 class="admin-h2">Productos publicados</h2>
        <?php foreach ($productos as $producto): ?>
            <article class="tienda-admin-row">
                <img src="../<?php echo htmlspecialchars($producto['imagen'], ENT_QUOTES, 'UTF-8'); ?>" alt="">
                <div><strong><?php echo htmlspecialchars($producto['titulo']); ?></strong><p><?php echo htmlspecialchars($producto['descripcion']); ?></p><small>WhatsApp: <?php echo htmlspecialchars($producto['whatsapp']); ?></small></div>
                <div class="item-actions">
                    <button class="btn btn-small" type="button" onclick='editarProducto(<?php echo json_encode($producto, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>)'>Editar</button>
                    <a class="btn btn-small btn-red" href="tienda_actions.php?action=delete&id=<?php echo (int)$producto['id']; ?>" onclick='abrirEliminarTienda(<?php echo (int)$producto['id']; ?>, <?php echo json_encode($producto['titulo'], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>); return false;'>Eliminar</a>
                </div>
            </article>
        <?php endforeach; ?>
        <?php if (!$productos): ?><p class="admin-sub">Todavía no hay productos publicados.</p><?php endif; ?>
    </section>
</div>
<div id="tiendaEditModal" class="modal" style="display:none;"><div class="modal-box"><h2 class="admin-h2">Editar producto</h2><form id="tiendaEditForm" method="post" action="tienda_actions.php" enctype="multipart/form-data"><input type="hidden" name="action" value="save"><input type="hidden" name="id" id="edit_id"><label class="admin-label">Título</label><input class="admin-input" name="titulo" id="edit_titulo" required><label class="admin-label">Descripción corta</label><textarea class="admin-input" name="descripcion" id="edit_descripcion" required></textarea><label class="admin-label">WhatsApp</label><input class="admin-input" name="whatsapp" id="edit_whatsapp" required><label class="admin-label">Nueva imagen (opcional)</label><input class="admin-input" type="file" name="imagen_file" accept="image/*"><div class="modal-actions"><button type="button" class="btn" onclick="cerrarTiendaModal()">Cancelar</button><button class="btn btn-green" type="submit">Guardar</button></div></form></div></div>
<div id="tiendaDeleteModal" class="modal" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="tiendaDeleteTitle">
    <div class="modal-box tienda-delete-box">
        <span class="tienda-delete-icon">!</span>
        <h2 id="tiendaDeleteTitle" class="admin-h2">¿Eliminar producto?</h2>
        <p>Esta acción eliminará <strong id="tiendaDeleteName"></strong> de la tienda y no se puede deshacer.</p>
        <div class="modal-actions">
            <button type="button" class="btn" onclick="cerrarEliminarTienda()">Cancelar</button>
            <a id="tiendaDeleteConfirm" class="btn btn-red" href="#">Sí, eliminar</a>
        </div>
    </div>
</div>
<style>
.tienda-admin { max-width:1100px; }
.admin-head-row { display:flex; justify-content:space-between; align-items:end; gap:20px; margin-bottom:24px; }
.admin-eyebrow { color:#03d437; font-size:10px; font-weight:800; letter-spacing:3px; }
.admin-count { padding:12px 16px; border-radius:10px; background:#0f9c60; font-size:12px; }
.admin-alert { margin-bottom:18px; padding:12px 15px; background:rgba(15,156,96,.25); border-left:4px solid #03d437; }
.tienda-admin > .admin-panel:first-of-type { padding:28px 30px 30px; border:1px solid rgba(255,255,255,.13); background:linear-gradient(145deg,rgba(42,31,26,.94),rgba(20,20,20,.86)); }
.tienda-form-heading { display:flex; align-items:center; justify-content:space-between; gap:20px; }
.tienda-form-heading .admin-h2 { margin:4px 0 0; font-size:28px; }
.tienda-form-kicker { color:#ffe08a; font-size:10px; font-weight:800; letter-spacing:2px; }
.tienda-form-mark { display:grid; place-items:center; width:42px; height:42px; border:1px solid rgba(255,224,138,.55); border-radius:50%; color:#ffe08a; font-size:27px; font-weight:300; }
.tienda-form-intro { max-width:690px; margin:10px 0 25px; color:rgba(255,255,255,.64); font-size:13px; line-height:1.6; }
.tienda-form { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:18px 20px; }
.tienda-field { min-width:0; }
.tienda-field--wide { grid-column:1/-1; }
.tienda-form .admin-label { margin:0 0 7px; color:rgba(255,255,255,.82); }
.tienda-form .admin-input { border-color:rgba(255,224,138,.25); background:rgba(255,255,255,.075); transition:border-color .2s,box-shadow .2s,background .2s; }
.tienda-form .admin-input::placeholder { color:rgba(255,255,255,.4); }
.tienda-form .admin-input:focus { border-color:#ffe08a; background:rgba(255,255,255,.12); box-shadow:0 0 0 3px rgba(255,224,138,.12); }
.tienda-form textarea.admin-input { min-height:115px; }
.tienda-help { display:block; margin-top:6px; color:rgba(255,255,255,.48); font-size:10px; }
.tienda-file { padding:10px; border:1px dashed rgba(255,224,138,.42); border-radius:8px; background:rgba(255,224,138,.045); }
.tienda-file .admin-input { margin:0; padding:7px; border:0; background:transparent; }
.tienda-file span { display:block; margin:5px 4px 0; color:rgba(255,255,255,.48); font-size:10px; }
.tienda-submit { display:flex; align-items:center; justify-content:space-between; gap:25px; grid-column:1/-1; width:fit-content; min-width:210px; margin-top:3px; padding:13px 17px; }
.tienda-submit b { font-size:20px; font-weight:400; line-height:1; }
.tienda-list { margin-top:22px; }
.tienda-admin-row { display:grid; grid-template-columns:90px 1fr auto; gap:18px; align-items:center; padding:14px 0; border-top:1px solid rgba(255,255,255,.13); }
.tienda-admin-row img { width:90px; height:75px; object-fit:cover; border-radius:7px; }
.tienda-admin-row strong { font-size:16px; }
.tienda-admin-row p { margin:5px 0; color:rgba(255,255,255,.7); font-size:12px; }
.tienda-admin-row small { color:#ffe08a; font-size:11px; }
.item-actions { display:flex; gap:7px; }
.modal { position:fixed; inset:0; z-index:500; display:grid; place-items:center; background:rgba(0,0,0,.7); }
.modal-box { width:min(560px,calc(100% - 30px)); max-height:90vh; overflow:auto; padding:24px; border-radius:12px; background:#201a18; color:#fff; }
.modal-actions { display:flex; justify-content:flex-end; gap:10px; margin-top:18px; }
@media(max-width:700px) {
    .admin-head-row { display:block; }
    .admin-count { display:inline-block; margin-top:12px; }
    .tienda-admin > .admin-panel:first-of-type { padding:23px 18px; }
    .tienda-form { display:block; }
    .tienda-field { margin-bottom:17px; }
    .tienda-submit { width:100%; }
    .tienda-admin-row { grid-template-columns:70px 1fr; }
    .tienda-admin-row .item-actions { grid-column:1/-1; }
    .tienda-admin-row img { width:70px; height:65px; }
}
</style>
<style>
/* Presentación editorial del formulario de productos. */
.tienda-form-panel {
    padding: 0 !important;
    overflow: hidden;
    border: 1px solid rgba(255,255,255,.16);
    background: #f5eee3;
    color: #38251f;
    box-shadow: 0 18px 45px rgba(0,0,0,.3);
}
.tienda-form-panel .tienda-form-heading {
    padding: 25px 30px 21px;
    background: #c6372e;
    color: #fff;
}
.tienda-form-panel .tienda-form-kicker { color: #ffe08a; }
.tienda-form-panel .tienda-form-heading .admin-h2 {
    color: #fff;
    font-size: 30px;
    letter-spacing: .2px;
}
.tienda-form-panel .tienda-form-mark {
    width: 46px;
    height: 46px;
    border-color: rgba(255,255,255,.6);
    color: #fff;
}
.tienda-form-panel .tienda-form-intro {
    max-width: 720px;
    margin: 0;
    padding: 22px 30px 0;
    color: #765c4b;
    font-size: 13px;
}
.tienda-form-panel .tienda-form {
    padding: 21px 30px 30px;
}
.tienda-form-panel .tienda-form .admin-label {
    color: #594238;
    font-size: 10px;
    letter-spacing: 1.4px;
}
.tienda-form-panel .tienda-form .admin-input {
    border: 1px solid #d7c5aa;
    border-radius: 4px;
    background: #fffaf3;
    color: #38251f;
    box-shadow: inset 0 1px 2px rgba(67,38,30,.05);
}
.tienda-form-panel .tienda-form .admin-input::placeholder { color: #aa9785; }
.tienda-form-panel .tienda-form .admin-input:focus {
    border-color: #397d27;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(57,125,39,.13);
}
.tienda-form-panel .tienda-help {
    color: #927e6b;
    font-size: 10px;
}
.tienda-form-panel .tienda-file {
    padding: 7px;
    border: 1px dashed #bca78c;
    border-radius: 4px;
    background: #fffaf3;
}
.tienda-form-panel .tienda-file .admin-input { background: transparent; }
.tienda-form-panel .tienda-file span { color: #927e6b; }
.tienda-form-panel .tienda-submit {
    min-width: 235px;
    margin-top: 5px;
    border-radius: 4px;
    background: #397d27;
    box-shadow: 0 8px 16px rgba(57,125,39,.2);
    font-size: 11px;
    letter-spacing: 1.1px;
}
.tienda-form-panel .tienda-submit:hover {
    background: #2d651f;
    transform: translateY(-1px);
}
@media (max-width:700px) {
    .tienda-form-panel .tienda-form-heading { padding: 22px 20px 18px; }
    .tienda-form-panel .tienda-form-intro { padding: 19px 20px 0; }
    .tienda-form-panel .tienda-form { padding: 18px 20px 22px; }
}
</style>
<style>
/* Composición centrada y paleta principal de la Tienda Cultural. */
body:has(.tienda-admin) {
    background:
        radial-gradient(circle at 15% 15%, rgba(198,55,46,.2), transparent 30%),
        radial-gradient(circle at 85% 25%, rgba(57,125,39,.18), transparent 32%),
        #201a18;
}
.tienda-admin {
    width: min(100% - 32px, 940px);
    max-width: 940px;
    margin: 0 auto;
    padding: 135px 0 80px;
}
.tienda-admin .admin-head-row {
    display: block;
    margin: 0 auto 34px;
    text-align: center;
}
.tienda-admin .admin-head-row > div { max-width: 700px; margin: auto; }
.tienda-admin .admin-eyebrow {
    display: block;
    color: #e5b85c;
    font-size: 11px;
    letter-spacing: 4px;
}
.tienda-admin .admin-title {
    margin: 10px 0 10px;
    color: #fff5e5;
    font-size: clamp(36px, 5vw, 58px);
    line-height: 1;
    text-shadow: 0 4px 18px rgba(0,0,0,.28);
}
.tienda-admin .admin-sub {
    max-width: 560px;
    margin: auto;
    color: rgba(255,245,229,.7);
    line-height: 1.6;
}
.tienda-admin .admin-count {
    display: inline-block;
    margin-top: 21px;
    border: 1px solid rgba(229,184,92,.5);
    border-radius: 99px;
    color: #fff5e5;
    background: rgba(198,55,46,.8);
    font-size: 11px;
    letter-spacing: 1px;
    text-transform: uppercase;
}
.tienda-admin .admin-alert {
    max-width: 720px;
    margin: 0 auto 22px;
    border: 1px solid rgba(229,184,92,.45);
    border-left: 4px solid #e5b85c;
    color: #fff5e5;
    background: rgba(57,125,39,.55);
    text-align: center;
}
.tienda-form-panel {
    width: min(100%, 820px);
    margin: 0 auto;
    border: 1px solid rgba(229,184,92,.55);
    border-radius: 18px;
    background: #fff8ed;
    box-shadow: 0 25px 65px rgba(0,0,0,.35);
}
.tienda-form-panel .tienda-form-heading {
    justify-content: center;
    padding: 30px 28px 24px;
    text-align: center;
    background: linear-gradient(135deg,#c6372e,#a82d27);
}
.tienda-form-panel .tienda-form-heading > div { display: grid; justify-items: center; }
.tienda-form-panel .tienda-form-heading .admin-h2 {
    margin-top: 8px;
    font-size: 32px;
}
.tienda-form-panel .tienda-form-kicker { color: #ffe5a4; }
.tienda-form-panel .tienda-form-mark { display: none; }
.tienda-form-panel .tienda-form-intro {
    max-width: 590px;
    margin: 0 auto;
    padding: 24px 28px 0;
    color: #765c4b;
    text-align: center;
}
.tienda-form-panel .tienda-form {
    max-width: 680px;
    margin: 0 auto;
    padding: 25px 28px 32px;
}
.tienda-form-panel .tienda-form .admin-label {
    color: #4e392d;
    text-align: center;
}
.tienda-form-panel .tienda-field .admin-input {
    min-height: 45px;
    text-align: center;
}
.tienda-form-panel .tienda-field textarea.admin-input {
    min-height: 125px;
    padding-top: 13px;
    text-align: left;
}
.tienda-form-panel .tienda-help { text-align: center; }
.tienda-form-panel .tienda-file { text-align: center; }
.tienda-form-panel .tienda-file .admin-input { text-align: left; }
.tienda-form-panel .tienda-file span { text-align: center; }
.tienda-form-panel .tienda-submit {
    justify-content: center;
    width: 100%;
    min-width: 0;
    border-radius: 8px;
    background: #397d27;
    box-shadow: 0 10px 22px rgba(57,125,39,.26);
}
.tienda-list {
    width: min(100%, 820px);
    margin: 28px auto 0;
    border: 1px solid rgba(229,184,92,.35);
    border-radius: 18px;
    background: rgba(43,31,26,.92);
}
.tienda-list > .admin-h2 {
    justify-content: center;
    margin-bottom: 18px;
    color: #fff5e5;
    text-align: center;
}
.tienda-admin-row {
    grid-template-columns: 82px 1fr auto;
    padding: 16px 0;
}
.tienda-admin-row img {
    width: 82px;
    height: 72px;
    border: 2px solid rgba(229,184,92,.35);
}
.tienda-admin-row strong { color: #ffe5a4; }
.tienda-admin-row .item-actions { justify-content: center; }
@media (max-width:700px) {
    .tienda-admin {
        width: min(100% - 24px, 560px);
        padding-top: 120px;
    }
    .tienda-form-panel .tienda-form-heading { padding: 26px 18px 21px; }
    .tienda-form-panel .tienda-form-heading .admin-h2 { font-size: 27px; }
    .tienda-form-panel .tienda-form-intro { padding-right: 18px; padding-left: 18px; }
    .tienda-form-panel .tienda-form { padding: 22px 18px 25px; }
    .tienda-list { padding: 18px; }
}
</style>
<style>
/* Presentacion limpia y moderna del formulario. */
.tienda-form-panel {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 12px 30px rgba(15,23,42,.08);
}
.tienda-form-panel .tienda-form-heading {
    justify-content: space-between;
    padding: 26px 32px;
    text-align: left;
    background: #fff;
    border-bottom: 1px solid #eef2f7;
}
.tienda-form-panel .tienda-form-heading > div {
    justify-items: start;
}
.tienda-form-panel .tienda-form-kicker {
    color: #64748b;
    font-size: 10px;
    letter-spacing: 1.5px;
}
.tienda-form-panel .tienda-form-heading .admin-h2 {
    margin-top: 7px;
    color: #172033;
    font-size: 26px;
    font-weight: 700;
}
.tienda-form-panel .tienda-form-mark {
    display: grid;
    flex: 0 0 auto;
    width: 38px;
    height: 38px;
    border: 1px solid #dbe4ef;
    border-radius: 9px;
    color: #2563eb;
    background: #eff6ff;
    font-size: 23px;
}
.tienda-form-panel .tienda-form-intro {
    max-width: none;
    padding: 24px 32px 0;
    color: #64748b;
    font-size: 13px;
    line-height: 1.6;
    text-align: left;
}
.tienda-form-panel .tienda-form {
    padding: 24px 32px 32px;
    gap: 20px 22px;
}
.tienda-form-panel .tienda-form .admin-label {
    display: block;
    margin-bottom: 7px;
    color: #334155;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0;
    text-align: left;
}
.tienda-form-panel .tienda-field .admin-input {
    width: 100%;
    min-height: 46px;
    padding: 11px 13px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    background: #f8fafc;
    color: #1e293b;
    font-size: 14px;
    text-align: left;
}
.tienda-form-panel .tienda-field textarea.admin-input {
    min-height: 118px;
    resize: vertical;
}
.tienda-form-panel .tienda-form .admin-input:focus {
    border-color: #2563eb;
    outline: 0;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(37,99,235,.12);
}
.tienda-form-panel .tienda-help {
    margin-top: 7px;
    color: #64748b;
    text-align: left;
}
.tienda-form-panel .tienda-file {
    padding: 8px;
    border: 1px dashed #b8c5d6;
    border-radius: 7px;
    background: #f8fafc;
}
.tienda-form-panel .tienda-file .admin-input {
    min-height: 38px;
    padding: 7px;
    border: 0;
    background: transparent;
}
.tienda-form-panel .tienda-file span {
    margin-left: 7px;
    color: #64748b;
    text-align: left;
}
.tienda-form-panel .tienda-submit {
    min-height: 46px;
    margin-top: 2px;
    border-radius: 7px;
    background: #2563eb;
    box-shadow: 0 6px 14px rgba(37,99,235,.2);
    font-size: 12px;
}
.tienda-form-panel .tienda-submit:hover {
    background: #1d4ed8;
}
body:has(.tienda-admin) {
    background: #f1f5f9;
}
.tienda-admin .admin-eyebrow {
    color: #2563eb;
}
.tienda-admin .admin-title {
    color: #172033;
    text-shadow: none;
}
.tienda-admin .admin-sub {
    color: #64748b;
}
.tienda-admin .admin-count {
    border-color: #bfdbfe;
    color: #1d4ed8;
    background: #dbeafe;
}
.tienda-admin .admin-alert {
    border-color: #bbf7d0;
    border-left-color: #16a34a;
    color: #166534;
    background: #f0fdf4;
}
.tienda-list {
    border-color: #e2e8f0;
    background: #fff;
    box-shadow: 0 12px 30px rgba(15,23,42,.06);
}
.tienda-list > .admin-h2 {
    color: #172033;
}
.tienda-admin-row {
    border-top-color: #e2e8f0;
}
.tienda-admin-row strong {
    color: #1e293b;
}
.tienda-admin-row p {
    color: #64748b;
}
.tienda-admin-row small {
    color: #2563eb;
}
@media (max-width:700px) {
    .tienda-form-panel .tienda-form-heading {
        padding: 22px 20px;
    }
    .tienda-form-panel .tienda-form-intro {
        padding: 19px 20px 0;
    }
    .tienda-form-panel .tienda-form {
        padding: 20px 20px 24px;
    }
}
</style>
<style>
/* Misma tipografia y lenguaje visual que Gestion de Musica. */
body:has(.tienda-admin) {
    background: var(--nikan-bg, #171717);
}
.tienda-admin {
    position: relative;
    z-index: 2;
    width: auto;
    max-width: 1100px;
    margin: 0 auto;
    padding: 120px 24px 60px;
    color: #fff;
}
.tienda-admin .admin-head-row {
    display: block;
    margin: 0 0 24px;
    text-align: left;
}
.tienda-admin .admin-head-row > div {
    max-width: none;
    margin: 0;
}
.tienda-admin .admin-eyebrow {
    display: none;
}
.tienda-admin .admin-title,
.tienda-admin .admin-h2 {
    font-family: 'Felthgothic', cursive;
    font-style: oblique;
    font-weight: bold;
}
.tienda-admin .admin-title {
    margin: 0 0 4px;
    color: #fff;
    font-size: 40px;
    line-height: 1.1;
    text-shadow: none;
}
.tienda-admin .admin-sub {
    max-width: none;
    margin: 0 0 24px;
    color: #fff;
    font-family: 'Montserrat', sans-serif;
    font-size: 14px;
    line-height: normal;
    opacity: .9;
}
.tienda-admin .admin-count {
    display: inline-block;
    margin: 0;
    padding: 10px 14px;
    border: 0;
    border-radius: 10px;
    color: #fff;
    background: rgba(255,255,255,.2);
    font-family: 'Montserrat', sans-serif;
    font-size: 12px;
    letter-spacing: 0;
    text-transform: none;
}
.tienda-admin .admin-alert {
    max-width: none;
    margin: 0 0 18px;
    padding: 12px 15px;
    border: 0;
    border-left: 4px solid #2e8b57;
    border-radius: 0;
    color: #fff;
    background: rgba(46,139,87,.35);
    font-family: 'Montserrat', sans-serif;
    text-align: left;
}
.tienda-form-panel,
.tienda-list {
    width: auto;
    border: 0;
    border-radius: 14px;
    background: rgba(20,20,20,.75);
    box-shadow: 0 10px 30px rgba(0,0,0,.35);
    color: #fff;
}
.tienda-form-panel {
    padding: 28px 30px 30px !important;
}
.tienda-form-panel .tienda-form-heading {
    padding: 0;
    border: 0;
    background: transparent;
}
.tienda-form-panel .tienda-form-heading > div {
    display: block;
}
.tienda-form-panel .tienda-form-kicker {
    display: none;
}
.tienda-form-panel .tienda-form-heading .admin-h2 {
    margin: 0 0 18px;
    color: #fff;
    font-size: 24px;
}
.tienda-form-panel .tienda-form-mark {
    display: none;
}
.tienda-form-panel .tienda-form-intro {
    max-width: none;
    margin: 0 0 20px;
    padding: 0;
    color: #fff;
    font-family: 'Montserrat', sans-serif;
    font-size: 14px;
    line-height: normal;
    opacity: .9;
    text-align: left;
}
.tienda-form-panel .tienda-form {
    padding: 0;
    gap: 22px 24px;
}
.tienda-form-panel .tienda-form .admin-label {
    display: block;
    margin: 14px 0 7px;
    color: #fff;
    font-family: 'Montserrat', sans-serif;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1px;
    text-align: left;
    text-transform: uppercase;
    opacity: .9;
}
.tienda-form-panel .tienda-field .admin-input,
.tienda-form-panel .tienda-file {
    box-sizing: border-box;
    border: 1px solid rgba(255,255,255,.25);
    border-radius: 8px;
    background: rgba(255,255,255,.1);
    color: #fff;
    font-family: 'Montserrat', sans-serif;
}
.tienda-form-panel .tienda-field .admin-input {
    min-height: 0;
    padding: 10px 12px;
    font-size: 14px;
}
.tienda-form-panel .tienda-field .admin-input::placeholder {
    color: rgba(255,255,255,.65);
}
.tienda-form-panel .tienda-form .admin-input:focus {
    border-color: #fff;
    background: rgba(255,255,255,.2);
    box-shadow: none;
}
.tienda-form-panel .tienda-field textarea.admin-input {
    min-height: 115px;
}
.tienda-form-panel .tienda-help,
.tienda-form-panel .tienda-file span {
    color: #fff;
    font-family: 'Montserrat', sans-serif;
    opacity: .65;
}
.tienda-form-panel .tienda-help {
    text-align: left;
}
.tienda-form-panel .tienda-file {
    padding: 8px;
}
.tienda-form-panel .tienda-file .admin-input {
    border: 0;
    background: transparent;
}
.tienda-form-panel .tienda-file span {
    margin: 5px 4px 0;
    text-align: left;
}
.tienda-form-panel .tienda-submit {
    min-height: 0;
    margin-top: 20px;
    border-radius: 0;
    background: #2e8b57;
    box-shadow: none;
    font-family: 'Montserrat', sans-serif;
}
.tienda-form-panel .tienda-submit:hover {
    background: #37a86a;
}
.tienda-list {
    margin-top: 24px;
    padding: 28px 30px 30px;
}
.tienda-list > .admin-h2 {
    justify-content: initial;
    margin: 0 0 20px;
    color: #fff;
    font-size: 24px;
    text-align: left;
}
.tienda-admin-row {
    gap: 20px;
    padding: 18px 4px;
    border-top-color: rgba(255,255,255,.13);
}
.tienda-admin-row strong {
    color: #fff;
    font-family: 'Montserrat', sans-serif;
}
.tienda-admin-row p,
.tienda-admin-row small {
    font-family: 'Montserrat', sans-serif;
}
.tienda-admin-row p {
    color: #fff;
    opacity: .7;
}
.tienda-admin-row small {
    color: #ffe9b3;
}
.tienda-delete-box {
    text-align: center;
}
.tienda-delete-icon {
    display: grid;
    place-items: center;
    width: 44px;
    height: 44px;
    margin: 0 auto 14px;
    border: 1px solid rgba(192,57,43,.6);
    border-radius: 50%;
    color: #fff;
    background: rgba(192,57,43,.25);
    font-size: 25px;
    font-weight: 700;
}
.tienda-delete-box .admin-h2 {
    margin-bottom: 10px;
}
.tienda-delete-box p {
    margin: 0;
    color: rgba(255,255,255,.78);
    font-family: 'Montserrat', sans-serif;
    font-size: 13px;
    line-height: 1.6;
}
.tienda-delete-box p strong {
    color: #fff;
}
.tienda-delete-box .modal-actions {
    justify-content: center;
    margin-top: 24px;
}
.tienda-delete-box .btn {
    border-radius: 0;
    font-family: 'Montserrat', sans-serif;
}
@media (max-width:700px) {
    .tienda-admin {
        width: auto;
        padding: 120px 12px 60px;
    }
    .tienda-form-panel,
    .tienda-list {
        padding: 22px 20px 24px !important;
    }
}
</style>
<script>
function editarProducto(producto) { document.getElementById('edit_id').value=producto.id; document.getElementById('edit_titulo').value=producto.titulo; document.getElementById('edit_descripcion').value=producto.descripcion; document.getElementById('edit_whatsapp').value=producto.whatsapp; document.getElementById('tiendaEditModal').style.display='grid'; }
function cerrarTiendaModal() { document.getElementById('tiendaEditModal').style.display='none'; }
function abrirEliminarTienda(id, titulo) { document.getElementById('tiendaDeleteName').textContent = titulo; document.getElementById('tiendaDeleteConfirm').href = 'tienda_actions.php?action=delete&id=' + encodeURIComponent(id); document.getElementById('tiendaDeleteModal').style.display = 'flex'; }
function cerrarEliminarTienda() { document.getElementById('tiendaDeleteModal').style.display='none'; document.getElementById('tiendaDeleteConfirm').removeAttribute('href'); }
</script>
