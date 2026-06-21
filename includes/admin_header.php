<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($pageTitle ?? 'Admin') ?> | <?= SITE_NAME ?> Admin</title>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/admin.css">
    <?php if (isset($extraCss)): foreach ((array)$extraCss as $css): ?>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/<?= $css ?>">
    <?php endforeach; endif; ?>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🇷🇴</text></svg>">
</head>
<body>
<div class="admin-wrapper">
    <!-- Admin Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <h2>⚙️ Admin</h2>
            <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
        </div>
        <nav class="sidebar-nav">
            <a href="<?= SITE_URL ?>/admin/index.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">
                <span class="nav-icon">📊</span> Dashboard
            </a>
            <a href="<?= SITE_URL ?>/admin/users.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'users.php') !== false ? 'active' : '' ?>">
                <span class="nav-icon">👥</span> Users
            </a>
            <a href="<?= SITE_URL ?>/admin/statuses.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'statuses.php') !== false ? 'active' : '' ?>">
                <span class="nav-icon">🏷️</span> Statuses
            </a>
            <a href="<?= SITE_URL ?>/admin/games.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'games.php') !== false ? 'active' : '' ?>">
                <span class="nav-icon">🎯</span> Games
            </a>
            <a href="<?= SITE_URL ?>/admin/announcements.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'announcements.php') !== false ? 'active' : '' ?>">
                <span class="nav-icon">📢</span> Announcements
            </a>
            <a href="<?= SITE_URL ?>/admin/notifications.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'notifications.php') !== false ? 'active' : '' ?>">
                <span class="nav-icon">🔔</span> Notifications
            </a>
            <a href="<?= SITE_URL ?>/admin/settings.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'settings.php') !== false ? 'active' : '' ?>">
                <span class="nav-icon">⚙️</span> Settings
            </a>
            <a href="<?= SITE_URL ?>/admin/logout.php" class="nav-item nav-logout">
                <span class="nav-icon">🚪</span> Logout
            </a>
        </nav>
    </aside>

    <!-- Admin Main Content -->
    <main class="admin-main">
        <header class="admin-topbar">
            <div class="topbar-left">
                <button class="mobile-menu-btn" onclick="toggleSidebar()">☰</button>
                <h1><?= e($pageTitle ?? 'Dashboard') ?></h1>
            </div>
            <div class="topbar-right">
                <span class="admin-user">👑 <?= e($_SESSION['full_name'] ?? 'Admin') ?></span>
                <a href="<?= SITE_URL ?>/" class="topbar-link" target="_blank">🔗 View Site</a>
            </div>
        </header>
        <div class="admin-content">
