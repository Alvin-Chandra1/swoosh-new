<?php
declare(strict_types=1);

final class Booking extends Model
{
    public function forUser(int $userId, int $limit = 20): array
    {
        $limit = max(1, min(100, $limit));
        $statement = $this->db->prepare(
            "SELECT b.*, f.name AS field_name, f.location, f.city
             FROM bookings b JOIN fields f ON f.id = b.field_id
             WHERE b.user_id = ? ORDER BY b.booking_date DESC, b.start_time DESC LIMIT {$limit}"
        );
        $statement->execute([$userId]);
        return $statement->fetchAll();
    }

    public function forAdmin(int $ownerId): array
    {
        $statement = $this->db->prepare(
            'SELECT b.*, f.name AS field_name, f.city, u.name AS customer_name, u.email AS customer_email, u.phone AS customer_phone
             FROM bookings b JOIN fields f ON f.id = b.field_id JOIN users u ON u.id = b.user_id
             WHERE f.owner_id = ? ORDER BY b.booking_date DESC, b.start_time DESC'
        );
        $statement->execute([$ownerId]);
        return $statement->fetchAll();
    }

    public function upcomingForUser(int $userId): array
    {
        $statement = $this->db->prepare(
            'SELECT b.*, f.name AS field_name FROM bookings b JOIN fields f ON f.id = b.field_id
             WHERE b.user_id = ? AND b.booking_date >= CURDATE() AND b.status <> "cancelled"
             ORDER BY b.booking_date, b.start_time LIMIT 5'
        );
        $statement->execute([$userId]);
        return $statement->fetchAll();
    }

    public function upcomingForAdmin(int $ownerId): array
    {
        $statement = $this->db->prepare(
            'SELECT b.*, f.name AS field_name, u.name AS customer_name
             FROM bookings b JOIN fields f ON f.id = b.field_id JOIN users u ON u.id = b.user_id
             WHERE f.owner_id = ? AND b.booking_date >= CURDATE() AND b.status <> "cancelled"
             ORDER BY b.booking_date, b.start_time LIMIT 8'
        );
        $statement->execute([$ownerId]);
        return $statement->fetchAll();
    }

    public function countForUser(int $userId): int
    {
        $statement = $this->db->prepare('SELECT COUNT(*) FROM bookings WHERE user_id = ? AND status <> "cancelled"');
        $statement->execute([$userId]);
        return (int) $statement->fetchColumn();
    }

    public function countForAdmin(int $ownerId): int
    {
        $statement = $this->db->prepare(
            'SELECT COUNT(*) FROM bookings b JOIN fields f ON f.id = b.field_id WHERE f.owner_id = ? AND b.status <> "cancelled"'
        );
        $statement->execute([$ownerId]);
        return (int) $statement->fetchColumn();
    }

    public function occupiedSlots(int $fieldId, string $date): array
    {
        $timezone = new DateTimeZone(date_default_timezone_get());
        $reference = new DateTimeImmutable($date . ' 00:00:00', $timezone);
        $fromDate = $reference->modify('-1 day')->format('Y-m-d');
        $throughDate = $reference->modify('+1 day')->format('Y-m-d');
        $statement = $this->db->prepare(
            'SELECT booking_date, start_time, end_time FROM bookings
             WHERE field_id = ? AND booking_date BETWEEN ? AND ?
               AND status IN (\'pending\', \'confirmed\')'
        );
        $statement->execute([$fieldId, $fromDate, $throughDate]);

        $occupied = [];
        foreach ($statement->fetchAll() as $booking) {
            $start = new DateTimeImmutable($booking['booking_date'] . ' ' . $booking['start_time'], $timezone);
            $end = new DateTimeImmutable($booking['booking_date'] . ' ' . $booking['end_time'], $timezone);
            if ($end <= $start) {
                $end = $end->modify('+1 day');
            }
            $startMinute = (int) (($start->getTimestamp() - $reference->getTimestamp()) / 60);
            $endMinute = (int) (($end->getTimestamp() - $reference->getTimestamp()) / 60);
            if ($endMinute > 0 && $startMinute < 2880) {
                $occupied[] = ['start_minute' => $startMinute, 'end_minute' => $endMinute];
            }
        }

        return $occupied;
    }

    public function create(array $data, int $userId): int
    {
        $fieldId = (int) ($data['field_id'] ?? 0);
        $date = (string) ($data['booking_date'] ?? '');
        $start = (string) ($data['start_time'] ?? '');
        $hours = (int) ($data['duration_hours'] ?? 0);
        $validDate = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if (!$validDate || $validDate->format('Y-m-d') !== $date) {
            throw new RuntimeException('Tanggal booking tidak valid.');
        }
        if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $start)) {
            throw new RuntimeException('Pilih jam booking yang valid.');
        }
        if (!in_array($hours, [1, 2, 3], true)) {
            throw new RuntimeException('Durasi booking harus 1 sampai 3 jam.');
        }
        if ($date < date('Y-m-d')) {
            throw new RuntimeException('Tanggal sewa tidak boleh berada di masa lalu.');
        }

        $this->db->beginTransaction();
        try {
            $fieldStatement = $this->db->prepare(
                'SELECT price_per_hour, open_time, close_time FROM fields WHERE id = ? AND is_active = 1 FOR UPDATE'
            );
            $fieldStatement->execute([$fieldId]);
            $field = $fieldStatement->fetch();
            if (!$field) {
                throw new RuntimeException('Lapangan tidak ditemukan.');
            }

            $openMinute = $this->minutesFromTime($field['open_time']);
            $closeMinute = $this->minutesFromTime($field['close_time']);
            $windowEnd = $closeMinute <= $openMinute ? $closeMinute + 1440 : $closeMinute;
            $startMinute = $this->minutesFromTime($start . ':00');
            if ($windowEnd > 1440 && $startMinute < ($windowEnd - 1440)) {
                $startMinute += 1440;
            }
            if ($startMinute < $openMinute || $startMinute + ($hours * 60) > $windowEnd) {
                throw new RuntimeException('Waktu sewa berada di luar jam operasional lapangan.');
            }

            $timezone = new DateTimeZone(date_default_timezone_get());
            $startDateTime = new DateTimeImmutable($date . ' ' . $start . ':00', $timezone);
            $endDateTime = $startDateTime->modify('+' . $hours . ' hours');
            $conflict = $this->db->prepare(
                'SELECT COUNT(*) FROM bookings
                 WHERE field_id = :field_id AND status IN ("pending", "confirmed")
                   AND TIMESTAMP(booking_date, start_time) < :candidate_end
                   AND CASE WHEN end_time <= start_time
                       THEN DATE_ADD(TIMESTAMP(booking_date, end_time), INTERVAL 1 DAY)
                       ELSE TIMESTAMP(booking_date, end_time)
                   END > :candidate_start'
            );
            $conflict->execute([
                ':field_id' => $fieldId,
                ':candidate_start' => $startDateTime->format('Y-m-d H:i:s'),
                ':candidate_end' => $endDateTime->format('Y-m-d H:i:s'),
            ]);
            if ((int) $conflict->fetchColumn() > 0) {
                throw new RuntimeException('Slot tersebut sudah dipesan. Pilih waktu lain.');
            }

            $statement = $this->db->prepare(
                'INSERT INTO bookings (user_id, field_id, booking_date, start_time, end_time, duration_hours, total_price, notes)
                 VALUES (:user_id, :field_id, :date, :start, :end, :hours, :total, :notes)'
            );
            $statement->execute([
                ':user_id' => $userId,
                ':field_id' => $fieldId,
                ':date' => $date,
                ':start' => $startDateTime->format('H:i:s'),
                ':end' => $endDateTime->format('H:i:s'),
                ':hours' => $hours,
                ':total' => (int) $field['price_per_hour'] * $hours,
                ':notes' => trim($data['notes'] ?? ''),
            ]);
            $bookingId = (int) $this->db->lastInsertId();
            $this->db->commit();
            return $bookingId;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }

    private function minutesFromTime(string $time): int
    {
        return ((int) substr($time, 0, 2) * 60) + (int) substr($time, 3, 2);
    }

    public function updateStatus(int $id, string $status, int $ownerId): void
    {
        $statement = $this->db->prepare(
            'UPDATE bookings b JOIN fields f ON f.id = b.field_id
             SET b.status = :status WHERE b.id = :id AND f.owner_id = :owner_id'
        );
        $statement->execute([':status' => $status, ':id' => $id, ':owner_id' => $ownerId]);
    }

    public function cancel(int $id, int $userId): void
    {
        $statement = $this->db->prepare('UPDATE bookings SET status = "cancelled" WHERE id = ? AND user_id = ? AND status IN ("pending", "confirmed")');
        $statement->execute([$id, $userId]);
    }

    public function calendarForUser(int $userId, string $from, string $to): array
    {
        $statement = $this->db->prepare(
            'SELECT b.id, b.booking_date AS start, b.start_time, b.end_time, b.status, f.name AS field_name
             FROM bookings b JOIN fields f ON f.id = b.field_id
             WHERE b.user_id = :user AND b.booking_date BETWEEN :from AND :to ORDER BY b.booking_date'
        );
        $statement->execute([':user' => $userId, ':from' => $from, ':to' => $to]);
        return $statement->fetchAll();
    }

    public function calendarForAdmin(int $ownerId, string $from, string $to): array
    {
        $statement = $this->db->prepare(
            'SELECT b.id, b.booking_date, b.start_time, b.end_time, b.status, f.name AS field_name, u.name AS customer_name
             FROM bookings b JOIN fields f ON f.id = b.field_id JOIN users u ON u.id = b.user_id
             WHERE f.owner_id = :owner AND b.booking_date BETWEEN :from AND :to ORDER BY b.booking_date, b.start_time'
        );
        $statement->execute([':owner' => $ownerId, ':from' => $from, ':to' => $to]);
        return $statement->fetchAll();
    }
}