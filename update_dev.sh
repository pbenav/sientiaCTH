#!/bin/bash

composer install
composer update
npm install
npm run dev
sudo chown dinamizador:www-data * -R
sudo find ./ -type d -exec chmod 775 {} \;
sudo find ./ -type f -exec chmod 664 {} \;
chmod ug+x node_modules/laravel-mix/bin/*
chmod ug+x *.sh
php artisan migrate --seed
php artisan optimize:clear
php artisan storeage:link
php artisan cache:clear
php artisan config:clear
php artisan config:cache
php artisan route:clear
php artisan route:cache
php artisan view:clear
