-- Migrasi aman untuk mempertahankan data pada database Swoosh yang sudah ada.
-- Cadangkan database terlebih dahulu, pilih database `swoosh`, lalu jalankan file ini.
-- Jika nama database berbeda, sesuaikan nama pada perintah ALTER DATABASE.

ALTER DATABASE `swoosh`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

ALTER TABLE `bookings`
  CONVERT TO CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

ALTER TABLE `fields`
  CONVERT TO CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

ALTER TABLE `payments`
  CONVERT TO CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

ALTER TABLE `users`
  CONVERT TO CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;
