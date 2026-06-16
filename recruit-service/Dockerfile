FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip \
    mysql-client \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer create-project laravel/laravel /tmp/laravel-template --prefer-dist 11.* --no-interaction \
    && cd /tmp/laravel-template \
    && composer require laravel/sanctum "^4.0" --no-interaction \
    && composer install --optimize-autoloader --no-interaction

WORKDIR /var/www/html

COPY . .

RUN chmod +x entrypoint.sh

EXPOSE 8000
