</main>
<footer class="site-footer">
    <div class="container footer-inner">
        <div>
            <a class="brand brand-footer" href="<?= url() ?>"><span class="brand-mark"><span></span><span></span><span></span></span><span>Swoosh</span></a>
            <p>Make your next move.</p>
        </div>
        <div class="footer-note">Sewa. Main. Ulangi.</div>
    </div>
</footer>
<script>window.SWOOSH = { baseUrl: <?= json_encode(appBasePath()) ?>, csrf: <?= json_encode(csrf_token()) ?> };</script>
<script src="<?= asset('assets/app.js') ?>?v=<?= (int) filemtime(PUBLIC_PATH . '/assets/app.js') ?>"></script>
</body>
</html>