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
fi

# Set environment to local for setup (will be changed to production later if needed)
echo "✓ Setting environment for setup..."
sed -i 's/APP_ENV=.*/APP_ENV=local/' .env
sed -i 's/SESSION_ENCRYPT=.*/SESSION_ENCRYPT=false/' .env
sed -i 's/APP_DEBUG=.*/APP_DEBUG=true/' .env

# Remove any invalid APP_KEY
echo "✓ Clearing any invalid APP_KEY..."
sed -i '/^APP_KEY=/d' .env

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
    
    if [ ! -d vendor ]; then
        echo "❌ ERROR: Composer install failed!"
        exit 1
    fi
else
    echo "✓ Vendor directory already exists"
fi

# Generate APP_KEY
echo "✓ Generating APP_KEY..."
php artisan key:generate --no-interaction 2>&1

# Verify APP_KEY was set
if grep -q "APP_KEY=base64:" .env; then
    echo "✓ APP_KEY generated successfully: $(grep APP_KEY .env | cut -d= -f1-2 | head -c 50)..."
else
    echo "❌ ERROR: APP_KEY was not generated!"
    grep APP_KEY .env || echo "No APP_KEY found in .env"
    exit 1
fi

# Run migrations
echo "✓ Running database migrations..."
php artisan migrate --force --no-interaction 2>&1 || true

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

# Monitor the log file
if [ ! -f storage/logs/laravel.log ]; then
    touch storage/logs/laravel.log
    chmod 666 storage/logs/laravel.log
fi

# Tail logs in background
tail -f storage/logs/laravel.log 2>/dev/null &
TAIL_PID=$!

# Start PHP server
php -S 0.0.0.0:8080 -t public/ 2>&1 &
PHP_PID=$!

# Wait for PHP
wait $PHP_PID
