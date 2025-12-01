# Quick Fix: 500 Error After Adding .htaccess

## The Problem
Missing `vendor/autoload.php` - Laravel can't find Composer dependencies.

## Quick Fix

**You need to install Composer dependencies on your server.**

### Method 1: Via cPanel Terminal (Easiest)

1. **Open cPanel Terminal** (in cPanel → Advanced → Terminal)
2. **Run these commands:**

```bash
cd ~/serviceman.sekimbi.com
composer install --optimize-autoloader --no-dev
```

If `composer` command doesn't work, try:
```bash
php composer.phar install --optimize-autoloader --no-dev
```

Or use the full path:
```bash
/usr/local/bin/composer install --optimize-autoloader --no-dev
```

3. **After installation completes**, refresh your website - the 500 error should be gone!

### Method 2: Run Full Deployment Script

If you have SSH or Terminal access, run the complete deployment script:

```bash
cd ~/serviceman.sekimbi.com
chmod +x deploy-cpanel.sh
bash deploy-cpanel.sh
```

This will:
- Install PHP dependencies ✅
- Install Node.js dependencies
- Build frontend assets
- Set up storage
- Configure permissions
- Run migrations

### Method 3: Manual Steps (If no Composer access)

If you can't run Composer on the server:

1. **On your local machine**, run:
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

2. **Compress the vendor folder:**
   ```bash
   tar -czf vendor.tar.gz vendor/
   ```

3. **Upload vendor.tar.gz** via cPanel File Manager

4. **Extract it** in your project root (`/serviceman.sekimbi.com/vendor/`)

5. **Refresh your website**

## Why This Happened

The `.htaccess` file is working perfectly - it's redirecting to `public/index.php` as expected.

But Laravel needs the `vendor` directory (Composer dependencies) to run. This was intentionally excluded from the deployment zip to keep the file size small.

## After Fixing

Once `vendor/autoload.php` exists, your site should work!

Make sure you also:
1. Configure your `.env` file with production settings
2. Run migrations if needed: `php artisan migrate --force`
3. Set permissions: `chmod -R 775 storage bootstrap/cache`

