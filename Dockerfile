# ============================================================
# DOCKERFILE — FIXED BETS RO 🇷🇴
# PHP 8.2 + Apache + MySQL for Railway
# ============================================================

FROM php:8.2-apache

# Enable Apache rewrite and headers modules
RUN a2enmod rewrite headers

# Fix MPM conflict: only prefork works with mod_php
RUN a2dismod mpm_event mpm_worker 2>/dev/null; a2enmod mpm_prefork; exit 0

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Copy application files
COPY . /var/www/html/

# Set ownership
RUN chown -R www-data:www-data /var/www/html

# PHP config
RUN echo "date.timezone = Europe/Bucharest" > /usr/local/etc/php/conf.d/fixedbets.ini \
 && echo "session.use_strict_mode = 1" >> /usr/local/etc/php/conf.d/fixedbets.ini \
 && echo "session.use_only_cookies = 1" >> /usr/local/etc/php/conf.d/fixedbets.ini \
 && echo "session.cookie_httponly = 1" >> /usr/local/etc/php/conf.d/fixedbets.ini

# Make start script executable
RUN chmod +x /var/www/html/start.sh

# Expose port
EXPOSE 8080

# Start with our wrapper
CMD ["/var/www/html/start.sh"]
