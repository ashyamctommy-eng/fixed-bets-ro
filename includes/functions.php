<?php
// ============================================================
// CORE FUNCTIONS
// FIXED BETS RO 🇷🇴
// ============================================================

require_once __DIR__ . '/../config/bootstrap.php';

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

/**
 * Validate CSRF token
 */
function validateCSRFToken($token) {
    if (!isset($_SESSION[CSRF_TOKEN_NAME]) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

/**
 * CSRF token hidden field
 */
function csrfField() {
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . generateCSRFToken() . '">';
}

/**
 * Redirect to URL
 */
function redirect($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Get site setting
 */
function getSiteSetting($pdo, $key) {
    $stmt = $pdo->prepare("SELECT setting_value FROM site_settings WHERE setting_key = ? LIMIT 1");
    $stmt->execute([$key]);
    $result = $stmt->fetch();
    return $result ? $result['setting_value'] : null;
}

/**
 * Update site setting
 */
function updateSiteSetting($pdo, $key, $value) {
    $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
    return $stmt->execute([$key, $value, $value]);
}

/**
 * Sanitize output
 */
function e($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize input
 */
function sanitize($str) {
    return trim(htmlspecialchars(strip_tags($str), ENT_QUOTES, 'UTF-8'));
}

/**
 * Format date
 */
function formatDate($date, $format = 'd M Y') {
    return date($format, strtotime($date));
}

/**
 * Format datetime
 */
function formatDateTime($date, $format = 'd M Y H:i') {
    return date($format, strtotime($date));
}

/**
 * Time elapsed
 */
function timeElapsed($datetime) {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    
    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'just now';
}

/**
 * Get unread notification count for user
 */
function getUnreadNotificationCount($pdo, $userId) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count FROM notifications 
        WHERE (user_id = ? OR type = 'global') AND is_read = 0
    ");
    $stmt->execute([$userId]);
    return $stmt->fetch()['count'];
}

/**
 * Get user by ID
 */
function getUserById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Get user by username
 */
function getUserByUsername($pdo, $username) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    return $stmt->fetch();
}

/**
 * Get status by ID
 */
function getStatusById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM custom_statuses WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    return $stmt->fetch();
}

/**
 * Log activity
 */
function logActivity($pdo, $userId, $action, $details = null) {
    $stmt = $pdo->prepare("
        INSERT INTO activity_log (user_id, action, details, ip_address)
        VALUES (?, ?, ?, ?)
    ");
    return $stmt->execute([
        $userId,
        $action,
        $details,
        $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
    ]);
}

/**
 * Generate a secure random password
 */
function generatePassword($length = 12) {
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    return substr(str_shuffle($chars), 0, $length);
}

/**
 * Validate date format
 */
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

/**
 * Get user status badge HTML
 */
function getStatusBadge($status) {
    if (!$status) return '<span class="badge badge-secondary">No Status</span>';
    return '<span class="badge" style="background-color: ' . e($status['color']) . ';">' . e($status['icon']) . ' ' . e($status['name']) . '</span>';
}

/**
 * Get result badge HTML
 */
function getResultBadge($result) {
    $badges = [
        'win' => '<span class="badge badge-win">✅ WIN</span>',
        'loss' => '<span class="badge badge-loss">❌ LOSS</span>',
        'pending' => '<span class="badge badge-pending">⏳ PENDING</span>',
    ];
    return $badges[$result] ?? $badges['pending'];
}

/**
 * Get result status badge for admin
 */
function getResultStatusOptions($selected = 'pending') {
    $options = [
        'pending' => '⏳ Pending',
        'win' => '✅ Win',
        'loss' => '❌ Loss'
    ];
    $html = '';
    foreach ($options as $value => $label) {
        $sel = $value === $selected ? 'selected' : '';
        $html .= "<option value=\"$value\" $sel>$label</option>";
    }
    return $html;
}
