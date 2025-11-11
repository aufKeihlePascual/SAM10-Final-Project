#!/bin/bash
# Laravel startup for Azure Web App Linux

# Set web root to Laravel public folder
export DOCUMENT_ROOT=/home/site/wwwroot/public

# Run migrations
php /home/site/wwwroot/artisan migrate --force

# Clear & cache Laravel caches
php /home/site/wwwroot/artisan cache:clear
php /home/site/wwwroot/artisan route:cache
php /home/site/wwwroot/artisan config:cache
php /home/site/wwwroot/artisan view:cache

# Storage link (optional if not using S3/Blob)
php /home/site/wwwroot/artisan storage:link