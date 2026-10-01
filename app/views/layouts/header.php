<?php
$pageTitle = isset($title) ? $title . ' · ' . APP_NAME : APP_NAME;
$successMessage = flash('success');
$errorMessage = flash('error');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Swoosh — platform sewa dan menyewakan lapangan basket.">
    <title><?= e($pageTitle) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/style.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/field-images.css') ?>">
</head>
<body>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="<?= url() ?>">
            <span class="brand-mark"><span></span><span></span><span></span></span>
            <span>Swoosh</span>
        </a>
        <nav class="main-nav">
            <a href="<?= url('fields') ?>">Cari lapangan</a>
            <?php if (isLoggedIn()): ?>
                <a href="<?= url('calendar') ?>">Kalender</a>
                <?php if (isAdmin()): ?>
                    <a href="<?= url('admin/fields') ?>">Lapangan saya</a>
                    <a href="<?= url('admin/bookings') ?>">Booking masuk</a>
                <?php else: ?>
                    <a href="<?= url('bookings') ?>">Booking saya</a>
                <?php endif; ?>
            <?php endif; ?>
        </nav>
        <div class="nav-actions">
            <?php if (isLoggedIn()): ?>
                <a class="user-chip" href="<?= url('dashboard') ?>"><span class="avatar"><?= e(strtoupper(substr(currentUser()['name'], 0, 1))) ?></span><?= e(currentUser()['name']) ?></a>
                <a class="btn btn-ghost btn-small" href="<?= url('logout') ?>">Keluar</a>
            <?php else: ?>
                <a class="btn btn-ghost btn-small" href="<?= url('login') ?>">Masuk</a>
                <a class="btn btn-dark btn-small" href="<?= url('register') ?>">Daftar</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<main>
    <?php if ($successMessage): ?><div class="container flash flash-success"><?= e($successMessage) ?></div><?php endif; ?>
    <?php if ($errorMessage): ?><div class="container flash flash-error"><?= e($errorMessage) ?></div><?php endif; ?>