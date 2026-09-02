FROM php:8.2-cli

# Install system dependencies
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

# Copy entire application
COPY . .

# Change to frontend and install dependencies
WORKDIR /app/frontend

# Install PHP dependencies with retry logic
RUN composer install --no-dev --optimize-autoloader 2>&1 || \
    (echo "First attempt failed, retrying..." && sleep 5 && composer install --no-dev --optimize-autoloader)

# Create database directory with permissions
RUN mkdir -p database && chmod -R 777 database storage

# Go back to root
WORKDIR /app

# Expose port
EXPOSE 8080

# Run start.sh
CMD ["bash", "start.sh"]
