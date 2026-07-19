FROM php:8.3-cli

# Install system dependencies and PHP extensions Laravel commonly needs
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    default-mysql-client \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
    && rm -rf /var/lib/apt/lists/*

# Install Composer (copied from the official Composer image)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy composer files first for better layer caching
COPY composer.json composer.lock* ./

# Allow Laravel 10.x versions flagged by Composer's security-advisory blocker
RUN composer config audit.block-insecure false || true

# Install PHP dependencies (no scripts yet — artisan isn't copied in until the next step)
RUN composer install --no-scripts --no-interaction --no-autoloader --prefer-dist

# Now copy the rest of the application code
COPY . .

COPY . .

# Ensure storage and cache directories exist and are writable
RUN mkdir -p storage/framework/sessions \
             storage/framework/views \
             storage/framework/cache/data \
             storage/logs \
             bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache
# Finish Composer setup now that directories exist
RUN composer dump-autoload --optimize \
    && php artisan config:clear \
    && php artisan cache:clear
EXPOSE 8080

# Use the platform-provided $PORT if set, otherwise default to 8080
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8080}

