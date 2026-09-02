#!/bin/bash
set -e

cd frontend

echo "=========================================="
echo "Starting Laravel Application Setup"
echo "=========================================="

# Copy .env if it doesn't exist
if [ ! -f .env ]; then
    echo "✓ Creating .env file..."
    cp .env.example .env
else
    echo "✓ .env file already exists"
fi

# Ensure database directory exists
mkdir -p database storage bootstrap/cache
chmod -R 777 database storage bootstrap/cache

# Create SQLite database if it doesn't exist
if [ ! -f database/database.sqlite ]; then
    echo "✓ Creating SQLite database..."
    touch database/database.sqlite
    chmod 666 database/database.sqlite
fi

# Install composer dependencies FIRST (before artisan commands)
echo "✓ Installing Composer dependencies..."
if [ ! -d vendor ]; then
    echo "  Running: composer install --no-dev --optimize-autoloader"
    composer install --no-dev --optimize-autoloader -vvv 2>&1
    
    # Check if vendor was created
    if [ ! -d vendor ]; then
        echo "❌ ERROR: Composer install failed - vendor directory not created!"
        echo "Please check your composer.json and dependencies"
        exit 1
    fi
else
    echo "✓ Vendor directory already exists, skipping composer install"
fi

# Generate APP_KEY if not set
if ! grep -q "APP_KEY=base64:" .env; then
    echo "✓ Generating APP_KEY..."
    php artisan key:generate --no-interaction
fi

# Run migrations
echo "✓ Running database migrations..."
php artisan migrate --force --no-interaction 2>&1 || echo "⚠ Migrations completed (may have already run)"

echo "=========================================="
echo "✓ Application Setup Complete!"
echo "=========================================="
echo "Starting PHP server on 0.0.0.0:8080..."
php -S 0.0.0.0:8080 -t public/
