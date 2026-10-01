-- Jalankan file ini hanya untuk database Swoosh yang sudah terpasang sebelumnya.
-- Untuk instalasi baru, cukup import database/swoosh.sql.
USE swoosh;

CREATE TABLE IF NOT EXISTS payments (
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