<?php
// ============================================================
// AJAX - MARK ALL NOTIFICATIONS AS READ
// FIXED BETS RO 🇷🇴
// ============================================================

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE (user_id = ? OR type = 'global') AND is_read = 0");
$stmt->execute([$_SESSION['user_id']]);

echo json_encode(['success' => true]);
