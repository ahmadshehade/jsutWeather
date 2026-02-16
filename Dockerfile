# استخدم صورة PHP مع Nginx
FROM richarvey/nginx-php-fpm:latest

COPY . .

# إعدادات الصورة
ENV SKIP_COMPOSER 0
ENV WEBROOT /var/www/html/public
ENV PHP_ERRORS_STDERR 1
ENV RUN_SCRIPTS 1
ENV REAL_IP_HEADER 1

# إعدادات Laravel
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr

ENV COMPOSER_ALLOW_SUPERUSER 1

# تشغيل Composer يدويًا أثناء البناء
RUN composer install --no-dev --optimize-autoloader

CMD ["/start.sh"]
