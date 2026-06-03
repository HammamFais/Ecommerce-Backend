#!/bin/sh
set -e

# Fix Apache MPM at runtime
a2dismod mpm_event mpm_worker 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true
a2enmod rewrite 2>/dev/null || true

# Bootstrap a writable .env file if the image does not include one.
if [ ! -f ".env" ] && [ -f ".env.example" ]; then
    cp .env.example .env
fi

# Generate APP_KEY directly in .env if it has not been provided by the deploy environment.
if [ -z "$APP_KEY" ]; then
    APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"

    if grep -q '^APP_KEY=' .env 2>/dev/null; then
        sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env
    else
        echo "APP_KEY=${APP_KEY}" >> .env
    fi

    export APP_KEY
fi

# Run migrations only when explicitly enabled.
if [ "${RUN_MIGRATIONS_ON_STARTUP:-false}" = "true" ]; then
    echo "Running migrations..."
    php artisan migrate --force
fi

# Run seeders only when explicitly enabled.
if [ "${RUN_SEED_ON_STARTUP:-false}" = "true" ]; then
    echo "Running seeders..."
    php artisan db:seed --force
fi

# Clear and cache config for production
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Fix permissions after volume mount
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "Starting Apache..."
exec "$@"