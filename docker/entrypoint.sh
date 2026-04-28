#!/bin/bash
set -e

echo "==> Starting Laravel container..."

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

# Cache config for performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
  echo "==> Running migrations..."
  php artisan migrate --force
fi

# Storage link
php artisan storage:link || true

echo "==> Starting supervisord..."
exec "$@"
