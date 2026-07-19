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
            <a href="notifications.php" class="notif-bell" style="display: inline-flex; align-items: center;">
                <svg class="nav-svg" style="width: 24px; height: 24px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                <?php if ($notifCount > 0): ?>
                <span class="notif-count"><?= $notifCount > 99 ? '99+' : $notifCount ?></span>
                <?php endif; ?>
            </a>
            <a href="logout.php" class="logout-btn" style="display: inline-flex; align-items: center; gap: 8px;">
                <svg class="nav-svg" style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                <span>Logout</span>
            </a>
        </div>
    </header>

    <!-- User Sidebar -->
    <aside class="user-sidebar" id="userSidebar">
        <div class="user-sidebar-header">
            <div class="user-avatar"><svg class="nav-svg" style="width: 40px; height: 40px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg></div>
            <div class="user-info">
                <strong><?= e($_SESSION['full_name'] ?? 'User') ?></strong>
                <small>@<?= e($_SESSION['username'] ?? '') ?></small>
            </div>
        </div>
        <nav class="user-nav">
            <a href="index.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">
                <span class="nav-icon"><svg class="nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg></span> Dashboard
            </a>
            <a href="games.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'games.php') !== false ? 'active' : '' ?>">
                <span class="nav-icon"><svg class="nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg></span> VIP Games
            </a>
            <a href="results.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'results.php') !== false ? 'active' : '' ?>">
                <span class="nav-icon"><svg class="nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg></span> Results
            </a>
            <a href="announcements.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'announcements.php') !== false ? 'active' : '' ?>">
                <span class="nav-icon"><svg class="nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg></span> Announcements
            </a>
            <a href="notifications.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'notifications.php') !== false ? 'active' : '' ?>">
                <span class="nav-icon"><svg class="nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg></span> Notifications
                <?php if ($notifCount > 0): ?>
                <span class="nav-badge"><?= $notifCount ?></span>
                <?php endif; ?>
            </a>
            <a href="logout.php" class="nav-item nav-logout">
                <span class="nav-icon"><svg class="nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg></span> Logout
            </a>
        </nav>

        <?php
        $telegramLink = getSiteSetting($pdo, 'telegram_link') ?? '#';
        $whatsappLink = getSiteSetting($pdo, 'whatsapp_link') ?? '#';
        ?>
        <div class="user-sidebar-footer">
            <a href="<?= e($telegramLink) ?>" target="_blank" class="sidebar-support telegram" style="display: inline-flex; align-items: center; gap: 8px;">
                <svg class="nav-svg" style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                <span>Telegram Support</span>
            </a>
            <a href="<?= e($whatsappLink) ?>" target="_blank" class="sidebar-support whatsapp" style="display: inline-flex; align-items: center; gap: 8px;">
                <svg class="nav-svg" style="width: 16px; height: 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.79 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                <span>WhatsApp Support</span>
            </a>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="dash-main" id="dashMain">
