#!/usr/bin/env bash
set -euo pipefail

PORT="${PORT:-10000}"

# Render exige escuchar en 0.0.0.0:$PORT (no hardcodear 80).
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/000-default.conf
sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-enabled/000-default.conf 2>/dev/null || true

cd /var/www/html

mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    storage/app/public \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rwx storage bootstrap/cache || true

# Enlace de storage (idempotente)
if [ ! -L public/storage ]; then
    php artisan storage:link || true
fi

# Cachés de producción (requiere APP_KEY y vars de entorno en Render)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migraciones al arrancar (desactiva con RUN_MIGRATIONS=false)
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

# Datos demo / catálogos (desactiva con RUN_SEEDERS=false).
# Debe correr en runtime (necesita DB); no en el build de la imagen.
if [ "${RUN_SEEDERS:-true}" = "true" ]; then
    php artisan db:seed --force
fi

exec "$@"
