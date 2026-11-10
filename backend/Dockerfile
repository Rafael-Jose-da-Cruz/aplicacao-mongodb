
FROM php:8.5-cli

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr

# Dependências e extensão MongoDB
RUN apt-get update && apt-get install -y --no-install-recommends \
    git \
    unzip \
    libssl-dev \
    $PHPIZE_DEPS \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && php --ri mongodb \
    && apt-get purge -y --auto-remove $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Dependências PHP
COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --no-scripts \
    --no-autoloader

# Código Laravel
COPY . .

# Preparação do Laravel
RUN mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    storage/app/public \
    bootstrap/cache \
    && composer dump-autoload --optimize --no-dev \
    && chown -R www-data:www-data storage bootstrap/cache

# Porta do Render
EXPOSE 10000

# Executa como usuário não privilegiado
USER www-data

# Inicialização
CMD ["sh", "-c", "exec php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]
