<?php
// ============================================================
// AJAX - TOGGLE VIP ACCESS
// FIXED BETS RO 🇷🇴
// ============================================================

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');

if (!isAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$userId = (int)($_POST['user_id'] ?? 0);
$vipAccess = (int)($_POST['vip_access'] ?? 0);

if ($userId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
    exit;
}

$stmt = $pdo->prepare("UPDATE users SET vip_access = ? WHERE id = ? AND role = 'vip_user'");
$stmt->execute([$vipAccess, $userId]);

if ($stmt->rowCount() > 0) {
    logActivity($pdo, $_SESSION['user_id'], 'ajax_toggle_vip', "Toggled VIP=" . ($vipAccess ? 'ON' : 'OFF') . " for user ID: $userId");
    echo json_encode([
        'success' => true,
        'message' => 'VIP access ' . ($vipAccess ? 'enabled' : 'disabled') . ' successfully.'
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'User not found or not a VIP user.']);
}
