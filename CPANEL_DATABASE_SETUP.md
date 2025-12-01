# cPanel Database Setup Guide

## Problem
Error: `SQLSTATE[HY000] [2002] Can't connect to server on '127.0.0.1'`

This means Laravel can't connect to your MySQL database. You need to configure the `.env` file with your cPanel database credentials.

## Step 1: Create Database in cPanel

1. **Login to cPanel**
2. **Go to "MySQL Databases"** (under "Databases" section)
3. **Create a new database:**
   - Enter database name (e.g., `sekionzy_serviceman`)
   - Click "Create Database"
   - Note the full database name (usually prefixed with your cPanel username)

## Step 2: Create Database User

1. **Scroll down to "MySQL Users"**
2. **Create a new user:**
   - Username: (e.g., `sekionzy_serviceman`)
   - Password: (Create a strong password - save this!)
   - Click "Create User"

## Step 3: Add User to Database

1. **Scroll down to "Add User To Database"**
2. **Select your user** and **database** from dropdowns
3. **Click "Add"**
4. **Check "ALL PRIVILEGES"**
5. **Click "Make Changes"**

## Step 4: Find Your Database Details

After creating the database, you'll see:

- **Database Name**: `sekionzy_serviceman` (full name with prefix)
- **Database User**: `sekionzy_serviceman` (full username with prefix)
- **Database Host**: Usually `localhost` (NOT `127.0.0.1` for cPanel)

## Step 5: Configure .env File

1. **Open cPanel File Manager**
2. **Navigate to:** `/serviceman.sekimbi.com/`
3. **Find `.env` file** (or create from `.env.example`)
4. **Edit the database section:**

```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=sekionzy_serviceman
DB_USERNAME=sekionzy_serviceman
DB_PASSWORD=your_database_password_here
```

**Important Notes:**
- `DB_HOST` should be `localhost` (not `127.0.0.1`)
- Use the **FULL** database name with prefix (e.g., `sekionzy_serviceman`)
- Use the **FULL** username with prefix (e.g., `sekionzy_serviceman`)
- Make sure password has no extra spaces

## Step 6: Test Database Connection

Via cPanel Terminal:

```bash
cd ~/serviceman.sekimbi.com
php artisan config:clear
php artisan config:cache
php artisan migrate:status
```

If you see migration status (even if empty), the connection works!

## Step 7: Run Migrations

```bash
php artisan migrate --force
```

## Common Issues

### Issue: "Access denied"
- **Fix**: Check username and password in `.env`
- Make sure user has ALL PRIVILEGES on the database

### Issue: "Unknown database"
- **Fix**: Verify database name in `.env` matches exactly (including prefix)
- Check database exists in cPanel

### Issue: "Can't connect to server"
- **Fix**: Change `DB_HOST=127.0.0.1` to `DB_HOST=localhost`
- Try without port: `DB_PORT=3306`

### Issue: Still using cached config
- **Fix**: Run:
  ```bash
  php artisan config:clear
  php artisan config:cache
  ```

## Full .env Database Section Example

```env
APP_NAME=ServiceMan
APP_ENV=production
APP_DEBUG=false
APP_URL=https://serviceman.sekimbi.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=sekionzy_serviceman
DB_USERNAME=sekionzy_serviceman
DB_PASSWORD=YourStrongPassword123!
```

After updating `.env`, clear config cache and your site should work!

