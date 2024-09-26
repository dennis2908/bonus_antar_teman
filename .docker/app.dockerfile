FROM cyberduck/php-fpm-laravel:8.0

WORKDIR /var/ww

ADD . .

COPY --from=composer:2.3.8 /usr/bin/composer /usr/bin/composer

RUN composer install --no-interaction --no-autoloader

COPY . /var/www

RUN composer dump-autoload --optimize

RUN chown -R www-data:www-data \
        /var/www/storage \
        /var/www/bootstrap/cache

RUN cp .env.example .env

RUN php artisan key:generate
