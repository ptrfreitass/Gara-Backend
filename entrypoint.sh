#!/bin/sh
set -e

echo "🚀 Starting Laravel Octane with Swoole..."

if [ ! -f ".env" ]; then
    echo "📝 Creating .env file from .env.example..."
    cp .env.example .env
fi

if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:=" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --ansi
fi

echo "⏳ Waiting for database connection..."
until php artisan db:show 2>/dev/null; do
    echo "⏳ Database is unavailable - sleeping"
    sleep 2
done

echo "✅ Database is ready!"

if [ "$APP_ENV" != "production" ]; then
    echo "🔄 Running migrations..."
    php artisan migrate --force --no-interaction
fi

echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "🔥 Starting Octane server..."
exec php artisan octane:start \
    --server=swoole \
    --host=0.0.0.0 \
    --port=8000 \
    --workers="${OCTANE_WORKERS:-4}" \
    --task-workers="${OCTANE_TASK_WORKERS:-6}" \
    --max-requests="${OCTANE_MAX_REQUESTS:-1000}"
