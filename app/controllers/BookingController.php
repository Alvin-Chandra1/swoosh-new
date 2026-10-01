<?php
declare(strict_types=1);

final class BookingController extends Controller
{
    public function store(): void
    {
        requireLogin();
        $this->requirePost();
        verify_csrf();
        if (isAdmin()) {
            flash('error', 'Admin hanya mengelola lapangan, bukan membuat booking.');
            $this->redirect('dashboard');
        }
        try {
            $bookingId = (new Booking())->create($_POST, (int) currentUser()['id']);
            flash('success', 'Slot berhasil diamankan. Selesaikan pembayaran untuk mengonfirmasi booking.');
            $this->redirect('payment/checkout&id=' . $bookingId);
        } catch (Throwable $exception) {
            flash('error', $exception->getMessage());
        }
        $this->redirect('field/show&id=' . (int) ($_POST['field_id'] ?? 0));
    }

    public function slots(): void
    {
        $fieldId = (int) ($_GET['field_id'] ?? 0);
        $date = (string) ($_GET['date'] ?? date('Y-m-d'));
        $parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if ($fieldId < 1 || !$parsedDate || $parsedDate->format('Y-m-d') !== $date) {
            $this->json(['error' => 'Tanggal atau lapangan tidak valid.'], 400);
        }
        $field = (new Field())->find($fieldId);
        if (!$field) {
            $this->json(['error' => 'Lapangan tidak ditemukan.'], 404);
        }
        $occupied = (new Booking())->occupiedSlots($fieldId, $date);
        $this->json([
            'field' => ['open_time' => $field['open_time'], 'close_time' => $field['close_time'], 'price_per_hour' => $field['price_per_hour']],
            'occupied' => $occupied,
        ]);
    }

    public function mine(): void
    {
        requireLogin();
        $bookings = (new Booking())->forUser((int) currentUser()['id']);
        $this->view('bookings/mine', ['title' => 'Booking saya', 'bookings' => $bookings]);
    }

    public function calendar(): void
    {
        requireLogin();
        $month = $_GET['month'] ?? date('Y-m');
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }
        $from = $month . '-01';
        $to = date('Y-m-t', strtotime($from));
        $bookings = isAdmin()
            ? (new Booking())->calendarForAdmin((int) currentUser()['id'], $from, $to)
            : (new Booking())->calendarForUser((int) currentUser()['id'], $from, $to);
        $this->view('bookings/calendar', ['title' => 'Kalender booking', 'month' => $month, 'bookings' => $bookings]);
    }

    public function cancel(): void
    {
        requireLogin();
        $this->requirePost();
        verify_csrf();
        (new Booking())->cancel((int) ($_POST['id'] ?? 0), (int) currentUser()['id']);
        flash('success', 'Booking dibatalkan.');
        $this->redirect('bookings');
    }

    private function json(array $payload, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}