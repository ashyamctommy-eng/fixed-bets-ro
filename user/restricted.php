<?php
// ============================================================
// RESTRICTED ACCESS PAGE
// FIXED BETS RO 🇷🇴
// ============================================================

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireAuth();

$user = getCurrentUser();
$status = null;
if ($user['status_id']) {
    $status = getStatusById($pdo, $user['status_id']);
}

$telegramLink = getSiteSetting($pdo, 'telegram_link') ?? '#';
$whatsappLink = getSiteSetting($pdo, 'whatsapp_link') ?? '#';

// If user has access, redirect to dashboard
if (checkVIPAccess($pdo)) {
    redirect('index.php');
}

$pageTitle = 'Access Restricted';
require_once __DIR__ . '/../includes/user_header.php';
?>

<div class="restricted-page">
    <div class="restricted-card">
        <div class="restricted-icon">🔒</div>
        <h1 class="restricted-title">Access Restricted</h1>
        <p class="restricted-message">
            Your VIP account has limited access. Please contact support for assistance.
        </p>
        
        <?php if ($status): ?>
        <div class="restricted-status" style="border-color: <?= e($status['color']) ?>;">
            <div class="restricted-status-title" style="color: <?= e($status['color']) ?>;">
                <?= e($status['icon']) ?> <?= e($status['title']) ?>
            </div>
            <div class="restricted-status-msg">
                <?= nl2br(e($status['message'])) ?>
            </div>
        </div>
        <?php else: ?>
        <div class="restricted-status">
            <div class="restricted-status-title" style="color: var(--gold);">🔒 VIP Not Active</div>
            <div class="restricted-status-msg">
                Your VIP access has not been enabled yet. Please contact support to activate your membership.
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Notifications section for restricted users -->
        <?php
        $notifications = $pdo->prepare("
            SELECT * FROM notifications 
            WHERE (user_id = ? OR type = 'global') AND is_read = 0
            ORDER BY created_at DESC LIMIT 3
        ");
        $notifications->execute([$user['id']]);
        $userNotifs = $notifications->fetchAll();
        ?>
        
        <?php if (count($userNotifs) > 0): ?>
        <div style="margin-bottom: 1.5rem; text-align: left;">
            <h3 style="font-size: 1rem; margin-bottom: 0.75rem; color: var(--text-primary);">📩 Your Notifications</h3>
            <?php foreach ($userNotifs as $n): ?>
            <div class="notif-item" style="padding: 0.75rem; background: rgba(255,255,255,0.03); border-radius: 8px; margin-bottom: 0.5rem;">
                <div class="notif-content">
                    <div class="notif-title" style="font-size: 0.9rem;"><?= e($n['title']) ?></div>
                    <div class="notif-message" style="font-size: 0.8rem;"><?= e($n['message']) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        
        <div class="restricted-support">
            <a href="<?= e($telegramLink) ?>" target="_blank" class="btn btn-telegram btn-lg">💬 Telegram Support</a>
            <a href="<?= e($whatsappLink) ?>" target="_blank" class="btn btn-whatsapp btn-lg">📱 WhatsApp Support</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/user_footer.php'; ?>
