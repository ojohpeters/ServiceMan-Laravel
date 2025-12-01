# Fix 500 Error After cPanel Deployment

## Problem
After adding the `.htaccess` file, you're getting a 500 error with this message:
```
Failed to open stream: No such file or directory in .../public/index.php
vendor/autoload.php missing
```

## Root Cause
The `vendor` directory (PHP dependencies) is excluded from the deployment zip to keep it small. Laravel requires these dependencies to run.

## Solution

You have **two options** depending on your cPanel access level:

### Option 1: Run Deployment Script via SSH (Recommended)

If you have SSH access to your cPanel account:

1. **Connect via SSH** to your cPanel account
2. **Navigate to your project directory:**
   ```bash
   cd ~/serviceman.sekimbi.com
   ```
3. **Make the script executable:**
   ```bash
   chmod +x deploy-cpanel.sh
   ```
4. **Run the deployment script:**
   ```bash
   bash deploy-cpanel.sh
   ```

This will automatically:
- Install PHP dependencies (`composer install`)
- Install Node.js dependencies (`npm install`)
- Build frontend assets
- Set up storage symlinks
- Configure permissions
- Run database migrations

### Option 2: Manual Installation via cPanel Terminal

If you don't have full SSH but have Terminal access in cPanel:

1. **Open Terminal** in cPanel (usually under "Advanced" or "Tools")
2. **Navigate to your project:**
   ```bash
   cd ~/serviceman.sekimbi.com
   ```
3. **Install Composer dependencies:**
   ```bash
   composer install --optimize-autoloader --no-dev
   ```
   
   **Note:** If `composer` command is not found, you may need to use the full path:
   ```bash
   /usr/local/bin/composer install --optimize-autoloader --no-dev
   ```
   
   Or use PHP directly:
   ```bash
   php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
   php composer-setup.php
   php composer.phar install --optimize-autoloader --no-dev
   ```

4. **Install Node.js dependencies and build:**
   ```bash
   npm install --production
   npm run build
   ```

5. **Generate application key:**
   ```bash
   php artisan key:generate --force
   ```

6. **Create storage symlink:**
   ```bash
   php artisan storage:link
   ```

7. **Set permissions:**
   ```bash
   chmod -R 775 storage bootstrap/cache public/uploads
   chmod -R 755 public
   ```

8. **Run migrations:**
   ```bash
   php artisan migrate --force
   ```

9. **Clear and cache config:**
   ```bash
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### Option 3: Use cPanel File Manager (If no Terminal/SSH)

If you don't have Terminal or SSH access:

1. **Check if Composer is installed:**
   - Contact your hosting provider to install Composer
   - Or ask them to run `composer install` for you

2. **Alternative: Upload vendor folder** (NOT recommended - very large ~50MB+)
   - Install dependencies locally: `composer install --optimize-autoloader --no-dev`
   - Compress the `vendor` folder
   - Upload and extract via cPanel File Manager
   - This is a last resort due to file size

## Verify Installation

After running the installation, check:

1. **Verify vendor directory exists:**
   ```bash
   ls -la vendor/autoload.php
   ```
   Should show the file exists.

2. **Check error logs:**
   - If still getting errors, check the error log in cPanel
   - Path: Usually `error_log` in your domain root or `public_html/error_log`

3. **Visit your website:**
   - Should load without 500 errors
   - May need to configure `.env` file first

## Next Steps

After fixing the 500 error:

1. **Configure `.env` file** with production settings
2. **Import database** if needed
3. **Test the application** thoroughly
4. **Check file permissions** for uploads directory

## Troubleshooting

### Composer Not Found
- Check if Composer is installed: `composer --version`
- If not, install via: `curl -sS https://getcomposer.org/installer | php`
- Or contact hosting provider

### Permission Denied
- Make sure you have proper permissions: `chmod -R 775 storage bootstrap/cache`

### Database Connection Error
- Check `.env` file database credentials
- Verify database exists in cPanel MySQL Databases
- Test connection

## Need Help?

If you continue to experience issues:
1. Check cPanel error logs
2. Verify PHP version (Laravel requires PHP 8.1+)
3. Ensure all required PHP extensions are installed
4. Contact your hosting provider for assistance

