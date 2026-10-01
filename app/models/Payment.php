<?php
declare(strict_types=1);

final class Payment extends Model
{
    public function checkoutData(int $bookingId, int $userId): ?array
    {
        $statement = $this->db->prepare(
            'SELECT b.*, f.name AS field_name, f.city, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone,
                    p.id AS payment_id, p.order_id, p.status AS payment_status, p.payment_type, p.snap_token, p.redirect_url
             FROM bookings b JOIN fields f ON f.id = b.field_id JOIN users u ON u.id = b.user_id
             LEFT JOIN payments p ON p.booking_id = b.id
             WHERE b.id = ? AND b.user_id = ? LIMIT 1'
        );
        $statement->execute([$bookingId, $userId]);
        return $statement->fetch() ?: null;
    }

    public function createPending(int $bookingId, int $amount): array
    {
        $existing = $this->findByBooking($bookingId);
        if ($existing) {
            return $existing;
        }

        $orderId = 'SWOOSH-' . $bookingId . '-' . date('ymdHis');
        $statement = $this->db->prepare(
            'INSERT INTO payments (booking_id, order_id, gross_amount, status) VALUES (?, ?, ?, "pending")'
        );
        $statement->execute([$bookingId, $orderId, $amount]);
        return $this->findByBooking($bookingId);
    }

    public function findByBooking(int $bookingId): ?array
    {
        $statement = $this->db->prepare('SELECT * FROM payments WHERE booking_id = ? LIMIT 1');
        $statement->execute([$bookingId]);
        return $statement->fetch() ?: null;
    }

    public function updateGateway(int $paymentId, ?string $token, string $redirectUrl): void
    {
        $statement = $this->db->prepare(
            'UPDATE payments SET snap_token = ?, redirect_url = ?, payment_type = "midtrans_snap" WHERE id = ?'
        );
        $statement->execute([$token, $redirectUrl, $paymentId]);
    }

    public function markDemoPaid(int $paymentId, int $userId): void
    {
        $statement = $this->db->prepare(
            'UPDATE payments p JOIN bookings b ON b.id = p.booking_id
             SET p.status = "paid", p.payment_type = "demo", p.paid_at = NOW(), b.status = "confirmed"
             WHERE p.id = ? AND b.user_id = ? AND p.status = "pending"'
        );
        $statement->execute([$paymentId, $userId]);
    }

    public function updateFromNotification(array $notification, string $serverKey): void
    {
        $orderId = (string) ($notification['order_id'] ?? '');
        $statusCode = (string) ($notification['status_code'] ?? '');
        $grossAmount = (string) ($notification['gross_amount'] ?? '');
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        if ($orderId === '' || !hash_equals($signature, (string) ($notification['signature_key'] ?? ''))) {
            throw new RuntimeException('Signature pembayaran tidak valid.');
        }

        $transactionStatus = (string) ($notification['transaction_status'] ?? '');
        $fraudStatus = (string) ($notification['fraud_status'] ?? '');
        $status = match ($transactionStatus) {
            'settlement' => 'paid',
            'capture' => $fraudStatus === 'deny' ? 'failed' : 'paid',
            'expire' => 'expired',
            'cancel', 'deny', 'failure' => 'failed',
            default => 'pending',
        };

        $statement = $this->db->prepare(
            'UPDATE payments p JOIN bookings b ON b.id = p.booking_id
             SET p.status = :status, p.payment_type = :type, p.paid_at = CASE WHEN :paid_at = 1 THEN NOW() ELSE p.paid_at END,
                 b.status = CASE WHEN :paid_status = 1 THEN "confirmed" WHEN :cancelled = 1 THEN "cancelled" ELSE b.status END
             WHERE p.order_id = :order_id'
        );
        $paid = $status === 'paid' ? 1 : 0;
        $cancelled = in_array($status, ['failed', 'expired'], true) ? 1 : 0;
        $statement->execute([
            ':status' => $status,
            ':type' => $notification['payment_type'] ?? 'midtrans',
            ':paid_at' => $paid,
            ':paid_status' => $paid,
            ':cancelled' => $cancelled,
            ':order_id' => $orderId,
        ]);
    }

    public function forUser(int $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT b.*, f.name AS field_name, f.location, f.city, p.status AS payment_status, p.id AS payment_id_real
             FROM bookings b JOIN fields f ON f.id = b.field_id
             LEFT JOIN payments p ON p.booking_id = b.id
             WHERE b.user_id = ? ORDER BY b.booking_date DESC, b.start_time DESC'
        );
        $statement->execute([$userId]);
        return $statement->fetchAll();
    }
}