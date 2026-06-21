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

### 🚀 InfinityFree Deploy (Free — 5 minutes)

**Step 1 — Create Account**
- Go to [infinityfree.com](https://infinityfree.com) and sign up
- Verify your email, log into the control panel

**Step 2 — Create MySQL Database**
- In the control panel → **MySQL Databases**
- Click **"Create Database"**
- Enter a name (e.g. `fixed_bets_ro`)
- Set a **password** and note it down
- Once created, click **"Details"** — you'll see:
  - **Host:** `sqlNNN.infinityfree.com`
  - **Database Name:** `epiz_NNNNN_fixed_bets_ro`
  - **Username:** `epiz_NNNNN`

**Step 3 — Update config/database.php**
- Open `config/database.php` in a text editor
- Scroll to the **MANUAL CONFIG** section
- Replace the placeholder values with your InfinityFree MySQL details from Step 2

**Step 4 — Upload Files**
- In the InfinityFree control panel → **File Manager**
- Navigate to `htdocs/`
- Upload the ZIP file → **Extract**

Or use **FTP** (FileZilla):
- FTP Host: `ftp.infinityfree.com`
- Username: your InfinityFree username
- Password: your InfinityFree password
- Upload all files to `htdocs/`

**Step 5 — Import Database**
- Control panel → **phpMyAdmin**
- Select your database
- Click **Import** → Choose File → select `sql/schema.sql`
- Click **Go**

**Step 6 — Login**
- Visit `https://your-site.infinityfreeapp.com`
- **Username:** `admin`
- **Password:** `Admin@123`
- **Change password immediately** in Admin → Settings

### ⚡ Install.php (Alternative)
1. Upload all files except `config/database.php`
2. Visit `http://yoursite.com/install.php`
3. Fill in DB details + admin password
4. Click **Install Now**
5. **Delete install.php** after completion

### 📦 cPanel Hosting (Manual)
1. Upload all files to your web server
2. Create a MySQL database in cPanel
3. Import `sql/schema.sql` via phpMyAdmin
4. Edit `config/database.php` with your DB credentials
5. Login at `/admin/index.php` with `admin` / `Admin@123`

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
