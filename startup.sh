#!/bin/bash
# Set Laravel's public folder as the document root
export DOCUMENT_ROOT=/home/site/wwwroot/public

# Go to app root
cd /home/site/wwwroot

# Copy .env if missing
cp -n .env.example .env

# Generate APP_KEY if missing
if [ -z "$(grep APP_KEY .env | cut -d '=' -f2)" ]; then
    php artisan key:generate --ansi
fi

# Clear & cache Laravel caches
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage symlink if missing
php artisan storage:link || true

# Start PHP-FPM in foreground
php-fpm -F
