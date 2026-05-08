CREATE DATABASE IF NOT EXISTS presupuestos_vidrio CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE presupuestos_vidrio;

CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(80) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(150) NOT NULL,
    created_at DATETIME NOT NULL,
    INDEX idx_users_username (username)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS quotes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quote_number VARCHAR(40) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    client_name VARCHAR(150) NOT NULL,
    client_email VARCHAR(150) DEFAULT NULL,
    client_phone VARCHAR(50) DEFAULT NULL,
    status ENUM('pending', 'accepted', 'rejected') NOT NULL DEFAULT 'pending',
    system_type VARCHAR(50) NOT NULL,
    opening_type VARCHAR(50) DEFAULT NULL,
    profile_color VARCHAR(80) DEFAULT NULL,
    glass_type VARCHAR(120) DEFAULT NULL,
    width_mm INT UNSIGNED NOT NULL,
    height_mm INT UNSIGNED NOT NULL,
    leaves TINYINT UNSIGNED NOT NULL,
    quantity INT UNSIGNED NOT NULL DEFAULT 1,
    aluminum_price_ml DECIMAL(10,2) NOT NULL,
    glass_price_m2 DECIMAL(10,2) NOT NULL,
    labor_cost DECIMAL(10,2) NOT NULL,
    margin_pct DECIMAL(5,2) NOT NULL,
    iva_pct DECIMAL(5,2) NOT NULL,
    aluminum_ml DECIMAL(10,3) NOT NULL,
    glass_m2 DECIMAL(10,3) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL,
    margin_amount DECIMAL(12,2) NOT NULL,
    taxable_base DECIMAL(12,2) NOT NULL,
    iva_amount DECIMAL(12,2) NOT NULL,
    total DECIMAL(12,2) NOT NULL,
    drawing_svg MEDIUMTEXT,
    config_json JSON,
    notes TEXT,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_client_name (client_name),
    CONSTRAINT fk_quotes_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

INSERT INTO users (username, password_hash, name, created_at)
VALUES ('admin', '$2y$10$UOLtLtGP.WnKjJX0I8XdFOo5DcdBYx1W.y0GNhmmm.ukEtYipgAF6', 'Administrador', NOW())
ON DUPLICATE KEY UPDATE username = VALUES(username);
