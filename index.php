<?php
// ============================================================
// HOMEPAGE - FIXED BETS RO
// ============================================================

require_once __DIR__ . '/config/bootstrap.php';
require_once __DIR__ . '/includes/functions.php';

$telegramLink = getSiteSetting($pdo, 'telegram_link') ?? 'https://t.me/fixedbetsro';
$whatsappLink = getSiteSetting($pdo, 'whatsapp_link') ?? 'https://wa.me/40700000000';

// Redirect if already logged in
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    if ($_SESSION['role'] === 'admin') {
        redirect(SITE_URL . '/admin/index.php');
    } else {
        redirect(SITE_URL . '/user/index.php');
    }
}

$pageTitle = 'Home';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?> | <?= SITE_TAGLINE ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="icon" type="image/svg+xml" href="<?= SITE_URL ?>/assets/favicon.svg">
</head>
<?php
$themeClass = getSiteSetting($pdo, 'dashboard_theme') ?? 'dark';
?>
<body class="theme-<?= e($themeClass) ?>">
<div class="landing-page">
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <div class="hero-logo">
                <span class="brand-mark"><?= svgIcon('shield-check') ?></span>
                <h1 class="site-title"><?= SITE_NAME ?></h1>
                <p class="site-tagline">VIP Betting Platform</p>
            </div>
            
            <div class="hero-badges">
                <span class="hero-badge"><?= svgIcon('lock') ?> Private access</span>
                <span class="hero-badge"><?= svgIcon('star') ?> VIP members</span>
                <span class="hero-badge"><?= svgIcon('chart') ?> Premium picks</span>
            </div>
            
            <div class="hero-actions">
                <a href="login.php" class="btn btn-primary btn-lg"><?= svgIcon('key') ?> Member login</a>
                <a href="results.php" class="btn btn-secondary btn-lg"><?= svgIcon('chart') ?> View results</a>
            </div>
            
            <div class="hero-links">
                <a href="<?= e($telegramLink) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-telegram"><?= svgIcon('message') ?> Join Telegram</a>
                <a href="<?= e($whatsappLink) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-whatsapp"><?= svgIcon('phone') ?> WhatsApp</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="features-grid">
            <div class="feature-card">
                <span class="feature-icon"><?= svgIcon('target') ?></span>
                <h3>VIP Games</h3>
                <p>Premium betting selections curated for VIP members only.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon"><?= svgIcon('chart') ?></span>
                <h3>Results</h3>
                <p>Review dated results for the selections that have been settled.</p>
                <a class="feature-link" href="results.php">Open results history <?= svgIcon('chevron-right') ?></a>
            </div>
            <div class="feature-card">
                <span class="feature-icon"><?= svgIcon('bell') ?></span>
                <h3>Real-Time Updates</h3>
                <p>Instant notifications for new games and announcements.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon"><?= svgIcon('message') ?></span>
                <h3>24/7 Support</h3>
                <p>Dedicated support via Telegram and WhatsApp.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="landing-footer">
        <p>&copy; <?= date('Y') ?> <?= SITE_NAME ?> — All rights reserved.</p>
        <div class="footer-links">
            <a href="<?= e($telegramLink) ?>" target="_blank" rel="noopener noreferrer"><?= svgIcon('message') ?> Telegram</a>
            <a href="<?= e($whatsappLink) ?>" target="_blank" rel="noopener noreferrer"><?= svgIcon('phone') ?> WhatsApp</a>
        </div>
    </footer>
</div>
</body>
</html>
