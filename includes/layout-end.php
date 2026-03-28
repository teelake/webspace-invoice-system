        </div>
    </main>
    <div id="toast-container" class="toast-container" aria-live="polite"></div>
    <script src="<?= APP_URL ?>/assets/js/app.js"></script>
    <?php if (isset($extraScripts)): ?>
    <?php foreach ($extraScripts as $s): ?>
    <script src="<?= preg_match('#^https?://#i', $s) ? htmlspecialchars($s) : htmlspecialchars(APP_URL . '/' . ltrim($s, '/')) ?>"></script>
    <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
