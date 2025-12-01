# Quick Fix for Directory Listing on cPanel

## Your Current Setup
- Document Root: `/serviceman.sekimbi.com`
- Files Location: All Laravel files are in this directory
- Problem: Web server is showing directory listing instead of Laravel app

## Solution: Upload Root .htaccess File

I've created a `.htaccess` file that will redirect all requests to the `public` folder.

### Steps:

1. **Upload `.htaccess` file to your subdomain root:**
   - Location: `/serviceman.sekimbi.com/.htaccess`
   - This is the same directory where `artisan`, `composer.json`, `app`, `public` folders are

2. **File should contain:**
   ```
   (The .htaccess content I created)
   ```

3. **Verify the file is there:**
   - Check in cPanel File Manager
   - File should be at: `serviceman.sekimbi.com/.htaccess`

4. **Set proper permissions:**
   - Right-click `.htaccess` in File Manager
   - Change Permissions → 644

5. **Clear browser cache and visit:**
   - `https://serviceman.sekimbi.com`
   - Should now show your Laravel app!

## Alternative: Change Document Root (Better Solution)

If you can change the document root:

1. Go to **Subdomains** in cPanel
2. Find `serviceman.sekimbi.com`
3. Click **Change Document Root**
4. Change from: `/serviceman.sekimbi.com`
5. Change to: `/serviceman.sekimbi.com/public`
6. Save

This is the preferred method and doesn't require the root `.htaccess` file.

## After Fix - Complete Setup

Once the site loads:

1. **Create/Configure `.env` file:**
   ```env
   APP_URL=https://serviceman.sekimbi.com
   APP_ENV=production
   APP_DEBUG=false
   ```

2. **Set permissions (via SSH or File Manager):**
   ```
   storage/ → 775
   bootstrap/cache/ → 775
   public/uploads/ → 775
   ```

3. **Create storage link:**
   ```bash
   php artisan storage:link
   ```

4. **Configure database in `.env`**

5. **Run migrations:**
   ```bash
   php artisan migrate --force
   ```

