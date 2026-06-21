<?php
// ============================================================
// INSTALLATION SCRIPT - FIXED BETS RO 🇷🇴
// Run ONCE after uploading files, then DELETE this file.
// ============================================================

// Display errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Railway auto-detection notice
$isRailway = (bool) getenv('RAILWAY_PUBLIC_DOMAIN');

$step = $_GET['step'] ?? 1;
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = trim($_POST['db_host'] ?? 'localhost');
    $dbName = trim($_POST['db_name'] ?? 'fixed_bets_ro');
    $dbUser = trim($_POST['db_user'] ?? 'root');
    $dbPass = trim($_POST['db_pass'] ?? '');
    $adminUser = trim($_POST['admin_user'] ?? 'admin');
    $adminPass = trim($_POST['admin_pass'] ?? '');
    $adminEmail = trim($_POST['admin_email'] ?? '');
    $siteUrl = trim($_POST['site_url'] ?? (getenv('RAILWAY_PUBLIC_DOMAIN') ? 'https://' . getenv('RAILWAY_PUBLIC_DOMAIN') : 'http://localhost:8000'));
    
    try {
        // Test database connection
        $pdo = new PDO("mysql:host=$dbHost;charset=utf8mb4", $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        // Create database
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$dbName`");
        
        // Run schema
        $schema = file_get_contents(__DIR__ . '/sql/schema.sql');
        if ($schema) {
            // Remove CREATE DATABASE and USE statements (already done)
            $schema = preg_replace('/CREATE DATABASE.*?;/i', '', $schema);
            $schema = preg_replace('/USE.*?;/i', '', $schema);
            
            // Execute statements one by one
            $statements = array_filter(array_map('trim', explode(';', $schema)));
            foreach ($statements as $stmt) {
                if (!empty($stmt) && stripos($stmt, 'CREATE DATABASE') === false && stripos($stmt, 'USE ') === false) {
                    $pdo->exec($stmt);
                }
            }
        }
        
        // Create admin account with provided password
        $hashedPassword = password_hash($adminPass, PASSWORD_BCRYPT, ['cost' => 10]);
        $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, email = ? WHERE role = 'admin' LIMIT 1");
        $stmt->execute([$adminUser, $hashedPassword, $adminEmail]);
        
        // Update site URL
        $stmt = $pdo->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_key = 'site_url'");
        $stmt->execute([$siteUrl]);
        
        // If no admin row existed, insert one
        if ($stmt->rowCount() === 0) {
            $stmt = $pdo->prepare("INSERT INTO users (full_name, username, password, email, role, vip_access) VALUES (?, ?, ?, ?, 'admin', 1)");
            $stmt->execute(['Super Admin', $adminUser, $hashedPassword, $adminEmail]);
        }
        
        // Write config file
        $configContent = <<<PHP
<?php
// ============================================================
// DATABASE CONFIGURATION
// FIXED BETS RO 🇷🇴
// ============================================================

define('DB_HOST', '$dbHost');
define('DB_NAME', '$dbName');
define('DB_USER', '$dbUser');
define('DB_PASS', '$dbPass');
define('DB_CHARSET', 'utf8mb4');

try {
    \$pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException \$e) {
    die("Database connection failed: " . \$e->getMessage());
}
PHP;
        
        file_put_contents(__DIR__ . '/config/database.php', $configContent);
        
        // Update config.php site URL
        $configFile = file_get_contents(__DIR__ . '/config/config.php');
        $configFile = preg_replace("/define\('SITE_URL', '.*?'\)/", "define('SITE_URL', '$siteUrl')", $configFile);
        file_put_contents(__DIR__ . '/config/config.php', $configFile);
        
        $success = '✅ Installation complete!';
        $step = 'done';
        
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Install - FIXED BETS RO 🇷🇴</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #0a0a0f;
            color: #f0f0f0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .install-container {
            width: 100%;
            max-width: 560px;
            background: #13131a;
            border: 1px solid #2a2a3a;
            border-radius: 16px;
            padding: 2.5rem;
        }
        h1 { 
            text-align: center; 
            font-size: 1.75rem;
            background: linear-gradient(135deg, #ffd700, #f0a500);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        .subtitle { text-align: center; color: #a0a0b0; margin-bottom: 2rem; font-size: 0.9rem; }
        .form-group { margin-bottom: 1.25rem; }
        label { display: block; margin-bottom: 0.5rem; color: #a0a0b0; font-size: 0.875rem; }
        input, select {
            width: 100%;
            padding: 12px 16px;
            background: #1a1a24;
            border: 1px solid #2a2a3a;
            border-radius: 8px;
            color: #f0f0f0;
            font-size: 1rem;
        }
        input:focus { outline: none; border-color: #ffd700; box-shadow: 0 0 0 3px rgba(255,215,0,0.1); }
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #ffd700, #f0a500);
            color: #111;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
        }
        .btn:hover { box-shadow: 0 4px 20px rgba(255,215,0,0.3); }
        .alert { padding: 14px; border-radius: 8px; margin-bottom: 1.5rem; }
        .alert-error { background: rgba(220,53,69,0.15); border: 1px solid rgba(220,53,69,0.3); color: #ff6b7a; }
        .alert-success { background: rgba(40,167,69,0.15); border: 1px solid rgba(40,167,69,0.3); color: #5cdb7a; }
        .success-content { text-align: center; }
        .success-content h2 { color: #5cdb7a; margin-bottom: 1rem; }
        .success-content p { color: #a0a0b0; margin-bottom: 1rem; line-height: 1.6; }
        .success-content .links { display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 1.5rem; }
        .success-content .links a {
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }
        .btn-admin { background: linear-gradient(135deg, #ffd700, #f0a500); color: #111; }
        .btn-site { background: #1a1a24; color: #f0f0f0; border: 1px solid #2a2a3a; }
        .note { text-align: center; color: #6b6b80; font-size: 0.8rem; margin-top: 1rem; }
        .note code { background: #1a1a24; padding: 2px 6px; border-radius: 4px; }
    </style>
</head>
<body>
    <div class="install-container">
        <h1>🇷🇴 FIXED BETS RO</h1>
        <p class="subtitle">Installation Wizard</p>
        
        <?php if ($error): ?>
        <div class="alert alert-error">❌ <?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
        <div class="success-content">
            <h2>🎉 Installation Complete!</h2>
            <p>
                Your VIP betting platform is ready.<br>
                <strong>Delete install.php</strong> for security before using the site.
            </p>
            <div class="links">
                <a href="admin/index.php" class="btn-admin">🔑 Go to Admin Panel</a>
                <a href="index.php" class="btn-site">🏠 View Site</a>
            </div>
            <div class="note">
                <strong>Important:</strong> Delete the <code>install.php</code> file from your server!
            </div>
        </div>
        <?php elseif ($step === 'done'): ?>
            <!-- Already handled above -->
        <?php else: ?>
        <form method="POST">
            <h3 style="color: #ffd700; margin-bottom: 1.5rem;">📦 Database Configuration</h3>
            
            <div class="form-group">
                <label>Database Host</label>
                <input type="text" name="db_host" value="<?= e(getenv('MYSQLHOST') ?: 'localhost') ?>" required>
            </div>
            <div class="form-group">
                <label>Database Name</label>
                <input type="text" name="db_name" value="fixed_bets_ro" required>
            </div>
            <div class="form-group">
                <label>Database Username</label>
                <input type="text" name="db_user" value="root" required>
            </div>
            <div class="form-group">
                <label>Database Password</label>
                <input type="password" name="db_pass" value="<?= e(getenv('MYSQLPASSWORD') ?: '') ?>">
            </div>
            
            <h3 style="color: #ffd700; margin: 1.5rem 0;">👑 Admin Account</h3>
            
            <div class="form-group">
                <label>Admin Username</label>
                <input type="text" name="admin_user" value="admin" required>
            </div>
            <div class="form-group">
                <label>Admin Password *</label>
                <input type="text" name="admin_pass" required placeholder="At least 6 characters" minlength="6">
            </div>
            <div class="form-group">
                <label>Admin Email</label>
                <input type="email" name="admin_email" placeholder="admin@example.com">
            </div>
            <div class="form-group">
                <label>Site URL</label>
                <input type="url" name="site_url" value="<?= e(getenv('RAILWAY_PUBLIC_DOMAIN') ? 'https://' . getenv('RAILWAY_PUBLIC_DOMAIN') : 'http://localhost:8000') ?>" required>
            </div>
            
            <button type="submit" class="btn">🚀 Install Now</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
ut type="url" name="site_url" value="<?= e(getenv('RAILWAY_PUBLIC_DOMAIN') ? 'https://' . getenv('RAILWAY_PUBLIC_DOMAIN') : 'http://localhost:8000') ?>" required>
            </div>
            
            <button type="submit" class="btn">🚀 Install Now</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
