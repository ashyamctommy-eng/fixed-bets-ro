<?php
// ============================================================
// SESSION MANAGEMENT
// FIXED BETS RO 🇷🇴
// ============================================================

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/config.php';

/**
 * Regenerate session ID securely
 */
function regenerateSession() {
    session_regenerate_id(true);
}

/**
 * Set a session variable
 */
function setSession($key, $value) {
    $_SESSION[$key] = $value;
}

/**
 * Get a session variable
 */
function getSession($key) {
    return $_SESSION[$key] ?? null;
}

/**
 * Check if session variable exists
 */
function hasSession($key) {
    return isset($_SESSION[$key]);
}

/**
 * Remove a session variable
 */
function removeSession($key) {
    unset($_SESSION[$key]);
}

/**
 * Destroy session completely
 */
function destroySession() {
    $_SESSION = [];
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

/**
 * Store session token in database
 */
function storeSessionToken($pdo, $userId, $token, $expiresAt) {
    $stmt = $pdo->prepare("
        INSERT INTO sessions (user_id, session_token, ip_address, user_agent, expires_at)
        VALUES (?, ?, ?, ?, ?)
    ");
    return $stmt->execute([
        $userId,
        $token,
        $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
        $_SERVER['HTTP_USER_AGENT'] ?? '',
        $expiresAt
    ]);
}

/**
 * Validate session token
 */
function validateSessionToken($pdo, $token) {
    $stmt = $pdo->prepare("
        SELECT s.*, u.id as user_id, u.role, u.vip_access, u.status_id, u.full_name, u.username
        FROM sessions s
        JOIN users u ON s.user_id = u.id
        WHERE s.session_token = ? AND s.expires_at > NOW()
        LIMIT 1
    ");
    $stmt->execute([$token]);
    return $stmt->fetch();
}

/**
 * Clean expired sessions
 */
function cleanExpiredSessions($pdo) {
    $stmt = $pdo->prepare("DELETE FROM sessions WHERE expires_at < NOW()");
    return $stmt->execute();
}

/**
 * Delete all sessions for a user
 */
function deleteUserSessions($pdo, $userId) {
    $stmt = $pdo->prepare("DELETE FROM sessions WHERE user_id = ?");
    return $stmt->execute([$userId]);
}
