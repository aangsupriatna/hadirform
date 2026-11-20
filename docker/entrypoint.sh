#!/bin/sh
set -e

# Pastikan folder penyimpanan tersedia dengan izin yang benar
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/app/public \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Buat symbolic link storage jika belum ada
php artisan storage:link --quiet 2>/dev/null || true

# Optimasi cache Laravel di lingkungan produksi jika APP_KEY sudah disetel
if [ -n "$APP_KEY" ]; then
    echo "Caching Laravel configuration..."
    php artisan config:cache 2>/dev/null || true
    php artisan route:cache 2>/dev/null || true
    php artisan view:cache 2>/dev/null || true
fi

exec "$@"
