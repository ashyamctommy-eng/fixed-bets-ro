<?php
// ============================================================
// SCHEMA AUTO-MIGRATION — FIXED BETS RO
// ============================================================
// Runs once on Railway (or any fresh DB) to create all tables
// and seed default data. Safe to run repeatedly — uses IF NOT EXISTS.
// ============================================================

/**
 * Run schema migration. Call once per request until tables exist.
 */
function runSchemaMigration($pdo) {
    $isSqlite = ($pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite');

    // Check if tables already exist
    $usersTableExists = false;
    try {
        if ($isSqlite) {
            $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
            $usersTableExists = (bool)$stmt->fetch();
        } else {
            $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
            $usersTableExists = $stmt->rowCount() > 0;
        }
    } catch (PDOException $e) {
        // DB might not exist yet
    }

    if ($usersTableExists) {
        upgradeVipGameOutcomeSchema($pdo, $isSqlite);
        return;
    }

    $execQuery = function($sql) use ($pdo, $isSqlite) {
        if ($isSqlite) {
            $sql = str_replace('ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci', '', $sql);
            $sql = str_replace('id INT AUTO_INCREMENT PRIMARY KEY', 'id INTEGER PRIMARY KEY AUTOINCREMENT', $sql);
            $sql = str_replace('INSERT IGNORE', 'INSERT OR IGNORE', $sql);
            $sql = preg_replace('/ENUM\([^)]+\)/i', 'TEXT', $sql);
        }
        return $pdo->exec($sql);
    };

    try {
        // ----- USERS -----
        $execQuery("CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            full_name VARCHAR(100) NOT NULL,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(100) DEFAULT NULL,
            telegram VARCHAR(100) DEFAULT NULL,
            whatsapp VARCHAR(100) DEFAULT NULL,
            role ENUM('admin', 'vip_user') NOT NULL DEFAULT 'vip_user',
            vip_access TINYINT(1) NOT NULL DEFAULT 0,
            status_id INT DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            last_login DATETIME DEFAULT NULL,
            remember_token VARCHAR(255) DEFAULT NULL,
            reset_token VARCHAR(255) DEFAULT NULL,
            reset_expires DATETIME DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // ----- CUSTOM STATUSES -----
        $execQuery("CREATE TABLE IF NOT EXISTS custom_statuses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(50) NOT NULL,
            icon VARCHAR(10) NOT NULL DEFAULT 'tag',
            color VARCHAR(7) NOT NULL DEFAULT '#FFD700',
            title VARCHAR(100) NOT NULL,
            message TEXT NOT NULL,
            restrict_access TINYINT(1) NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // ----- VIP GAMES -----
        $execQuery("CREATE TABLE IF NOT EXISTS vip_games (
            id INT AUTO_INCREMENT PRIMARY KEY,
            match_name VARCHAR(200) NOT NULL,
            league VARCHAR(100) DEFAULT NULL,
            selection VARCHAR(200) NOT NULL,
            odds DECIMAL(6,2) NOT NULL,
            match_date DATE NOT NULL,
            match_time TIME NOT NULL,
            confidence VARCHAR(50) DEFAULT NULL,
            notes TEXT DEFAULT NULL,
            result_status ENUM('win', 'loss', 'pending', 'void', 'push') NOT NULL DEFAULT 'pending',
            final_score VARCHAR(32) DEFAULT NULL,
            settled_at DATETIME DEFAULT NULL,
            is_archived TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // ----- ANNOUNCEMENTS -----
        $execQuery("CREATE TABLE IF NOT EXISTS announcements (
            id INT AUTO_INCREMENT PRIMARY KEY,
            title VARCHAR(200) NOT NULL,
            message TEXT NOT NULL,
            priority ENUM('low', 'normal', 'high', 'urgent') NOT NULL DEFAULT 'normal',
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // ----- NOTIFICATIONS -----
        $execQuery("CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT DEFAULT NULL,
            title VARCHAR(200) NOT NULL,
            message TEXT NOT NULL,
            type ENUM('personal', 'global') NOT NULL DEFAULT 'personal',
            priority ENUM('low', 'normal', 'high', 'urgent') NOT NULL DEFAULT 'normal',
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // ----- SITE SETTINGS -----
        $execQuery("CREATE TABLE IF NOT EXISTS site_settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(50) NOT NULL UNIQUE,
            setting_value TEXT DEFAULT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // ----- ACTIVITY LOG -----
        $execQuery("CREATE TABLE IF NOT EXISTS activity_log (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT DEFAULT NULL,
            action VARCHAR(100) NOT NULL,
            details TEXT DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // ----- SESSIONS -----
        $execQuery("CREATE TABLE IF NOT EXISTS sessions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            session_token VARCHAR(255) NOT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent TEXT DEFAULT NULL,
            expires_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // ----- SEED DEFAULT DATA (only if empty) -----
        $check = $pdo->query("SELECT COUNT(*) FROM custom_statuses");
        if ($check->fetchColumn() == 0) {
            // Default admin (password: Admin@123 — change after first login)
            $adminPass = password_hash('Admin@123', PASSWORD_BCRYPT, ['cost' => 10]);

            $uQuery = "INSERT IGNORE INTO users (full_name, username, password, role, vip_access) VALUES (?, ?, ?, 'admin', 1)";
            if ($isSqlite) $uQuery = str_replace('INSERT IGNORE', 'INSERT OR IGNORE', $uQuery);
            $pdo->prepare($uQuery)->execute(['Super Admin', 'admin', $adminPass]);

            // Default statuses
            $statuses = [
                ['Active', 'check', '#28a745', 'Account Active', 'Your account is fully active. Enjoy VIP betting!', 0],
                ['Pending Setup', 'clock', '#ffc107', 'Account Pending Setup', 'Your account is being set up.', 1],
                ['Payment Pending', 'card', '#ff9800', 'Payment Pending', 'Your payment is being processed.', 1],
                ['VIP Expired', 'calendar', '#dc3545', 'VIP Membership Expired', 'Your VIP membership has expired.', 1],
                ['Verification Required', 'shield', '#e83e8c', 'Verification Required', 'Your account requires verification.', 1],
                ['Suspended', 'shield', '#dc3545', 'Account Suspended', 'Your account has been suspended.', 1],
                ['Under Review', 'search', '#17a2b8', 'Account Under Review', 'Your account is currently under review.', 1],
                ['Account Restricted', 'lock', '#dc3545', 'Account Restricted', 'Your account has restricted access.', 1],
                ['Renewal Required', 'refresh', '#fd7e14', 'VIP Renewal Required', 'Your VIP membership has expired.', 1],
            ];

            $statusQuery = "INSERT IGNORE INTO custom_statuses (name, icon, color, title, message, restrict_access) VALUES (?, ?, ?, ?, ?, ?)";
            if ($isSqlite) $statusQuery = str_replace('INSERT IGNORE', 'INSERT OR IGNORE', $statusQuery);
            $stmt = $pdo->prepare($statusQuery);
            foreach ($statuses as $s) {
                $stmt->execute($s);
            }

            // Default site settings
            $settings = [
                ['site_name', 'FIXED BETS RO'],
                ['telegram_link', 'https://t.me/fixedbetsro'],
                ['whatsapp_link', 'https://wa.me/40700000000'],
                ['support_email', 'support@fixedbetsro.com'],
                ['dashboard_theme', 'dark'],
            ];

            $settingsQuery = "INSERT IGNORE INTO site_settings (setting_key, setting_value) VALUES (?, ?)";
            if ($isSqlite) $settingsQuery = str_replace('INSERT IGNORE', 'INSERT OR IGNORE', $settingsQuery);
            $sStmt = $pdo->prepare($settingsQuery);
            foreach ($settings as $s) {
                $sStmt->execute($s);
            }
        }
    } catch (PDOException $e) {
        // Silently fail — the app will show a connection error if critical
        error_log("Schema migration error: " . $e->getMessage());
    }
}

/**
 * Additive upgrade for installations created before settlement metadata existed.
 */
function upgradeVipGameOutcomeSchema($pdo, $isSqlite) {
    try {
        if ($isSqlite) {
            $columns = $pdo->query("PRAGMA table_info(vip_games)")->fetchAll(PDO::FETCH_ASSOC);
            $names = array_column($columns, 'name');
            if (!in_array('final_score', $names, true)) {
                $pdo->exec("ALTER TABLE vip_games ADD COLUMN final_score TEXT DEFAULT NULL");
            }
            if (!in_array('settled_at', $names, true)) {
                $pdo->exec("ALTER TABLE vip_games ADD COLUMN settled_at TEXT DEFAULT NULL");
            }
            return;
        }

        $columns = $pdo->query("SHOW COLUMNS FROM vip_games")->fetchAll(PDO::FETCH_ASSOC);
        $byName = [];
        foreach ($columns as $column) {
            $byName[$column['Field']] = $column;
        }
        if (!isset($byName['final_score'])) {
            $pdo->exec("ALTER TABLE vip_games ADD COLUMN final_score VARCHAR(32) DEFAULT NULL AFTER result_status");
        }
        if (!isset($byName['settled_at'])) {
            $pdo->exec("ALTER TABLE vip_games ADD COLUMN settled_at DATETIME DEFAULT NULL AFTER final_score");
        }
        if (
            isset($byName['result_status'])
            && (strpos(strtolower($byName['result_status']['Type']), 'void') === false
                || strpos(strtolower($byName['result_status']['Type']), 'push') === false)
        ) {
            $pdo->exec("ALTER TABLE vip_games MODIFY result_status ENUM('win', 'loss', 'pending', 'void', 'push') NOT NULL DEFAULT 'pending'");
        }
    } catch (PDOException $e) {
        error_log('VIP game outcome schema upgrade failed: ' . $e->getMessage());
    }
}
