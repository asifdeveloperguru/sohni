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

# Generate APP_KEY - now in local environment
echo "✓ Generating APP_KEY..."
php artisan key:generate --force --no-interaction 2>&1

# Verify APP_KEY was set
if grep -q "^APP_KEY=base64:" .env; then
    KEY=$(grep "^APP_KEY=" .env)
    echo "✓ APP_KEY generated successfully!"
    echo "  $KEY" | head -c 80
    echo "..."
else
    echo "❌ ERROR: APP_KEY was not generated!"
    echo "Current .env APP_KEY line:"
    grep "^APP_KEY=" .env || echo "No APP_KEY found"
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
php -S 0.0.0.0:8080 -t public/
