# Stage 1: Base image with PHP and Swoole
FROM php:8.4-alpine AS gogitidze

# Install system dependencies
RUN apk add --no-cache \
    git \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    postgresql-dev \
    openssl-dev \
    linux-headers \
    $PHPIZE_DEPS

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    gd \
    pdo \
    pdo_pgsql \
    zip \
    exif \
    pdo_mysql \
    bcmath \
    pcntl \
    sockets

# Install Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# Install Swoole extension
RUN pecl install swoole \
    && docker-php-ext-enable swoole

# Install MongoDB extension
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Enable and configure opcache
RUN docker-php-ext-enable opcache \
    && echo 'opcache.memory_consumption=128' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo 'opcache.interned_strings_buffer=8' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo 'opcache.max_accelerated_files=4000' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo 'opcache.revalidate_freq=2' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo 'opcache.fast_shutdown=1' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    && echo 'opcache.enable_cli=1' >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini

# Stage 2: Build Laravel application
FROM gogitidze AS application

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY ../ /var/www/html

# Allow Composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER=1

# Install dependencies and optimize autoloader
RUN composer install --ignore-platform-reqs --no-interaction --no-scripts --no-autoloader --prefer-dist \
    && composer dump-autoload --optimize

# Set correct permissions
RUN chown -R nobody:nobody /var/www/html/storage /var/www/html/bootstrap/cache

# Expose Octane port
EXPOSE 8585

# Switch to non-root user
USER nobody

# Start Octane with Swoole
CMD ["php", "artisan", "octane:start", "--host=0.0.0.0", "--port=8585"]
