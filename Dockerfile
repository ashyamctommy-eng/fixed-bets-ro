# ============================================================
# DOCKERFILE — FIXED BETS RO 🇷🇴
# PHP 8.2 + Apache + MySQL
# ============================================================

FROM php:8.2-apache

# Enable Apache rewrite
RUN a2enmod rewrite

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

# Expose port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
