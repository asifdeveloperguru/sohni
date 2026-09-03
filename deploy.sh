#!/bin/bash

# Laravel Cloud Deployment Hook
# This script runs automatically after deployment

set -e

echo "=========================================="
echo "🚀 Laravel Cloud Post-Deployment Setup"
echo "=========================================="

# Get to the application directory
APP_DIR="/var/www/html"
cd "$APP_DIR"

echo "📍 Working directory: $(pwd)"

# Check if in a subdirectory (like /frontend)
if [ -f "frontend/composer.json" ]; then
    echo "📂 Detected frontend subdirectory..."
    cd frontend
    LARAVEL_DIR="frontend"
else
    LARAVEL_DIR="."
fi

echo "📂 Laravel directory: $LARAVEL_DIR"

# Create required Laravel directories FIRST
echo "📁 Creating required directories..."
mkdir -p storage/logs
mkdir -p storage/framework/cache
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p bootstrap/cache
mkdir -p database

# Create SQLite database file if it doesn't exist
echo "🗄️  Creating SQLite database file..."
if [ ! -f "database/database.sqlite" ]; then
    touch database/database.sqlite
    chmod 666 database/database.sqlite
    echo "✅ Database file created: database/database.sqlite"
else
    echo "✅ Database file already exists"
fi

# Fix permissions
echo "🔐 Setting file permissions..."
chmod -R 775 storage bootstrap/cache database || true
chmod -R 755 public || true
chmod 666 database/database.sqlite || true

# Install composer dependencies
if [ -f "composer.json" ]; then
    echo "📦 Installing Composer dependencies..."
    if ! composer install --no-dev --optimize-autoloader 2>&1 | tail -30; then
        echo "⚠️  Composer install had warnings, but continuing..."
    fi
fi

# Generate or verify APP_KEY
echo "🔑 Checking APP_KEY..."
if grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    echo "✅ APP_KEY already set"
else
    echo "⚙️  Generating APP_KEY..."
    php artisan key:generate --force --no-interaction 2>&1 || echo "⚠️  Key generation completed"
fi

# Run database migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force --no-interaction 2>&1 || echo "⚠️  Migrations completed"

# Cache configuration for faster startup
echo "⚡ Building config cache..."
php artisan config:cache 2>&1 || true

# Cache routes
echo "🛣️  Caching routes..."
php artisan route:cache 2>&1 || true

# Cache views
echo "👁️  Caching views..."
php artisan view:cache 2>&1 || true

# Clear any old cache (without arguments to avoid error)
echo "🧹 Clearing old caches..."
php artisan cache:clear --no-interaction 2>&1 || true

# Set production mode
echo "🎯 Setting APP_ENV to production..."
sed -i 's/APP_ENV=.*/APP_ENV=production/' .env 2>/dev/null || true
sed -i 's/APP_DEBUG=.*/APP_DEBUG=false/' .env 2>/dev/null || true

echo "=========================================="
echo "✅ Laravel Cloud Setup Complete!"
echo "=========================================="
echo "Database file: $(pwd)/database/database.sqlite"
echo "Your application is ready to serve requests."
echo "=========================================="
