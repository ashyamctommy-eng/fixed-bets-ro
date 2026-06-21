        </div><!-- .admin-content -->
        <footer class="admin-footer">
            <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?> Admin Panel</p>
        </footer>
    </main>
</div><!-- .admin-wrapper -->
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
<script src="<?= SITE_URL ?>/assets/js/admin.js"></script>
<?php if (isset($extraJs)): foreach ((array)$extraJs as $js): ?>
<script src="<?= SITE_URL ?>/assets/js/<?= $js ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
