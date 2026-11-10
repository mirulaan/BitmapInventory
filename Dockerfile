# ==============================================================================
# Stage 1: Build frontend assets with Vite
# ==============================================================================
FROM node:22-alpine AS assets-builder

WORKDIR /app

# Install npm dependencies
COPY package*.json ./
RUN npm ci --prefer-offline --no-audit

# Copy asset sources and build
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

# ==============================================================================
# Stage 2: Install Composer PHP dependencies
# ==============================================================================
FROM composer:2 AS composer-builder

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# ==============================================================================
# Stage 3: Production Runtime
# ==============================================================================
FROM php:8.4-fpm-alpine AS production

# Install system dependencies
RUN apk add --no-cache \
    bash \
    curl \
    nginx \
    supervisor \
    shadow \
    sqlite \
    su-exec \
    tzdata

# Install PHP extensions using docker-php-extension-installer
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
    bcmath \
    calendar \
    curl \
    exif \
    gd \
    intl \
    mbstring \
    opcache \
    pcntl \
    pdo_mysql \
    pdo_pgsql \
    pdo_sqlite \
    redis \
    zip

# Copy configuration files
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/99-opcache.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/http.d/default.conf
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Setup Entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

WORKDIR /var/www/html

# Copy application files
COPY . .

# Copy compiled frontend assets from assets-builder
COPY --from=assets-builder /app/public/build /var/www/html/public/build

# Copy vendor dependencies from composer-builder
COPY --from=composer-builder /app/vendor /var/www/html/vendor

# Create necessary directories and set proper ownership/permissions
RUN mkdir -p /var/www/html/storage/framework/cache/data \
             /var/www/html/storage/framework/sessions \
             /var/www/html/storage/framework/views \
             /var/www/html/storage/logs \
             /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expose default HTTP port (80)
EXPOSE 80

# Health check using Laravel's /up endpoint
HEALTHCHECK --interval=30s --timeout=5s --start-period=10s --retries=3 \
    CMD curl -f http://127.0.0.1/up || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
