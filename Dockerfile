FROM dunglas/frankenphp:latest

RUN install-php-extensions \
    pdo \
    pdo_pgsql \
    pgsql \
    intl \
    opcache \
    zip \
    gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV APP_ENV=prod

WORKDIR /app

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts && \
    composer dump-autoload --optimize && \
    ls -la vendor/autoload_runtime.php

ENV FRANKENPHP_CONFIG="worker ./public/index.php"
ENV SERVER_NAME=":8080"
ENV DOCUMENT_ROOT=/app/public

EXPOSE 8080