<?php
declare(strict_types=1);

final class PaymentController extends Controller
{
    public function checkout(): void
    {
        requireLogin();
        if (isAdmin()) {
            flash('error', 'Admin tidak perlu melakukan pembayaran.');
            $this->redirect('dashboard');
        }

        $booking = (new Payment())->checkoutData((int) ($_GET['id'] ?? 0), (int) currentUser()['id']);
        if (!$booking) {
            flash('error', 'Booking tidak ditemukan.');
            $this->redirect('bookings');
        }
        if ($booking['payment_status'] === 'paid') {
            $this->view('payments/checkout', ['title' => 'Pembayaran selesai', 'booking' => $booking, 'payment' => null, 'paid' => true]);
            return;
        }

        $paymentModel = new Payment();
        $payment = $paymentModel->createPending((int) $booking['id'], (int) $booking['total_price']);
        $gatewayError = null;
        if (PaymentService::isConfigured() && empty($payment['redirect_url'])) {
            try {
                $gateway = PaymentService::createSnapTransaction($booking, $payment['order_id']);
                $paymentModel->updateGateway((int) $payment['id'], $gateway['token'], $gateway['redirect_url']);
                $payment = $paymentModel->findByBooking((int) $booking['id']);
            } catch (Throwable $exception) {
                $gatewayError = $exception->getMessage();
            }
        }

        $this->view('payments/checkout', [
            'title' => 'Pembayaran booking',
            'booking' => $booking,
            'payment' => $payment,
            'paid' => false,
            'gatewayError' => $gatewayError,
            'isLive' => PaymentService::isConfigured(),
        ]);
    }

    public function completeDemo(): void
    {
        requireLogin();
        $this->requirePost();
        verify_csrf();
        if (PaymentService::isConfigured()) {
            flash('error', 'Gunakan halaman pembayaran Midtrans untuk menyelesaikan transaksi.');
            $this->redirect('payment/checkout&id=' . (int) ($_POST['booking_id'] ?? 0));
        }
        $payment = new Payment();
        $booking = $payment->checkoutData((int) ($_POST['booking_id'] ?? 0), (int) currentUser()['id']);
        if ($booking && !empty($booking['payment_id'])) {
            $payment->markDemoPaid((int) $booking['payment_id'], (int) currentUser()['id']);
            flash('success', 'Pembayaran demo berhasil. Booking dikonfirmasi.');
        }
        $this->redirect('bookings');
    }

    public function webhook(): void
    {
        $payload = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            http_response_code(400);
            echo json_encode(['message' => 'Invalid JSON']);
            return;
        }
        try {
            (new Payment())->updateFromNotification($payload, (string) ($_ENV['MIDTRANS_SERVER_KEY'] ?? ''));
            header('Content-Type: application/json');
            echo json_encode(['status' => 'ok']);
        } catch (Throwable $exception) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => $exception->getMessage()]);
        }
    }
}