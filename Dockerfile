# ==========================================
# ETAPA 1: Compilar Frontend (Vite, CSS, JS)
# ==========================================
FROM node:20-alpine AS frontend
WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .
RUN npm run build

# ==========================================
# ETAPA 2: Entorno PHP 8.2 + Apache (Producción)
# ==========================================
FROM php:8.2-apache
WORKDIR /var/www/html

# Instalar dependencias del sistema y extensiones requeridas para PostgreSQL y Laravel
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpq-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libicu-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_pgsql \
        pgsql \
        zip \
        bcmath \
        gd \
        intl \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite

# Instalar Composer desde la imagen oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiar el código del proyecto
COPY . .

# Copiar los assets compilados de Vite desde la Etapa 1
COPY --from=frontend /app/public/build ./public/build

# Instalar dependencias PHP optimizadas para producción (sin dev)
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Configurar Apache para apuntar a la carpeta /public de Laravel
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Permisos para storage y cache de Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Exponer el puerto por defecto
EXPOSE 80

# Arranque dinámico con soporte para el puerto de Render ($PORT) y caché de Laravel
CMD bash -c "\
    PORT=\${PORT:-80}; \
    sed -i \"s/Listen 80/Listen \$PORT/g\" /etc/apache2/ports.conf; \
    sed -i \"s/<VirtualHost \*:80>/<VirtualHost *:\$PORT>/g\" /etc/apache2/sites-available/000-default.conf; \
    php artisan storage:link || true; \
    php artisan optimize:clear; \
    php artisan config:cache; \
    php artisan route:cache; \
    php artisan view:cache; \
    apache2-foreground"
