#!/usr/bin/env sh
set -e

# Ensure required directories exist with correct permissions
mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true

# Install PHP dependencies
if [ ! -d vendor ]; then
  echo "[entrypoint] Installing composer dependencies..."
else
  echo "[entrypoint] Updating composer dependencies (install)..."
fi
composer install --no-interaction --prefer-dist --no-progress

# Generate app key if missing
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:xxxxxxxx" ]; then
  echo "[entrypoint] Generating APP_KEY..."
  php artisan key:generate --force || true
fi

# Try running migrations with retries (lets Laravel read .env)
echo "[entrypoint] Running migrations (with retries)..."
ATTEMPTS_LEFT=20
until php artisan migrate --force; do
  ATTEMPTS_LEFT=$((ATTEMPTS_LEFT - 1))
  if [ $ATTEMPTS_LEFT -le 0 ]; then
    echo "[entrypoint] Migrations did not complete after several attempts. Continuing startup."
    break
  fi
  sleep 2
done

echo "[entrypoint] Starting php-fpm..."
exec php-fpm
