# Local Deployment Preparation Guide

## For cPanel Upload (Everything Prepared Locally)

This guide is for preparing everything locally, then uploading the complete zip to cPanel.

### Step 1: Install Composer Dependencies Locally

First, make sure you have Composer dependencies installed for production:

```bash
composer install --optimize-autoloader --no-dev
```

This will create the `vendor` directory with all PHP dependencies.

### Step 2: Build Frontend Assets

Build your frontend assets:

```bash
npm run build
```

### Step 3: Run the Preparation Script

Run the automated script that does everything:

```bash
bash prepare-cpanel-deployment.sh
```

This script will:
- ✅ Backup your database
- ✅ Install/update Composer dependencies (vendor directory)
- ✅ Build frontend assets
- ✅ Clear caches
- ✅ Create a complete deployment zip **including vendor directory**

### Step 4: Upload to cPanel

1. **Upload the zip file** via cPanel File Manager
   - File: `ServiceManLaravel_Deployment_YYYYMMDD_HHMMSS.zip`
   
2. **Extract the zip** in your subdomain folder
   - Location: `/serviceman.sekimbi.com/`

3. **Upload the .htaccess file** (if not included in zip)
   - Should be in the root directory (same level as `artisan`, `composer.json`)

4. **Create/Configure .env file**
   - Copy `.env.example` to `.env`
   - Update with production database credentials
   - Set `APP_ENV=production` and `APP_DEBUG=false`

5. **Set file permissions** (via cPanel File Manager or Terminal)
   - `storage/` → 775
   - `bootstrap/cache/` → 775
   - `public/uploads/` → 775

6. **Create storage symlink** (if needed, via Terminal):
   ```bash
   php artisan storage:link
   ```

7. **Import database** (if needed)
   - Use phpMyAdmin in cPanel
   - Import your database backup from `backups/` folder

8. **Visit your website** - it should work! 🎉

### Important Notes

- ✅ The zip file now **includes vendor directory** - no need to run `composer install` on the server
- ✅ Everything is prepared locally - just upload and extract
- ✅ The `.htaccess` file redirects requests to the `public` folder
- ⚠️ The zip file will be larger (includes vendor ~50-100MB) but it's complete

### Troubleshooting

**If you get 500 error after upload:**
- Check that `.htaccess` file is in the root directory
- Verify `vendor/autoload.php` exists in the extracted files
- Check error logs in cPanel
- Verify `.env` file has correct database credentials

**If vendor directory is missing:**
- Run `composer install --optimize-autoloader --no-dev` locally
- Re-run `prepare-cpanel-deployment.sh`

**If permissions error:**
- Set correct permissions via cPanel File Manager
- Or use Terminal: `chmod -R 775 storage bootstrap/cache public/uploads`

