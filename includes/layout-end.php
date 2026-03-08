        </div>
    </main>
    <div id="toast-container" class="toast-container" aria-live="polite"></div>
    <script src="<?= APP_URL ?>/assets/js/app.js"></script>
    <?php if (isset($extraScripts)): ?>
    <?php foreach ($extraScripts as $s): ?>
    <script src="<?= APP_URL ?>/<?= $s ?>"></script>
    <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
