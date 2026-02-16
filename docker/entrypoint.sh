#!/usr/bin/env sh
set -eu

cd /var/www/html

if [ ! -f vendor/autoload.php ]; then
  echo "[entrypoint] vendor/autoload.php not found. Running composer install..."
  composer install --no-interaction --prefer-dist --optimize-autoloader
fi

if [ -f package-lock.json ] && [ ! -d node_modules ]; then
  echo "[entrypoint] node_modules not found. Running npm ci..."
  npm ci
fi

# Generate APP_KEY if not set
if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
  echo "[entrypoint] Generating APP_KEY..."
  php artisan key:generate
fi

mkdir -p storage bootstrap/cache || true
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

echo "[entrypoint] Clearing Laravel cache..."
php artisan cache:clear
php artisan config:clear

echo "[entrypoint] Running database migrations..."
php artisan migrate --force

echo "[entrypoint] Running database seeders..."
php artisan db:seed --force

echo "[entrypoint] Container setup complete!"

exec php-fpm

