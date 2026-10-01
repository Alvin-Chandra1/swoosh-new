<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/app/config/config.php';
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
]);
require_once APP_PATH . '/core/bootstrap.php';

$route = trim($_GET['route'] ?? '', '/');
$routes = [
    '' => [HomeController::class, 'index'],
    'dashboard' => [HomeController::class, 'dashboard'],
    'login' => [AuthController::class, 'showLogin'],
    'login/submit' => [AuthController::class, 'login'],
    'register' => [AuthController::class, 'showRegister'],
    'register/submit' => [AuthController::class, 'register'],
    'logout' => [AuthController::class, 'logout'],
    'fields' => [FieldController::class, 'index'],
    'field/show' => [FieldController::class, 'show'],
    'booking/store' => [BookingController::class, 'store'],
    'booking/slots' => [BookingController::class, 'slots'],
    'bookings' => [BookingController::class, 'mine'],
    'calendar' => [BookingController::class, 'calendar'],
    'booking/cancel' => [BookingController::class, 'cancel'],
    'payment/checkout' => [PaymentController::class, 'checkout'],
    'payment/demo-complete' => [PaymentController::class, 'completeDemo'],
    'payment/webhook' => [PaymentController::class, 'webhook'],
    'admin/fields' => [AdminController::class, 'fields'],
    'admin/bookings' => [AdminController::class, 'bookings'],
    'admin/booking/status' => [AdminController::class, 'updateBooking'],
    'admin/fields/create' => [FieldController::class, 'create'],
    'admin/fields/store' => [FieldController::class, 'store'],
    'admin/fields/edit' => [FieldController::class, 'edit'],
    'admin/fields/update' => [FieldController::class, 'update'],
    'admin/fields/delete' => [FieldController::class, 'delete'],
];

if (!isset($routes[$route])) {
    http_response_code(404);
    $title = 'Halaman tidak ditemukan';
    require APP_PATH . '/views/errors/404.php';
    exit;
}

[$controllerClass, $method] = $routes[$route];
try {
    (new $controllerClass())->$method();
} catch (Throwable $exception) {
    http_response_code(500);
    if ($exception instanceof PDOException) {
        error_log('Swoosh database error: ' . $exception->getMessage());
        $setupError = databaseSetupMessage($exception);
    } else {
        $setupError = (($_ENV['APP_ENV'] ?? 'local') === 'local')
            ? $exception->getMessage()
            : 'Terjadi kesalahan pada aplikasi.';
    }
    if ($route === 'booking/slots') {
        if (!$exception instanceof PDOException) {
            error_log('Swoosh slot endpoint error: ' . $exception->getMessage());
        }
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        echo json_encode(['error' => $setupError], JSON_UNESCAPED_UNICODE);
        exit;
    }
    require APP_PATH . '/views/errors/500.php';
}