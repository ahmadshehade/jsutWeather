FROM serversideup/php:8.3-fpm-nginx

COPY . /var/www/html

USER root
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

USER www-data

RUN composer install --no-dev --working-dir=/var/www/html


ENV PORT=8080
ENV AUTORUN_MIGRATIONS=true
