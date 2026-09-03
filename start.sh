#!/bin/bash
set -e

ROOT_DIR="$(cd "$(dirname "$0")" && pwd)"
APP_DIR="$ROOT_DIR/frontend"

cd "$APP_DIR"

php scripts/prepare-cloud-deployment.php

if [ ! -d vendor ]; then
    composer install --no-dev --optimize-autoloader --no-interaction
fi

php artisan migrate --force --no-interaction
php artisan storage:link || true
php artisan optimize:clear
php artisan config:cache
php artisan view:cache

php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
