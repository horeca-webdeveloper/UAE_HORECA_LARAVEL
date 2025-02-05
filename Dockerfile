# Use official PHP 8.3 image with Apache as base
FROM php:8.3-apache

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

# Enable Apache rewrite module
RUN a2enmod rewrite headers expires

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

# Set Apache DocumentRoot to Laravel's public directory
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# Enable caching for static files
RUN echo '<IfModule mod_expires.c>\n\
    ExpiresActive On\n\
    ExpiresByType text/css "access plus 1 month"\n\
    ExpiresByType text/javascript "access plus 1 month"\n\
    ExpiresByType application/javascript "access plus 1 month"\n\
    ExpiresByType image/png "access plus 1 year"\n\
    ExpiresByType image/jpeg "access plus 1 year"\n\
    ExpiresByType image/gif "access plus 1 year"\n\
</IfModule>' > /etc/apache2/conf-available/expires.conf && \
    a2enconf expires

# Restart Apache to apply changes
RUN service apache2 restart

# Expose Apache port
EXPOSE 80
