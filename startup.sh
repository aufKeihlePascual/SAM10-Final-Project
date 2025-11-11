#!/bin/bash
# Laravel startup for Azure Web App Linux

# Set web root to Laravel public folder
export DOCUMENT_ROOT=/home/site/wwwroot/public

# Start PHP-FPM
php-fpm
