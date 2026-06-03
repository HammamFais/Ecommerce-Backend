#!/bin/bash
set -e

echo "🔄 Running Laravel migrations..."

# Copy .env.example to .env if it doesn't exist
if [ ! -f ".env" ]; then
    cp .env.example .env
fi

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ]; then
    APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
    sed -i "s|^APP_KEY=.*|APP_KEY=${APP_KEY}|" .env || echo "APP_KEY=${APP_KEY}" >> .env
    export APP_KEY
fi

# Run migrations
php artisan migrate --force

echo "✅ Migrations completed successfully!"

