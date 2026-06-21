<?php
// ============================================================
// AJAX - DELETE NOTIFICATION
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

$notifId = (int)($_POST['notification_id'] ?? 0);
if ($notifId > 0) {
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND (user_id = ? OR type = 'global')");
    $stmt->execute([$notifId, $_SESSION['user_id']]);
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
