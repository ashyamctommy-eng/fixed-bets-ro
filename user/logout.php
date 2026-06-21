<?php
// ============================================================
// USER LOGOUT
// FIXED BETS RO 🇷🇴
// ============================================================

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../includes/auth.php';

logoutUser($pdo);
redirect(SITE_URL . '/login.php');
