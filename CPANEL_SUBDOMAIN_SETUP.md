# cPanel Subdomain Setup Guide

## Problem: Seeing "Index of /" Directory Listing

When you see a directory listing instead of your Laravel application, it means the web server isn't pointing to the `public` directory correctly.

## Solution: Configure Subdomain Document Root

### Option 1: Point Subdomain to `public` Directory (Recommended)

1. **In cPanel:**
   - Go to **Subdomains**
   - Find your subdomain: `serviceman.sekimbi.com`
   - Click **Change Document Root** or **Manage**
   - Set Document Root to: `public_html/serviceman/public`
   - Save

   OR manually set:
   - Document Root: `serviceman/public`

2. **Via SSH (if available):**
   ```bash
   # Your files should be in: public_html/serviceman/
   # But web root should point to: public_html/serviceman/public
   ```

### Option 2: Move Files to Correct Structure

If you can't change the document root, restructure your files:

1. **Move Laravel files:**
   - All files except `public` folder should be one level up
   - OR move `public` folder contents to subdomain root

2. **Update paths in `public/index.php`:**
   ```php
   // Change these lines:
   require __DIR__.'/../vendor/autoload.php';
   $app = require_once __DIR__.'/../bootstrap/app.php';
   
   // To (if files are in parent directory):
   require __DIR__.'/../vendor/autoload.php';
   $app = require_once __DIR__.'/../bootstrap/app.php';
   ```

### Option 3: Use .htaccess Redirect (Temporary Fix)

Create/update `.htaccess` in the subdomain root:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

## Current Structure on Server

Your files should be structured like this:

```
public_html/
└── serviceman/              # Subdomain directory
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── public/              # THIS should be the document root
    │   ├── index.php
    │   ├── .htaccess
    │   └── ...
    ├── resources/
    ├── routes/
    ├── storage/
    ├── vendor/
    └── .env
```

## Quick Fix Steps

1. **In cPanel Subdomains:**
   - Change Document Root from `serviceman` to `serviceman/public`

2. **Or create redirect in root:**
   - Add `.htaccess` in `serviceman/` directory that redirects to `public/`

3. **Verify .htaccess in public folder:**
   - Ensure `public/.htaccess` exists and is correct

4. **Check file permissions:**
   ```bash
   chmod 755 public
   chmod 644 public/index.php
   chmod 644 public/.htaccess
   ```

## Verify Configuration

After changes, visit:
- `https://serviceman.sekimbi.com`
- Should show Laravel application, not directory listing

If you still see directory listing, the document root isn't pointing to `public` folder yet.

