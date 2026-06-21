-- ============================================================
-- FIXED BETS RO 🇷🇴 - Database Schema
-- Complete VIP Betting Platform
-- ============================================================

CREATE DATABASE IF NOT EXISTS fixed_bets_ro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fixed_bets_ro;

-- ============================================================
-- USERS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
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
    reset_expires DATETIME DEFAULT NULL,
    INDEX idx_username (username),
    INDEX idx_vip (vip_access),
    INDEX idx_status (status_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- CUSTOM STATUSES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS custom_statuses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    icon VARCHAR(10) NOT NULL DEFAULT '📋',
    color VARCHAR(7) NOT NULL DEFAULT '#FFD700',
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    restrict_access TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- VIP GAMES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS vip_games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    match_name VARCHAR(200) NOT NULL,
    league VARCHAR(100) DEFAULT NULL,
    selection VARCHAR(200) NOT NULL,
    odds DECIMAL(6,2) NOT NULL,
    match_date DATE NOT NULL,
    match_time TIME NOT NULL,
    confidence VARCHAR(50) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    result_status ENUM('win', 'loss', 'pending') NOT NULL DEFAULT 'pending',
    is_archived TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_date (match_date),
    INDEX idx_result (result_status),
    INDEX idx_archived (is_archived)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ANNOUNCEMENTS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    priority ENUM('low', 'normal', 'high', 'urgent') NOT NULL DEFAULT 'normal',
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_active (is_active),
    INDEX idx_priority (priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- NOTIFICATIONS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('personal', 'global') NOT NULL DEFAULT 'personal',
    priority ENUM('low', 'normal', 'high', 'urgent') NOT NULL DEFAULT 'normal',
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_read (is_read),
    INDEX idx_type (type),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SITE SETTINGS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS site_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT DEFAULT NULL,
    INDEX idx_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ACTIVITY LOG TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SESSIONS TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_token VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_token (session_token),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DEFAULT DATA
-- ============================================================

-- Insert default admin account (password: Admin@123)
INSERT INTO users (full_name, username, password, role, vip_access)
VALUES ('Super Admin', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 1);

-- Insert default statuses
INSERT INTO custom_statuses (name, icon, color, title, message, restrict_access) VALUES
('Active', '✅', '#28a745', 'Account Active', 'Your account is fully active. Enjoy VIP betting!', 0),
('Pending Setup', '⏳', '#ffc107', 'Account Pending Setup', 'Your account is being set up. Please wait for confirmation.', 1),
('Payment Pending', '💳', '#ff9800', 'Payment Pending', 'Your payment is being processed. Access will be granted upon confirmation.', 1),
('VIP Expired', '📅', '#dc3545', 'VIP Membership Expired', 'Your VIP membership has expired. Please contact support for renewal.', 1),
('Verification Required', '⚠️', '#e83e8c', 'Verification Required', 'Your account requires verification before access can be granted.', 1),
('Suspended', '🚫', '#dc3545', 'Account Suspended', 'Your account has been suspended. Please contact support.', 1),
('Under Review', '🔍', '#17a2b8', 'Account Under Review', 'Your account is currently under review.', 1),
('Account Restricted', '🔒', '#dc3545', 'Account Restricted', 'Your account currently has restricted access. Please contact support for assistance.', 1),
('Renewal Required', '🔄', '#fd7e14', 'VIP Renewal Required', 'Your VIP membership has expired. Please contact support to renew access.', 1);

-- Insert default site settings
INSERT INTO site_settings (setting_key, setting_value) VALUES
('site_name', 'FIXED BETS RO 🇷🇴'),
('site_logo', ''),
('telegram_link', 'https://t.me/fixedbetsro'),
('whatsapp_link', 'https://wa.me/40700000000'),
('support_email', 'support@fixedbetsro.com'),
('dashboard_theme', 'dark');
