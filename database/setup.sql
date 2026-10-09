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

-- Registro de clics en fichas de obras
CREATE TABLE IF NOT EXISTS obra_visitas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    area VARCHAR(20) NOT NULL,
    obra_id INT UNSIGNED NOT NULL,
    origen VARCHAR(30) NOT NULL DEFAULT 'catalogo',
    visitado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_obra_visitas_area_obra (area, obra_id),
    INDEX idx_obra_visitas_fecha (visitado_en)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS museos_virtuales (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NOT NULL,
    latitud DECIMAL(10,7) NOT NULL,
    longitud DECIMAL(10,7) NOT NULL,
    modelo VARCHAR(255) NOT NULL,
    creado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_museos_virtuales_ubicacion (latitud, longitud)
) ENGINE=InnoDB;

-- Si ya ejecutaste esto antes, actualiza el hash con el correcto:
--UPDATE users SET password = '$2y$10$...' WHERE username = 'admin';
