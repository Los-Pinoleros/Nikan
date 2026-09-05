<?php
/**
 * Script de configuracion de la base de datos
 * Ejecutar una sola vez: php database/install.php
 */

require_once __DIR__ . '/../config/env.php';

define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'nikannicaragua'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));

define('ADMIN_USER', env('ADMIN_USER', 'admin'));
define('ADMIN_EMAIL', env('ADMIN_EMAIL', 'admin@nikan.com'));
define('ADMIN_PASSWORD', env('ADMIN_PASSWORD', 'admin123'));

try {
    // Conexion sin seleccionar base de datos
    $pdo = new PDO("mysql:host=" . DB_HOST . ";charset=utf8mb4", DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Crear base de datos
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `" . DB_NAME . "`");

    // Crear tabla users
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB");

    // Crear usuario admin con password del .env
    $hash = password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE password = VALUES(password), role = VALUES(role)");
    $stmt->execute([ADMIN_USER, ADMIN_EMAIL, $hash, 'admin']);

    echo "Base de datos configurada correctamente.\n";
    echo "Usuario admin creado - usuario: " . ADMIN_USER . " | password: " . ADMIN_PASSWORD . "\n";
    echo "Hash generado: " . $hash . "\n";

} catch (PDOException $e) {
    die("Error: " . $e->getMessage() . "\n");
}
