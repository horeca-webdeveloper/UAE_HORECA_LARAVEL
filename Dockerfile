# Use official PHP 8.3 image as base
FROM php:8.3-fpm

# Set working directory inside the container
WORKDIR /var/www/html

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libxml2-dev \
    git \
    unzip && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install gd zip pdo pdo_mysql xml calendar && \
    pecl install xdebug && \
    docker-php-ext-enable xdebug && \
    apt-get clean

# Install Composer (PHP Dependency Manager)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Fix permissions to avoid git ownership issues
RUN chown -R www-data:www-data /var/www/html
RUN git config --global --add safe.directory /var/www/html  # Add safe directory for git

# Copy the existing application to the container
COPY . /var/www/html

# Set appropriate permissions for the copied files
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Install Composer dependencies
RUN composer install --no-interaction --optimize-autoloader

# Set environment variable for Laravel's APP_KEY
#RUN php artisan key:generate

# Expose the necessary port
EXPOSE 8000

# Start PHP-FPM server (for Laravel)
# ENTRYPOINT ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
