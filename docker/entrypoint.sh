#!/bin/sh

set -eu

cd /app

if [ ! -f .env ]; then
    cp .env.example .env
fi

mkdir -p storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache database
touch database/database.sqlite

if ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --no-interaction --force
fi

# Run migrations
echo "Running migrations..."
php artisan migrate --force --no-interaction || { echo "Migration failed!"; exit 1; }

# Seed if users table is empty
echo "Checking if seeding is required..."
USER_COUNT=$(php artisan tinker --execute="try { echo \App\Models\User::count(); } catch (\Exception \$e) { echo -1; }" | tr -d '\r\n')

if [ "${USER_COUNT:- -1}" = "-1" ]; then
    echo "Users table missing or error during check. Migrations might have failed."
elif [ "$USER_COUNT" = "0" ]; then
    echo "Table empty, running seeders..."
    php artisan db:seed --force --no-interaction
else
    echo "Database already has $USER_COUNT users. Skipping seed."
fi

php artisan storage:link || true
php artisan optimize

# Ensure correct permissions for SQLite
chown -R www-data:www-data storage bootstrap/cache database

exec "$@"