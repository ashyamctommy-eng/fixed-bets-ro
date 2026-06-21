# 🇷🇴 FIXED BETS RO — VIP Betting Platform

## Overview
A complete VIP betting platform where all user accounts are created and managed exclusively by the Admin. Users receive login credentials from the Admin and cannot self-register.

## Features
- 🔐 Admin-only account creation and management
- ⭐ VIP Access Control (per-user toggle)
- 🏷️ Unlimited custom status system with icons, colors, and messages
- 🎯 VIP Games management with results tracking
- 📢 Announcements system with priority levels
- 🔔 Personal & Global notification system
- 👤 User dashboard with restricted access views
- 📊 Results history with win/loss tracking
- 💬 Telegram and WhatsApp support integration
- ⚙️ Full site settings management
- 🌙 Dark theme with gold accents
- 📱 Mobile responsive design

## Requirements
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- cPanel shared hosting compatible

## Installation

### Quick Install (Recommended)
1. Upload all files to your web server
2. Navigate to `http://yoursite.com/install.php`
3. Fill in database and admin details
4. Click "Install Now"
5. **Delete install.php after installation!**

### Manual Install
1. Upload all files to your web server
2. Create a MySQL database
3. Import `sql/schema.sql` into your database
4. Edit `config/database.php` with your database credentials
5. Edit `config/config.php` with your site URL
6. Access `admin/index.php` and login with:
   - Username: `admin`
   - Password: `Admin@123`

## Security
- All passwords hashed with bcrypt
- CSRF protection on all forms
- SQL injection prevention via prepared statements
- XSS protection through output escaping
- Session management with remember-me tokens
- Role-based access control (Admin vs VIP User)
- .htaccess protection for sensitive directories

## Default Admin Login
After install:
- **Username:** (what you set during install)
- **Password:** (what you set during install)

Default if using manual schema import:
- **Username:** admin
- **Password:** Admin@123

## Directory Structure
```
fixed-bets-ro/
├── config/          # Configuration files
├── includes/        # Core includes (auth, header, footer)
├── admin/           # Admin dashboard pages
├── user/            # User dashboard pages
├── api/             # AJAX endpoints
├── assets/          # CSS, JS, images
├── sql/             # Database schema
├── archive/         # Archived files
├── install.php      # Installation wizard
├── index.php        # Homepage
├── login.php        # Login page
└── .htaccess        # Security rules
```

## Support
- Telegram: [@fixedbetsro](https://t.me/fixedbetsro)
- Email: support@fixedbetsro.com
