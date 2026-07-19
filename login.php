<?php
// ============================================================
// LOGIN PAGE - FIXED BETS RO 🇷🇴
// ============================================================

require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

$telegramLink = getSiteSetting($pdo, 'telegram_link') ?? 'https://t.me/fixedbetsro';
$whatsappLink = getSiteSetting($pdo, 'whatsapp_link') ?? 'https://wa.me/40700000000';
$error = '';

// Redirect if already logged in
if (isLoggedIn()) {
    if (isAdmin()) {
        redirect(SITE_URL . '/admin/index.php');
    }
    redirect(SITE_URL . '/user/index.php');
}

// Handle login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error = 'Invalid security token. Please try again.';
    } else {
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        
        if (empty($username) || empty($password)) {
            $error = 'Please enter your username and password.';
        } else {
            $user = authenticateUser($pdo, $username, $password, $remember);
            
            if ($user) {
                // Regenerate CSRF token
                unset($_SESSION[CSRF_TOKEN_NAME]);
                
                if ($user['role'] === 'admin') {
                    redirect(SITE_URL . '/admin/index.php');
                } else {
                    redirect(SITE_URL . '/user/index.php');
                }
            } else {
                $error = 'Invalid username or password.';
            }
        }
    }
}

$pageTitle = 'Login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🇷🇴</text></svg>">
</head>
<?php
$themeClass = getSiteSetting($pdo, 'dashboard_theme') ?? 'dark';
?>
<body class="theme-<?= e($themeClass) ?>">
<div class="login-page">
    <div class="login-container">
        <div class="login-header">
            <span class="login-logo">🇷🇴</span>
            <h1><?= SITE_NAME ?></h1>
            <p>VIP Member Login</p>
        </div>
        
        <?php if ($error): ?>
        <div class="alert alert-danger">
            <span>⚠️</span> <?= e($error) ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="" class="login-form" autocomplete="off">
            <?= csrfField() ?>
            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required autofocus>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            
            <div class="form-group form-checkbox">
                <label>
                    <input type="checkbox" name="remember" value="1">
                    <span>Remember me</span>
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary btn-block">🔑 Sign In</button>
        </form>
        
        <div class="login-footer">
            <p>No registration — accounts are created by the Admin.</p>
            <div class="login-links">
                <a href="<?= e($telegramLink) ?>" target="_blank" class="btn btn-sm btn-telegram">💬 Telegram Support</a>
                <a href="<?= e($whatsappLink) ?>" target="_blank" class="btn btn-sm btn-whatsapp">📱 WhatsApp</a>
            </div>
            <a href="<?= SITE_URL ?>" class="login-back">← Back to Home</a>
        </div>
    </div>
</div>
</body>
</html>
