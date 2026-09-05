-- Base de datos nikannicaragua
CREATE DATABASE IF NOT EXISTS nikannicaragua CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nikannicaragua;

-- Tabla de usuarios
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Admin de prueba: usuario admin / password admin123
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@nikan.com', '$2y$10$YourHashedPasswordHere', 'admin')
ON DUPLICATE KEY UPDATE username=username;

-- Si ya ejecutaste esto antes, actualiza el hash con el correcto:
--UPDATE users SET password = '$2y$10$...' WHERE username = 'admin';
