web: vendor/bin/heroku-php-apache2 public/
release: php artisan migrate --force
worker: php artisan queue:work --sleep=3 --tries=3
