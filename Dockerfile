# Dockerfile (production-ready, simple)
FROM php:8.2-apache

# إعداد system packages و PHP extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo pdo_pgsql zip

# تمكين mod_rewrite
RUN a2enmod rewrite

# نسخ Composer (multi-stage style)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# انسخ ملفات المشروع
COPY . .

# صلاحيات
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# تثبيت composer (بدون dev)
RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-interaction --no-scripts

# لا تقم بعمل config:cache هنا! (سيتم عمل التصاريح أثناء start)
# أنشئ ملف entrypoint لتشغيل المهام عند بدء الحاوية

EXPOSE 80

ENTRYPOINT ["./docker-entrypoint.sh"]
CMD ["apache2-foreground"]
