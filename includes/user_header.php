<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle ?? 'Dashboard') ?> | <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/dashboard.css">
    <?php if (isset($extraCss)): foreach ((array)$extraCss as $css): ?>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/<?= $css ?>">
    <?php endforeach; endif; ?>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🇷🇴</text></svg>">
</head>
<?php
$themeClass = getSiteSetting($pdo, 'dashboard_theme') ?? 'dark';
?>
<body class="theme-<?= e($themeClass) ?>">
<div class="dashboard-wrapper">
    <!-- User Top Navigation -->
    <header class="dash-topbar">
        <div class="dash-topbar-left">
            <button class="mobile-menu-btn" onclick="toggleUserMenu()">☰</button>
            <span class="dash-logo"><?= SITE_NAME ?></span>
        </div>
        <div class="dash-topbar-right">
            <?php
            $notifCount = 0;
            if (isset($_SESSION['user_id'])) {
                $notifCount = getUnreadNotificationCount($pdo, $_SESSION['user_id']);
            }
            ?>
            <a href="notifications.php" class="notif-bell">
                🔔
                <?php if ($notifCount > 0): ?>
                <span class="notif-count"><?= $notifCount > 99 ? '99+' : $notifCount ?></span>
                <?php endif; ?>
            </a>
            <a href="logout.php" class="logout-btn">🚪 Logout</a>
        </div>
    </header>

    <!-- User Sidebar -->
    <aside class="user-sidebar" id="userSidebar">
        <div class="user-sidebar-header">
            <div class="user-avatar">👤</div>
            <div class="user-info">
                <strong><?= e($_SESSION['full_name'] ?? 'User') ?></strong>
                <small>@<?= e($_SESSION['username'] ?? '') ?></small>
            </div>
        </div>
        <nav class="user-nav">
            <a href="index.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">
                <span class="nav-icon">🏠</span> Dashboard
            </a>
            <a href="games.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'games.php') !== false ? 'active' : '' ?>">
                <span class="nav-icon">🎯</span> VIP Games
            </a>
            <a href="results.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'results.php') !== false ? 'active' : '' ?>">
                <span class="nav-icon">📊</span> Results
            </a>
            <a href="announcements.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'announcements.php') !== false ? 'active' : '' ?>">
                <span class="nav-icon">📢</span> Announcements
            </a>
            <a href="notifications.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'notifications.php') !== false ? 'active' : '' ?>">
                <span class="nav-icon">🔔</span> Notifications
                <?php if ($notifCount > 0): ?>
                <span class="nav-badge"><?= $notifCount ?></span>
                <?php endif; ?>
            </a>
            <a href="logout.php" class="nav-item nav-logout">
                <span class="nav-icon">🚪</span> Logout
            </a>
        </nav>

        <?php
        $telegramLink = getSiteSetting($pdo, 'telegram_link') ?? '#';
        $whatsappLink = getSiteSetting($pdo, 'whatsapp_link') ?? '#';
        ?>
        <div class="user-sidebar-footer">
            <a href="<?= e($telegramLink) ?>" target="_blank" class="sidebar-support telegram">💬 Telegram Support</a>
            <a href="<?= e($whatsappLink) ?>" target="_blank" class="sidebar-support whatsapp">📱 WhatsApp Support</a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="dash-main" id="dashMain">
