<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php?page=inicio');
    exit;
}

require_once __DIR__ . '/../config/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Por favor completa todos los campos.';
    } else {
        try {
            $pdo = getDB();
            $stmt = $pdo->prepare("SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ? LIMIT 1");
            $stmt->execute([$username, $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header('Location: ../admin/dashboard.php');
                } else {
                    header('Location: ../index.php?page=inicio');
                }
                exit;
            } else {
                $error = 'Usuario o contraseña incorrectos.';
            }
        } catch (PDOException $e) {
            $error = 'Error en el servidor. Inténtalo más tarde.';
            error_log($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión — NIKAN</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600;800&family=Montserrat:wght@500;600;800&display=swap" rel="stylesheet">
    <style>
        @font-face {
            font-family: 'Felthgothic';
            src: url('../fonts/Felthgothic Bold Italic.otf') format('opentype');
            font-weight: bold;
            font-style: italic;
        }
        :root {
            --nikan-bg: #C6372E;
            --nikan-bg-rgb: 195, 55, 46;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Montserrat', sans-serif;
            -webkit-font-smoothing: antialiased;
            min-height: 100vh;
            width: 100%;
            background-image: url('../assets/fondo.svg');
            background-position: center center;
            background-repeat: no-repeat;
            background-size: cover;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.45);
            z-index: 1;
            pointer-events: none;
        }
        .login {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }
        .login__card {
            background: rgb(var(--nikan-bg-rgb));
            border-radius: 16px;
            padding: 40px 36px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
        }
        .login__logo {
            font: oblique bold 100% 'Felthgothic', cursive;
            font-size: 40px;
            letter-spacing: 2px;
            color: #fff;
            text-align: center;
            margin-bottom: 6px;
        }
        .login__subtitle {
            text-align: center;
            color: #fff;
            opacity: 0.9;
            font-size: 14px;
            margin-bottom: 28px;
        }
        .login__field {
            margin-bottom: 18px;
        }
        .login__label {
            display: block;
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 7px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .login__input {
            width: 100%;
            padding: 13px 15px;
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.12);
            color: #fff;
            font-family: 'Montserrat', sans-serif;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s, background 0.2s;
        }
        .login__input::placeholder {
            color: rgba(255, 255, 255, 0.65);
        }
        .login__input:focus {
            border-color: #fff;
            background: rgba(255, 255, 255, 0.2);
        }
        .login__error {
            background: rgba(0, 0, 0, 0.35);
            color: #fff;
            border-left: 4px solid #fff;
            padding: 11px 14px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 18px;
        }
        .login__btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            background: #fff;
            color: var(--nikan-bg);
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 16px;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .login__btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        .login__back {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #fff;
            text-decoration: none;
            font-size: 13px;
            opacity: 0.85;
        }
        .login__back:hover {
            opacity: 1;
        }
        .login__hint {
            text-align: center;
            color: rgba(255, 255, 255, 0.7);
            font-size: 12px;
            margin-top: 18px;
        }
    </style>
</head>
<body>
    <div class="login">
        <div class="login__card">
            <div class="login__logo">NIKAN</div>
            <div class="login__subtitle">Acceso al museo virtual</div>

            <?php if ($error !== ''): ?>
                <div class="login__error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="post" action="login.php" autocomplete="off">
                <div class="login__field">
                    <label class="login__label" for="username">Usuario o email</label>
                    <input class="login__input" type="text" id="username" name="username"
                        placeholder="Tu usuario o email" required>
                </div>
                <div class="login__field">
                    <label class="login__label" for="password">Contraseña</label>
                    <input class="login__input" type="password" id="password" name="password"
                        placeholder="Tu contraseña" required>
                </div>
                <button type="submit" class="login__btn">Entrar</button>
            </form>

            <a class="login__back" href="../index.php">← Volver al inicio</a>
        </div>
    </div>
</body>
</html>
