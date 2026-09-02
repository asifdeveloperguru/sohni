#!/bin/bash
cd frontend
composer install --no-dev
php artisan migrate --force
php -S 0.0.0.0:8080 -t public/