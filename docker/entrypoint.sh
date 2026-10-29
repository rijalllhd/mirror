#!/bin/sh
set -eu

mkdir -p storage/app/public storage/app/private storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Hanya service app yang menjalankan migrasi/cache agar scheduler dan queue tidak saling balapan.
if [ "${CONTAINER_ROLE:-app}" = "app" ]; then
    php artisan package:discover --ansi --no-interaction
    php artisan storage:link --force || true
    php artisan optimize:clear
    php artisan config:cache
fi

exec "$@"
