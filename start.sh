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

# Enable headers module
a2enmod headers 2>/dev/null || true

# Fix MPM conflicts (especially on Railway) by disabling event/worker and forcing prefork
a2dismod mpm_event 2>/dev/null || true
a2dismod mpm_worker 2>/dev/null || true
a2dismod mpm_prefork 2>/dev/null || true
a2enmod mpm_prefork

# Start Apache
exec apache2-foreground
