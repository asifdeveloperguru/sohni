FROM php:8.2-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    wget \
    zip \
    unzip \
    libpq-dev \
    libsqlite3-dev \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_sqlite \
    pdo_pgsql \
    zip \
    mbstring \
    curl

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /app

# Copy entire application
COPY . .

# Install PHP dependencies in frontend directory
WORKDIR /app/frontend
RUN composer install --no-dev --no-interaction --prefer-dist

# Return to app root
WORKDIR /app

# Make start.sh executable
RUN chmod +x start.sh

# Expose port
EXPOSE 8080

# Run start.sh
CMD ["bash", "start.sh"]
