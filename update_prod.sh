#!/bin/bash
composer update --no-dev -w -o
npm run prod
php artisan optimize:clear
php artisan storage:link
php artisan cache:clear
php artisan route:clear
php artisan route:cache
