<?php
declare(strict_types=1);

final class PaymentService
{
    public static function isConfigured(): bool
    {
        return ($_ENV['PAYMENT_PROVIDER'] ?? 'demo') === 'midtrans'
            && trim((string) ($_ENV['MIDTRANS_SERVER_KEY'] ?? '')) !== '';
    }

    public static function createSnapTransaction(array $booking, string $orderId): array
    {
        $serverKey = trim((string) ($_ENV['MIDTRANS_SERVER_KEY'] ?? ''));
        $production = filter_var($_ENV['MIDTRANS_IS_PRODUCTION'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $endpoint = $production
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $booking['total_price'],
            ],
            'item_details' => [[
                'id' => 'FIELD-' . $booking['field_id'],
                'price' => (int) $booking['total_price'],
                'quantity' => 1,
                'name' => substr($booking['field_name'] . ' · ' . $booking['booking_date'], 0, 50),
            ]],
            'customer_details' => [
                'first_name' => $booking['customer_name'],
                'email' => $booking['customer_email'],
                'phone' => $booking['customer_phone'] ?? '',
            ],
        ];

        if (!function_exists('curl_init')) {
            throw new RuntimeException('Ekstensi cURL PHP belum aktif. Aktifkan php_curl atau gunakan mode demo.');
        }

        $curl = curl_init($endpoint);
        curl_setopt_array($curl, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode($serverKey . ':'),
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);
        $response = curl_exec($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $curlError = curl_error($curl);
        curl_close($curl);

        $result = json_decode((string) $response, true);
        if ($response === false || $curlError !== '' || $httpCode < 200 || $httpCode >= 300 || !is_array($result) || empty($result['redirect_url'])) {
            $message = $result['error_messages'][0] ?? ($curlError !== '' ? $curlError : 'Midtrans menolak transaksi.');
            throw new RuntimeException('Pembayaran belum bisa dibuat: ' . $message);
        }

        return [
            'token' => $result['token'] ?? null,
            'redirect_url' => $result['redirect_url'],
        ];
    }
}