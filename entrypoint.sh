#!/bin/bash
set -e

echo "⏳ Aguardando banco de dados..."
until pg_isready -h postgres -U gara_user -d gara_db; do
  sleep 2
done

echo "✅ Banco de dados pronto!"
echo "🔧 Configurando Laravel..."

php artisan config:clear || true
php artisan cache:clear || true
php artisan route:clear || true
php artisan view:clear || true

if grep -q "APP_KEY=$" .env 2>/dev/null || [ ! -f .env ]; then
    if [ ! -f .env ]; then
        cp .env.example .env
    fi
    php artisan key:generate
fi

php artisan migrate --force || true

echo "🚀 Iniciando Octane..."
exec php artisan octane:start --server=swoole --host=0.0.0.0 --port=8000
