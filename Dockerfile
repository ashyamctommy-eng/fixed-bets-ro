# ============================================================
# DOCKERFILE — FIXED BETS RO 🇷🇴
# PHP 8.2 + Apache + MySQL on Railway
# ============================================================

FROM php:8.2-apache

# Enable Apache rewrite (for .htaccess)
RUN a2enmod rewrite

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Set Apache document root
ENV APACHE_DOCUMENT_ROOT=/var/www/html
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Copy application files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# PHP config
RUN echo "date.timezone = Europe/Bucharest" > /usr/local/etc/php/conf.d/timezone.ini
RUN echo "session.use_strict_mode = 1" >> /usr/local/etc/php/conf.d/session.ini
RUN echo "session.use_only_cookies = 1" >> /usr/local/etc/php/conf.d/session.ini
RUN echo "session.cookie_httponly = 1" >> /usr/local/etc/php/conf.d/session.ini

# Expose port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
