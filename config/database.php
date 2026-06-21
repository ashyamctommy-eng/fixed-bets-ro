<?php
// ============================================================
// DATABASE CONFIGURATION
// FIXED BETS RO 🇷🇴
// ============================================================
// Supports:
//   - Manual constants (fallback)
//   - Railway MySQL env vars (MYSQLHOST, MYSQLPORT, MYSQLUSER, MYSQLPASSWORD, MYSQLDATABASE)
//   - Generic DATABASE_URL (JAWSDB, CLEARDB, etc.)
// ============================================================

// --- Railway MySQL ---
$railwayHost = getenv('MYSQLHOST') ?: null;
$railwayPort = getenv('MYSQLPORT') ?: '3306';
$railwayUser = getenv('MYSQLUSER') ?: null;
$railwayPass = getenv('MYSQLPASSWORD') ?: null;
$railwayDb   = getenv('MYSQLDATABASE') ?: null;

// --- Generic DATABASE_URL (e.g. JAWSDB, CLEARDB) ---
$dbUrl = getenv('DATABASE_URL') ?: null;

if ($railwayHost && $railwayUser && $railwayDb) {
    // Railway MySQL plugin
    define('DB_HOST', $railwayHost);
    define('DB_PORT', $railwayPort);
    define('DB_NAME', $railwayDb);
    define('DB_USER', $railwayUser);
    define('DB_PASS', $railwayPass ?? '');
    define('DB_CHARSET', 'utf8mb4');
} elseif ($dbUrl) {
    // Generic DATABASE_URL: mysql://user:pass@host:port/dbname
    $parts = parse_url($dbUrl);
    define('DB_HOST', $parts['host'] ?? 'localhost');
    define('DB_PORT', $parts['port'] ?? '3306');
    define('DB_NAME', ltrim($parts['path'] ?? 'fixed_bets_ro', '/'));
    define('DB_USER', $parts['user'] ?? 'root');
    define('DB_PASS', $parts['pass'] ?? '');
    define('DB_CHARSET', 'utf8mb4');
} else {
    // Manual fallback
    define('DB_HOST', 'localhost');
    define('DB_PORT', '3306');
    define('DB_NAME', 'fixed_bets_ro');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DB_CHARSET', 'utf8mb4');
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
    // On Railway the DB may not be ready yet during build — fail gracefully
    die("Database connection failed: " . $e->getMessage());
}
