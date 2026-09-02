#!/bin/bash
set -e

cd frontend

# Copy .env if it doesn't exist
if [ ! -f .env ]; then
    echo "Creating .env file..."
    cp .env.example .env
fi

# Generate APP_KEY if not set
if ! grep -q "APP_KEY=base64:" .env; then
    echo "Generating APP_KEY..."
    php artisan key:generate
fi

# Ensure database directory exists
mkdir -p database
chmod -R 777 database storage

# Create SQLite database if it doesn't exist
touch database/database.sqlite
chmod 666 database/database.sqlite

# Run migrations
echo "Running migrations..."
php artisan migrate --force --no-interaction || echo "Migration completed or failed gracefully"

# Start PHP development server
echo "Starting application on 0.0.0.0:8080..."
php -S 0.0.0.0:8080 -t public/
