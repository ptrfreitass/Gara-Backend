FROM php:8.4-cli-alpine AS base

ARG WWWGROUP=1000
ARG WWWUSER=1000

WORKDIR /var/www

ENV DEBIAN_FRONTEND=noninteractive \
    TERM=xterm-color \
    OCTANE_SERVER=swoole \
    PHP_OPCACHE_ENABLE=1 \
    PHP_OPCACHE_REVALIDATE_FREQ=0 \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=0 \
    PHP_OPCACHE_MAX_ACCELERATED_FILES=10000 \
    PHP_OPCACHE_MEMORY_CONSUMPTION=192 \
    PHP_OPCACHE_MAX_WASTED_PERCENTAGE=10

RUN apk add --no-cache \
    curl \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    oniguruma-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev \
    libxml2-dev \
    autoconf \
    g++ \
    make \
    openssl-dev \
    pcre-dev \
    postgresql-dev \
    linux-headers \
    bash

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        zip \
        bcmath \
        opcache \
        intl \
        exif \
        pcntl \
        gd \
        sockets

RUN pecl install redis swoole \
    && docker-php-ext-enable redis swoole

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN addgroup -g ${WWWGROUP} sail \
    && adduser -D -u ${WWWUSER} -G sail sail

FROM base AS development

COPY php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY entrypoint.sh /usr/local/bin/entrypoint.sh

RUN chmod +x /usr/local/bin/entrypoint.sh

COPY --chown=sail:sail composer.json composer.lock* /var/www/

RUN composer install --no-interaction --no-progress --no-scripts

COPY --chown=sail:sail . /var/www

RUN composer dump-autoload --optimize

RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R sail:sail storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

USER sail

EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

FROM base AS production

COPY docker/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

COPY --chown=sail:sail . /var/www

RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader --classmap-authoritative \
    && composer clear-cache

RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R sail:sail storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

USER sail

EXPOSE 8000

CMD ["php", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=8000", "--workers=4", "--task-workers=6", "--max-requests=1000"]
