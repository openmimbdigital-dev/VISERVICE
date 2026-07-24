# syntax=docker/dockerfile:1
# VISERVICE — Laravel 12 + Livewire + Vite (deploy en Render)
# Runtime: PHP 8.3 + Apache. Escucha en 0.0.0.0:$PORT (requerido por Render).

# ── Frontend (Vite / Tailwind) ───────────────────────────────────────────────
FROM node:22-bookworm-slim AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY resources ./resources
COPY public ./public
COPY vite.config.js ./
COPY tailwind.config.js ./

RUN npm run build


# ── Dependencias PHP (Composer) ──────────────────────────────────────────────
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --no-interaction \
    --ignore-platform-reqs

COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY routes ./routes
COPY lang ./lang
COPY artisan ./

RUN mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && composer dump-autoload --optimize --classmap-authoritative --no-dev --no-interaction


# ── Imagen de producción ────────────────────────────────────────────────────
FROM php:8.3-apache-bookworm

LABEL org.opencontainers.image.title="VISERVICE" \
      org.opencontainers.image.description="ERP Laravel 12 para Render"

ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_CUSTOM_ERROR_PAGES=false \
    LOG_CHANNEL=stderr \
    COMPOSER_ALLOW_SUPERUSER=1 \
    APACHE_DOCUMENT_ROOT=/var/www/html/public \
    RUN_MIGRATIONS=true \
    RUN_SEEDERS=true

# Extensiones requeridas: MySQL/Postgres, DomPDF (gd/zip), Laravel, Spatie, etc.
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        curl \
        unzip \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libzip-dev \
        libicu-dev \
        libonig-dev \
        libxml2-dev \
        libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        exif \
        gd \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        pdo_pgsql \
        zip \
    && rm -rf /var/lib/apt/lists/*

# mod_php requiere un solo MPM (prefork). Evita AH00534: More than one MPM loaded.
RUN (a2dismod mpm_event || true) \
    && (a2dismod mpm_worker || true) \
    && a2enmod mpm_prefork \
    && a2enmod rewrite headers \
    && printf '%s\n' 'ServerName localhost' > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername

# DocumentRoot → public/ + AllowOverride para rutas de Laravel
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && printf '%s\n' \
        '<Directory ${APACHE_DOCUMENT_ROOT}>' \
        '    AllowOverride All' \
        '    Require all granted' \
        '</Directory>' \
        > /etc/apache2/conf-available/laravel-public.conf \
    && a2enconf laravel-public

# Opcache recomendado en producción
RUN printf '%s\n' \
        'opcache.enable=1' \
        'opcache.memory_consumption=256' \
        'opcache.max_accelerated_files=20000' \
        'opcache.validate_timestamps=0' \
        'opcache.revalidate_freq=0' \
        > /usr/local/etc/php/conf.d/opcache-laravel.ini

WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor

COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY lang ./lang
COPY public ./public
COPY resources ./resources
COPY routes ./routes
COPY artisan composer.json composer.lock ./

# Assets compilados (después de public/ para no sobrescribirlos)
COPY --from=frontend /app/public/build ./public/build

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh \
    && mkdir -p storage/framework/{cache,sessions,views} storage/logs storage/app/public bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

# Render inyecta PORT (por defecto 10000)
EXPOSE 10000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]
