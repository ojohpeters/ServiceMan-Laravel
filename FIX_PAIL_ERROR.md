# Quick Fix: Laravel Pail Service Provider Error

## Problem
Error: `Class "Laravel\Pail\PailServiceProvider" not found`

This happens because cached service provider files include references to dev dependencies that aren't installed in production.

## Quick Fix (Run on Server)

**Via cPanel Terminal:**

```bash
cd ~/serviceman.sekimbi.com

# Delete cached service provider files
rm -f bootstrap/cache/packages.php
rm -f bootstrap/cache/services.php
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/routes-v7.php

# Regenerate service providers (Laravel will auto-discover)
php artisan package:discover

# Clear all caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Rebuild cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**OR simpler - just delete the cached files:**

```bash
cd ~/serviceman.sekimbi.com
rm -f bootstrap/cache/*.php
php artisan optimize:clear
php artisan optimize
```

This will regenerate all cached files without the dev dependencies.

## Alternative: Via File Manager

1. Open cPanel File Manager
2. Navigate to `/serviceman.sekimbi.com/bootstrap/cache/`
3. Delete these files:
   - `packages.php`
   - `services.php`
   - `config.php` (if exists)
   - `routes-v7.php` (if exists)
4. Run via Terminal:
   ```bash
   php artisan optimize:clear
   php artisan optimize
   ```

## Why This Happened

The cached service provider files were generated during local development when dev dependencies (like Laravel Pail) were installed. These cached files reference packages that aren't in production.

After deleting and regenerating, Laravel will only cache packages that are actually installed.

