<?php
// ============================================================
// USER - ANNOUNCEMENTS
// FIXED BETS RO 🇷🇴
// ============================================================

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth();

$announcements = $pdo->query("
    SELECT * FROM announcements WHERE is_active = 1 
    ORDER BY FIELD(priority, 'urgent', 'high', 'normal', 'low'), created_at DESC
")->fetchAll();

$pageTitle = 'Announcements';
require_once __DIR__ . '/../includes/user_header.php';
?>

<div class="dash-card">
    <div class="dash-card-header">
        <h3>📢 Announcements</h3>
    </div>
    <div class="dash-card-body">
        <?php if (count($announcements) > 0): ?>
        <?php foreach ($announcements as $a): ?>
        <div class="announcement-card <?= $a['priority'] ?>" style="margin-bottom: 1rem;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
                <div class="announcement-title"><?= e($a['title']) ?></div>
                <span class="announcement-priority <?= $a['priority'] ?>"><?= e($a['priority']) ?></span>
            </div>
            <div class="announcement-message"><?= nl2br(e($a['message'])) ?></div>
            <div class="announcement-date">📅 <?= formatDateTime($a['created_at']) ?></div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="empty-state">
            <span class="empty-icon">📢</span>
            <p>No announcements at this time.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/user_footer.php'; ?>
