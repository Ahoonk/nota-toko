# Deployment Guide

This project is ready for production deployment on a Linux VPS with MySQL.

## One-command update

After the first setup, you can update the app with:

```bash
./deploy.sh
```

## First-time setup

```bash
cd /var/www/nota-toko/nota-toko
composer install --no-dev --optimize-autoloader
cp .env.example .env
php artisan key:generate
php artisan config:clear
php artisan migrate --force
php artisan db:seed --force
npm install
npm run build
php artisan storage:link --force
chmod +x deploy.sh
```

## Production notes

- `APP_URL` should match your public domain, for example `https://nota.askarya.id`.
- If you change database structure later, run `php artisan migrate --force` manually or uncomment that line in `deploy.sh`.
- The web server should point to `/var/www/nota-toko/nota-toko/public`.
