FROM dunglas/frankenphp:latest

RUN install-php-extensions \
    pdo \
    pdo_pgsql \
    pgsql \
    intl \
    opcache \
    zip \
    gd

ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /app

COPY . .

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install --no-dev --optimize-autoloader --no-interaction

ENV APP_ENV=prod
ENV FRANKENPHP_CONFIG="worker ./public/index.php"
ENV SERVER_NAME=":8080"
ENV DOCUMENT_ROOT=/app/public

EXPOSE 8080