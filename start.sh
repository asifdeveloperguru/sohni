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

# Create a PHP script to properly modify .env file
cat > /tmp/fix_env.php << 'EOF'
<?php
$envFile = '.env';
$lines = file($envFile, FILE_IGNORE_NEW_LINES);
$output = [];

foreach ($lines as $line) {
    if (strpos($line, 'APP_ENV=') === 0) {
        $output[] = 'APP_ENV=local';
    } elseif (strpos($line, 'SESSION_ENCRYPT=') === 0) {
        $output[] = 'SESSION_ENCRYPT=false';
    } elseif (strpos($line, 'APP_DEBUG=') === 0) {
        $output[] = 'APP_DEBUG=true';
    } elseif (strpos($line, 'APP_KEY=') === 0) {
        // Skip - will be regenerated
        continue;
    } else {
        $output[] = $line;
    }
}

file_put_contents($envFile, implode("\n", $output) . "\n");
echo "✓ .env file configured\n";
EOF

echo "✓ Configuring .env file..."
php /tmp/fix_env.php

# CRITICAL: Unset APP_KEY from environment
echo "✓ Clearing APP_KEY from environment..."
unset APP_KEY
unset app_key

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

# Generate APP_KEY
echo "✓ Generating APP_KEY..."
php artisan key:generate --force --no-interaction 2>&1

# Verify APP_KEY was set in .env file
if grep -q "^APP_KEY=base64:" .env; then
    KEY=$(grep "^APP_KEY=" .env | head -c 80)
    echo "✅ APP_KEY generated successfully!"
    echo "  $KEY..."
else
    echo "❌ ERROR: APP_KEY was not written to .env file!"
    exit 1
fi

# Run migrations
echo "✓ Running database migrations..."
php artisan migrate --force --no-interaction 2>&1 || true

# CRITICAL: Build config cache with the new APP_KEY
echo "✓ Building config cache with new APP_KEY..."
php artisan config:cache 2>&1

# Clear all other caches
echo "✓ Clearing caches..."
php artisan cache:clear 2>&1 || true
php artisan route:clear 2>&1 || true
php artisan view:clear 2>&1 || true

# Set to production mode
echo "✓ Setting APP_ENV to production..."
sed -i 's/APP_ENV=.*/APP_ENV=production/' .env

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
