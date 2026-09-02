FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpq-dev \
    libsqlite3-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite pdo_pgsql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /app

# Copy application files
COPY . .

# Install dependencies in frontend folder
WORKDIR /app/frontend
RUN composer install --no-dev --optimize-autoloader --no-scripts || true

# Create database directory if it doesn't exist
RUN mkdir -p database && chmod -R 777 database

# Go back to app root
WORKDIR /app

# Expose port
EXPOSE 8080

# Run start.sh
CMD ["bash", "start.sh"]
