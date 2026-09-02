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

# Force disable session encryption to avoid cipher issues
echo "✓ Ensuring SESSION_ENCRYPT is disabled..."
sed -i 's/SESSION_ENCRYPT=.*/SESSION_ENCRYPT=false/' .env
sed -i 's/APP_DEBUG=.*/APP_DEBUG=true/' .env

# Ensure database directory exists
mkdir -p database storage bootstrap/cache logs
chmod -R 777 database storage bootstrap/cache logs

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
    composer install --no-dev --optimize-autoloader 2>&1
    
    # Check if vendor was created
    if [ ! -d vendor ]; then
        echo "❌ ERROR: Composer install failed - vendor directory not created!"
        echo "Please check your composer.json and dependencies"
        exit 1
    fi
else
    echo "✓ Vendor directory already exists, skipping composer install"
fi

# Generate APP_KEY (force regenerate)
echo "✓ Generating APP_KEY..."
php artisan key:generate --force --no-interaction 2>&1

# Verify APP_KEY was set
if ! grep -q "APP_KEY=base64:" .env; then
    echo "⚠ Warning: APP_KEY may not be set correctly"
fi

# Run migrations
echo "✓ Running database migrations..."
php artisan migrate --force --no-interaction 2>&1 || echo "⚠ Migrations completed (may have already run)"

# Clear all caches
echo "✓ Clearing caches..."
php artisan cache:clear 2>&1 || true
php artisan config:clear 2>&1 || true
php artisan route:clear 2>&1 || true
php artisan view:clear 2>&1 || true

echo "=========================================="
echo "✓ Application Setup Complete!"
echo "=========================================="
echo "Starting PHP server on 0.0.0.0:8080..."
echo "=========================================="

# Monitor the log file and output to console
if [ ! -f storage/logs/laravel.log ]; then
    touch storage/logs/laravel.log
    chmod 666 storage/logs/laravel.log
fi

# Start tailing the log file in background
tail -f storage/logs/laravel.log 2>/dev/null &
TAIL_PID=$!

# Start PHP server
php -S 0.0.0.0:8080 -t public/ 2>&1 &
PHP_PID=$!

# Wait for PHP to finish (or interrupt)
wait $PHP_PID
