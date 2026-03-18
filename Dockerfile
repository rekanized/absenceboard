FROM php:8.3-cli-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libsqlite3-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo_sqlite mbstring dom xml \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

COPY . .

RUN cp .env.example .env \
    && touch database/database.sqlite \
    && php artisan key:generate --no-interaction \
    && php artisan migrate:fresh --seed --force --no-interaction \
    && php artisan storage:link \
    && php artisan optimize:clear

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]