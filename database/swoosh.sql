-- Swoosh — database schema for MySQL 8+ / MariaDB 10.4+
-- Import file ini melalui phpMyAdmin setelah membuat database `swoosh`.

CREATE DATABASE IF NOT EXISTS swoosh CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE swoosh;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS payments;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS fields;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    phone VARCHAR(30) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE fields (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    owner_id INT UNSIGNED NOT NULL,
    name VARCHAR(160) NOT NULL,
    description TEXT NULL,
    location VARCHAR(190) NOT NULL,
    city VARCHAR(100) NOT NULL,
    price_per_hour INT UNSIGNED NOT NULL,
    open_time TIME NOT NULL DEFAULT '08:00:00',
    close_time TIME NOT NULL DEFAULT '22:00:00',
    amenities VARCHAR(255) NULL,
    image_url VARCHAR(500) NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_fields_owner FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_fields_city (city),
    INDEX idx_fields_active (is_active)
) ENGINE=InnoDB;

CREATE TABLE bookings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    field_id INT UNSIGNED NOT NULL,
    booking_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    duration_hours TINYINT UNSIGNED NOT NULL,
    total_price INT UNSIGNED NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') NOT NULL DEFAULT 'pending',
    notes VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_bookings_field FOREIGN KEY (field_id) REFERENCES fields(id) ON DELETE CASCADE,
    INDEX idx_bookings_calendar (field_id, booking_date, status),
    INDEX idx_bookings_user (user_id, booking_date)
) ENGINE=InnoDB;

CREATE TABLE payments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    booking_id INT UNSIGNED NOT NULL UNIQUE,
    order_id VARCHAR(80) NOT NULL UNIQUE,
    gross_amount INT UNSIGNED NOT NULL,
    status ENUM('pending', 'paid', 'failed', 'expired') NOT NULL DEFAULT 'pending',
    payment_type VARCHAR(60) NULL,
    snap_token VARCHAR(255) NULL,
    redirect_url VARCHAR(500) NULL,
    paid_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_payments_booking FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    INDEX idx_payments_status (status)
) ENGINE=InnoDB;

-- Demo admin. Password: password
-- Ganti password setelah login di aplikasi produksi.
INSERT INTO users (name, email, password, role, phone) VALUES
('Swoosh Admin', 'admin@swoosh.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCtaWQ5x8xQ0N9aBzj6e', 'admin', '081234567890');

SET @admin_id = LAST_INSERT_ID();

INSERT INTO fields (owner_id, name, description, location, city, price_per_hour, open_time, close_time, amenities) VALUES
(@admin_id, 'Swoosh Kemang Court', 'Indoor court dengan lantai nyaman dan pencahayaan yang siap untuk sesi malam.', 'Jl. Kemang Raya No. 12', 'Jakarta Selatan', 150000, '08:00:00', '23:00:00', 'Indoor · Lampu malam · Parkir'),
(@admin_id, 'Northside Basketball Lab', 'Court komunitas untuk pickup game, latihan, dan pertandingan kecil.', 'Jl. Boulevard Utara No. 8', 'Jakarta Utara', 125000, '07:00:00', '22:00:00', 'Outdoor · Locker · Kantin'),
(@admin_id, 'Hoop District Arena', 'Arena modern dengan ruang tunggu luas dan suasana kompetitif.', 'Jl. Ciumbuleuit No. 45', 'Bandung', 175000, '09:00:00', '22:00:00', 'Indoor · Tribun · Shower');