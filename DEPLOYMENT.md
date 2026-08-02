# Deployment Guide

This project is ready for production deployment on a Linux VPS with MySQL.

## 1. Clone or update the repo

```bash
cd /var/www
git clone https://github.com/Ahoonk/nota-toko.git
cd /var/www/nota-toko/nota-toko
git pull origin main
```

## 2. Install PHP dependencies

```bash
composer install --no-dev --optimize-autoloader
```

## 3. Prepare the environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

## 4. Run database migrations

```bash
php artisan config:clear
php artisan migrate --force
php artisan db:seed --force
```

## 5. Build frontend assets

```bash
npm install
npm run build
```

## 6. Fix permissions

```bash
chown -R www-data:www-data /var/www/nota-toko/nota-toko
chmod -R 775 storage bootstrap/cache
```

## 7. Clear and optimize caches

```bash
php artisan optimize:clear
php artisan optimize
```

## 8. Nginx example

Point your web root to `/var/www/nota-toko/nota-toko/public`.

```nginx
server {
    listen 80;
    server_name your-domain.example;
    root /var/www/nota-toko/nota-toko/public;

    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

## 9. Verify in browser

Open:

```text
https://your-domain.example
```

If you only want to test quickly, you can temporarily use:

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

