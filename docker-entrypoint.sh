#!/bin/bash
set -e

# Support dynamic PORT environment variable (Render uses $PORT)
if [ -n "$PORT" ]; then
    sed -ri -e "s!Listen 80!Listen ${PORT}!g" /etc/apache2/ports.conf
    sed -ri -e "s!<VirtualHost \*:80>!<VirtualHost \*:${PORT}>!g" /etc/apache2/sites-available/*.conf
fi

# Ensure storage directories exist and have proper permissions
mkdir -p /var/www/html/storage/framework/{sessions,views,cache} /var/www/html/storage/logs
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# If SQLite is selected, create database file if missing
if [ "$DB_CONNECTION" = "sqlite" ]; then
    if [ ! -f /var/www/html/database/database.sqlite ]; then
        touch /var/www/html/database/database.sqlite
        chown www-data:www-data /var/www/html/database/database.sqlite
        chmod 664 /var/www/html/database/database.sqlite
    fi
fi

# Run Laravel optimizations
php artisan storage:link --force || true
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Import backup if available, or run standard migrations
if [ -f /var/www/html/database/srm_residence_backup.sql ]; then
    php artisan app:import-backup || php artisan migrate --force || true
else
    php artisan migrate --force || true
fi

exec "$@"
