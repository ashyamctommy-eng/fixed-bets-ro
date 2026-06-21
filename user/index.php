<?php
// ============================================================
// USER DASHBOARD
// FIXED BETS RO 🇷🇴
// ============================================================

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth();

$user = getCurrentUser();

// Check VIP access for content
$hasVIPAccess = checkVIPAccess($pdo);

// Get user's status
$status = null;
if ($user['status_id']) {
    $status = getStatusById($pdo, $user['status_id']);
}

// Get recent games (only if VIP access)
$recentGames = [];
if ($hasVIPAccess) {
    $stmt = $pdo->query("SELECT * FROM vip_games WHERE is_archived = 0 ORDER BY created_at DESC LIMIT 5");
    $recentGames = $stmt->fetchAll();
}

// Get active announcements
$announcements = $pdo->query("
    SELECT * FROM announcements WHERE is_active = 1 ORDER BY 
    FIELD(priority, 'urgent', 'high', 'normal', 'low'), created_at DESC 
    LIMIT 3
")->fetchAll();

// Get unread notifications
$notifications = $pdo->prepare("
    SELECT * FROM notifications 
    WHERE (user_id = ? OR type = 'global') AND is_read = 0
    ORDER BY created_at DESC LIMIT 5
");
$notifications->execute([$user['id']]);
$userNotifs = $notifications->fetchAll();

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/user_header.php';
?>

<!-- Welcome Section -->
<div class="welcome-card">
    <div class="welcome-title">👋 Welcome back, <?= e($user['full_name']) ?>!</div>
    <div class="welcome-subtitle">You are logged in as <strong>@<?= e($user['username']) ?></strong></div>
    <div class="welcome-badges">
        <?php if ($status): ?>
        <span class="badge" style="background: <?= e($status['color']) ?>; font-size: 0.9rem; padding: 6px 16px;">
            <?= e($status['icon']) ?> <?= e($status['title']) ?>
        </span>
        <?php endif; ?>
        
        <?php if ($hasVIPAccess): ?>
        <span class="badge badge-gold" style="font-size: 0.9rem; padding: 6px 16px;">⭐ VIP Active</span>
        <?php else: ?>
        <span class="badge badge-danger" style="font-size: 0.9rem; padding: 6px 16px;">🔒 VIP Inactive</span>
        <?php endif; ?>
    </div>
</div>

<?php if (!$hasVIPAccess && $status): ?>
<!-- Restricted Access Message -->
<div class="restricted-card" style="margin-bottom: 2rem;">
    <div style="font-size: 3rem; margin-bottom: 1rem;"><?= e($status['icon'] ?? '🔒') ?></div>
    <h2 style="color: <?= e($status['color'] ?? '#dc3545') ?>; margin-bottom: 0.75rem;">
        <?= e($status['title'] ?? 'Access Restricted') ?>
    </h2>
    <p style="color: var(--text-secondary); margin-bottom: 1.5rem; line-height: 1.6;">
        <?= nl2br(e($status['message'] ?? 'Your account currently has restricted access. Please contact support.')) ?>
    </p>
    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
        <?php
        $tg = getSiteSetting($pdo, 'telegram_link') ?? '#';
        $wa = getSiteSetting($pdo, 'whatsapp_link') ?? '#';
        ?>
        <a href="<?= e($tg) ?>" target="_blank" class="btn btn-telegram">💬 Telegram Support</a>
        <a href="<?= e($wa) ?>" target="_blank" class="btn btn-whatsapp">📱 WhatsApp Support</a>
    </div>
</div>
<?php endif; ?>

<div class="dash-grid">
    <?php if ($hasVIPAccess && count($recentGames) > 0): ?>
    <!-- Recent Games -->
    <div class="dash-card dash-grid-full">
        <div class="dash-card-header">
            <h3>🎯 Latest VIP Games</h3>
            <a href="games.php" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <div class="dash-card-body">
            <?php foreach ($recentGames as $game): ?>
            <div class="game-card" style="margin-bottom: 0.75rem;">
                <div class="game-card-header">
                    <div>
                        <div class="game-match"><?= e($game['match_name']) ?></div>
                        <?php if ($game['league']): ?>
                        <div class="game-league"><?= e($game['league']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div style="text-align: right;">
                        <?php if ($game['confidence']): ?>
                        <span class="game-confidence <?= strtolower($game['confidence']) ?>"><?= e($game['confidence']) ?></span>
                        <?php endif; ?>
                        <div class="game-odds" style="margin-top: 4px;">@<?= number_format($game['odds'], 2) ?></div>
                    </div>
                </div>
                <div style="color: var(--gold); font-weight: 600; margin-bottom: 0.5rem;">
                    🎯 <?= e($game['selection']) ?>
                </div>
                <div class="game-details">
                    <div class="game-detail">
                        <span class="game-detail-label">Date</span>
                        <span class="game-detail-value"><?= formatDate($game['match_date'], 'd M Y') ?></span>
                    </div>
                    <?php if ($game['match_time']): ?>
                    <div class="game-detail">
                        <span class="game-detail-label">Time</span>
                        <span class="game-detail-value"><?= formatDate($game['match_time'], 'H:i') ?></span>
                    </div>
                    <?php endif; ?>
                    <div class="game-detail">
                        <span class="game-detail-label">Result</span>
                        <span class="game-detail-value"><?= getResultBadge($game['result_status']) ?></span>
                    </div>
                </div>
                <?php if ($game['notes']): ?>
                <div class="game-notes"><?= nl2br(e($game['notes'])) ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($hasVIPAccess): ?>
    <!-- Announcements -->
    <div class="dash-card">
        <div class="dash-card-header">
            <h3>📢 Announcements</h3>
            <a href="announcements.php" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <div class="dash-card-body">
            <?php if (count($announcements) > 0): ?>
            <?php foreach ($announcements as $a): ?>
            <div class="announcement-card <?= $a['priority'] ?>" style="margin-bottom: 0.75rem;">
                <div class="announcement-title"><?= e($a['title']) ?></div>
                <div class="announcement-message"><?= nl2br(e($a['message'])) ?></div>
                <div class="announcement-date">📅 <?= formatDateTime($a['created_at']) ?></div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <p style="color: var(--text-muted); text-align: center; padding: 1rem;">No announcements.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Notifications -->
    <div class="dash-card">
        <div class="dash-card-header">
            <h3>🔔 Recent Notifications</h3>
            <a href="notifications.php" class="btn btn-sm btn-secondary">View All</a>
        </div>
        <div class="dash-card-body" style="padding: 0;">
            <?php if (count($userNotifs) > 0): ?>
            <?php foreach ($userNotifs as $n): ?>
            <div class="notif-item unread" data-id="<?= $n['id'] ?>" style="padding: 1rem 1.25rem;">
                <div class="notif-icon">📩</div>
                <div class="notif-content">
                    <div class="notif-title"><?= e($n['title']) ?></div>
                    <div class="notif-message"><?= e(mb_substr($n['message'], 0, 80)) ?><?= mb_strlen($n['message']) > 80 ? '...' : '' ?></div>
                    <div class="notif-time"><?= timeElapsed($n['created_at']) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <p style="color: var(--text-muted); text-align: center; padding: 1.5rem;">No new notifications.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/user_footer.php'; ?>
