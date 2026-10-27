# 🇷🇴 FIXED BETS RO — VIP Betting Platform

A private-access betting platform. Admins create and manage user accounts; users do not self-register.

## Requirements
- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- PHP PDO MySQL enabled

## Fresh installation and deployment on InfinityFree

The installer creates the tables inside a database that you create in the InfinityFree control panel. It does not create a MySQL database. Use an empty database; the installer deliberately stops if it finds an existing users table.

### 1. Back up the current site

Before replacing files, download a copy of the current htdocs/ files and export the current database from phpMyAdmin. Even if there are no user accounts, the database may contain settings or other site data.

### 2. Download the project files

On GitHub, open the ashyamctommy-eng/fixed-bets-ro repository, select the main branch, then choose **Code → Download ZIP**. Extract the ZIP on your computer.

### 3. Create a fresh MySQL database

In the InfinityFree control panel, open **MySQL Databases** and create a database. Open **Details** and keep the exact database host, database name, username, and password. InfinityFree adds an account prefix to the database name and username. The port is normally 3306.

If the account has reached its database limit, back up the old database first. Only delete the old database in the hosting control panel after confirming it is the one used by this site and that you no longer need its data. Then create a fresh empty database.

### 4. Upload the files

In InfinityFree **File Manager**, open htdocs/. Back up or remove the old application files, then upload and extract the project ZIP so install.php is directly inside htdocs/. Upload hidden files such as .htaccess too. Keep any hosting-managed verification files that are not part of this project.

The config/ folder must be writable during setup so the installer can save config/database.php and config/install.lock. Do not paste database credentials into chat or public files.

### 5. Run the installer

Open [https://fixedbetsro.page.gd/install.php](https://fixedbetsro.page.gd/install.php) (or replace that domain with your own). Enter the exact database details from **MySQL Databases → Details**, your site URL, and a new admin username and strong password. The installer creates the tables and admin account and saves the database configuration.

If you see a message that the database already has a users table, stop and choose a new empty database. Do not re-import the schema into a database with existing data.

### 6. Sign in and secure the installation

Go to [https://fixedbetsro.page.gd/login.php](https://fixedbetsro.page.gd/login.php) and sign in with the admin username and password you chose in the installer. Admins are redirected to /admin/index.php. After signing in, you can change your password under **Admin → Settings**.

Delete install.php from htdocs/ after setup. Keep config/install.lock; it prevents the installer from running again. InfinityFree runs PHP itself, so start.sh is not used there; that script is for the Railway/Apache container setup.

## Updating an existing InfinityFree site

1. Back up the website files and export the database before an update.
2. Check the changed files in the GitHub commit and upload only the runtime files that changed.
3. Keep the live config/database.php. The copy in the repository contains placeholders and would break the live database connection if uploaded over it.
4. Do not run install.php against an existing database, and do not re-import sql/schema.sql into a live database. Schema imports are for a fresh database; they do not update existing passwords.
5. README.md is documentation and does not need to be uploaded to the website.

## Admin login routes
- Shared sign-in page for admins and VIP users: /login.php
- Admin dashboard after successful sign-in: /admin/index.php

The recommended InfinityFree setup is the installer above. If you manually import sql/schema.sql into a fresh database, its seed account is admin / Admin@123; change that password immediately. Never re-import the schema into an existing database to fix a login.

## Project structure


\`\`\`text
fixed-bets-ro/
├── admin/           # Admin pages
├── assets/          # CSS, JavaScript, images
├── config/          # Database, session, and app configuration
├── includes/        # Authentication and shared UI/functions
├── sql/schema.sql   # Database schema and manual-import seed data
├── user/            # VIP user pages
├── install.php      # One-time installation wizard
├── login.php        # Shared admin/VIP login page
└── start.sh         # Railway container startup only
\`\`\`text
