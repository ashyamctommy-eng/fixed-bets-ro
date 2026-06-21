#!/bin/bash
# ============================================================
# STARTUP SCRIPT — FIXED BETS RO 🇷🇴
# Configures Apache for Railway's dynamic PORT
# ============================================================

# Set Apache to listen on Railway's PORT
PORT=${PORT:-8080}
echo "Listen 0.0.0.0:${PORT}" > /etc/apache2/ports.conf

# Update the site config to use this port
sed -i "s/:80/:${PORT}/g" /etc/apache2/sites-available/000-default.conf

# Start Apache
exec apache2-foreground
