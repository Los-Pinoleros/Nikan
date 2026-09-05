<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

include __DIR__ . '/components/header.php';
?>

<div class="dash">
    <div class="dash__card">
        <div class="dash__title">Panel de Administración</div>
        <div class="dash__subtitle">Bienvenido al área de gestión de NIKAN</div>

        <div class="dash__user">
            <img src="<?php echo $_SESSION['role'] === 'admin' ? '../assets/leon-verde.svg' : '../assets/leon.svg'; ?>" alt="Admin">
            <div>
                <div class="dash__user-name"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                <div class="dash__user-role"><?php echo htmlspecialchars($_SESSION['role']); ?></div>
            </div>
        </div>

        <div class="dash__grid">
            <div class="dash__stat">
                <div class="dash__stat-num">—</div>
                <div class="dash__stat-label">Usuarios</div>
            </div>
            <div class="dash__stat">
                <div class="dash__stat-num">—</div>
                <div class="dash__stat-label">Obras</div>
            </div>
            <div class="dash__stat">
                <div class="dash__stat-num">—</div>
                <div class="dash__stat-label">Visitas</div>
            </div>
        </div>

        <div class="dash__hint">Este panel está en construcción. Más funciones pronto.</div>
    </div>
</div>

<style>
    .dash {
        position: relative;
        z-index: 2;
        max-width: 900px;
        margin: 0 auto;
        padding: 120px 24px 60px;
    }
    .dash__card {
        background: rgb(var(--nikan-bg-rgb));
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        color: #fff;
    }
    .dash__title {
        font: oblique bold 100% 'Felthgothic', cursive;
        font-size: 30px;
        margin-bottom: 6px;
    }
    .dash__subtitle {
        opacity: 0.9;
        font-size: 14px;
        margin-bottom: 24px;
    }
    .dash__user {
        background: rgba(255,255,255,0.15);
        border-radius: 10px;
        padding: 16px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 18px;
    }
    .dash__user img {
        width: 52px;
        height: 52px;
    }
    .dash__user-name {
        font-size: 18px;
        font-weight: 800;
    }
    .dash__user-role {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 1px;
        opacity: 0.85;
    }
    .dash__grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
    }
    .dash__stat {
        background: rgba(255,255,255,0.15);
        border-radius: 10px;
        padding: 20px;
        text-align: center;
    }
    .dash__stat-num {
        font: oblique bold 100% 'Felthgothic', cursive;
        font-size: 36px;
    }
    .dash__stat-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1px;
        opacity: 0.85;
        margin-top: 4px;
    }
    .dash__hint {
        margin-top: 18px;
        font-size: 13px;
        opacity: 0.85;
        text-align: center;
    }
</style>

</body>
</html>
