<?php
// ============================================================
// DATABASE CONFIGURATION
// FIXED BETS RO 🇷🇴
// ============================================================
//
// 👇 FOR INFINITYFREE — Edit the "MANUAL CONFIG" section below
//    with the MySQL details from your control panel.
//
//    Host format: sqlNNN.infinityfree.com
//    DB Name format: epiz_NNNNN_fixed_bets_ro
//
// ============================================================

// --- Railway MySQL (auto) ---
$railwayHost = getenv('MYSQLHOST') ?: null;

// --- Generic DATABASE_URL (auto) ---
$dbUrl = getenv('DATABASE_URL') ?: null;

if ($railwayHost) {
    define('DB_HOST', getenv('MYSQLHOST'));
    define('DB_PORT', getenv('MYSQLPORT') ?: '3306');
    define('DB_NAME', getenv('MYSQLDATABASE'));
    define('DB_USER', getenv('MYSQLUSER'));
    define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
    define('DB_CHARSET', 'utf8mb4');
} elseif ($dbUrl) {
    $parts = parse_url($dbUrl);
    define('DB_HOST', $parts['host'] ?? 'localhost');
    define('DB_PORT', $parts['port'] ?? '3306');
    define('DB_NAME', ltrim($parts['path'] ?? 'fixed_bets_ro', '/'));
    define('DB_USER', $parts['user'] ?? 'root');
    define('DB_PASS', $parts['pass'] ?? '');
    define('DB_CHARSET', 'utf8mb4');
} else {
    // ============================================================
    // 🔧 MANUAL CONFIG — EDIT THESE FOR INFINITYFREE
    // ============================================================
    // Get these values from your InfinityFree control panel:
    //   MySQL Databases → (your database) → Details
    // ============================================================
    define('DB_HOST', 'sql123.infinityfree.com');   // ← Change to your MySQL host
    define('DB_PORT', '3306');                       // ← Usually 3306
    define('DB_NAME', 'epiz_NNNNN_fixed_bets_ro');  // ← Change to your DB name
    define('DB_USER', 'epiz_NNNNN');                 // ← Change to your DB username
    define('DB_PASS', 'your_password_here');         // ← Change to your DB password
    define('DB_CHARSET', 'utf8mb4');
    // ============================================================
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed. Check your config/database.php settings.<br>" . $e->getMessage());
}
