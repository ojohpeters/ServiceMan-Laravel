# Complete cPanel Deployment Guide

## 📦 Step 1: Download the Deployment Package

**File:** `serviceman.sekimbi.com.zip`  
**Location:** Your project root directory

---

## 🚀 Step 2: Login to cPanel

1. Go to your cPanel login page
2. Login with your cPanel credentials
3. Navigate to **File Manager**

---

## 🗑️ Step 3: Delete Old Files (Fresh Start)

1. In **File Manager**, navigate to: `/serviceman.sekimbi.com/`
2. Select **ALL** files and folders (Ctrl+A or Cmd+A)
3. Click **Delete** button
4. Confirm deletion
5. ✅ Folder should now be empty

---

## 📤 Step 4: Upload the Zip File

1. Make sure you're in `/serviceman.sekimbi.com/` directory
2. Click **Upload** button (top menu)
3. Click **Select File** and choose `serviceman.sekimbi.com.zip`
4. Wait for upload to complete (15MB - may take 1-2 minutes)
5. ✅ You should see `serviceman.sekimbi.com.zip` in the file list

---

## 📂 Step 5: Extract the Zip File

1. **Right-click** on `serviceman.sekimbi.com.zip`
2. Select **Extract**
3. In the popup, make sure the path is: `/serviceman.sekimbi.com/`
4. Click **Extract File(s)**
5. Wait for extraction (may take 2-3 minutes)
6. ✅ You should see all project files extracted

---

## ⚙️ Step 6: Configure .env File

1. In File Manager, find the `.env.example` file
2. **Copy** it (right-click → Copy)
3. **Rename** the copy to `.env`
4. **Edit** the `.env` file and update these settings:

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

# JWT Secret (if you have one)
JWT_SECRET=your_jwt_secret_here

# Paystack keys
PAYSTACK_PUBLIC_KEY=your_public_key
PAYSTACK_SECRET_KEY=your_secret_key
```

5. **Save** the file

---

## 💻 Step 7: Run Setup Commands (Terminal)

1. In cPanel, go to **Terminal** (search for it in cPanel)
2. Run these commands one by one:

```bash
# Navigate to project directory
cd ~/serviceman.sekimbi.com

# Regenerate service providers (no dev dependencies)
php artisan package:discover

# Set file permissions
chmod -R 775 storage bootstrap/cache public/uploads

# Create storage symlink
php artisan storage:link

# Clear all caches
php artisan optimize:clear

# Rebuild production caches
php artisan optimize
```

✅ If all commands run successfully, you're good!

---

## 🔒 Step 8: Set File Permissions (Alternative - File Manager)

If Terminal doesn't work, use File Manager:

1. Right-click on `storage` folder → **Change Permissions** → Set to `775`
2. Right-click on `bootstrap/cache` folder → **Change Permissions** → Set to `775`
3. Right-click on `public/uploads` folder → **Change Permissions** → Set to `775`

---

## ✅ Step 9: Test Your Website

1. Open your browser
2. Visit: `https://serviceman.sekimbi.com`
3. Test:
   - ✅ Homepage loads
   - ✅ Can register/login
   - ✅ Email verification works
   - ✅ Mobile responsive

---

## 🔧 Troubleshooting

### Error: "500 Internal Server Error"
```bash
cd ~/serviceman.sekimbi.com
php artisan optimize:clear
php artisan optimize
```

### Error: "Storage link not working"
```bash
cd ~/serviceman.sekimbi.com
php artisan storage:link
```

### Error: "Permission denied"
```bash
chmod -R 775 storage bootstrap/cache
```

### Check error logs
```bash
tail -f ~/serviceman.sekimbi.com/storage/logs/laravel.log
```

---

## 📝 Quick Checklist

- [ ] Old files deleted
- [ ] Zip file uploaded
- [ ] Zip file extracted
- [ ] .env file configured
- [ ] Terminal commands run
- [ ] Permissions set
- [ ] Website tested

---

## 🎉 Done!

Your website should now be live at: `https://serviceman.sekimbi.com`

**Need Help?** Check the error logs or contact support.

