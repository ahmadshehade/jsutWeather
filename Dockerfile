FROM serversideup/php:8.3-fpm-nginx


COPY . /var/www/html


USER root
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache


USER www-data


RUN chmod +x /var/www/html/scripts/00-laravel-deploy.sh


RUN /var/www/html/scripts/00-laravel-deploy.sh


ENV PORT=8080
