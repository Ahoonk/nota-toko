#!/usr/bin/env bash

set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$APP_DIR"

echo "Pulling latest code..."
git pull origin main

echo "Installing PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "Installing frontend dependencies..."
npm install

echo "Building frontend assets..."
npm run build

echo "Ensuring public storage link exists..."
php artisan storage:link --force

echo "Clearing and rebuilding Laravel caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
php artisan optimize

echo "Deployment finished."

# Optional, if you change database structure:
# php artisan migrate --force
