# syntax=docker/dockerfile:1
#
# Multi-stage build with two selectable outputs:
#
#   base ──┬── dev    (docker build --target dev) — code arrives by bind mount
#          └── prod   (docker build .)            — code baked in, last stage
#   vendor ────┘

# ── base ─────────────────────────────────────────────────────────────────────
# Everything dev and prod share: PHP extensions, nginx, supervisor.
FROM php:8.3-fpm-alpine AS base

RUN apk add --no-cache \
        nginx \
        supervisor \
        bash \
        curl \
        libzip-dev \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        oniguruma-dev \
        icu-dev \
        mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mysqli \
        bcmath \
        zip \
        gd \
        intl \
        opcache \
        pcntl \
    && rm -rf /var/cache/apk/*

WORKDIR /var/www/html

COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh \
    && mkdir -p /run/nginx /var/log/supervisor

EXPOSE 8080

ENTRYPOINT ["entrypoint.sh"]
CMD ["supervisord", "-c", "/etc/supervisord.conf"]

# ── dev ──────────────────────────────────────────────────────────────────────
# Used by docker-compose.
FROM base AS dev

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Xdebug
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && pecl clear-cache \
    && apk del .build-deps

COPY docker/php.dev.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/xdebug.ini  /usr/local/etc/php/conf.d/xdebug.ini

# ── vendor ───────────────────────────────────────────────────────────────────
# Composer dependencies.
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
COPY packages ./packages
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs

# ── prod ─────────────────────────────────────────────────────────────────────
FROM base AS prod

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini

COPY --from=vendor /app/vendor ./vendor
COPY . .

RUN composer dump-autoload --optimize --no-dev

# Create the full storage tree.
RUN mkdir -p \
        storage/app/documents \
        storage/app/private \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

HEALTHCHECK --interval=30s --timeout=5s --start-period=40s --retries=3 \
    CMD curl -fsS http://127.0.0.1:8080/up || exit 1
