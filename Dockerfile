FROM php:8.4-cli

# Install system dependencies and SQLite / MySQL PDO extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libsqlite3-dev \
    sqlite3 \
    && docker-php-ext-install pdo pdo_sqlite pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Prepare environment and SQLite database seeded with bincom_test.sql
RUN cp -n .env.example .env || true \
    && touch database/database.sqlite \
    && php artisan key:generate --force \
    && php artisan migrate:fresh --seed --force

EXPOSE 8000

CMD sh -c "php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"

