# ==========================================
# STAGE 1: Build Frontend Assets (Vite)
# ==========================================
FROM node:22-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# ==========================================
# STAGE 2: Install Composer Dependencies
# ==========================================
FROM composer:2 AS composer
WORKDIR /app
COPY composer.json composer.lock* ./
COPY app ./app
COPY bootstrap ./bootstrap
COPY database ./database
COPY routes ./routes
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts \
    --ignore-platform-reqs

# ==========================================
# STAGE 3: Final Production Image (PHP 8.4 + Nginx)
# ==========================================
FROM php:8.4-fpm-alpine

# Install Nginx, Supervisor, dan tools dasar
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    bash

# Pasang helper resmi untuk instalasi ekstensi PHP di container Docker
COPY --from=mlocati/docker-php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/

# Install ekstensi PHP yang dibutuhkan HadirForm
# (gd & zip untuk PDF / Excel, pdo untuk MySQL & Postgres, opcache untuk performa)
RUN install-php-extensions \
    pdo_mysql \
    pdo_pgsql \
    pdo_sqlite \
    gd \
    zip \
    bcmath \
    intl \
    opcache \
    pcntl

# Optimasi PHP untuk produksi
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 12M/g' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/post_max_size = 8M/post_max_size = 16M/g' "$PHP_INI_DIR/php.ini" \
    && sed -i 's/memory_limit = 128M/memory_limit = 256M/g' "$PHP_INI_DIR/php.ini"

WORKDIR /var/www/html

# Salin source code aplikasi
COPY . .

# Salin vendor dari Stage 2 & compiled assets dari Stage 1
COPY --from=composer /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

# Salin binary composer jika diperlukan runtime command di container
COPY --from=composer /usr/bin/composer /usr/bin/composer

# Hapus konfigurasi nginx bawaan Alpine agar tidak konflik, lalu salin milik kita
RUN rm -f /etc/nginx/http.d/default.conf

# Salin konfigurasi Nginx, Supervisor, dan Entrypoint
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Atur hak akses folder storage dan cache
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
