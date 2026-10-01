<?php require APP_PATH . '/views/layouts/header.php'; ?>
<section class="detail-hero">
    <div class="container">
        <a class="back-link" href="<?= url('fields') ?>">← Kembali ke semua lapangan</a>
        <div class="detail-grid">
            <div class="detail-visual <?= $field['id'] % 3 === 1 ? 'field-image-2' : ($field['id'] % 3 === 2 ? 'field-image-3' : '') ?>">
                <?php if ($imageUrl = safeImageUrl($field)): ?><img class="field-photo" src="<?= e($imageUrl) ?>" alt="<?= e($field['name']) ?>" loading="lazy" onerror="this.remove()"><?php endif; ?>
                <span class="field-tag"><?= e($field['city']) ?></span>
                <span class="detail-code">SWOOSH / COURT <?= str_pad((string) $field['id'], 2, '0', STR_PAD_LEFT) ?></span>
                <span class="field-surface-line"></span>
            </div>
            <div class="detail-copy">
                <div class="eyebrow">Available court</div>
                <h1><?= e($field['name']) ?></h1>
                <p class="detail-location">⌖ <?= e($field['location']) ?>, <?= e($field['city']) ?></p>
                <p><?= e($field['description'] ?: 'Lapangan basket pilihan untuk latihan, pickup game, dan pertandingan komunitas.') ?></p>
                <div class="detail-facts">
                    <div><span class="overline">HARGA</span><strong><?= formatRupiah($field['price_per_hour']) ?><small>/jam</small></strong></div>
                    <div><span class="overline">BUKA</span><strong><?= substr($field['open_time'], 0, 5) ?>—<?= substr($field['close_time'], 0, 5) ?></strong></div>
                </div>
                <div class="amenities"><?= e($field['amenities'] ?: 'Indoor · Lampu malam · Parkir tersedia') ?></div>
            </div>
        </div>
    </div>
</section>
<section class="section section-light booking-section">
    <div class="container booking-layout">
        <div>
            <div class="eyebrow">Pick your slot</div>
            <h2>Atur jadwal mainmu.</h2>
            <p class="muted">Pilih tanggal dan jam. Slot yang sudah dipesan akan ditandai dan tidak bisa dipilih.</p>
            <div class="calendar-picker">
                <label>Tanggal bermain<input id="booking-date" type="date" min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>"></label>
                <div id="slot-list" class="slot-list"><div class="slot-loading">Pilih tanggal untuk melihat slot...</div></div>
            </div>
        </div>
        <div class="booking-form-card">
            <?php if (isLoggedIn() && !isAdmin()): ?>
                <form method="post" action="<?= url('booking/store') ?>" id="booking-form">
                    <input type="hidden" name="_csrf" value="<?= csrf_token() ?>">
                    <input type="hidden" name="field_id" value="<?= e($field['id']) ?>">
                    <input type="hidden" name="start_time" id="selected-start">
                    <input type="hidden" name="booking_date" id="selected-date" value="<?= date('Y-m-d') ?>">
                    <span class="overline">YOUR BOOKING</span>
                    <h3><?= e($field['name']) ?></h3>
                    <div class="selected-summary" id="selected-summary">Pilih slot dari kalender</div>
                    <label>Durasi bermain
                        <select name="duration_hours" id="duration-hours">
                            <option value="1">1 jam</option><option value="2">2 jam</option><option value="3">3 jam</option>
                        </select>
                    </label>
                    <label>Catatan <span class="optional">(opsional)</span><textarea name="notes" rows="3" placeholder="Contoh: butuh bola, datang dengan tim..."></textarea></label>
                    <div class="total-line"><span>Estimasi total</span><strong id="total-price">—</strong></div>
                    <button class="btn btn-primary btn-wide" type="submit" id="book-submit" disabled>Booking sekarang <span>↗</span></button>
                </form>
            <?php elseif (!isLoggedIn()): ?>
                <span class="overline">READY TO PLAY?</span><h3>Masuk untuk booking.</h3>
                <p>Buat akun gratis untuk memilih slot dan menyimpan jadwalmu.</p>
                <a class="btn btn-primary btn-wide" href="<?= url('login') ?>">Masuk untuk booking ↗</a>
                <a class="text-link centered" href="<?= url('register') ?>">Belum punya akun? Daftar</a>
            <?php else: ?>
                <span class="overline">OWNER VIEW</span><h3>Ini lapangan Anda.</h3>
                <p>Kelola detail dan lihat booking masuk dari dashboard admin.</p>
                <a class="btn btn-dark btn-wide" href="<?= url('admin/fields') ?>">Kelola lapangan</a>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>