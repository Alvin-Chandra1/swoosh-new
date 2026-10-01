<?php
require APP_PATH . '/views/layouts/header.php';
$monthTime = strtotime($month . '-01');
$daysInMonth = (int) date('t', $monthTime);
$firstDay = (int) date('N', $monthTime);
$monthNames = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$eventsByDay = [];
foreach ($bookings as $booking) {
    $date = isAdmin() ? $booking['booking_date'] : $booking['start'];
    $eventsByDay[(int) date('j', strtotime($date))][] = $booking;
}
$previous = date('Y-m', strtotime('-1 month', $monthTime));
$next = date('Y-m', strtotime('+1 month', $monthTime));
?>
<section class="page-head compact"><div class="container page-head-inner"><div><div class="eyebrow">Your schedule</div><h1>Kalender <em>main.</em></h1><p><?= isAdmin() ? 'Pantau semua booking yang masuk ke lapanganmu.' : 'Satu tampilan untuk semua jadwal di court pilihanmu.' ?></p></div></div></section>
<section class="section section-light"><div class="container calendar-page"><div class="calendar-toolbar"><a class="round-arrow" href="<?= url('calendar', ['month' => $previous]) ?>">←</a><h2><?= $monthNames[(int) date('n', $monthTime)] ?> <?= date('Y', $monthTime) ?></h2><a class="round-arrow" href="<?= url('calendar', ['month' => $next]) ?>">→</a><a class="text-link today-link" href="<?= url('calendar') ?>">Hari ini</a></div><div class="month-calendar"><div class="calendar-weekdays"><span>Senin</span><span>Selasa</span><span>Rabu</span><span>Kamis</span><span>Jumat</span><span>Sabtu</span><span>Minggu</span></div><div class="calendar-days"><?php for ($i = 1; $i < $firstDay; $i++): ?><div class="calendar-day blank"></div><?php endfor; ?><?php for ($day = 1; $day <= $daysInMonth; $day++): $dateString = $month . '-' . str_pad((string) $day, 2, '0', STR_PAD_LEFT); $isToday = $dateString === date('Y-m-d'); ?><div class="calendar-day <?= $isToday ? 'today' : '' ?>"><span class="day-number"><?= $day ?></span><?php if (isset($eventsByDay[$day])): foreach (array_slice($eventsByDay[$day], 0, 2) as $event): ?><div class="calendar-event <?= ($event['status'] ?? '') === 'cancelled' ? 'event-cancelled' : '' ?>"><strong><?= substr($event['start_time'] ?? '00:00', 0, 5) ?></strong><span><?= e($event['field_name']) ?></span></div><?php endforeach; if (count($eventsByDay[$day]) > 2): ?><small class="more-events">+<?= count($eventsByDay[$day]) - 2 ?> lainnya</small><?php endif; endif; ?></div><?php endfor; ?></div></div><div class="calendar-legend"><span><i class="legend-dot"></i> Booking aktif</span><span><i class="legend-dot legend-muted"></i> Dibatalkan</span></div></div></section>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>