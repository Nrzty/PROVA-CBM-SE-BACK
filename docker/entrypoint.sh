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

mkdir -p storage bootstrap/cache || true
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true

exec php-fpm

