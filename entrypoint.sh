#!/usr/bin/env bash
set -e

cd /var/www/html

# Prepare environment file
if [ ! -f .env ]; then
  cp .env.example .env
fi

# Set app key if not exists
php artisan key:generate --force || true

# Wait for database to be ready, if configured
DB_HOST=${DB_HOST:-}
DB_PORT=${DB_PORT:-3306}
if [ -n "$DB_HOST" ]; then
  echo "Waiting for database at $DB_HOST:$DB_PORT..."
  for i in {1..60}; do
    if nc -z "$DB_HOST" "$DB_PORT"; then
      echo "Database is up"
      break
    fi
    echo "...retry $i"
    sleep 2
  done
fi

# Run migrations unless running scheduler
if [ "$1" != "php" ] || [ "$2" != "artisan" ] || [ "$3" != "schedule:work" ]; then
  php artisan migrate --force || true
fi

# Create storage symlink
php artisan storage:link || true

# Optimize framework caches
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Ensure permissions for storage and cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rw storage bootstrap/cache

# If a custom command is provided (e.g., `php artisan schedule:work`), run it; otherwise start PHP-FPM
if [ $# -gt 0 ]; then
  exec "$@"
else
  exec php-fpm
fi