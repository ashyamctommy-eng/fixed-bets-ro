# 🇷🇴 FIXED BETS RO — VIP Betting Platform

A private-access betting platform. Admins create and manage user accounts; users do not self-register.

## Features
- Admin-managed accounts and VIP access
- Custom user statuses and access restrictions
- VIP games and results tracking
- Announcements and notifications
- User dashboards, Telegram/WhatsApp support links, and site settings

## Requirements
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- PHP PDO MySQL enabled

## InfinityFree installation

1. In the InfinityFree control panel, create a MySQL database. Open its **Details** and keep the exact host, database name, username, and password. InfinityFree database names and usernames include an account prefix.
2. Upload the project files to htdocs/. The config/ folder must be writable during setup so the wizard can save the database configuration.
3. Open https://your-domain/install.php and enter the database details, site URL, and the admin username/password you want. Use an empty database; the wizard creates tables but does not create the MySQL database.
4. When setup completes, sign in at https://your-domain/login.php. Admin accounts are redirected to /admin/index.php.
5. **Delete install.php from htdocs after setup.** The wizard also writes config/install.lock to block a second install.

InfinityFree runs PHP itself. start.sh is for the Railway/Apache container setup and is not used on InfinityFree.

## Admin login routes
- Sign-in page for both admin and VIP accounts: /login.php
- Admin dashboard after sign-in: /admin/index.php

If the admin password was set during installation, use that password. If you imported sql/schema.sql manually, its seed account is admin / Admin@123; change that password immediately. The InfinityFree installer is the recommended setup method because the SQL file's database name is not automatically prefixed for your account.

## Updating an existing InfinityFree site

- Back up the website files and MySQL database first.
- Do not replace your live `config/database.php` with the GitHub copy; the repository copy contains placeholder values.
- Do not re-import `sql/schema.sql` into a live database. It is for a fresh database and does not update passwords in an existing database.
- Upload only the runtime files you are intentionally changing. `README.md` is documentation and does not need to be uploaded to the website.

## Security notes
- Passwords are stored as password hashes.
- The installer rejects an existing users table rather than overwriting a live database.
- Remove install.php after installing, even though the completed wizard is locked.
- Keep config/database.php private; the project .htaccess denies direct web access to the config directory.

## Project structure

```text
fixed-bets-ro/
├── admin/           # Admin pages
├── assets/           # CSS, JavaScript, images
├── config/           # Database, session, and app configuration
├── includes/         # Authentication and shared UI/functions
├── sql/schema.sql    # Database schema and manual-import seed data
├── user/             # VIP user pages
├── install.php       # One-time installation wizard
├── login.php         # Shared admin/VIP login page
└── start.sh          # Railway container startup only
```
