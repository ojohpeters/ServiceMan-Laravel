# Fix: "Please provide a valid cache path" Error

## Problem
Laravel needs storage cache directories to exist. These directories were excluded from the zip file (which is correct - we don't want cached files), but the directories themselves must exist.

## Quick Fix

Run these commands via cPanel Terminal or SSH:

```bash
cd ~/serviceman.sekimbi.com

# Create storage directories
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p storage/app/public/profile_pictures
mkdir -p bootstrap/cache
mkdir -p public/uploads/profile_pictures

# Set permissions
chmod -R 775 storage bootstrap/cache public/uploads
chmod -R 755 public

# Create storage symlink
php artisan storage:link

# Clear and recache
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear

# Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Alternative: Create a Setup Script

Create a file called `setup-storage.sh` in your project root with these contents, then run it.

