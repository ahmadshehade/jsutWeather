FROM php:8.2-fpm

WORKDIR /var/www/html

# تثبيت dependencies (مع دعم PostgreSQL)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    unzip \
    git \
    curl \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip

# نسخ المشروع
COPY . .

# تثبيت Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# إعداد Laravel: نضمن أن يتم بناء الإعدادات من متغيرات البيئة فقط
# لا تنسخ .env.example ولا تولد key هنا، لأنها ستأتي من البيئة
RUN php artisan config:cache || true
RUN php artisan route:cache || true
RUN php artisan view:cache || true

# صلاحيات التخزين
RUN chmod -R 777 storage bootstrap/cache

# expose port
EXPOSE 8000

CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
