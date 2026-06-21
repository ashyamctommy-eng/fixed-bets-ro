<?php
// ============================================================
// APPLICATION CONFIGURATION
// FIXED BETS RO 🇷🇴
// ============================================================

// Site URL — auto-detect Railway domain, fallback to env or local
$railwayDomain = getenv('RAILWAY_PUBLIC_DOMAIN');
if ($railwayDomain) {
    define('SITE_URL', 'https://' . $railwayDomain);
} elseif (getenv('SITE_URL')) {
    define('SITE_URL', getenv('SITE_URL'));
} else {
    define('SITE_URL', 'http://localhost:8000');
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

// Error reporting — disable in production
$isRailway = (bool) getenv('RAILWAY_PUBLIC_DOMAIN');
if ($isRailway) {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'secure'   => $isRailway,       // HTTPS on Railway
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}
