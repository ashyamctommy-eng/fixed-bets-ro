<?php
// ============================================================
// CORE FUNCTIONS
// FIXED BETS RO
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
    $isSqlite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');
    if ($isSqlite) {
        $stmt = $pdo->prepare("INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?) ON CONFLICT(setting_key) DO UPDATE SET setting_value = excluded.setting_value");
        return $stmt->execute([$key, $value]);
    }
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
 * Render an allow-listed inline SVG icon. Icon names are never treated as markup.
 */
function svgIcon($name, $class = 'ui-icon') {
    $icons = [
        'activity' => '<path d="M3 12h4l3-8 4 16 3-8h4"/>',
        'archive' => '<rect x="3" y="4" width="18" height="4" rx="1"/><path d="M5 8v12h14V8M10 12h4"/>',
        'bell' => '<path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M10 21h4"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/>',
        'card' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M7 15h3"/>',
        'chart' => '<path d="M4 19V5M4 19h16M8 15v-4M12 15V7M16 15v-7"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'chevron-left' => '<path d="m15 18-6-6 6-6"/>',
        'chevron-right' => '<path d="m9 18 6-6-6-6"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'close' => '<path d="m6 6 12 12M18 6 6 18"/>',
        'crown' => '<path d="m2 7 5 4 5-7 5 7 5-4-2 12H4L2 7Z"/><path d="M4 22h16"/>',
        'edit' => '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/>',
        'external' => '<path d="M14 3h7v7M10 14 21 3"/><path d="M19 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2h6"/>',
        'filter' => '<path d="M4 5h16M7 12h10M10 19h4"/>',
        'game' => '<path d="M6 9h12a4 4 0 0 1 3.8 5.2l-1.2 4A2.5 2.5 0 0 1 16 19l-2-2h-4l-2 2a2.5 2.5 0 0 1-4.6-.8l-1.2-4A4 4 0 0 1 6 9Z"/><path d="M7 12v4M5 14h4M16 13h.01M18 15h.01"/>',
        'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"/>',
        'history' => '<path d="M3 12a9 9 0 1 0 2.6-6.4L3 8"/><path d="M3 3v5h5M12 7v5l3 2"/>',
        'home' => '<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1Z"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>',
        'key' => '<circle cx="8" cy="15" r="4"/><path d="m10.8 12.2 8.7-8.7 2 2-2 2 2 2-3 3-2-2-3.7 3.7"/>',
        'lock' => '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 1 1 8 0v3M12 14v3"/>',
        'logout' => '<path d="M10 17l5-5-5-5M15 12H3"/><path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'megaphone' => '<path d="m3 11 18-5v12L3 13v-2Z"/><path d="m7 14 2 6h4l-2-7M21 10v4"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'message' => '<path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5 9 9 0 0 1-4-.9L3 21l1.9-5.5a9 9 0 0 1-.9-4A8.5 8.5 0 0 1 12.5 3h.5a8.5 8.5 0 0 1 8 8v.5Z"/>',
        'minus' => '<path d="M5 12h14"/>',
        'phone' => '<path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7l.5 2.8a2 2 0 0 1-.6 1.9L7.7 9.7a16 16 0 0 0 6.6 6.6l1.3-1.3a2 2 0 0 1 1.9-.6l2.8.5a2 2 0 0 1 1.7 2Z"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'refresh' => '<path d="M20 7v5h-5M4 17v-5h5"/><path d="M5.6 9A7 7 0 0 1 18 6l2 6M4 12l2 6a7 7 0 0 0 12.4-3"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/>',
        'send' => '<path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1 1.4 1.1-1.4 2.4-1.7-.6a7.8 7.8 0 0 1-1.6.9L16 21h-8l-.3-2.1a7.8 7.8 0 0 1-1.6-.9l-1.7.6L3 16.2l1.4-1.1a7.8 7.8 0 0 1 0-1.9L3 12.1l1.4-2.4 1.7.6a7.8 7.8 0 0 1 1.6-.9L8 7h8l.3 2.1a7.8 7.8 0 0 1 1.6.9l1.7-.6 1.4 2.4-1.4 1.1a7.8 7.8 0 0 1-.2 2.1Z"/>',
        'shield' => '<path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/>',
        'shield-check' => '<path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/><path d="m9 12 2 2 4-4"/>',
        'star' => '<path d="m12 3 2.8 5.7 6.2.9-4.5 4.4 1.1 6.2-5.6-3-5.6 3 1.1-6.2L3 9.6l6.2-.9L12 3Z"/>',
        'tag' => '<path d="m20.6 13.4-7.2 7.2a2 2 0 0 1-2.8 0L2 12V2h10l8.6 8.6a2 2 0 0 1 0 2.8Z"/><circle cx="7" cy="7" r="1"/>',
        'target' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/>',
        'trash' => '<path d="M3 6h18M8 6V4h8v2m3 0-1 14H6L5 6m4 4v7m6-7v7"/>',
        'trophy' => '<path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4Z"/><path d="M7 6H4v2a4 4 0 0 0 4 4M17 6h3v2a4 4 0 0 1-4 4"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/>',
        'x' => '<path d="m6 6 12 12M18 6 6 18"/>',
    ];

    $markup = $icons[$name] ?? $icons['tag'];
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $markup . '</svg>';
}

/**
 * Render a semantic status icon, including legacy records that stored emoji.
 */
function statusSvgIcon($storedIcon, $statusName = '') {
    $allowed = ['activity', 'archive', 'bell', 'calendar', 'card', 'chart', 'check', 'clock', 'crown', 'game', 'globe', 'home', 'key', 'lock', 'mail', 'megaphone', 'message', 'phone', 'refresh', 'search', 'shield', 'shield-check', 'star', 'tag', 'target', 'trophy', 'user', 'users'];
    $icon = strtolower(trim((string)$storedIcon));

    if (in_array($icon, $allowed, true)) {
        return svgIcon($icon, 'status-svg-icon');
    }

    $name = strtolower((string)$statusName);
    if (strpos($name, 'payment') !== false) $icon = 'card';
    elseif (strpos($name, 'expired') !== false || strpos($name, 'renewal') !== false) $icon = 'calendar';
    elseif (strpos($name, 'verif') !== false || strpos($name, 'suspend') !== false) $icon = 'shield';
    elseif (strpos($name, 'review') !== false) $icon = 'search';
    elseif (strpos($name, 'restrict') !== false) $icon = 'lock';
    elseif (strpos($name, 'active') !== false || strpos($name, 'open') !== false) $icon = 'check';
    elseif (strpos($name, 'pending') !== false) $icon = 'clock';
    else $icon = 'tag';

    return svgIcon($icon, 'status-svg-icon');
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
    return '<span class="badge status-badge" style="background-color: ' . e($status['color']) . ';">' . statusSvgIcon($status['icon'] ?? '', $status['name'] ?? '') . '<span>' . e($status['name']) . '</span></span>';
}

/**
 * Get result badge HTML
 */
function getResultBadge($result) {
    $badges = [
        'win' => '<span class="badge badge-win">' . svgIcon('check') . '<span>WIN</span></span>',
        'loss' => '<span class="badge badge-loss">' . svgIcon('x') . '<span>LOSS</span></span>',
        'void' => '<span class="badge badge-secondary">' . svgIcon('minus') . '<span>VOID</span></span>',
        'push' => '<span class="badge badge-secondary">' . svgIcon('minus') . '<span>PUSH</span></span>',
        'pending' => '<span class="badge badge-pending">' . svgIcon('clock') . '<span>PENDING</span></span>',
    ];
    return $badges[$result] ?? $badges['pending'];
}

/**
 * Get result status badge for admin
 */
function getResultStatusOptions($selected = 'pending') {
    $options = [
        'pending' => 'Pending',
        'win' => 'Win',
        'loss' => 'Loss',
        'void' => 'Void',
        'push' => 'Push',
    ];
    $html = '';
    foreach ($options as $value => $label) {
        $sel = $value === $selected ? 'selected' : '';
        $html .= "<option value=\"$value\" $sel>$label</option>";
    }
    return $html;
}
