#!/bin/bash
set -e

# LOCAL_DEV (not APP_ENV) drives the dev branches below.
IS_LOCAL=0
[ "${LOCAL_DEV:-0}" = "1" ] && IS_LOCAL=1

if [ "$IS_LOCAL" = "1" ]; then
  echo "==> Starting Laravel container (local dev mode)..."
else
  echo "==> Starting Laravel container..."
fi

# In dev the working tree is bind-mounted, so vendor/ can be missing on a fresh clone.
if [ "$IS_LOCAL" = "1" ] && [ ! -f vendor/autoload.php ]; then
  echo "==> vendor/ is missing — running composer install (first boot is slow)..."
  composer install --no-interaction --prefer-dist
fi

# Recreate the storage tree.
mkdir -p \
  storage/app/documents \
  storage/app/private \
  storage/app/public \
  storage/framework/cache/data \
  storage/framework/sessions \
  storage/framework/views \
  storage/logs \
  bootstrap/cache

if [ "$IS_LOCAL" = "1" ]; then
  # Bind-mounted files carry the host's ownership, which won't match www-data.
  chmod -R a+rwX storage bootstrap/cache 2>/dev/null || true
fi

# Wait for MySQL to be ready
if [ -n "$DB_HOST" ]; then
  echo "==> Waiting for MySQL at ${DB_HOST}:${DB_PORT:-3306}..."
  until mysqladmin ping \
          --skip-ssl \
          --connect-timeout=3 \
          -h"$DB_HOST" \
          -P"${DB_PORT:-3306}" \
          -u"$DB_USERNAME" \
          -p"$DB_PASSWORD" >/dev/null 2>&1; do
    echo "  MySQL not ready yet, retrying in 2s..."
    sleep 2
  done
  echo "==> MySQL is ready."
fi

# Clear old cached config
php artisan config:clear
php artisan route:clear
php artisan view:clear

if [ "$IS_LOCAL" = "1" ]; then
  echo "==> LOCAL_DEV=1 — leaving config/routes/views uncached."
else
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
fi

# Run migrations
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  echo "==> Running migrations..."
  php artisan migrate --force
fi

# public/storage absolute symlink
if [ -L public/storage ] && [ ! -e public/storage ]; then
  echo "==> Removing dangling public/storage symlink."
  rm -f public/storage
fi
php artisan storage:link >/dev/null 2>&1 || true

echo "==> Exec: $*"
exec "$@"
