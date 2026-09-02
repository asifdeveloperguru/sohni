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
$hasAppKey = false;

foreach ($lines as $line) {
    if (strpos($line, 'APP_ENV=') === 0) {
        $output[] = 'APP_ENV=local';
    } elseif (strpos($line, 'SESSION_ENCRYPT=') === 0) {
        $output[] = 'SESSION_ENCRYPT=false';
    } elseif (strpos($line, 'APP_DEBUG=') === 0) {
        $output[] = 'APP_DEBUG=true';
    } elseif (strpos($line, 'APP_KEY=') === 0) {
        $output[] = 'APP_KEY=';
        $hasAppKey = true;
    } else {
        $output[] = $line;
    }
}

if (!$hasAppKey) {
    $output[] = 'APP_KEY=';
}

file_put_contents($envFile, implode("\n", $output) . "\n");
echo "✓ .env file configured\n";
EOF

echo "✓ Configuring .env file..."
php /tmp/fix_env.php

echo "✓ Ensuring .env file permissions..."
chmod u+rw .env 2>/dev/null || true
if [ ! -r .env ] || [ ! -w .env ]; then
    echo "❌ ERROR: .env must be readable and writable."
    ls -l .env
    exit 1
fi

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

# Generate APP_KEY - FORCE it to overwrite
echo "✓ Generating APP_KEY..."
set +e
KEYGEN_OUTPUT=$(php artisan key:generate --force --no-interaction 2>&1)
KEYGEN_STATUS=$?
set -e

if [ $KEYGEN_STATUS -ne 0 ]; then
    echo "⚠️  key:generate failed with exit code $KEYGEN_STATUS"
    echo "$KEYGEN_OUTPUT"
fi

if ! grep -q "^APP_KEY=base64:" .env; then
    echo "⚠️  APP_KEY was not written by key:generate, attempting fallback..."
    set +e
    GENERATED_KEY=$(php artisan key:generate --show --no-interaction 2>&1)
    SHOW_STATUS=$?
    set -e

    if [ $SHOW_STATUS -ne 0 ]; then
        echo "❌ ERROR: Unable to generate fallback APP_KEY."
        echo "$GENERATED_KEY"
        exit 1
    fi

    if [[ "$GENERATED_KEY" != base64:* ]]; then
        echo "❌ ERROR: Generated fallback APP_KEY is invalid."
        echo "Output: $GENERATED_KEY"
        exit 1
    fi

    if grep -q "^APP_KEY=" .env; then
        sed -i "s|^APP_KEY=.*|APP_KEY=$GENERATED_KEY|" .env
    else
        echo "APP_KEY=$GENERATED_KEY" >> .env
    fi
fi

# Verify APP_KEY was set in .env file
if grep -q "^APP_KEY=base64:" .env; then
    KEY=$(grep "^APP_KEY=" .env | head -c 80)
    echo "✅ APP_KEY generated successfully!"
    echo "  $KEY..."
else
    echo "❌ ERROR: APP_KEY was not written to .env file!"
    echo "Checking .env file for APP_KEY:"
    grep "^APP_KEY" .env || echo "No APP_KEY line found in .env"
    echo ""
    echo "Tail of .env file:"
    tail -5 .env
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
