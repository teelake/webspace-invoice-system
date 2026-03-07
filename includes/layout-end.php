        </div>
    </main>
    <script src="<?= APP_URL ?>/assets/js/app.js"></script>
    <?php if (isset($extraScripts)): ?>
    <?php foreach ($extraScripts as $s): ?>
    <script src="<?= APP_URL ?>/<?= $s ?>"></script>
    <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
