<?php require APP_PATH . '/views/layouts/header.php'; ?>
<section class="page-head compact">
    <div class="container page-head-inner">
        <div>
            <div class="eyebrow">Explore courts</div>
            <h1>Cari lapangan yang <em>pas.</em></h1>
            <p>Lokasi tepat, waktu fleksibel, main tanpa ribet.</p>
        </div>
    </div>
</section>
<section class="section section-light listing-section">
    <div class="container">
        <form class="filter-bar" method="get">
            <input type="hidden" name="route" value="fields">
            <div class="search-input">⌕<input type="search" name="search" value="<?= e($_GET['search'] ?? '') ?>" placeholder="Cari nama atau lokasi..."></div>
            <select name="city">
                <option value="">Semua kota</option>
                <?php foreach ($cities as $city): ?>
                    <option value="<?= e($city) ?>" <?= ($_GET['city'] ?? '') === $city ? 'selected' : '' ?>><?= e($city) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-dark" type="submit">Terapkan filter</button>
        </form>
        <div class="listing-meta"><span><?= count($fields) ?> lapangan ditemukan</span><span class="sort-label">Diperbarui terbaru ↕</span></div>
        <div class="field-grid field-grid-large">
            <?php foreach ($fields as $field): ?>
                <a class="field-card" href="<?= url('field/show', ['id' => $field['id']]) ?>">
                    <div class="field-image <?= $field['id'] % 3 === 1 ? 'field-image-2' : ($field['id'] % 3 === 2 ? 'field-image-3' : '') ?>">
                        <?php if ($imageUrl = safeImageUrl($field)): ?><img class="field-photo" src="<?= e($imageUrl) ?>" alt="<?= e($field['name']) ?>" loading="lazy" onerror="this.remove()"><?php endif; ?>
                        <span class="field-tag"><?= e($field['city']) ?></span>
                        <span class="field-arrow">↗</span>
                        <span class="field-surface-line"></span>
                    </div>
                    <div class="field-info">
                        <div><h3><?= e($field['name']) ?></h3><p>⌖ <?= e($field['location']) ?></p></div>
                        <strong><?= formatRupiah($field['price_per_hour']) ?><small>/jam</small></strong>
                    </div>
                </a>
            <?php endforeach; ?>
            <?php if (!$fields): ?>
                <div class="empty-state"><div class="empty-icon">⌁</div><h3>Belum menemukan court</h3><p>Coba ubah kata kunci atau pilih kota lain.</p></div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php require APP_PATH . '/views/layouts/footer.php'; ?>