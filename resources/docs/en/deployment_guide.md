# Deployment Guide

This guide explains how to deploy the application on Apache or Nginx servers.

## Server Requirements
- PHP 8.4
- Composer
- Node.js & npm
- Chrome/Chromium (for Browsershot)

## Installation Steps
1. Clone the repository.
2. Run `composer install`.
3. Copy `.env.example` to `.env` and configure the database.
4. Run `php artisan migrate`.
5. Build assets with `npm run dev` or `npm run prod`.

## Apache Configuration
```apacheconf
<VirtualHost *:80>
    ServerName example.com
    DocumentRoot /var/www/cth/public
    <Directory /var/www/cth/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## Nginx Configuration
```nginx
server {
    listen 80;
    server_name example.com;
    root /var/www/cth/public;
    index index.php;
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

*(Draft to be expanded.)*
