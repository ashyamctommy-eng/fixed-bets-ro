<?php
// ============================================================
// AUTHENTICATION SYSTEM
// FIXED BETS RO 🇷🇴
// ============================================================

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/session.php';

/**
 * Authenticate user login
 */
function authenticateUser($pdo, $username, $password, $remember = false) {
    $user = getUserByUsername($pdo, $username);
    
    if (!$user || !password_verify($password, $user['password'])) {
        return false;
    }
    
    // Update last login
    $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
    $stmt->execute([$user['id']]);
    
    // Set session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['full_name'] = $user['full_name'];
    $_SESSION['logged_in'] = true;
    
    // Regenerate session ID
    regenerateSession();
    
    // Handle remember me
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', time() + REMEMBER_LIFETIME);
        
        storeSessionToken($pdo, $user['id'], $token, $expiresAt);
        
        setcookie('remember_token', $token, time() + REMEMBER_LIFETIME, '/', '', false, true);
        
        // Store in user record
        $stmt = $pdo->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
        $stmt->execute([password_hash($token, PASSWORD_DEFAULT), $user['id']]);
    }
    
    logActivity($pdo, $user['id'], 'login', 'User logged in successfully');
    
    return $user;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Check if user is VIP user
 */
function isVIPUser() {
    return isLoggedIn() && isset($_SESSION['role']) && $_SESSION['role'] === 'vip_user';
}

/**
 * Get current user data from session
 */
function getCurrentUser() {
    global $pdo;
    
    if (!isLoggedIn() || !isset($_SESSION['user_id'])) {
        return null;
    }
    
    return getUserById($pdo, $_SESSION['user_id']);
}

/**
 * Require authentication - redirects to login if not authenticated
 */
function requireAuth() {
    if (!isLoggedIn()) {
        // Try remember me
        if (!tryRememberMe()) {
            $_SESSION['redirect_after'] = $_SERVER['REQUEST_URI'];
            redirect('../login.php');
        }
    }
}

/**
 * Require admin authentication
 */
function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        redirect('../index.php');
    }
}

/**
 * Try to authenticate via remember me cookie
 */
function tryRememberMe() {
    global $pdo;
    
    if (!isset($_COOKIE['remember_token'])) {
        return false;
    }
    
    $token = $_COOKIE['remember_token'];
    
    $sessionData = validateSessionToken($pdo, $token);
    
    if (!$sessionData) {
        return false;
    }
    
    // Set session
    $_SESSION['user_id'] = $sessionData['user_id'];
    $_SESSION['username'] = $sessionData['username'];
    $_SESSION['role'] = $sessionData['role'];
    $_SESSION['full_name'] = $sessionData['full_name'];
    $_SESSION['logged_in'] = true;
    
    regenerateSession();
    
    return true;
}

/**
 * Logout user
 */
function logoutUser($pdo) {
    if (isset($_SESSION['user_id'])) {
        logActivity($pdo, $_SESSION['user_id'], 'logout', 'User logged out');
        
        // Remove remember token from database
        $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        
        // Delete sessions
        deleteUserSessions($pdo, $_SESSION['user_id']);
    }
    
    // Clear cookie
    if (isset($_COOKIE['remember_token'])) {
        setcookie('remember_token', '', time() - 3600, '/');
    }
    
    destroySession();
}

/**
 * Check if VIP user has access
 */
function checkVIPAccess($pdo) {
    $user = getCurrentUser();
    if (!$user) return false;
    if ($user['role'] === 'admin') return true;
    return (bool)$user['vip_access'];
}

/**
 * Enforce VIP access
 */
function requireVIPAccess($pdo) {
    requireAuth();
    
    if (!checkVIPAccess($pdo)) {
        redirect('restricted.php');
    }
}

/**
 * Reset user password
 */
function resetUserPassword($pdo, $userId, $newPassword) {
    $hashed = password_hash($newPassword, HASH_ALGO, ['cost' => HASH_COST]);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    return $stmt->execute([$hashed, $userId]);
}
