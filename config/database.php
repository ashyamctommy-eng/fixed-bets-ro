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

// --- Detect Environment Configurations ---
$railwayHost = getenv('MYSQLHOST') ?: null;
$dbUrl = getenv('DATABASE_URL') ?: null;
$envHost = getenv('DB_HOST') ?: null;
$isContainer = getenv('PORT') || getenv('RAILWAY_STATIC_URL');

// Force SQLite for testing/local setups OR automatically fallback to SQLite on Railway if no MySQL credentials are provided
// We also verify that we aren't trying to connect to a dummy local host (e.g., if sql123.infinityfree.com is set in configuration, but we are running in a container like Railway, we should fallback to SQLite to prevent dns lookup failures).
$isDummyInfinityFree = ($isContainer && isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'railway.app') !== false);
$hasNoCredentials = (!$railwayHost && !$dbUrl && !$envHost);

if (getenv('USE_SQLITE') || file_exists(__DIR__ . '/use_sqlite.txt') || $hasNoCredentials || $isDummyInfinityFree) {
    define('DB_HOST', 'localhost');
    define('DB_PORT', '0');
    define('DB_NAME', 'sqlite');
    define('DB_USER', 'sqlite');
    define('DB_PASS', 'sqlite');
    define('DB_CHARSET', 'utf8mb4');

    try {
        $pdo = new PDO("sqlite:" . __DIR__ . "/database.sqlite");
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $pdo->sqliteCreateFunction('NOW', function() {
            return date('Y-m-d H:i:s');
        });
        $pdo->sqliteCreateFunction('CURDATE', function() {
            return date('Y-m-d');
        });
        return; // Connection established, exit script
    } catch (PDOException $e) {
        die("SQLite connection failed: " . $e->getMessage());
    }
}

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
} elseif ($envHost) {
    define('DB_HOST', getenv('DB_HOST'));
    define('DB_PORT', getenv('DB_PORT') ?: '3306');
    define('DB_NAME', getenv('DB_NAME') ?: 'fixed_bets_ro');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASS') ?: '');
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
