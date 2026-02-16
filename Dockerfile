 متاكد  من هذا  الحل ؟FROM php:8.2-apache

# تفعيل mod_rewrite
RUN a2enmod rewrite

# تغيير DocumentRoot إلى public
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# تثبيت الحزم المطلوبة
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo pdo_pgsql zip

WORKDIR /var/www/html

COPY . .

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# صلاحيات
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 80

CMD ["apache2-foreground"]
