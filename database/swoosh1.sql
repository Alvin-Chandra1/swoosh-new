-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 01, 2026 at 12:28 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `swoosh`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `field_id` int UNSIGNED NOT NULL,
  `booking_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `duration_hours` tinyint UNSIGNED NOT NULL,
  `total_price` int UNSIGNED NOT NULL,
  `status` enum('pending','confirmed','cancelled','completed') NOT NULL DEFAULT 'pending',
  `notes` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fields`
--

CREATE TABLE `fields` (
  `id` int UNSIGNED NOT NULL,
  `owner_id` int UNSIGNED NOT NULL,
  `name` varchar(160) NOT NULL,
  `description` text,
  `location` varchar(190) NOT NULL,
  `city` varchar(100) NOT NULL,
  `price_per_hour` int UNSIGNED NOT NULL,
  `open_time` time NOT NULL DEFAULT '08:00:00',
  `close_time` time NOT NULL DEFAULT '22:00:00',
  `amenities` varchar(255) DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `fields`
--

INSERT INTO `fields` (`id`, `owner_id`, `name`, `description`, `location`, `city`, `price_per_hour`, `open_time`, `close_time`, `amenities`, `image_url`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Swoosh Kemang Court', 'Indoor court dengan lantai nyaman dan pencahayaan yang siap untuk sesi malam.', 'Jl. Kemang Raya No. 12', 'Jakarta Selatan', 150000, '08:00:00', '23:00:00', 'Indoor · Lampu malam · Parkir', NULL, 1, '2026-09-25 02:45:45', '2026-09-25 02:45:45'),
(2, 1, 'Northside Basketball Lab', 'Court komunitas untuk pickup game, latihan, dan pertandingan kecil.', 'Jl. Boulevard Utara No. 8', 'Jakarta Utara', 125000, '07:00:00', '22:00:00', 'Outdoor · Locker · Kantin', NULL, 1, '2026-09-25 02:45:45', '2026-09-25 02:45:45'),
(3, 1, 'Hoop District Arena', 'Arena modern dengan ruang tunggu luas dan suasana kompetitif.', 'Jl. Ciumbuleuit No. 45', 'Bandung', 175000, '09:00:00', '22:00:00', 'Indoor · Tribun · Shower', NULL, 1, '2026-09-25 02:45:45', '2026-09-25 02:45:45'),
(4, 2, 'swossh chandra', 'lapangan basket mewah', 'Jl. dadap no. 12', 'Tangerang', 200000, '12:00:00', '02:00:00', 'indoor', 'https://user37308.na.imgto.link/public/20261001/bsket-1.avif', 1, '2026-10-01 00:14:43', '2026-10-01 00:20:04');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int UNSIGNED NOT NULL,
  `booking_id` int UNSIGNED NOT NULL,
  `order_id` varchar(80) NOT NULL,
  `gross_amount` int UNSIGNED NOT NULL,
  `status` enum('pending','paid','failed','expired') NOT NULL DEFAULT 'pending',
  `payment_type` varchar(60) DEFAULT NULL,
  `snap_token` varchar(255) DEFAULT NULL,
  `redirect_url` varchar(500) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `email` varchar(190) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `phone` varchar(30) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `phone`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Swoosh Admin', 'admin@swoosh.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llCtaWQ5x8xQ0N9aBzj6e', 'admin', '081234567890', 1, '2026-09-25 02:45:45', '2026-09-25 02:45:45'),
(2, 'Alvin', 'alvinchandra666@gmail.com', '$2y$10$XkE9V8Mq15ba1PpQbvVSb.JUh8QoClPiDHL7zy0mvGnhF44d8QE9O', 'admin', 'alvinchandra666@gmail.com', 1, '2026-10-01 00:12:34', '2026-10-01 00:12:34'),
(3, 'ahau', 'alvinchandra4023@gmail.com', '$2y$10$VeTTueD4somgzHfH09Wgde5e9UoXvpcEjys3877vCpQh7aR2rHAz2', 'user', '', 1, '2026-10-01 00:22:25', '2026-10-01 00:22:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bookings_calendar` (`field_id`,`booking_date`,`status`),
  ADD KEY `idx_bookings_user` (`user_id`,`booking_date`);

--
-- Indexes for table `fields`
--
ALTER TABLE `fields`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_fields_owner` (`owner_id`),
  ADD KEY `idx_fields_city` (`city`),
  ADD KEY `idx_fields_active` (`is_active`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_id` (`booking_id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `idx_payments_status` (`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fields`
--
ALTER TABLE `fields`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `fk_bookings_field` FOREIGN KEY (`field_id`) REFERENCES `fields` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bookings_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `fields`
--
ALTER TABLE `fields`
  ADD CONSTRAINT `fk_fields_owner` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_booking` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
