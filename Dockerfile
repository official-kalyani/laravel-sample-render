FROM php:8.2-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl libzip-dev unzip zip \
    libpng-dev libonig-dev libxml2-dev \
    sqlite3 libsqlite3-dev

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql zip mbstring exif pcntl bcmath gd pdo_sqlite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy project files
COPY . .

# Install PHP dependencies
RUN composer install --optimize-autoloader --no-dev

# Set permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Create SQLite DB file (optional)
RUN mkdir -p /data && touch /data/database.sqlite

# Cache Laravel config
RUN php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache

# Expose port (Render expects this)
EXPOSE 8000

# Start the Laravel app
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
