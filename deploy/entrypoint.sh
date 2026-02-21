#!/bin/bash
set -e

cd /var/www/html

# Ensure supervisor log directory exists
mkdir -p /var/log/supervisor

# Ensure storage directories have correct permissions
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Wait for PostgreSQL to be ready
if [ "$DB_CONNECTION" = "pgsql" ]; then
    echo "Waiting for PostgreSQL at $DB_HOST:$DB_PORT..."
    for i in $(seq 1 30); do
        if php -r "try { new PDO('pgsql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); echo 'ok'; } catch (Exception \$e) { exit(1); }" 2>/dev/null; then
            echo "PostgreSQL is ready."
            break
        fi
        echo "Attempt $i/30 - PostgreSQL not ready, retrying in 2s..."
        sleep 2
    done
fi

# Create .env file if it doesn't exist (Dokploy injects vars via environment)
if [ ! -f .env ]; then
    touch .env
fi

# Generate APP_KEY only if not already provided via environment
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
    export APP_KEY=$(grep APP_KEY .env | cut -d '=' -f2-)
    echo "Generated APP_KEY=$APP_KEY — save this in Dokploy env vars!"
fi

# Cache config, routes, and views for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
php artisan migrate --force

# Link storage
php artisan storage:link --force 2>/dev/null || true

# Start supervisor (nginx + php-fpm + queue worker)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
