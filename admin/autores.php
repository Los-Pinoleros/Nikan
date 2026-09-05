<?php
require_once __DIR__ . '/auth.php';
require_admin();

$pdo = getDB();
$autores = $pdo->query('SELECT * FROM autores ORDER BY nacimiento ASC, nombre ASC')->fetchAll();

// Conteo de obras ligadas por autor para mostrar
$counts = [];
foreach (['arte_obras', 'lit_obras', 'poe_obras'] as $t) {
    foreach ($pdo->query("SELECT autor_id, COUNT(*) c FROM $t WHERE autor_id IS NOT NULL GROUP BY autor_id") as $r) {
        $counts[$r['autor_id']] = ($counts[$r['autor_id']] ?? 0) + (int)$r['c'];
    }
}

include __DIR__ . '/components/header.php';
?>

<div class="admin-body">
    <h1 class="admin-title">Gestión de Autores</h1>
    <p class="admin-sub">Crea los autores primero: así podrás ligarlos a las obras de Arte, Literatura y Poesía.</p>

    <button class="btn btn-green" style="margin-bottom:20px;" onclick="autorEditar()">+ Nuevo autor</button>

    <div class="autor-grid" id="autorGrid">
        <?php foreach ($autores as $a): ?>
            <div class="autor-card">
                <div class="autor-retrato">
                    <?php if ($a['retrato']): ?>
                        <img src="../<?php echo htmlspecialchars($a['retrato']); ?>" alt="Retrato de <?php echo htmlspecialchars($a['nombre']); ?>">
                    <?php else: ?>
                        <span>Sin retrato</span>
                    <?php endif; ?>
                </div>
                <div class="autor-info">
                    <strong class="autor-nombre"><?php echo htmlspecialchars($a['nombre']); ?></strong>
                    <span class="autor-sub">
                        <?php echo htmlspecialchars($a['lugar']); ?>
                        <?php if (!empty($a['nacimiento'])): ?> · <?php echo htmlspecialchars($a['nacimiento']); ?><?php if (!empty($a['fallecimiento'])): ?>–<?php echo htmlspecialchars($a['fallecimiento']); ?><?php endif; ?><?php endif; ?>
                    </span>
                    <span class="autor-sub">Obras ligadas: <?php echo $counts[$a['id']] ?? 0; ?></span>
                </div>
                <div class="item-actions">
                    <button class="btn btn-small" onclick="autorEditar(<?php echo $a['id']; ?>)">Editar</button>
                    <button class="btn btn-small btn-red" onclick="autorEliminar(<?php echo $a['id']; ?>)">Eliminar</button>
                </div>
            </div>
        <?php endforeach; ?>
        <?php if (!$autores): ?>
            <p style="color:#fff;opacity:0.8;">Aún no hay autores registrados.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Modal autor -->
<div id="modalAutor" class="modal" style="display:none;">
    <div class="modal-box modal-box--wide">
        <h3 id="modalAutorTitle" class="admin-h2">Nuevo autor</h3>
        <form id="formAutor" method="post" action="autor_actions.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" id="autor_id">

            <label class="admin-label">Nombre *</label>
            <input name="nombre" id="autor_nombre" class="admin-input" required>

            <div class="modal-row">
                <div>
                    <label class="admin-label">Nacimiento (año)</label>
                    <input name="nacimiento" id="autor_nacimiento" class="admin-input" placeholder="Ej. 1867">
                </div>
                <div>
                    <label class="admin-label">Fallecimiento (año)</label>
                    <input name="fallecimiento" id="autor_fallecimiento" class="admin-input" placeholder="Ej. 1916">
                </div>
            </div>

            <div class="modal-row">
                <div>
                    <label class="admin-label">Lugar</label>
                    <input name="lugar" id="autor_lugar" class="admin-input" placeholder="Ciudad, país">
                </div>
                <div>
                    <label class="admin-label">Época</label>
                    <input name="epoca" id="autor_epoca" class="admin-input" placeholder="Ej. Modernismo, Siglo XX">
                </div>
            </div>

            <label class="admin-label">Biografía breve</label>
            <textarea name="bio" id="autor_bio" class="admin-input"></textarea>

            <label class="admin-label">Trayectoria</label>
            <textarea name="trayectoria" id="autor_trayectoria" class="admin-input"></textarea>

            <label class="admin-label">Estilo y aportes</label>
            <textarea name="estilo_aportes" id="autor_estilo" class="admin-input"></textarea>

            <div class="modal-row">
                <div>
                    <label class="admin-label">Foto / retrato</label>
                    <input type="hidden" name="retrato" id="autor_retrato">
                    <input type="file" name="retrato_file" id="autor_retrato_file" class="admin-input" accept="image/*">
                    <div id="autor_retrato_preview" style="margin-top:8px;"></div>
                </div>
                <div>
                    <label class="admin-label">Obra representativa</label>
                    <input type="hidden" name="obra_representativa" id="autor_obra_rep">
                    <input type="file" name="obra_rep_file" id="autor_obra_rep_file" class="admin-input" accept="image/*">
                    <div id="autor_obra_preview" style="margin-top:8px;"></div>
                </div>
            </div>
        </form>
        <div class="modal-actions">
            <button class="btn" onclick="cerrar('modalAutor')">Cancelar</button>
            <button class="btn btn-green" onclick="guardarAutor()">Guardar</button>
        </div>
    </div>
</div>

<style>
    .admin-body { position: relative; z-index: 2; max-width: 1100px; margin: 0 auto; padding: 120px 24px 60px; color: #fff; }
    .admin-title { font: oblique bold 100% 'Felthgothic', cursive; font-size: 40px; margin-bottom: 4px; }
    .admin-sub { font-size: 14px; opacity: 0.9; margin-bottom: 24px; }
    .admin-h2 { font: oblique bold 100% 'Felthgothic', cursive; font-size: 24px; margin-bottom: 14px; }
    .admin-label { display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin: 12px 0 5px; opacity: 0.9; }
    .admin-input { width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.25); background: rgba(255,255,255,0.1); color: #fff; font-family: 'Montserrat', sans-serif; margin-bottom: 4px; }
    .admin-input:focus { outline: none; border-color: #fff; }
    .admin-input::placeholder { color: rgba(255,255,255,0.5); }
    .admin-input option { background: #fff; color: #333; }
    .admin-input[type="file"]::file-selector-button {
        background: rgba(255,255,255,0.2);
        color: #fff;
        border: 1px solid rgba(255,255,255,0.5);
        border-radius: 0;
        padding: 6px 12px;
        font-family: 'Montserrat', sans-serif;
        font-size: 12px;
        cursor: pointer;
    }
    .autor-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 18px; }
    .autor-card {
        background: rgba(20, 20, 20, 0.75);
        border-radius: 14px;
        padding: 18px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.35);
        display: flex;
        gap: 14px;
        align-items: center;
        flex-wrap: wrap;
    }
    .autor-retrato { width: 64px; height: 64px; border-radius: 50%; overflow: hidden; background: rgba(255,255,255,0.1); flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 10px; color: #fff; }
    .autor-retrato img { width: 100%; height: 100%; object-fit: cover; }
    .autor-info { flex: 1; min-width: 0; }
    .autor-nombre { display: block; font-size: 16px; }
    .autor-sub { display: block; font-size: 12px; opacity: 0.8; margin-top: 2px; }
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
        max-width: 560px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
        color: #fff;
    }
    .modal-box--wide { max-width: 700px; }
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
    .modal-box .admin-input:focus { border-color: #fff; background: rgba(255, 255, 255, 0.2); }
    .modal-box .admin-input::placeholder { color: rgba(255, 255, 255, 0.65); }
    .modal-box .admin-input option { background: #fff; color: #333; }
    .modal-box .admin-input[type="file"] { background: rgba(255, 255, 255, 0.12); }
    .modal-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    @media (max-width: 560px) { .modal-row { grid-template-columns: 1fr; } }
    .modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 24px; }
    .modal-box .btn { background: rgba(255, 255, 255, 0.25); color: #fff; font-weight: 700; }
    .modal-box .btn:hover { background: rgba(255, 255, 255, 0.35); }
    .modal-box .btn-green { background: #fff; color: var(--nikan-bg); font-weight: 800; letter-spacing: 1px; }
    .modal-box .btn-green:hover { background: #f2f2f2; }
</style>

<script>
const autores = <?php echo json_encode(array_map(function ($a) {
    return ['id' => $a['id'], 'nombre' => $a['nombre'], 'lugar' => $a['lugar'], 'nacimiento' => $a['nacimiento'],
        'fallecimiento' => $a['fallecimiento'], 'epoca' => $a['epoca'], 'bio' => $a['bio'],
        'trayectoria' => $a['trayectoria'], 'estilo_aportes' => $a['estilo_aportes'],
        'retrato' => $a['retrato'], 'obra_representativa' => $a['obra_representativa']];
}, $autores)); ?>;

function cerrar(id) { document.getElementById(id).style.display = 'none'; }
function abrir(id) { document.getElementById(id).style.display = 'flex'; }

async function postForm(url, formData) {
    const res = await fetch(url, { method: 'POST', body: formData });
    return res.json();
}

function autorEditar(id) {
    document.getElementById('autor_id').value = id || '';
    document.getElementById('formAutor').reset();
    document.getElementById('autor_retrato_preview').innerHTML = '';
    document.getElementById('autor_obra_preview').innerHTML = '';
    if (id) {
        const a = autores.find(x => x.id == id);
        document.getElementById('autor_nombre').value = a.nombre;
        document.getElementById('autor_nacimiento').value = a.nacimiento || '';
        document.getElementById('autor_fallecimiento').value = a.fallecimiento || '';
        document.getElementById('autor_lugar').value = a.lugar || '';
        document.getElementById('autor_epoca').value = a.epoca || '';
        document.getElementById('autor_bio').value = a.bio || '';
        document.getElementById('autor_trayectoria').value = a.trayectoria || '';
        document.getElementById('autor_estilo').value = a.estilo_aportes || '';
        document.getElementById('autor_retrato').value = a.retrato || '';
        document.getElementById('autor_obra_rep').value = a.obra_representativa || '';
        if (a.retrato) document.getElementById('autor_retrato_preview').innerHTML = '<img src="../' + a.retrato + '" style="max-width:100px;border-radius:6px;">';
        if (a.obra_representativa) document.getElementById('autor_obra_preview').innerHTML = '<img src="../' + a.obra_representativa + '" style="max-width:100px;border-radius:6px;">';
        document.getElementById('modalAutorTitle').textContent = 'Editar autor';
    } else {
        document.getElementById('modalAutorTitle').textContent = 'Nuevo autor';
    }
    abrir('modalAutor');
}

async function guardarAutor() {
    const fd = new FormData(document.getElementById('formAutor'));
    const r = await postForm('autor_actions.php', fd);
    if (r.ok) location.reload(); else alert(r.msg);
}

async function autorEliminar(id) {
    if (!confirm('¿Eliminar este autor? Las obras ligadas quedarán sin autor.')) return;
    const fd = new FormData();
    fd.append('action', 'delete');
    fd.append('id', id);
    const r = await postForm('autor_actions.php', fd);
    if (r.ok) location.reload(); else alert(r.msg);
}
</script>

</body>
</html>