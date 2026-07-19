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
<?php
$themeClass = getSiteSetting($pdo, 'dashboard_theme') ?? 'dark';
?>
<body class="theme-<?= e($themeClass) ?>">
<div class="admin-wrapper">
    <!-- Admin Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="sidebar-header">
            <h2>⚙️ Admin</h2>
            <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
        </div>
        <nav class="sidebar-nav">
            <a href="<?= SITE_URL ?>/admin/index.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">
                <span class="nav-icon"><svg class="nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg></span> Dashboard
            </a>
            <a href="<?= SITE_URL ?>/admin/users.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'users.php') !== false ? 'active' : '' ?>">
                <span class="nav-icon"><svg class="nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg></span> Users
            </a>
            <a href="<?= SITE_URL ?>/admin/statuses.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'statuses.php') !== false ? 'active' : '' ?>">
                <span class="nav-icon"><svg class="nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg></span> Statuses
            </a>
            <a href="<?= SITE_URL ?>/admin/games.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'games.php') !== false ? 'active' : '' ?>">
                <span class="nav-icon"><svg class="nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="6"></circle><circle cx="12" cy="12" r="2"></circle></svg></span> Games
            </a>
            <a href="<?= SITE_URL ?>/admin/announcements.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'announcements.php') !== false ? 'active' : '' ?>">
                <span class="nav-icon"><svg class="nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon><path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path></svg></span> Announcements
            </a>
            <a href="<?= SITE_URL ?>/admin/notifications.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'notifications.php') !== false ? 'active' : '' ?>">
                <span class="nav-icon"><svg class="nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg></span> Notifications
            </a>
            <a href="<?= SITE_URL ?>/admin/settings.php" class="nav-item <?= strpos($_SERVER['PHP_SELF'], 'settings.php') !== false ? 'active' : '' ?>">
                <span class="nav-icon"><svg class="nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg></span> Settings
            </a>
            <a href="<?= SITE_URL ?>/admin/logout.php" class="nav-item nav-logout">
                <span class="nav-icon"><svg class="nav-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg></span> Logout
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
