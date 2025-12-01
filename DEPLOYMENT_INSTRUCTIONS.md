# Clean Deployment Instructions

## ✅ Clean Deployment Package Ready

**File:** `serviceman.sekimbi.com.zip` (15MB)
**Location:** Project root directory

This package includes:
- ✅ Vendor directory (PHP dependencies)
- ✅ Built frontend assets (npm run build executed)
- ✅ Storage directories with .gitkeep files
- ✅ All latest fixes (mobile responsiveness, email verification, etc.)
- ❌ Bootstrap cache files EXCLUDED (will regenerate on server - prevents Pail error)

---

## 🚀 Deployment Steps

### Step 1: Delete Old Files on cPanel

1. **Login to cPanel**
2. **Open File Manager**
3. **Navigate to:** `/serviceman.sekimbi.com/`
4. **Select ALL files and folders** (Ctrl+A or Cmd+A)
5. **Delete everything** (this gives you a fresh start)

### Step 2: Upload Clean Zip

1. **Upload:** `serviceman.sekimbi.com.zip` via File Manager
2. **Right-click the zip** → Select "Extract"
3. **Extract to:** `/serviceman.sekimbi.com/` (current directory)
4. **Wait for extraction to complete**

### Step 3: Configure .env File

1. **Find or create `.env` file**
2. **Edit with production settings:**

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

### Step 4: Run Setup Commands (Terminal)

Open **cPanel Terminal** and run:

```bash
cd ~/serviceman.sekimbi.com

# Regenerate service providers (no dev dependencies)
php artisan package:discover

# Set permissions
chmod -R 775 storage bootstrap/cache public/uploads

# Create storage symlink
php artisan storage:link

# Clear and rebuild caches
php artisan optimize:clear
php artisan optimize
```

### Step 5: Test

1. **Visit:** `https://serviceman.sekimbi.com`
2. **Test mobile view:** Check that buttons are responsive
3. **Test email verification:** Register and verify

---

## ✨ What's Fixed in This Deployment

- ✅ Mobile responsive buttons (no horizontal scrolling)
- ✅ Email verification enforcement
- ✅ Resend verification email feature
- ✅ Clean bootstrap cache (no Pail errors)
- ✅ All latest UI/UX improvements

---

## 🎉 Done!

Your clean deployment is ready to go!

**File to upload:** `serviceman.sekimbi.com.zip`

