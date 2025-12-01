# cPanel Deployment Steps - Quick Guide

## 📦 Deployment Package Ready

**File:** `ServiceManLaravel_Deployment_20251201_092657.zip` (17MB)
**Location:** Project root directory

---

## Step-by-Step Deployment to cPanel

### Step 1: Upload Deployment Zip

1. **Login to cPanel**
2. **Open File Manager**
3. **Navigate to your subdomain folder:**
   - Go to `/serviceman.sekimbi.com/` (or your subdomain folder)
4. **Upload the zip file:**
   - Click "Upload" button
   - Select `ServiceManLaravel_Deployment_20251201_092657.zip`
   - Wait for upload to complete

### Step 2: Extract Files

1. **In File Manager, find the zip file**
2. **Right-click on the zip file** → Select "Extract"
3. **Extract to:** `/serviceman.sekimbi.com/` (current directory)
4. **Wait for extraction to complete**

### Step 3: Verify Files Are Extracted

Check that these directories exist:
- ✅ `app/`
- ✅ `vendor/` (important - should be there!)
- ✅ `public/`
- ✅ `storage/`
- ✅ `.htaccess` (in root)
- ✅ `artisan`

### Step 4: Configure .env File

1. **In File Manager, find `.env` file** (or create from `.env.example`)
2. **Edit `.env` file** and update:

```env
APP_NAME=ServiceMan
APP_ENV=production
APP_DEBUG=false
APP_URL=https://serviceman.sekimbi.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_full_database_name
DB_USERNAME=your_full_username
DB_PASSWORD=your_database_password

MAIL_MAILER=smtp
MAIL_HOST=mail.sekimbi.com
MAIL_PORT=587
MAIL_USERNAME=noreply@sekimbi.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@sekimbi.com"
MAIL_FROM_NAME="ServiceMan"
```

**Important:**
- `DB_HOST` should be `localhost` (NOT `127.0.0.1`)
- Use full database name with prefix (e.g., `sekionzy_serviceman`)
- Use full username with prefix (e.g., `sekionzy_serviceman`)

### Step 5: Set File Permissions

**Via cPanel File Manager:**
1. **Right-click on `storage/` folder** → Change Permissions
2. **Set to:** `775` (or `rwxrwxr-x`)
3. **Do the same for:**
   - `bootstrap/cache/` → `775`
   - `public/uploads/` → `775`

**OR via Terminal:**
```bash
cd ~/serviceman.sekimbi.com
chmod -R 775 storage bootstrap/cache public/uploads
```

### Step 6: Create Storage Symlink

**Via cPanel Terminal:**
1. **Open Terminal** in cPanel
2. **Run:**
   ```bash
   cd ~/serviceman.sekimbi.com
   php artisan storage:link
   ```

### Step 7: Clear and Cache Config

**Via Terminal:**
```bash
cd ~/serviceman.sekimbi.com
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 8: Import Database (If Needed)

1. **Open phpMyAdmin** in cPanel
2. **Select your database**
3. **Click "Import"**
4. **Upload your database backup** (from `backups/` folder if you have one)
5. **Or run migrations:**
   ```bash
   php artisan migrate --force
   ```

### Step 9: Verify Deployment

1. **Visit your website:** `https://serviceman.sekimbi.com`
2. **Test login/registration**
3. **Check that email verification works**

---

## 🔍 Troubleshooting

### If you get 500 Error:

1. **Check error logs** in cPanel → Error Log
2. **Verify `.env` file** has correct database credentials
3. **Check file permissions:**
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```
4. **Verify vendor directory exists:**
   - Should be in `/serviceman.sekimbi.com/vendor/`
   - Should contain `autoload.php`

### If Database Connection Error:

1. **Check `.env` file:**
   - `DB_HOST=localhost` (NOT `127.0.0.1`)
   - Database name and username include cPanel prefix
   - Password is correct
2. **Clear config cache:**
   ```bash
   php artisan config:clear
   php artisan config:cache
   ```

### If Email Not Working:

1. **Check `.env` mail settings**
2. **Verify email account exists** in cPanel → Email Accounts
3. **Test with log driver first:**
   ```env
   MAIL_MAILER=log
   ```

---

## ✅ Deployment Checklist

- [ ] Uploaded deployment zip to cPanel
- [ ] Extracted zip file
- [ ] Verified vendor directory exists
- [ ] Configured `.env` file with production settings
- [ ] Set file permissions (storage, bootstrap/cache, public/uploads)
- [ ] Created storage symlink
- [ ] Cleared and cached config
- [ ] Imported database or ran migrations
- [ ] Tested website functionality
- [ ] Verified email verification works
- [ ] Tested resend verification email feature

---

## 🎉 Done!

Your ServiceMan application should now be live on cPanel!

**Website:** https://serviceman.sekimbi.com

If you encounter any issues, check the error logs or contact support.

