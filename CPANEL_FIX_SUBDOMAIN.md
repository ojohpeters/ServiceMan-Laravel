# Fix: Directory Listing on cPanel Subdomain

## Problem
You're seeing "Index of /" instead of your Laravel application because the document root isn't pointing to the `public` folder.

## Quick Fix Options

### Option 1: Change Document Root in cPanel (BEST SOLUTION)

1. **Log into cPanel**
2. **Go to Subdomains** (or "Subdomain" section)
3. **Find your subdomain**: `serviceman.sekimbi.com`
4. **Click "Change Document Root"** or edit the subdomain
5. **Change from**: `serviceman` 
   **To**: `serviceman/public`
6. **Save/Update**

Now visit `https://serviceman.sekimbi.com` - it should work!

---

### Option 2: Use .htaccess Redirect (QUICK FIX)

I've created a `.htaccess` file in the root. This will redirect all requests to the `public` folder.

**Steps:**
1. The `.htaccess` file has been created in the root directory
2. Upload it to your cPanel subdomain root folder (where artisan, composer.json, etc. are)
3. It will automatically redirect all requests to the `public` folder

**File location on server:**
```
public_html/serviceman/.htaccess  ← Put the .htaccess here
```

---

### Option 3: Move Files to Correct Structure

If Options 1 & 2 don't work, restructure:

**Current (Wrong):**
```
public_html/serviceman/        ← Document root
├── app/
├── public/                    ← Should be document root
├── routes/
└── ...
```

**Move everything from `public/` to subdomain root:**

1. **Via cPanel File Manager:**
   - Go to `serviceman/public/` folder
   - Select all files
   - Move them up one level to `serviceman/`
   - Update paths in moved `index.php` if needed

2. **Or use SSH:**
   ```bash
   cd public_html/serviceman
   mv public/* .
   mv public/.htaccess .
   ```

---

## Recommended Solution

**Use Option 1** - Change the document root in cPanel to point to `serviceman/public`. This is the cleanest and most correct way.

If you can't change document root, use **Option 2** - the `.htaccess` redirect I've created.

---

## After Fix: Additional Setup

Once the site loads, you still need to:

1. **Configure .env file:**
   ```env
   APP_URL=https://serviceman.sekimbi.com
   APP_ENV=production
   APP_DEBUG=false
   ```

2. **Set permissions:**
   ```bash
   chmod -R 755 storage bootstrap/cache
   chmod -R 775 public/uploads
   ```

3. **Create storage link:**
   ```bash
   php artisan storage:link
   ```

4. **Configure database in .env**

5. **Run migrations:**
   ```bash
   php artisan migrate --force
   ```

---

## File Structure Should Be:

```
public_html/
└── serviceman/              # Subdomain folder
    ├── .htaccess           # NEW: Root redirect (if using Option 2)
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── public/             # Document root (if using Option 1)
    │   ├── .htaccess
    │   ├── index.php
    │   └── ...
    ├── routes/
    ├── storage/
    ├── vendor/
    └── .env
```

