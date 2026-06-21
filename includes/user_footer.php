    </main><!-- .dash-main -->
    <footer class="dash-footer">
        <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?> — All rights reserved.</p>
    </footer>
</div><!-- .dashboard-wrapper -->
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
<script src="<?= SITE_URL ?>/assets/js/dashboard.js"></script>
<?php if (isset($extraJs)): foreach ((array)$extraJs as $js): ?>
<script src="<?= SITE_URL ?>/assets/js/<?= $js ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
