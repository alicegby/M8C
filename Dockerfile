FROM dunglas/frankenphp:latest

# Installation des extensions PHP nécessaires
RUN install-php-extensions \
    pdo \
    pdo_pgsql \
    pgsql \
    intl \
    opcache \
    zip \
    gd

# Dossier de travail
WORKDIR /app

# Copie des fichiers
COPY . .

# Installation des dépendances Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Variables d'environnement
ENV APP_ENV=prod
ENV FRANKENPHP_CONFIG="worker ./public/index.php"
ENV SERVER_NAME=":8080"

# Document root Symfony
ENV DOCUMENT_ROOT=/app/public

EXPOSE 8080