# Guía de Despliegue

Esta guía describe cómo desplegar la aplicación en servidores Apache o Nginx.

## Requisitos del servidor
- PHP 8.4
- Composer
- Node.js & npm
- Chrome/Chromium (para Browsershot)

## Instalación
1. Clonar el repositorio.
2. Ejecutar `composer install`.
3. Copiar `.env.example` a `.env` y configurar la base de datos.
4. Ejecutar `php artisan migrate`.
5. Compilar assets con `npm run dev` o `npm run prod`.

## Configuración de Apache
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

## Configuración de Nginx
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

*(Este es un borrador que será completado.)*
