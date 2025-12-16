# syntax=docker/dockerfile:1

# Build frontend assets with Vite
FROM node:20-alpine AS node_builder
WORKDIR /app

COPY package*.json ./
RUN npm ci

# Copy only what Vite needs
COPY resources ./resources
COPY vite.config.js ./vite.config.js
COPY tailwind.config.js ./tailwind.config.js
COPY postcss.config.js ./postcss.config.js
COPY public ./public

# Build assets to public/build
RUN npm run build

# PHP-FPM with required extensions
FROM php:8.2-fpm AS app
WORKDIR /var/www/html

# System dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpng-dev \
    libonig-dev \
    libzip-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libxml2-dev \
    libicu-dev \
    netcat-openbsd \
    bash \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd zip xml intl opcache \
    && rm -rf /var/lib/apt/lists/*

# Enable recommended PHP production settings (opcache)
RUN set -eux; \
    echo "opcache.enable=1"            > /usr/local/etc/php/conf.d/opcache.ini; \
    echo "opcache.revalidate_freq=0" >> /usr/local/etc/php/conf.d/opcache.ini; \
    echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini; \
    echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini; \
    echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini; \
    echo "opcache.interned_strings_buffer=16" >> /usr/local/etc/php/conf.d/opcache.ini;

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy application source
COPY . .

# Copy built assets from node builder
COPY --from=node_builder /app/public/build ./public/build

# Install PHP dependencies
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction

# Ensure correct permissions for storage and cache
RUN chown -R www-data:www-data storage bootstrap/cache

# Entrypoint to warm caches and start php-fpm
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Nginx stage to serve the Laravel app
FROM nginx:stable-alpine AS web
COPY nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=app /var/www/html /var/www/html
EXPOSE 80
CMD ["nginx", "-g", "daemon off;"]