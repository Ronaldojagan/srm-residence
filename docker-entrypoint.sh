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

# Ensure storage symlink exists
php artisan storage:link --force || true

# Run background database migrations if needed without blocking web server startup
(
    sleep 3
    php artisan migrate --force || true
    TENANT_COUNT=$(php -r "require 'vendor/autoload.php'; \$app = require 'bootstrap/app.php'; \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap(); try { echo \App\Models\Tenant::count(); } catch (\Throwable \$e) { echo 0; }" 2>/dev/null || echo "0")
    if [ "$TENANT_COUNT" = "0" ]; then
        echo "Database has 0 tenants. Importing backup data..."
        php artisan app:import-backup || true
    fi
) &

# Start Apache immediately so Render health check succeeds in seconds
exec "$@"
