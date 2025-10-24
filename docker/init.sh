#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

echo "[init] Aguardando banco de dados (db:3306) ficar pronto"
for i in $(seq 1 60); do
  if bash -c 'exec 3<>/dev/tcp/db/3306' 2>/dev/null; then
    echo "[init] DB disponível"
    exec 3>&-
    exec 3<&-
    break
  fi
  echo "[init] Aguardando DB... ($i)" && sleep 2
done

echo "[init] Verificando .env"
if [ ! -f .env ]; then
  cp .env.example .env
  # Ajusta para MySQL no Docker
  sed -i 's/^DB_CONNECTION=.*/DB_CONNECTION=mysql/' .env || true
  sed -i 's/^#\?\s*DB_HOST=.*/DB_HOST=db/' .env || true
  sed -i 's/^#\?\s*DB_PORT=.*/DB_PORT=3306/' .env || true
  sed -i 's/^#\?\s*DB_DATABASE=.*/DB_DATABASE=laravel/' .env || true
  sed -i 's/^#\?\s*DB_USERNAME=.*/DB_USERNAME=laravel/' .env || true
  sed -i 's/^#\?\s*DB_PASSWORD=.*/DB_PASSWORD=secret/' .env || true
  # Redis cliente
  sed -i 's/^REDIS_CLIENT=.*/REDIS_CLIENT=phpredis/' .env || true
fi

echo "[init] Composer install"
composer install --no-interaction --prefer-dist --optimize-autoloader

echo "[init] Gerando APP_KEY"
php artisan key:generate --force

echo "[init] Migrando banco"
php artisan migrate --force

echo "[init] Link storage"
php artisan storage:link || true

echo "[init] Instalando dependências front"
npm ci || npm install

echo "[init] Build de assets"
npm run build

echo "[init] Finalizado"
