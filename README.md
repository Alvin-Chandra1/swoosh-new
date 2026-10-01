# Swoosh — booking lapangan basket

Swoosh adalah aplikasi PHP MVC sederhana untuk menyewakan dan memesan lapangan basket. Pemain dapat mencari lapangan, melihat jadwal tersedia, membuat booking, menyelesaikan pembayaran, dan melihat riwayatnya. Pemilik lapangan dapat mengelola lapangan serta memantau booking masuk.

## Cara kerja booking

1. Pemain masuk menggunakan akun biasa, membuka detail lapangan, lalu memilih tanggal, slot, dan durasi.
2. Sebelum disimpan, server memeriksa jam operasional dan memeriksa ulang bentrok booking di database. Pemeriksaan di server ini tetap berlaku walaupun dua pemain mencoba slot yang sama hampir bersamaan.
3. Booking baru disimpan dengan status **Menunggu** dan slot langsung ditandai sebagai terisi. Pemain diarahkan ke halaman checkout; booking juga dapat dilihat di menu **Booking saya** dan **Kalender** sebelum pembayaran selesai.
4. Pada mode demo, tombol **Simulasikan pembayaran berhasil** mengubah pembayaran menjadi lunas dan booking menjadi **Dikonfirmasi**. Untuk pembayaran nyata, aplikasi menggunakan Midtrans Snap dan webhook.
5. Booking yang dibatalkan tidak lagi mengunci slot. Booking yang waktunya melewati tengah malam juga didukung; slot setelah tengah malam diberi label `(+1 hari)`.

Link gambar yang disimpan pada kolom `fields.image_url` ditampilkan pada halaman utama, daftar lapangan, detail lapangan, dan daftar admin. Link harus dapat diakses publik melalui HTTP/HTTPS; URL relatif di dalam folder `public` juga dapat digunakan.

## Kebutuhan

- PHP 8.0 atau lebih baru dengan ekstensi `pdo_mysql`.
- MySQL 8 atau MariaDB yang mendukung skema aplikasi.
- Apache (XAMPP/Laragon) atau server PHP lain.

Dump database yang dilampirkan dibuat dengan MySQL 8.0.30. Gunakan MySQL 8 untuk mengimpor dump itu karena dump memakai collation `utf8mb4_0900_ai_ci`.

## Menjalankan secara lokal (XAMPP/Laragon)

1. Salin folder proyek ke `htdocs/swoosh` (XAMPP) atau `www/swoosh` (Laragon).
2. Jalankan Apache dan MySQL.
3. Siapkan database bernama `swoosh`:
   - Untuk instalasi baru, import `database/swoosh.sql` melalui phpMyAdmin.
   - Jika ingin memakai dump database Anda sendiri, buat database kosong lalu import dump tersebut. Jangan import `database/swoosh.sql` ke database yang sudah berisi data: file contoh itu menghapus tabel Swoosh lama sebelum membuat tabel dan data demo.
4. Salin `.env.example` menjadi `.env`, kemudian sesuaikan koneksi database:

   ```env
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_NAME=swoosh
   DB_USER=root
   DB_PASS=
   ```

   Biarkan `APP_URL=` kosong agar alamat situs dan endpoint pemuatan slot mengikuti lokasi folder secara otomatis. Jika aplikasi dipasang di alamat khusus, isi `APP_URL` dengan alamat dasar aplikasi sampai folder `public`, tanpa garis miring di akhir.
5. Buka `http://localhost/swoosh/public`.

## Pembayaran

Mode demo aktif secara default dan tidak membutuhkan akun atau kunci payment gateway. Pada halaman checkout, gunakan tombol simulasi untuk menguji konfirmasi booking.

Untuk Midtrans Sandbox, isi `.env`:

```env
PAYMENT_PROVIDER=midtrans
MIDTRANS_SERVER_KEY=SB-Mid-server-...
MIDTRANS_CLIENT_KEY=SB-Mid-client-...
MIDTRANS_IS_PRODUCTION=false
```

Atur URL webhook Midtrans ke `https://alamat-aplikasi/?route=payment/webhook`. Jangan masukkan kunci Midtrans ke dalam ZIP atau repositori publik; simpan sebagai environment secret pada hosting.

## Akun demo

- Email admin: `admin@swoosh.test`
- Password: `password`
- Kode pendaftaran admin baru: `SwooshAdmin2026` (dapat diubah melalui `.env`)

Daftarkan akun dengan role **user** untuk mencoba alur booking sebagai pemain.

## Teknologi dan struktur

- PHP native 8 dengan pola MVC; tidak memerlukan Composer atau framework tambahan.
- MySQL/MariaDB melalui PDO prepared statements.
- HTML, CSS, dan JavaScript vanilla.

```text
app/
  config/         konfigurasi dan pembacaan environment
  controllers/    Auth, Home, Field, Booking, Payment, Admin
  core/           Database, Controller, Model, helper, bootstrap
  models/         User, Field, Booking, Payment
  services/       layanan pembayaran
  views/          halaman dan layout
database/
  swoosh.sql      skema dan data contoh untuk instalasi baru
public/
  index.php       front controller dan route
  assets/         CSS, JavaScript, dan tampilan gambar lapangan
```

## Catatan keamanan

- Password disimpan menggunakan `password_hash` dan diperiksa menggunakan `password_verify`.
- Query dinamis menggunakan PDO prepared statements dan form POST menggunakan token CSRF.
- Validasi ketersediaan slot selalu dilakukan kembali di server.
- Untuk penggunaan produksi, ganti kode admin demo, gunakan akun database khusus, aktifkan HTTPS, dan atur payment gateway sesuai kebutuhan.