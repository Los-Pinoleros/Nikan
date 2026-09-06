<?php
require_once __DIR__ . '/auth.php';
require_admin();

$pdo = getDB();
$secciones = $pdo->query('SELECT * FROM musica_secciones ORDER BY orden ASC')->fetchAll();

$obras = [];
if ($secciones) {
    $in = implode(',', array_map('intval', array_column($secciones, 'id')));
    $obras = $pdo->query("SELECT * FROM musica_obras WHERE seccion_id IN ($in) ORDER BY orden ASC")->fetchAll();
}

$autores = $pdo->query('SELECT id, nombre FROM autores ORDER BY nombre ASC')->fetchAll();

include __DIR__ . '/components/header.php';
?>

<div class="admin-body">
    <h1 class="admin-title">Gestión de Música</h1>
    <p class="admin-sub">Administra las secciones y las piezas musicales del área de música.</p>

    <div class="admin-layout">
        <!-- SECCIONES -->
        <div class="admin-panel">
            <h2 class="admin-h2">Secciones</h2>
            <button class="btn btn-green" onclick="seccionEditar()">+ Nueva sección</button>
            <div id="seccionList" class="admin-list">
                <?php foreach ($secciones as $s): ?>
                    <div class="admin-item" data-id="<?php echo $s['id']; ?>">
                        <div class="item-main">
                            <strong><?php echo htmlspecialchars($s['titulo']); ?></strong>
                            <span class="item-sub"><?php echo htmlspecialchars($s['descripcion']); ?></span>
                        </div>
                        <div class="item-actions">
                            <button class="btn btn-small" onclick="seccionEditar(<?php echo $s['id']; ?>)">Editar</button>
                            <button class="btn btn-small btn-red" onclick="seccionEliminar(<?php echo $s['id']; ?>)">Eliminar</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- OBRAS -->
        <div class="admin-panel">
            <h2 class="admin-h2">Obras</h2>
            <div class="obra-filtro">
                <div class="obra-filtro-select">
                    <label class="admin-label" for="selectSeccion">Sección</label>
                    <select id="selectSeccion" class="admin-input" onchange="filtrarObras()">
                        <option value="">-- Todas --</option>
                        <?php foreach ($secciones as $s): ?>
                            <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['titulo']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn btn-green" onclick="obraEditar()">+ Nueva obra</button>
            </div>
            <div id="obraList" class="admin-list" data-per="4">
                <?php foreach ($obras as $o): ?>
                    <div class="admin-item obra-item" data-seccion="<?php echo $o['seccion_id']; ?>">
                        <div class="obra-thumb">
                            <?php if ($o['imagen']): ?>
                                <img src="../<?php echo htmlspecialchars($o['imagen']); ?>" alt="">
                            <?php else: ?>
                                <span>Sin img</span>
                            <?php endif; ?>
                        </div>
                        <div class="item-main">
                            <strong><?php echo htmlspecialchars($o['titulo']); ?></strong>
                            <span class="item-sub"><?php echo htmlspecialchars($o['autor']); ?> · <?php echo htmlspecialchars($o['genero']); ?> · <?php echo htmlspecialchars($o['anio']); ?></span>
                        </div>
                        <div class="item-actions">
                            <button class="btn btn-small" onclick="obraEditar(<?php echo $o['id']; ?>)">Editar</button>
                            <button class="btn btn-small btn-red" onclick="obraEliminar(<?php echo $o['id']; ?>)">Eliminar</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="pag-nav" data-target="obraList">
                <button type="button" class="pag-btn pag-prev">‹ Anterior</button>
                <span class="pag-info">Página 1 de 1</span>
                <button type="button" class="pag-btn pag-next">Siguiente ›</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal sección -->
<div id="modalSeccion" class="modal" style="display:none;">
    <div class="modal-box">
        <h3 id="modalSeccionTitle" class="admin-h2">Nueva sección</h3>
        <form id="formSeccion" method="post" action="mus_actions.php">
            <input type="hidden" name="action" value="seccion_save">
            <input type="hidden" name="id" id="seccion_id">
            <label class="admin-label">Título</label>
            <input name="titulo" id="seccion_titulo" class="admin-input" required>
            <label class="admin-label">Descripción</label>
            <textarea name="descripcion" id="seccion_desc" class="admin-input"></textarea>
        </form>
        <div class="modal-actions">
            <button class="btn" onclick="cerrar('modalSeccion')">Cancelar</button>
            <button class="btn btn-green" onclick="guardarSeccion()">Guardar</button>
        </div>
    </div>
</div>

<!-- Modal obra -->
<div id="modalObra" class="modal" style="display:none;">
    <div class="modal-box">
        <h3 id="modalObraTitle" class="admin-h2">Nueva obra</h3>
        <form id="formObra" method="post" action="mus_actions.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="obra_save">
            <input type="hidden" name="id" id="obra_id">
            <label class="admin-label">Sección</label>
            <select name="seccion_id" id="obra_seccion" class="admin-input"></select>
            <label class="admin-label">Título</label>
            <input name="titulo" id="obra_titulo" class="admin-input" required>
            <label class="admin-label">Autor/a</label>
            <select name="autor_id" id="obra_autor" class="admin-input" required>
                <option value="">-- Selecciona un autor --</option>
            </select>
            <span class="autor-aviso">¿Todavía no existe? Créalo en <a href="autores.php">Autores</a>.</span>
            <label class="admin-label">Género / estilo</label>
            <input name="genero" id="obra_genero" class="admin-input">
            <label class="admin-label">Año</label>
            <input name="anio" id="obra_anio" class="admin-input">
            <label class="admin-label">Imagen</label>
            <input type="hidden" name="imagen" id="obra_imagen">
            <input type="file" name="imagen_file" id="obra_file" class="admin-input" accept="image/*">
            <div id="obra_img_preview" style="margin-top:8px;"></div>
            <label class="admin-label">Audio: enlace (URL)</label>
            <input name="audio" id="obra_audio" class="admin-input" placeholder="https://... (YouTube / SoundCloud / MP3)">
            <label class="admin-label">Audio: subir archivo (mp3 u ogg)</label>
            <input type="file" name="audio_file" id="obra_audio_file" class="admin-input" accept=".mp3,.ogg,audio/mpeg,audio/ogg">
            <input type="hidden" name="audio_src" id="obra_audio_src" value="">
            <div id="obra_audio_preview" style="margin-top:8px;"></div>
            <label class="admin-label">Descripción breve</label>
            <textarea name="descripcion" id="obra_desc" class="admin-input"></textarea>
            <label class="admin-label">Detalle / descripción completa</label>
            <textarea name="detalle" id="obra_detalle" class="admin-input admin-textarea"></textarea>
        </form>
        <div class="modal-actions">
            <button class="btn" onclick="cerrar('modalObra')">Cancelar</button>
            <button class="btn btn-green" onclick="guardarObra()">Guardar</button>
        </div>
    </div>
</div>

<style>
    .admin-body { position: relative; z-index: 2; max-width: 1100px; margin: 0 auto; padding: 120px 24px 60px; color: #fff; }
    .admin-title { font: oblique bold 100% 'Felthgothic', cursive; font-size: 40px; margin-bottom: 4px; }
    .admin-sub { font-size: 14px; opacity: 0.9; margin-bottom: 24px; }
    .admin-layout { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    .admin-panel { background: rgba(20, 20, 20, 0.75); border-radius: 14px; padding: 22px; box-shadow: 0 10px 30px rgba(0,0,0,0.35); }
    .admin-h2 { font: oblique bold 100% 'Felthgothic', cursive; font-size: 24px; margin-bottom: 14px; }
    .admin-label { display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin: 12px 0 5px; opacity: 0.9; }
    .admin-input { width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.25); background: rgba(255,255,255,0.1); color: #fff; font-family: 'Montserrat', sans-serif; margin-bottom: 4px; }
    .admin-input:focus { outline: none; border-color: #fff; }
    .admin-input option { background: #fff; color: #333; }
    .admin-input[type="file"] { padding: 8px; }
    .admin-input[type="file"]::file-selector-button {
        background: rgba(255,255,255,0.2);
        color: #fff;
        border: 1px solid rgba(255,255,255,0.5);
        border-radius: 6px;
        padding: 6px 12px;
        font-family: 'Montserrat', sans-serif;
        font-size: 12px;
        cursor: pointer;
    }
    .admin-list { margin-top: 14px; display: flex; flex-direction: column; gap: 10px; }
    .obra-filtro { display: flex; align-items: flex-end; gap: 12px; margin-top: 12px; }
    .obra-filtro-select { flex: 1; min-width: 0; }
    .obra-filtro .btn { white-space: nowrap; margin-bottom: 4px; }
    @media (max-width: 600px) { .obra-filtro { flex-direction: column; align-items: stretch; } }
    .admin-item { display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.1); border-radius: 10px; padding: 12px 14px; }
    .item-main { flex: 1; min-width: 0; }
    .item-main strong { display: block; font-size: 15px; }
    .item-sub { display: block; font-size: 12px; opacity: 0.8; margin-top: 2px; }
    .item-actions { display: flex; gap: 6px; }
    .obra-thumb { width: 44px; height: 44px; border-radius: 8px; overflow: hidden; background: rgba(0,0,0,0.4); flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 10px; }
    .obra-thumb img { width: 100%; height: 100%; object-fit: cover; }
    .btn { border: none; border-radius: 0; padding: 10px 16px; font-family: 'Montserrat', sans-serif; font-weight: 700; font-size: 13px; cursor: pointer; background: rgba(255,255,255,0.2); color: #fff; }
    .btn:hover { background: rgba(255,255,255,0.3); }
    .btn-green { background: #2e8b57; }
    .btn-green:hover { background: #37a86a; }
    .btn-red { background: #c0392b; }
    .btn-red:hover { background: #d54838; }
    .btn-small { padding: 6px 10px; font-size: 12px; }
    .modal { position: fixed; inset: 0; background: rgba(0,0,0,0.6); z-index: 200; display: flex; align-items: center; justify-content: center; padding: 20px; }
    .modal-box {
        background: rgb(var(--nikan-bg-rgb));
        border-radius: 16px;
        padding: 32px 28px;
        width: 100%;
        max-width: 460px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
        color: #fff;
    }
    .modal-box .admin-h2 { color: #fff; text-align: center; font-size: 26px; }
    .modal-box .admin-label {
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin: 16px 0 7px;
        opacity: 1;
    }
    .modal-box .admin-input {
        padding: 13px 15px;
        border: 2px solid rgba(255, 255, 255, 0.4);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.12);
        color: #fff;
        font-size: 15px;
        margin-bottom: 4px;
    }
    .modal-box .admin-input:focus {
        border-color: #fff;
        background: rgba(255, 255, 255, 0.2);
    }
    .modal-box .admin-input::placeholder { color: rgba(255, 255, 255, 0.65); }
    .modal-box .admin-input option { background: #fff; color: #333; }
    .modal-box .autor-aviso { display: block; font-size: 12px; color: #ffe9b3; margin-top: 4px; }
    .modal-box .autor-aviso a { color: #fff; font-weight: 700; }
    .modal-box .admin-input[type="file"] {
        background: rgba(255, 255, 255, 0.12);
    }
    .modal-box .admin-input[type="file"]::file-selector-button {
        background: rgba(255,255,255,0.25);
        color: #fff;
        border: 1px solid rgba(255,255,255,0.5);
        border-radius: 6px;
        padding: 6px 12px;
        font-family: 'Montserrat', sans-serif;
        font-size: 12px;
        cursor: pointer;
    }
    .modal-box .admin-textarea { min-height: 120px; resize: vertical; }
    .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; }
    .modal-box .btn { background: rgba(255, 255, 255, 0.25); color: #fff; font-weight: 700; }
    .modal-box .btn:hover { background: rgba(255, 255, 255, 0.35); }
    .modal-box .btn-green {
        background: #fff;
        color: var(--nikan-bg);
        font-weight: 800;
        letter-spacing: 1px;
    }
    .modal-box .btn-green:hover { background: #f2f2f2; }
    .obra-item { flex-wrap: wrap; }
    .audio-actual { display: inline-flex; align-items: center; gap: 10px; font-size: 12px; color: #ffe9b3; background: rgba(0,0,0,0.25); border-radius: 8px; padding: 8px 12px; }
    .audio-actual strong { color: #fff; word-break: break-all; }
    .pag-nav { display: flex; align-items: center; justify-content: center; gap: 12px; margin-top: 18px; }
    .pag-btn { border: 1px solid rgba(255,255,255,0.3); background: rgba(255,255,255,0.12); color: #fff; border-radius: 8px; padding: 7px 14px; font-family: 'Montserrat', sans-serif; font-size: 12px; font-weight: 700; cursor: pointer; transition: background .2s; }
    .pag-btn:hover:not(:disabled) { background: rgba(255,255,255,0.25); }
    .pag-btn:disabled { opacity: 0.4; cursor: default; }
    .pag-info { font-size: 12px; opacity: 0.85; }
    @media (max-width: 800px) { .admin-layout { grid-template-columns: 1fr; } }
</style>

<script>
let secciones = <?php echo json_encode($secciones); ?>;

function cerrar(id) { document.getElementById(id).style.display = 'none'; }
function abrir(id) { document.getElementById(id).style.display = 'flex'; }

async function postForm(url, formData) {
    const res = await fetch(url, { method: 'POST', body: formData });
    return res.json();
}

/* Secciones */
function seccionEditar(id) {
    document.getElementById('seccion_id').value = id || '';
    document.getElementById('formSeccion').reset();
    if (id) {
        const s = secciones.find(x => x.id == id);
        document.getElementById('seccion_titulo').value = s.titulo;
        document.getElementById('seccion_desc').value = s.descripcion || '';
        document.getElementById('modalSeccionTitle').textContent = 'Editar sección';
    } else {
        document.getElementById('modalSeccionTitle').textContent = 'Nueva sección';
    }
    abrir('modalSeccion');
}

async function guardarSeccion() {
    const fd = new FormData(document.getElementById('formSeccion'));
    const r = await postForm('mus_actions.php', fd);
    if (r.ok) location.reload(); else alert(r.msg);
}

async function seccionEliminar(id) {
    if (!confirm('¿Eliminar esta sección y todas sus obras?')) return;
    const fd = new FormData();
    fd.append('action', 'seccion_delete');
    fd.append('id', id);
    const r = await postForm('mus_actions.php', fd);
    if (r.ok) location.reload(); else alert(r.msg);
}

/* Obras */
const obras = <?php echo json_encode($obras); ?>;
const autores = <?php echo json_encode($autores); ?>;

function llenarSelectSeccion() {
    const sel = document.getElementById('obra_seccion');
    sel.innerHTML = '';
    secciones.forEach(s => {
        const op = document.createElement('option');
        op.value = s.id; op.textContent = s.titulo;
        sel.appendChild(op);
    });
}

function llenarSelectAutores() {
    const sel = document.getElementById('obra_autor');
    sel.innerHTML = '<option value="">-- Selecciona un autor --</option>';
    autores.forEach(a => {
        const op = document.createElement('option');
        op.value = a.id; op.textContent = a.nombre;
        sel.appendChild(op);
    });
}

function filtrarObras() {
    obraCurrentPage = 0;
    renderObras();
}

let obraCurrentPage = 0;
function renderObras() {
    const f = document.getElementById('selectSeccion').value;
    const list = document.getElementById('obraList');
    const items = Array.prototype.slice.call(list.children);
    const visible = items.filter(el => f === '' || el.dataset.seccion == f);
    const per = 4;
    const pages = Math.max(1, Math.ceil(visible.length / per));
    if (obraCurrentPage >= pages) obraCurrentPage = pages - 1;

    items.forEach(el => { el.style.display = 'none'; });
    visible.slice(obraCurrentPage * per, obraCurrentPage * per + per).forEach(el => { el.style.display = ''; });

    const nav = document.querySelector('.pag-nav[data-target="obraList"]');
    if (!nav) return;
    const info = nav.querySelector('.pag-info');
    const prev = nav.querySelector('.pag-prev');
    const next = nav.querySelector('.pag-next');
    info.textContent = 'Página ' + (obraCurrentPage + 1) + ' de ' + pages;
    prev.disabled = obraCurrentPage === 0;
    next.disabled = obraCurrentPage === pages - 1;

    function jump(delta) {
        obraCurrentPage = Math.min(pages - 1, Math.max(0, obraCurrentPage + delta));
        renderObras();
    }
    prev.onclick = function () { jump(-1); };
    next.onclick = function () { jump(1); };
}

function obraEditar(id) {
    llenarSelectSeccion();
    llenarSelectAutores();
    document.getElementById('obra_id').value = id || '';
    document.getElementById('formObra').reset();
    document.getElementById('obra_img_preview').innerHTML = '';
    if (id) {
        const o = obras.find(x => x.id == id);
        document.getElementById('obra_seccion').value = o.seccion_id;
        document.getElementById('obra_autor').value = o.autor_id || '';
        document.getElementById('obra_titulo').value = o.titulo;
        document.getElementById('obra_genero').value = o.genero || '';
        document.getElementById('obra_anio').value = o.anio || '';
        document.getElementById('obra_audio').value = o.audio || '';
        document.getElementById('obra_audio_file').value = '';
        document.getElementById('obra_audio_src').value = o.audio_file || '';
        if (o.audio_file) {
            document.getElementById('obra_audio_preview').innerHTML = '<span class="audio-actual">Archivo actual: <strong>' + o.audio_file + '</strong> <button type="button" class="btn btn-small btn-red" onclick="quitarAudio()">Quitar</button></span>';
        } else {
            document.getElementById('obra_audio_preview').innerHTML = '';
        }
        document.getElementById('obra_desc').value = o.descripcion || '';
        document.getElementById('obra_detalle').value = o.detalle || '';
        document.getElementById('obra_imagen').value = o.imagen || '';
        if (o.imagen) {
            document.getElementById('obra_img_preview').innerHTML = '<img src="../' + o.imagen + '" style="max-width:120px;border-radius:6px;">';
        }
        document.getElementById('modalObraTitle').textContent = 'Editar obra';
    } else {
        document.getElementById('obra_imagen').value = '';
        document.getElementById('obra_audio').value = '';
        document.getElementById('obra_audio_file').value = '';
        document.getElementById('obra_audio_preview').innerHTML = '';
        document.getElementById('modalObraTitle').textContent = 'Nueva obra';
    }
    abrir('modalObra');
}

async function quitarAudio() {
    document.getElementById('obra_audio_src').value = '';
    document.getElementById('obra_audio_preview').innerHTML = '';
}

async function guardarObra() {
    const fd = new FormData(document.getElementById('formObra'));
    const r = await postForm('mus_actions.php', fd);
    if (r.ok) location.reload(); else alert(r.msg);
}

async function obraEliminar(id) {
    if (!confirm('¿Eliminar esta obra?')) return;
    const fd = new FormData();
    fd.append('action', 'obra_delete');
    fd.append('id', id);
    const r = await postForm('mus_actions.php', fd);
    if (r.ok) location.reload(); else alert(r.msg);
}

renderObras();
</script>

</body>
</html>