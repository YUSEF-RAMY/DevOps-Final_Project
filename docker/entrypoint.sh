#!/bin/sh
set -e

echo "============================================"
echo "  🍊 Depi_DevOps_Project — Docker Entrypoint"
echo "============================================"

# -----------------------------------------------
# 1. Populate shared public volume
# -----------------------------------------------
# The app_public named volume starts empty on first run.
# We keep a pristine copy at /var/www/html/public-base (from build)
# and sync it into the live /var/www/html/public so Nginx can serve it.
echo "📦 Syncing public assets to shared volume..."
cp -rn /var/www/html/public-base/. /var/www/html/public/ 2>/dev/null || true

# -----------------------------------------------
# 2. Wait for MySQL to be ready
# -----------------------------------------------
echo "⏳ Waiting for MySQL at ${DB_HOST}:${DB_PORT}..."
MAX_RETRIES=30
RETRY_COUNT=0
until php -r "
    \$c = @new PDO('mysql:host=${DB_HOST};port=${DB_PORT}', '${DB_USERNAME}', '${DB_PASSWORD}');
    echo 'ok';
" 2>/dev/null | grep -q 'ok'; do
    RETRY_COUNT=$((RETRY_COUNT + 1))
    if [ "$RETRY_COUNT" -ge "$MAX_RETRIES" ]; then
        echo "❌ MySQL not reachable after ${MAX_RETRIES} attempts. Exiting."
        exit 1
    fi
    echo "   Attempt ${RETRY_COUNT}/${MAX_RETRIES} — retrying in 2s..."
    sleep 2
done
echo "✅ MySQL is ready!"

# -----------------------------------------------
# 3. Ensure storage directory structure exists
# -----------------------------------------------
echo "📁 Ensuring storage directory structure..."
mkdir -p storage/app/public/products \
         storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs

chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# -----------------------------------------------
# 4. Create storage symlink
# -----------------------------------------------
echo "🔗 Creating storage symlink..."
rm -f public/storage
ln -sf /var/www/html/storage/app/public public/storage

# -----------------------------------------------
# 5. Clear stale caches and re-discover packages
# -----------------------------------------------
echo "🧹 Clearing stale caches..."
php artisan config:clear
php artisan cache:clear 2>/dev/null || true
php artisan package:discover --ansi

# -----------------------------------------------
# 6. Generate APP_KEY if not set
# -----------------------------------------------
if [ -z "$(grep '^APP_KEY=base64:' .env)" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
else
    echo "🔑 APP_KEY already set."
fi

# -----------------------------------------------
# 6. Run migrations
# -----------------------------------------------
echo "🗃️  Running database migrations..."
php artisan migrate --force

# -----------------------------------------------
# 7. Seed database (idempotent)
# -----------------------------------------------
echo "🌱 Seeding database..."
php artisan db:seed --force

# -----------------------------------------------
# 8. Optimize for production
# -----------------------------------------------
echo "⚡ Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "============================================"
echo "  ✅ Depi DevOps Project is ready!"
echo "  🌐 http://localhost:8080"
echo "  🔐 Admin: http://localhost:8080/admin/login"
echo "============================================"

# -----------------------------------------------
# 9. Start PHP-FPM
# -----------------------------------------------
exec php-fpm
