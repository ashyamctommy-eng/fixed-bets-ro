<?php
// ============================================================
// ADMIN - SITE SETTINGS
// FIXED BETS RO 🇷🇴
// ============================================================

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();

$pageTitle = 'Site Settings';
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid security token.';
    } else {
        switch ($action) {
            case 'update_settings':
                updateSiteSetting($pdo, 'site_name', sanitize($_POST['site_name'] ?? SITE_NAME));
                updateSiteSetting($pdo, 'site_logo', sanitize($_POST['site_logo'] ?? ''));
                updateSiteSetting($pdo, 'telegram_link', sanitize($_POST['telegram_link'] ?? ''));
                updateSiteSetting($pdo, 'whatsapp_link', sanitize($_POST['whatsapp_link'] ?? ''));
                updateSiteSetting($pdo, 'support_email', sanitize($_POST['support_email'] ?? ''));
                $success = 'Settings updated successfully!';
                break;
            
            case 'change_password':
                $currentPassword = $_POST['current_password'] ?? '';
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';
                
                // Verify current password
                $admin = getUserById($pdo, $_SESSION['user_id']);
                
                if (!password_verify($currentPassword, $admin['password'])) {
                    $error = 'Current password is incorrect.';
                } elseif (strlen($newPassword) < 6) {
                    $error = 'New password must be at least 6 characters.';
                } elseif ($newPassword !== $confirmPassword) {
                    $error = 'Passwords do not match.';
                } else {
                    $hashed = password_hash($newPassword, HASH_ALGO, ['cost' => HASH_COST]);
                    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashed, $_SESSION['user_id']]);
                    
                    logActivity($pdo, $_SESSION['user_id'], 'change_password', 'Admin changed their password');
                    $success = 'Password changed successfully!';
                }
                break;
        }
    }
}

// Get current settings
$siteName = getSiteSetting($pdo, 'site_name') ?? SITE_NAME;
$siteLogo = getSiteSetting($pdo, 'site_logo') ?? '';
$telegramLink = getSiteSetting($pdo, 'telegram_link') ?? 'https://t.me/fixedbetsro';
$whatsappLink = getSiteSetting($pdo, 'whatsapp_link') ?? 'https://wa.me/40700000000';
$supportEmail = getSiteSetting($pdo, 'support_email') ?? 'support@fixedbetsro.com';

require_once __DIR__ . '/../includes/admin_header.php';
?>

<?php if ($error): ?><div class="alert alert-danger"><span>❌</span> <?= $error ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><span>✅</span> <?= $success ?></div><?php endif; ?>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
    <!-- Site Settings -->
    <div class="card">
        <div class="card-header">
            <h2>⚙️ Site Settings</h2>
        </div>
        <div class="card-body">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="update_settings">
                
                <div class="form-group">
                    <label>Website Name</label>
                    <input type="text" name="site_name" value="<?= e($siteName) ?>">
                </div>
                <div class="form-group">
                    <label>Website Logo (URL or emoji)</label>
                    <input type="text" name="site_logo" value="<?= e($siteLogo) ?>" placeholder="Leave empty for default">
                </div>
                <div class="form-group">
                    <label>Telegram Link</label>
                    <input type="url" name="telegram_link" value="<?= e($telegramLink) ?>" placeholder="https://t.me/yourchannel">
                </div>
                <div class="form-group">
                    <label>WhatsApp Link</label>
                    <input type="url" name="whatsapp_link" value="<?= e($whatsappLink) ?>" placeholder="https://wa.me/407XXXXXXXX">
                </div>
                <div class="form-group">
                    <label>Support Email</label>
                    <input type="email" name="support_email" value="<?= e($supportEmail) ?>" placeholder="support@example.com">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">💾 Save Settings</button>
            </form>
        </div>
    </div>

    <!-- Change Password -->
    <div class="card">
        <div class="card-header">
            <h2>🔑 Change Admin Password</h2>
        </div>
        <div class="card-body">
            <form method="POST">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="change_password">
                
                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" name="current_password" required>
                </div>
                <div class="form-group">
                    <label>New Password (min 6 characters)</label>
                    <input type="password" name="new_password" required minlength="6">
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required minlength="6">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">🔑 Change Password</button>
            </form>
        </div>
    </div>
</div>

<!-- Quick Info -->
<div class="card">
    <div class="card-header">
        <h2>📋 Site Information</h2>
    </div>
    <div class="card-body">
        <table class="data-table">
            <tbody>
                <tr>
                    <td style="font-weight: 600;">PHP Version</td>
                    <td><?= phpversion() ?></td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Database</td>
                    <td>MySQL (<?= $pdo->getAttribute(PDO::ATTR_SERVER_VERSION) ?>)</td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Site URL</td>
                    <td><?= SITE_URL ?></td>
                </tr>
                <tr>
                    <td style="font-weight: 600;">Server Time</td>
                    <td><?= date('Y-m-d H:i:s') ?> (Europe/Bucharest)</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin_footer.php'; ?>
