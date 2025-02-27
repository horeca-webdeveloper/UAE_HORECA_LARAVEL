FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    apache2 \
    libapache2-mod-fcgid \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip calendar \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules and configs
RUN a2enmod rewrite headers proxy_fcgi setenvif
RUN a2enconf serve-cgi-bin

# Configure PHP
COPY php.ini /usr/local/etc/php/conf.d/app.ini

# Configure PHP-FPM
RUN mkdir -p /var/run/php-fpm && \
    mkdir -p /var/log/php-fpm && \
    touch /var/log/php-fpm/error.log

# Configure Apache
COPY apache-default.conf /etc/apache2/sites-available/000-default.conf
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Set the working directory
WORKDIR /var/www

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy the application files
COPY . /var/www/

# Copy .env file
COPY .env /var/www/.env

# Create all necessary directories with proper permissions
RUN mkdir -p /var/www/storage/framework/cache && \
    mkdir -p /var/www/storage/framework/sessions && \
    mkdir -p /var/www/storage/framework/views && \
    mkdir -p /var/www/storage/logs && \
    mkdir -p /var/www/storage/app/public && \
    mkdir -p /var/www/bootstrap/cache && \
    mkdir -p /var/www/public/storage && \
    chown -R www-data:www-data /var/www && \
    chmod -R 775 /var/www/storage && \
    chmod -R 775 /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/public

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Create startup script with storage link and permissions
RUN echo '#!/bin/bash\n\
\n\
# Remove existing storage link and create new one\n\
rm -f /var/www/public/storage\n\
php artisan storage:link\n\
\n\
# Clear all caches\n\
php artisan cache:clear\n\
php artisan config:clear\n\
php artisan view:clear\n\
php artisan route:clear\n\
\n\
# Run migrations\n\
php artisan migrate --force\n\
\n\
# Reset permissions\n\
chown -R www-data:www-data /var/www/storage\n\
chown -R www-data:www-data /var/www/bootstrap/cache\n\
chown -R www-data:www-data /var/www/public/storage\n\
chmod -R 775 /var/www/storage\n\
chmod -R 775 /var/www/bootstrap/cache\n\
chmod -R 775 /var/www/public/storage\n\
\n\
# Start services\n\
php-fpm -D\n\
/usr/sbin/apache2ctl -D FOREGROUND' > /usr/local/bin/start-apache-php-fpm && \
    chmod +x /usr/local/bin/start-apache-php-fpm

# Expose port 80
EXPOSE 80

# Start Apache and PHP-FPM
CMD ["/usr/local/bin/start-apache-php-fpm"]
