FROM php:8.3-cli

# Dependencias del sistema y extensiones de PHP necesarias para Laravel con PostgreSQL
RUN apt-get update && apt-get install -y --no-install-recommends \
        git unzip libzip-dev libpq-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql mbstring zip bcmath gd \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Al arrancar: crea las tablas (si no existen) y levanta el servidor en el puerto que asigna Render
CMD sh -c "php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"
