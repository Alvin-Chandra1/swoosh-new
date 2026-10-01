<?php require APP_PATH . '/views/layouts/header.php'; ?>
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-copy">
            <div class="eyebrow"><span class="eyebrow-dot"></span> The court is yours</div>
            <h1>Temukan lapangan.<br><em>Menangkan momen.</em></h1>
            <p class="hero-lead">Swoosh menghubungkan kamu dengan lapangan basket terbaik di kotamu — untuk bermain, atau menyewakan court milikmu.</p>
            <div class="hero-actions">
                <a class="btn btn-primary" href="<?= url('fields') ?>">Cari lapangan <span>↗</span></a>
                <?php if (!isLoggedIn()): ?><a class="text-link" href="<?= url('register') ?>">Punya lapangan? Mulai menyewakan →</a><?php endif; ?>
            </div>
            <div class="hero-proof"><div class="avatar-stack"><i>R</i><i>A</i><i>D</i><i>+</i></div><span><strong>1.2k+</strong> pemain sudah menemukan court-nya</span></div>
        </div>
        <div class="hero-visual">
            <div class="court-card">
                <div class="court-surface">
                    <div class="court-halfline"></div><div class="court-circle"></div><div class="court-key left"></div><div class="court-key right"></div><div class="court-arc left"></div><div class="court-arc right"></div>
                    <span class="court-label">SWOOSH / 01</span>
                    <span class="court-coord">-6.2088° S<br>106.8456° E</span>
                </div>
                <div class="court-card-meta"><div><span class="overline">NEXT AVAILABLE</span><strong>Hari ini, 18:00</strong></div><a href="<?= url('fields') ?>" class="round-arrow">↗</a></div>
            </div>
            <div class="floating-note note-top"><span class="note-icon">✦</span><div><strong>Pick your court</strong><small>Lebih banyak pilihan, lebih sedikit chat</small></div></div>
            <div class="floating-note note-bottom"><span class="note-icon note-orange">✓</span><div><strong>Booking aman</strong><small>Slot terkonfirmasi real-time</small></div></div>
        </div>
    </div>
</section>
<section class="marquee"><div class="marquee-track">FIND YOUR FLOW <span>✦</span> RENT YOUR COURT <span>✦</span> PLAY YOUR GAME <span>✦</span> FIND YOUR FLOW <span>✦</span> RENT YOUR COURT <span>✦</span></div></section>
<section class="section section-light">
    <div class="container">
        <div class="section-heading"><div><div class="eyebrow">Pilih tempatmu</div><h2>Lapangan yang siap<br><em>dipakai main.</em></h2></div><a class="text-link" href="<?= url('fields') ?>">Lihat semua lapangan →</a></div>
        <div class="field-grid">
            <?php foreach (array_slice($fields, 0, 3) as $field): ?>
                <a class="field-card" href="<?= url('field/show', ['id' => $field['id']]) ?>">
                    <div class="field-image <?= $field['id'] % 3 === 1 ? 'field-image-2' : ($field['id'] % 3 === 2 ? 'field-image-3' : '') ?>">
                        <?php if ($imageUrl = safeImageUrl($field)): ?><img class="field-photo" src="<?= e($imageUrl) ?>" alt="<?= e($field['name']) ?>" loading="lazy" onerror="this.remove()"><?php endif; ?>
                        <span class="field-tag"><?= e($field['city']) ?></span><span class="field-arrow">↗</span><span class="field-surface-line"></span>
                    </div>
                    <div class="field-info"><div><h3><?= e($field['name']) ?></h3><p>⌖ <?= e($field['location']) ?></p></div><strong><?= formatRupiah($field['price_per_hour']) ?><small>/jam</small></strong></div>
                </a>
            <?php endforeach; ?>
            <?php if (!$fields): ?><div class="empty-state"><div class="empty-icon">⌁</div><h3>Belum ada lapangan</h3><p>Jadilah admin pertama yang menambahkan court ke Swoosh.</p></div><?php endif; ?>
        </div>
    </div>
</section>
<section class="section section-dark">
    <div class="container feature-grid">
        <div><div class="eyebrow eyebrow-light">Satu platform, dua peran</div><h2>Rent atau<br><em>Renting.</em></h2><p class="dark-copy">Kamu yang menentukan langkah berikutnya. Cari court untuk bermain, atau buka lapanganmu untuk lebih banyak pemain.</p></div>
        <div class="feature-list"><div class="feature-item"><b>01</b><div><h3>Find your court</h3><p>Filter lokasi, lihat harga, dan cek jadwal kosong dalam satu tampilan.</p></div></div><div class="feature-item"><b>02</b><div><h3>Make it yours</h3><p>Booking slot tanpa chat panjang. Kalender selalu ter-update.</p></div></div><div class="feature-item"><b>03</b><div><h3>Rent it out</h3><p>Kelola lapangan, jam operasional, dan booking dari dashboard admin.</p></div></div></div>
    </div>
</section>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>