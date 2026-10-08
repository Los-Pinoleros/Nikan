<script type="importmap">
{
    "imports": {
        "three": "https://cdn.jsdelivr.net/npm/three@0.161.0/build/three.module.js"
    }
}
</script>

<main class="vr-page">
    <section class="vr-intro">
        <span class="vr-kicker">RECORRIDO INMERSIVO</span>
        <h1>Camina por el Museo Managua</h1>
        <p>
            Haz clic dentro del museo para comenzar. Usa <strong>WASD</strong> o las flechas
            para caminar y mueve el ratón para mirar, como en un recorrido virtual.
        </p>
        <p class="vr-note">En móvil, gira la vista deslizando y utiliza los controles de pantalla para desplazarte.</p>
    </section>

    <section class="vr-viewer-wrap" aria-label="Recorrido 3D del Museo Managua">
        <canvas id="vrCanvas" class="vr-canvas"></canvas>
        <div id="vrLoading" class="vr-loading">Cargando el museo…</div>
        <div id="vrError" class="vr-error" hidden>No se pudo cargar el modelo 3D.</div>
        <div class="vr-hud">
            <span id="vrStatus">Haz clic para entrar</span>
            <button id="vrFullscreen" type="button">Pantalla completa</button>
            <button id="vrReset" type="button">Volver a la entrada</button>
        </div>
        <div class="vr-mobile-controls" aria-label="Controles de movimiento">
            <button type="button" data-key="ArrowUp" aria-label="Avanzar">▲</button>
            <div>
                <button type="button" data-key="ArrowLeft" aria-label="Girar a la izquierda">◀</button>
                <button type="button" data-key="ArrowDown" aria-label="Retroceder">▼</button>
                <button type="button" data-key="ArrowRight" aria-label="Girar a la derecha">▶</button>
            </div>
        </div>
    </section>
</main>

<script type="module">
import * as THREE from 'https://cdn.jsdelivr.net/npm/three@0.161.0/build/three.module.js';
import { GLTFLoader } from 'https://cdn.jsdelivr.net/npm/three@0.161.0/examples/jsm/loaders/GLTFLoader.js';
import { PointerLockControls } from 'https://cdn.jsdelivr.net/npm/three@0.161.0/examples/jsm/controls/PointerLockControls.js';

const canvas = document.getElementById('vrCanvas');
const container = canvas.parentElement;
const loading = document.getElementById('vrLoading');
const error = document.getElementById('vrError');
const status = document.getElementById('vrStatus');
const resetButton = document.getElementById('vrReset');
const fullscreenButton = document.getElementById('vrFullscreen');
const scene = new THREE.Scene();
scene.background = new THREE.Color(0x332720);
scene.fog = new THREE.Fog(0x332720, 30, 240);

const camera = new THREE.PerspectiveCamera(70, 1, 0.1, 1000);
const renderer = new THREE.WebGLRenderer({ canvas, antialias: true });
renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
renderer.outputColorSpace = THREE.SRGBColorSpace;
renderer.shadowMap.enabled = true;

scene.add(new THREE.HemisphereLight(0xfff1d0, 0x382c27, 2.1));
const sun = new THREE.DirectionalLight(0xffe0a3, 2.4);
sun.position.set(20, 50, 20);
sun.castShadow = true;
scene.add(sun);

const controls = new PointerLockControls(camera, canvas);
const keys = {};
const raycaster = new THREE.Raycaster();
const clock = new THREE.Clock();
const start = new THREE.Vector3();
let model;
let floorY = 0;
let walkHeight = 1.7;
let walkSpeed = 2.8;
let collisionRadius = 0.55;
let ready = false;

function resize() {
    const width = container.clientWidth;
    const height = container.clientHeight;
    camera.aspect = width / height;
    camera.updateProjectionMatrix();
    renderer.setSize(width, height, false);
}
window.addEventListener('resize', resize);
resize();

function setStartFromModel(root) {
    const box = new THREE.Box3().setFromObject(root);
    const size = box.getSize(new THREE.Vector3());
    const center = box.getCenter(new THREE.Vector3());
    const scale = Math.max(size.x, size.z);
    walkHeight = Math.max(size.y * 0.06, 1.7);
    walkSpeed = Math.max(scale * 0.012, 2.8);
    collisionRadius = Math.max(scale * 0.004, 0.55);
    floorY = box.min.y + walkHeight;

    // La entrada se asume en el frente del modelo (-Z). Se puede ajustar aquí
    // si el archivo GLB usa otra orientación.
    start.set(center.x, floorY, box.max.z - Math.max(scale * 0.08, 4));
    camera.position.copy(start);
    camera.lookAt(center.x, floorY + walkHeight * 0.35, center.z);
}

function collides(position) {
    if (!model) return false;
    const direction = new THREE.Vector3();
    const directions = [
        new THREE.Vector3(1, 0, 0), new THREE.Vector3(-1, 0, 0),
        new THREE.Vector3(0, 0, 1), new THREE.Vector3(0, 0, -1)
    ];
    for (const side of directions) {
        direction.copy(side);
        raycaster.set(position, direction);
        raycaster.far = collisionRadius;
        if (raycaster.intersectObject(model, true).length) return true;
    }
    return false;
}

function resetPosition() {
    if (!ready) return;
    camera.position.copy(start);
    camera.lookAt(model.userData.lookAt);
    status.textContent = 'Entrada del museo';
}

async function enterFullscreen() {
    if (!document.fullscreenElement && container.requestFullscreen) {
        try {
            await container.requestFullscreen();
        } catch (fullscreenError) {
            status.textContent = 'Pantalla completa no disponible en este navegador';
        }
    }
}

function move(delta) {
    const mobile = window.innerWidth <= 680;
    if (!ready || (!controls.isLocked && !mobile)) return;
    const distance = walkSpeed * delta;
    const previous = camera.position.clone();
    if (keys.KeyW || keys.ArrowUp) controls.moveForward(distance);
    if (keys.KeyS || keys.ArrowDown) controls.moveForward(-distance);
    if (keys.KeyA) controls.moveRight(-distance);
    if (keys.KeyD) controls.moveRight(distance);
    if (keys.ArrowLeft) camera.rotation.y += delta * 1.8;
    if (keys.ArrowRight) camera.rotation.y -= delta * 1.8;
    if (collides(camera.position)) camera.position.copy(previous);
    camera.position.y = floorY;
}

const loader = new GLTFLoader();
loader.load(
    '3dmodels/MuseoManagua.glb',
    (gltf) => {
        model = gltf.scene;
        model.traverse((object) => {
            if (object.isMesh) {
                object.castShadow = true;
                object.receiveShadow = true;
            }
        });
        scene.add(model);
        setStartFromModel(model);
        const center = new THREE.Box3().setFromObject(model).getCenter(new THREE.Vector3());
        model.userData.lookAt = new THREE.Vector3(center.x, floorY + walkHeight * 0.35, center.z);
        ready = true;
        loading.hidden = true;
        status.textContent = 'Haz clic para comenzar';
    },
    (event) => {
        if (event.total) status.textContent = `Cargando ${Math.round(event.loaded / event.total * 100)}%`;
    },
    () => {
        loading.hidden = true;
        error.hidden = false;
        status.textContent = 'No se pudo cargar el museo';
    }
);

canvas.addEventListener('click', async () => {
    if (!ready) return;
    await enterFullscreen();
    if (!controls.isLocked) controls.lock();
});
controls.addEventListener('lock', () => { status.textContent = 'WASD/flechas para caminar · ESC para salir'; });
controls.addEventListener('unlock', () => { status.textContent = 'Haz clic para continuar'; });
window.addEventListener('keydown', (event) => { keys[event.code] = true; });
window.addEventListener('keyup', (event) => { keys[event.code] = false; });
resetButton.addEventListener('click', resetPosition);
fullscreenButton.addEventListener('click', enterFullscreen);

document.querySelectorAll('[data-key]').forEach((button) => {
    const code = button.dataset.key;
    const press = (event) => { event.preventDefault(); keys[code] = true; };
    const release = (event) => { event.preventDefault(); keys[code] = false; };
    button.addEventListener('pointerdown', press);
    button.addEventListener('pointerup', release);
    button.addEventListener('pointerleave', release);
    button.addEventListener('pointercancel', release);
});

function animate() {
    requestAnimationFrame(animate);
    move(Math.min(clock.getDelta(), 0.05));
    renderer.render(scene, camera);
}
animate();
</script>

<style>
    .vr-page {
        min-height: 100vh;
        padding: 150px 6vw 70px;
        position: relative;
        z-index: 2;
        color: #fff;
        background: linear-gradient(135deg, rgba(36, 27, 24, .97), rgba(72, 35, 31, .94));
        font-family: 'Montserrat', sans-serif;
    }
    .vr-intro { max-width: 780px; margin: 0 auto 30px; text-align: center; }
    .vr-kicker { color: #ffe08a; font-size: 13px; font-weight: 800; letter-spacing: 3px; }
    .vr-intro h1 {
        margin: 12px 0 14px; color: #fff2cd;
        font-family: 'Nikan Felthgothic', 'Felthgothic', serif;
        font-size: clamp(38px, 6vw, 76px); line-height: 1;
    }
    .vr-intro p { max-width: 650px; margin: 0 auto; color: rgba(255,255,255,.86); line-height: 1.7; }
    .vr-note { margin-top: 12px !important; color: rgba(255,224,138,.82) !important; font-size: 12px; }
    .vr-viewer-wrap {
        width: min(1200px, 100%); height: min(68vh, 720px); min-height: 420px;
        margin: 0 auto; overflow: hidden; position: relative;
        border: 1px solid rgba(255,224,138,.45); border-radius: 22px;
        background: #332720; box-shadow: 0 24px 70px rgba(0,0,0,.4);
    }
    .vr-canvas { display: block; width: 100%; height: 100%; cursor: crosshair; }
    [hidden] { display: none !important; }
    .vr-loading, .vr-error {
        position: absolute; inset: 0; display: grid; place-items: center;
        color: #ffe08a; font-weight: 700; pointer-events: none;
    }
    .vr-error { color: #fff; background: rgba(50, 30, 25, .8); }
    .vr-hud {
        position: absolute; left: 18px; right: 18px; bottom: 18px;
        display: flex; align-items: center; justify-content: space-between;
        pointer-events: none;
    }
    .vr-hud span, .vr-hud button {
        border: 0; border-radius: 999px; padding: 10px 14px;
        color: #3b2920; background: rgba(255,240,190,.92);
        font: 800 11px 'Montserrat', sans-serif;
    }
    .vr-hud button { pointer-events: auto; cursor: pointer; }
    .vr-mobile-controls { display: none; }
    @media (max-width: 680px) {
        .vr-page { padding: 112px 18px 35px; }
        .vr-viewer-wrap { min-height: 55vh; height: 62vh; border-radius: 14px; }
        .vr-hud { bottom: 12px; left: 12px; right: 12px; }
        .vr-mobile-controls { display: block; position: absolute; left: 14px; bottom: 12px; }
        .vr-mobile-controls button {
            width: 38px; height: 34px; margin: 2px; border: 0; border-radius: 8px;
            color: #3b2920; background: rgba(255,240,190,.86); font-weight: 800;
        }
    }
</style>
