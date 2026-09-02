#!/bin/bash
set -e

cd frontend

echo "=========================================="
echo "Starting Laravel Application Setup"
echo "=========================================="

# Copy .env if it doesn't exist
if [ ! -f .env ]; then
    echo "✓ Creating .env file from example..."
    cp .env.example .env
fi

# Ensure database directory exists
mkdir -p database storage bootstrap/cache logs
chmod -R 777 database storage bootstrap/cache logs

# Create SQLite database if it doesn't exist
if [ ! -f database/database.sqlite ]; then
    echo "✓ Creating SQLite database..."
    touch database/database.sqlite
    chmod 666 database/database.sqlite
fi

# Install composer dependencies
echo "✓ Installing Composer dependencies..."
if [ ! -d vendor ]; then
    echo "  Running: composer install --no-dev --optimize-autoloader"
    composer install --no-dev --optimize-autoloader 2>&1 | tail -20
    
    if [ ! -d vendor ]; then
        echo "❌ ERROR: Composer install failed!"
        exit 1
    fi
else
    echo "✓ Vendor directory already exists"
fi

# Check if APP_KEY already exists and is valid
if grep -q "^APP_KEY=base64:" .env; then
    EXISTING_KEY=$(grep "^APP_KEY=" .env | head -c 80)
    echo "✅ APP_KEY already exists in .env"
    echo "  $EXISTING_KEY..."
else
    echo "✓ APP_KEY not found, generating new one..."
    
    # Generate a temporary APP_KEY
    TEMP_KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));")
    
    # Add APP_KEY to .env if it doesn't exist
    if ! grep -q "^APP_KEY=" .env; then
        echo "APP_KEY=$TEMP_KEY" >> .env
        echo "✅ APP_KEY added to .env"
        echo "  $TEMP_KEY"
    else
        # Replace existing empty APP_KEY
        sed -i.bak "s/^APP_KEY=.*/APP_KEY=$TEMP_KEY/" .env
        rm -f .env.bak
        echo "✅ APP_KEY updated in .env"
    fi
fi

# Verify APP_KEY one final time
if ! grep -q "^APP_KEY=base64:" .env; then
    echo "❌ ERROR: APP_KEY is still not properly set!"
    echo "Current .env APP_KEY line:"
    grep "^APP_KEY" .env || echo "No APP_KEY line found"
    exit 1
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
echo "✅ Application Setup Complete!"
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
php -S 0.0.0.0:8080 -t public/
