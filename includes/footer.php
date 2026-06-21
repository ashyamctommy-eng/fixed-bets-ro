<?php
$telegramLink = getSiteSetting($pdo, 'telegram_link') ?? 'https://t.me/fixedbetsro';
$whatsappLink = getSiteSetting($pdo, 'whatsapp_link') ?? 'https://wa.me/40700000000';
?>
    <footer class="site-footer">
        <div class="footer-content">
            <p>&copy; <?= date('Y') ?> <a href="<?= SITE_URL ?>"><?= SITE_NAME ?></a> — All rights reserved.</p>
            <div class="footer-links">
                <a href="<?= e($telegramLink) ?>" target="_blank" class="social-link telegram-link">💬 Telegram</a>
                <a href="<?= e($whatsappLink) ?>" target="_blank" class="social-link whatsapp-link">📱 WhatsApp</a>
            </div>
        </div>
    </footer>
</div><!-- .app-wrapper -->
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
<?php if (isset($extraJs)): foreach ((array)$extraJs as $js): ?>
<script src="<?= SITE_URL ?>/assets/js/<?= $js ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
