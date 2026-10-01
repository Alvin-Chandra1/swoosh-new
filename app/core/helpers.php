<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function appBasePath(): string
{
    if (APP_URL !== '') {
        return APP_URL;
    }

    $scriptPath = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
    $directory = rtrim(dirname($scriptPath), '/.');
    return $directory === '' ? '' : $directory;
}

function url(string $route = '', array $params = []): string
{
    $query = $route !== '' ? ['route' => ltrim($route, '/')] : [];
    $query = array_merge($query, $params);
    return appBasePath() . ($query ? '/?' . http_build_query($query) : '/');
}

function asset(string $path): string
{
    return appBasePath() . '/' . ltrim($path, '/');
}

function safeImageUrl(array $field): ?string
{
    $imageUrl = trim((string) ($field['image_url'] ?? ''));
    if ($imageUrl === '') {
        return null;
    }

    $scheme = strtolower((string) parse_url($imageUrl, PHP_URL_SCHEME));
    if (in_array($scheme, ['http', 'https'], true) && filter_var($imageUrl, FILTER_VALIDATE_URL)) {
        return $imageUrl;
    }

    if (str_starts_with($imageUrl, '/') && !str_starts_with($imageUrl, '//')) {
        return $imageUrl;
    }

    return null;
}

function databaseSetupMessage(PDOException $exception): string
{
    if (!extension_loaded('pdo_mysql')) {
        return 'Ekstensi pdo_mysql PHP belum aktif. Aktifkan ekstensi tersebut lalu muat ulang aplikasi.';
    }

    $mysqlError = (int) ($exception->errorInfo[1] ?? 0);
    return match ($mysqlError) {
        1044, 1045 => 'Akses MySQL ditolak. Periksa DB_USER, DB_PASS, dan izin pengguna database di file .env.',
        1049 => 'Database yang dikonfigurasi belum ada. Buat database sesuai DB_NAME, lalu import skema Swoosh jika tabelnya belum tersedia.',
        2002, 2003 => 'Server MySQL tidak dapat dijangkau. Pastikan MySQL aktif dan DB_HOST serta DB_PORT di file .env sudah benar.',
        default => !is_file(BASE_PATH . '/.env')
            ? 'File .env tidak ditemukan di folder utama proyek. Salin .env.example menjadi .env, lalu isi koneksi MySQL.'
            : 'Operasi database gagal. Pastikan tabel Swoosh tersedia dan periksa log PHP untuk detail tanpa membagikan isi file .env.',
    };
}

function old(string $key, string $default = ''): string
{
    return e($_POST[$key] ?? $default);
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }

    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $value;
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(24));
    }
    return $_SESSION['_csrf'];
}

function verify_csrf(): void
{
    $token = $_POST['_csrf'] ?? '';
    if (!hash_equals($_SESSION['_csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('Sesi formulir tidak valid. Silakan kembali dan coba lagi.');
    }
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function isLoggedIn(): bool
{
    return currentUser() !== null;
}

function isAdmin(): bool
{
    return (currentUser()['role'] ?? '') === 'admin';
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        flash('error', 'Silakan login terlebih dahulu untuk melanjutkan.');
        header('Location: ' . url('login'));
        exit;
    }
}

function requireAdmin(): void
{
    requireLogin();
    if (!isAdmin()) {
        flash('error', 'Halaman ini khusus untuk admin lapangan.');
        header('Location: ' . url('dashboard'));
        exit;
    }
}

function formatRupiah(int|float $amount): string
{
    return 'Rp ' . number_format((float) $amount, 0, ',', '.');
}

function formatDateId(?string $date): string
{
    if (!$date) {
        return '-';
    }
    $months = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    $time = strtotime($date);
    return date('d', $time) . ' ' . $months[(int) date('n', $time)] . ' ' . date('Y', $time);
}

function statusLabel(string $status): string
{
    return match ($status) {
        'confirmed' => 'Dikonfirmasi',
        'cancelled' => 'Dibatalkan',
        'completed' => 'Selesai',
        default => 'Menunggu',
    };
}

function statusClass(string $status): string
{
    return match ($status) {
        'confirmed' => 'badge-success',
        'cancelled' => 'badge-danger',
        'completed' => 'badge-neutral',
        default => 'badge-warning',
    };
}