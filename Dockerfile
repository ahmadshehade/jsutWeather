# Dockerfile
FROM php:8.2-fpm

WORKDIR /var/www/html

# تثبيت dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev unzip git curl

RUN docker-php-ext-install pdo pdo_mysql zip

# نسخ المشروع كله
COPY . .

# تثبيت Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# expose port
EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=8000
