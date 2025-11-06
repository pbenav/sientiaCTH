# CTH Installation Guide

This guide will help you install and configure the CTH (Time and Schedule Control) application.

## System Requirements

- **PHP** (`^8.1`)
- **Composer**
- **Node.js** and **npm**
- **MySQL** or **MariaDB**
- **Apache** or **Nginx** web server

## Quick Installation

### 1. Update Composer Dependencies

```bash
composer update
```

### 2. Update Node Dependencies

```bash
npm install && npm run dev
```

### 3. Configure Database

Copy the `.env.example` file to `.env` and modify the database options as needed:

```bash
cp .env.example .env
```

Edit the database configuration in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cth_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Run Migrations

```bash
php artisan migrate
```

### 6. Optional: Include Test Data

If you need to include test data, use this command:

```bash
php artisan migrate:refresh --seed
```

### 7. Run Development Server

```bash
php artisan serve
```

You can view the application at the address and ports configured in the `.env` file, by default: **http://localhost:8000**.

---

## Production Installation with Apache on Debian

### Prerequisites

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Apache
sudo apt install apache2 -y

# Install PHP 8.1 and extensions
sudo apt install php8.1 php8.1-fpm php8.1-mysql php8.1-xml php8.1-curl php8.1-mbstring php8.1-zip php8.1-gd -y

# Install MySQL
sudo apt install mysql-server -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs -y
```

### Apache Configuration

Create a virtual host configuration:

```bash
sudo nano /etc/apache2/sites-available/cth.conf
```

Add the following configuration:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/cth/public
    
    <Directory /var/www/cth/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/cth_error.log
    CustomLog ${APACHE_LOG_DIR}/cth_access.log combined
</VirtualHost>
```

Enable the site and required modules:

```bash
sudo a2ensite cth.conf
sudo a2enmod rewrite
sudo systemctl restart apache2
```

### Application Deployment

```bash
# Clone the repository
cd /var/www
sudo git clone https://github.com/your-repo/cth.git
cd cth

# Set permissions
sudo chown -R www-data:www-data /var/www/cth
sudo chmod -R 755 /var/www/cth
sudo chmod -R 775 /var/www/cth/storage
sudo chmod -R 775 /var/www/cth/bootstrap/cache

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Configure environment
cp .env.example .env
php artisan key:generate

# Run migrations
php artisan migrate --force

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Database Setup

```bash
# Access MySQL
sudo mysql -u root -p

# Create database and user
CREATE DATABASE cth_production;
CREATE USER 'cth_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON cth_production.* TO 'cth_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Update your `.env` file with the database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cth_production
DB_USERNAME=cth_user
DB_PASSWORD=strong_password
```

### SSL Configuration (Recommended)

Install Certbot for Let's Encrypt SSL:

```bash
sudo apt install certbot python3-certbot-apache -y
sudo certbot --apache -d your-domain.com
```

### Scheduled Tasks

Add to crontab for automated maintenance:

```bash
sudo crontab -e
```

Add the following lines:

```bash
# CTH Application Tasks
0 2 * * * cd /var/www/cth && php artisan events:autoclose >> /var/log/cth/autoclose.log 2>&1
0 3 * * 0 cd /var/www/cth && php artisan events:verify-and-fix --dry-run >> /var/log/cth/weekly-check.log 2>&1

# Laravel Queue (if using queues)
* * * * * cd /var/www/cth && php artisan queue:work --stop-when-empty >> /var/log/cth/queue.log 2>&1
```

Create log directory:

```bash
sudo mkdir -p /var/log/cth
sudo chown www-data:www-data /var/log/cth
```

## Post-Installation

### 1. Create Admin User

Access the application and register the first user, or use Tinker:

```bash
php artisan tinker
```

```php
$user = App\Models\User::create([
    'name' => 'Administrator',
    'email' => 'admin@example.com',
    'password' => Hash::make('secure_password')
]);
```

### 2. Configure Teams and Work Centers

1. Log in to the application
2. Create your first team
3. Configure work centers and schedules
4. Set up event types
5. Configure holidays

### 3. Test the System

- Test user registration and login
- Create test events
- Verify clock-in functionality
- Check reports and statistics

## Troubleshooting

### Common Issues

**Permission Problems**:
```bash
sudo chown -R www-data:www-data /var/www/cth
sudo chmod -R 755 /var/www/cth
sudo chmod -R 775 /var/www/cth/storage /var/www/cth/bootstrap/cache
```

**Apache Not Loading**:
```bash
sudo systemctl status apache2
sudo tail -f /var/log/apache2/error.log
```

**Database Connection Issues**:
```bash
# Test database connection
php artisan migrate:status
```

**Cache Issues**:
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

### Performance Optimization

```bash
# Optimize Composer autoloader
composer install --optimize-autoloader --no-dev

# Cache configurations
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Enable OPcache in PHP
sudo nano /etc/php/8.1/apache2/php.ini
# Set: opcache.enable=1
```

## Security Considerations

1. **Keep the system updated**:
   ```bash
   composer update
   php artisan migrate
   ```

2. **Use strong passwords**
3. **Enable SSL/HTTPS**
4. **Regular backups**:
   ```bash
   mysqldump -u cth_user -p cth_production > backup_$(date +%Y%m%d).sql
   ```

5. **Monitor logs regularly**
6. **Restrict file permissions**

## Support

For issues or questions:
1. Check the application logs: `storage/logs/laravel.log`
2. Review this documentation
3. Use the available diagnostic commands
4. Check the GitHub repository for updates

---

*Last updated: November 6, 2025*