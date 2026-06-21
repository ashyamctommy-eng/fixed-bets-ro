<?php
// ============================================================
// USER - NOTIFICATIONS
// FIXED BETS RO 🇷🇴
// ============================================================

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth();

$user = getCurrentUser();

// Mark notification as read via GET
if (isset($_GET['mark_read'])) {
    $notifId = (int)$_GET['mark_read'];
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND (user_id = ? OR type = 'global')");
    $stmt->execute([$notifId, $user['id']]);
    redirect('notifications.php');
}

// Mark all as read
if (isset($_GET['mark_all'])) {
    $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE (user_id = ? OR type = 'global') AND is_read = 0")->execute([$user['id']]);
    redirect('notifications.php');
}

// Delete notification
if (isset($_GET['delete'])) {
    $notifId = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND (user_id = ? OR type = 'global')");
    $stmt->execute([$notifId, $user['id']]);
    redirect('notifications.php');
}

$notifications = $pdo->prepare("
    SELECT * FROM notifications 
    WHERE (user_id = ? OR type = 'global') 
    ORDER BY created_at DESC
");
$notifications->execute([$user['id']]);
$allNotifs = $notifications->fetchAll();

// Count
$unreadCount = 0;
foreach ($allNotifs as $n) {
    if (!$n['is_read']) $unreadCount++;
}

$pageTitle = 'Notifications';
require_once __DIR__ . '/../includes/user_header.php';
?>

<div class="dash-card">
    <div class="dash-card-header">
        <h3>🔔 Notifications</h3>
        <div style="display: flex; gap: 0.5rem;">
            <?php if ($unreadCount > 0): ?>
            <a href="?mark_all=1" class="btn btn-sm btn-primary">✅ Mark All Read</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="dash-card-body" style="padding: 0;">
        <?php if (count($allNotifs) > 0): ?>
        <?php foreach ($allNotifs as $n): ?>
        <div class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>" data-id="<?= $n['id'] ?>">
            <div class="notif-icon"><?= $n['type'] === 'global' ? '🌍' : '📩' ?></div>
            <div class="notif-content">
                <div class="notif-title"><?= e($n['title']) ?></div>
                <div class="notif-message"><?= nl2br(e($n['message'])) ?></div>
                <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap; margin-top: 4px;">
                    <span class="notif-time">📅 <?= timeElapsed($n['created_at']) ?></span>
                    <span class="announcement-priority <?= $n['priority'] ?>"><?= e($n['priority']) ?></span>
                    <?php if ($n['type'] === 'global'): ?>
                    <span class="badge badge-info" style="font-size: 0.7rem;">🌍 All Users</span>
                    <?php endif; ?>
                </div>
                <div class="notif-actions">
                    <?php if (!$n['is_read']): ?>
                    <a href="?mark_read=<?= $n['id'] ?>" class="btn btn-sm btn-primary">✅ Mark Read</a>
                    <?php endif; ?>
                    <a href="?delete=<?= $n['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this notification?')">🗑️ Delete</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="empty-state">
            <span class="empty-icon">🔔</span>
            <p>No notifications yet.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/user_footer.php'; ?>
