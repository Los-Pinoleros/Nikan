<script type="importmap">
{
    "imports": {
        "three": "https://cdn.jsdelivr.net/npm/three@0.161.0/build/three.module.js"
    }
}
</script>

<main class="vr-page">
    <section class="vr-intro">
        <div class="vr-intro__eyebrow"><span>01</span><i></i> SALA DIGITAL NIKAN</div>
        <div class="vr-intro__layout">
            <div>
                <span class="vr-kicker">RECORRIDO INMERSIVO</span>
                <h1>Entra.<br><em>Explora.</em></h1>
            </div>
            <div class="vr-intro__copy">
                <span class="vr-copy-brand">NIKAN</span>
                <p>Un recorrido libre por el Museo Managua. Comienza en la entrada y descubre el espacio a tu propio ritmo.</p>
                <p class="vr-note">Haz clic en la escena para caminar · La visita se abre en pantalla completa</p>
            </div>
        </div>
    </section>

    <section class="vr-viewer-wrap" aria-label="Recorrido 3D del Museo Managua">
        <div class="vr-scene-label">
            <span class="vr-scene-label__mark">N</span>
            <span><strong>MUSEO MANAGUA</strong><small>RECORRIDO VIRTUAL</small></span>
        </div>
        <div class="vr-entrance-mark">ENTRADA <span></span></div>
        <canvas id="vrCanvas" class="vr-canvas"></canvas>
        <div id="vrLoading" class="vr-loading">Cargando el museo…</div>
        <div id="vrError" class="vr-error" hidden>No se pudo cargar el modelo 3D.</div>
        <div class="vr-hud">
            <span id="vrStatus">Haz clic para comenzar el recorrido</span>
            <div class="vr-hud__actions">
                <button id="vrFullscreen" type="button"><b>⛶</b> Pantalla completa</button>
                <button id="vrReset" type="button">↺ Entrada</button>
            </div>
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
let sprintUntil = 0;
let lastForwardTap = 0;
const sprintWindow = 360;
const sprintDuration = 1200;

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
    const sprinting = performance.now() < sprintUntil;
    const distance = walkSpeed * (sprinting ? 1.75 : 1) * delta;
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
    new URL('3dmodels/MuseoManagua.glb', document.baseURI).href,
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
controls.addEventListener('lock', () => { status.textContent = 'WASD/flechas para caminar · doble toque para correr'; });
controls.addEventListener('unlock', () => { status.textContent = 'Haz clic para continuar'; });
window.addEventListener('keydown', (event) => {
    if (!event.repeat && (event.code === 'KeyW' || event.code === 'ArrowUp')) {
        const now = performance.now();
        if (now - lastForwardTap <= sprintWindow) {
            sprintUntil = now + sprintDuration;
            status.textContent = 'Corriendo';
        }
        lastForwardTap = now;
    }
    keys[event.code] = true;
});
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
        padding: 142px 6vw 72px;
        position: relative;
        z-index: 2;
        color: #fff;
        background:
            radial-gradient(circle at 12% 20%, rgba(201,169,79,.16), transparent 28%),
            linear-gradient(112deg, #211a18 0%, #3b2924 52%, #1e1917 100%);
        font-family: 'Montserrat', sans-serif;
    }
    .vr-intro { max-width: 1200px; margin: 0 auto 28px; }
    .vr-intro__eyebrow {
        display: flex; align-items: center; gap: 12px; margin-bottom: 20px;
        color: rgba(255,255,255,.58); font-size: 10px; font-weight: 800; letter-spacing: 3px;
    }
    .vr-intro__eyebrow span { color: #2fded6; font-family: 'Alegreya', serif; font-size: 18px; letter-spacing: 0; }
    .vr-intro__eyebrow i { width: 52px; height: 1px; background: #2fded6; }
    .vr-intro__layout { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(260px, .8fr); gap: 9vw; align-items: end; }
    .vr-kicker { color: #2fded6; font-size: 11px; font-weight: 800; letter-spacing: 4px; }
    .vr-intro h1 {
        margin: 10px 0 0; color: #f0f4f1;
        font-family: 'Nikan Felthgothic', 'Felthgothic', serif;
        font-size: clamp(48px, 7vw, 98px); line-height: .88; letter-spacing: 1px;
    }
    .vr-intro h1 em { color: #2fded6; font-style: normal; }
    .vr-intro__copy { border-left: 1px solid rgba(255,224,138,.45); padding: 6px 0 4px 28px; }
    .vr-copy-brand {
        display: block;
        margin-bottom: 10px;
        color: #03d437;
        font: 800 24px/1 'Nikan Felthgothic', 'Felthgothic', serif;
        letter-spacing: 5px;
        text-align: center;
    }
    .vr-intro p { max-width: 430px; margin: 0; color: rgba(255,255,255,.82); line-height: 1.7; font-size: 14px; }
    .vr-note { margin-top: 14px !important; color: #2fded6 !important; font-size: 10px !important; letter-spacing: .4px; }
    .vr-viewer-wrap {
        width: min(1200px, 100%); height: min(67vh, 720px); min-height: 420px;
        margin: 0 auto; overflow: hidden; position: relative;
        border: 2px solid #07531a; border-radius: 4px;
        background: #332720; box-shadow: 0 30px 80px rgba(0,0,0,.5);
    }
    .vr-viewer-wrap::after { content: ""; position: absolute; inset: 14px; border: 1px solid rgba(3,212,55,.32); pointer-events: none; }
    .vr-scene-label { position: absolute; top: 30px; left: 30px; z-index: 3; display: flex; align-items: center; gap: 10px; color: #fff; pointer-events: none; }
    .vr-scene-label__mark { display: grid; place-items: center; width: 30px; height: 30px; color: #3b2920; background: #2fded6; font: 800 17px 'Nikan Felthgothic', serif; }
    .vr-scene-label strong, .vr-scene-label small { display: block; }
    .vr-scene-label strong { font-size: 10px; letter-spacing: 2px; }
    .vr-scene-label small { margin-top: 3px; color: #2fded6; font-size: 8px; letter-spacing: 2px; }
    .vr-entrance-mark { position: absolute; top: 35px; right: 35px; z-index: 3; color: rgba(255,255,255,.7); font-size: 9px; letter-spacing: 2px; writing-mode: vertical-rl; pointer-events: none; }
    .vr-entrance-mark span { display: block; width: 1px; height: 34px; margin: 8px auto 0; background: #2fded6; }
    .vr-canvas { display: block; width: 100%; height: 100%; cursor: crosshair; }
    [hidden] { display: none !important; }
    .vr-loading, .vr-error {
        position: absolute; inset: 0; display: grid; place-items: center;
        color: #2fded6; font-weight: 700; pointer-events: none;
    }
    .vr-error { color: #fff; background: rgba(50, 30, 25, .8); }
    .vr-hud {
        position: absolute; left: 30px; right: 30px; bottom: 30px; z-index: 4;
        display: flex; align-items: center; justify-content: space-between;
        pointer-events: none;
    }
    .vr-hud span, .vr-hud button {
        border: 1px solid rgba(27, 132, 6, 0.6); border-radius: 0; padding: 11px 15px;
        color: #1f9103; background: rgba(31,23,20,.82);
        font: 800 10px 'Montserrat', sans-serif; letter-spacing: .5px;
    }
    .vr-hud__actions { display: flex; gap: 8px; }
    .vr-hud button { pointer-events: auto; cursor: pointer; transition: background .2s, color .2s; }
    .vr-hud button:hover { color: #3b2920; background: #2fded6; }
    .vr-hud button b { font-size: 17px; font-weight: 400; vertical-align: -2px; }
    .vr-mobile-controls { display: none; }
    @media (max-width: 680px) {
        .vr-page { padding: 112px 18px 35px; }
        .vr-intro__layout { display: block; }
        .vr-intro__copy { margin-top: 24px; padding: 0 0 0 16px; }
        .vr-intro h1 { font-size: clamp(54px, 17vw, 78px); }
        .vr-viewer-wrap { min-height: 55vh; height: 62vh; }
        .vr-scene-label { top: 25px; left: 25px; }
        .vr-hud { bottom: 25px; left: 25px; right: 25px; display: block; }
        .vr-hud__actions { margin-top: 8px; }
        .vr-hud__actions button { padding: 9px 10px; font-size: 9px; }
        .vr-mobile-controls { display: block; position: absolute; left: 14px; bottom: 12px; }
        .vr-mobile-controls button {
            width: 38px; height: 34px; margin: 2px; border: 0; border-radius: 8px;
            color: #3b2920; background: #2fded6; font-weight: 800;
        }
    }
</style>
