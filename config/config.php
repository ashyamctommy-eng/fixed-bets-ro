<?php
// ============================================================
// APPLICATION CONFIGURATION
// FIXED BETS RO 🇷🇴
// ============================================================

// Site URL — auto-detect from the server request, Railway, or env
if (getenv('RAILWAY_PUBLIC_DOMAIN')) {
    define('SITE_URL', 'https://' . getenv('RAILWAY_PUBLIC_DOMAIN'));
} elseif (getenv('SITE_URL')) {
    define('SITE_URL', getenv('SITE_URL'));
} elseif (isset($_SERVER['HTTP_HOST'])) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    define('SITE_URL', $protocol . '://' . $_SERVER['HTTP_HOST']);
} else {
    define('SITE_URL', 'http://localhost');
}

define('SITE_NAME', 'FIXED BETS RO 🇷🇴');
define('SITE_TAGLINE', 'VIP Betting Platform');

// Session settings
define('SESSION_LIFETIME', 86400);    // 24 hours
define('REMEMBER_LIFETIME', 2592000); // 30 days

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');
define('HASH_ALGO', PASSWORD_BCRYPT);
define('HASH_COST', 10);

// Timezone
date_default_timezone_set('Europe/Bucharest');

// Error reporting — quiet on Railway, visible elsewhere
if (getenv('RAILWAY_PUBLIC_DOMAIN')) {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'secure'   => $isSecure,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}
