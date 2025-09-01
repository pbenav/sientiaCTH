## About CTH

CTH is a web application based on Laravel and is intended to track time control in bussines.

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](webmaster@zafarraya.net). All security vulnerabilities will be promptly addressed.

## License

The CTH & Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

### **Instalación**

Para poner en funcionamiento esta aplicación, debes tener instalados **PHP** (`^8.1`), **Composer**, **Node** y **npm**.

1.  **Actualizar dependencias de Composer**:

    ```bash
    composer update
    ```

2.  **Actualizar dependencias de Node**:

    ```bash
    npm install && npm run dev
    ```

3.  **Configurar la base de datos**:
    Copia el archivo `.env.example` a `.env` y modifica las opciones de la base de datos según sea necesario.

4.  **Generar la clave de la aplicación**:

    ```bash
    php artisan key:generate
    ```

5.  **Correr las migraciones**:

    ```bash
    php artisan migrate
    ```

6.  **Opcional: Incluir datos de prueba**:
    Si necesitas incluir datos de prueba, usa este comando:

    ```bash
    php artisan migrate:refresh --seed
    ```

7.  **Ejecutar el servidor de desarrollo**:

    ```bash
    php artisan serve
    ```

    Puedes ver la aplicación en funcionamiento en la dirección y puertos configurados en el archivo `.env`, por defecto: **http://localhost:8000**.

-----

### **Instalación con Apache en Debian**

1.  **Añadir extensiones de PHP**:

    ```bash
    sudo apt install php-curl php-xml php-gd php-zip
    ```

2.  **Asegurar que `mod_rewrite` esté activo**:

    ```bash
    sudo a2enmod rewrite
    ```

3.  **Añadir alias de Apache**:
    Abre el archivo de configuración `000-default.conf` con `nano`:

    ```bash
    sudo nano /etc/apache2/sites-available/000-default.conf
    ```

    Añade el siguiente bloque de alias y directorio dentro de `<VirtualHost *:80>`:

    ```
    Alias /cth /var/www/html/cth/public

    <Directory /var/www/html/cth/public>
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
    ```

4.  **Actualizar las rutas de Laravel**:

    ```bash
    php artisan route:clear
    ```

5.  **Configurar permisos de la carpeta**:
    Ejecuta estos comandos desde la carpeta de tu aplicación para corregir los permisos:

    ```bash
    chown user:www-data * -R
    find ./ -type d -exec chmod 775 {} \;
    find ./ -type f -exec chmod 664 {} \;
    ```

6.  **Optimizar para producción**:
    Finalmente, puedes usar el script `update_prod.sh` para optimizar la configuración para un entorno de producción.
