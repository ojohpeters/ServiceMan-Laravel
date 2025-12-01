#!/bin/bash

# ServiceMan Laravel - Prepare for cPanel Deployment
# This script will: backup database, build assets, and create deployment zip

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}🚀 ServiceMan Laravel - cPanel Deployment Preparation${NC}"
echo ""

# Step 1: Backup Database
echo -e "${YELLOW}📦 Step 1: Backing up database...${NC}"

# Check if .env.local exists, otherwise use .env
ENV_FILE=".env.local"
if [ ! -f "$ENV_FILE" ]; then
    ENV_FILE=".env"
fi

if [ ! -f "$ENV_FILE" ]; then
    echo -e "${YELLOW}   ⚠️  No .env file found. Skipping database backup.${NC}"
else
    # Read database config from env file
    DB_CONNECTION=$(grep "^DB_CONNECTION=" "$ENV_FILE" | cut -d '=' -f2 | tr -d '"' | tr -d "'" | xargs || echo "mysql")
    DB_HOST=$(grep "^DB_HOST=" "$ENV_FILE" | cut -d '=' -f2 | tr -d '"' | tr -d "'" | xargs || echo "127.0.0.1")
    DB_PORT=$(grep "^DB_PORT=" "$ENV_FILE" | cut -d '=' -f2 | tr -d '"' | tr -d "'" | xargs || echo "3306")
    DB_DATABASE=$(grep "^DB_DATABASE=" "$ENV_FILE" | cut -d '=' -f2 | tr -d '"' | tr -d "'" | xargs)
    DB_USERNAME=$(grep "^DB_USERNAME=" "$ENV_FILE" | cut -d '=' -f2 | tr -d '"' | tr -d "'" | xargs)
    DB_PASSWORD=$(grep "^DB_PASSWORD=" "$ENV_FILE" | cut -d '=' -f2 | tr -d '"' | tr -d "'" | xargs)
    
    # Create backups directory
    mkdir -p backups
    BACKUP_DIR="backups"
    TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
    BACKUP_FILE="$BACKUP_DIR/database_backup_$TIMESTAMP.sql"
    
    if [ "$DB_CONNECTION" = "mysql" ] && [ ! -z "$DB_DATABASE" ] && [ ! -z "$DB_USERNAME" ]; then
        echo -e "${YELLOW}   Backing up MySQL database: $DB_DATABASE${NC}"
        
        # Try MySQL backup
        if [ ! -z "$DB_PASSWORD" ]; then
            if MYSQL_PWD="$DB_PASSWORD" mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" "$DB_DATABASE" > "$BACKUP_FILE" 2>/dev/null; then
                echo -e "${GREEN}   ✅ Database backup created: $BACKUP_FILE${NC}"
                gzip -f "$BACKUP_FILE"
                echo -e "${GREEN}   ✅ Backup compressed: ${BACKUP_FILE}.gz${NC}"
            else
                echo -e "${YELLOW}   ⚠️  Automatic backup failed. Continuing...${NC}"
            fi
        else
            echo -e "${YELLOW}   ⚠️  No password in env file. Skipping automatic backup.${NC}"
        fi
    elif [ "$DB_CONNECTION" = "sqlite" ]; then
        echo -e "${YELLOW}   Backing up SQLite database${NC}"
        SQLITE_DB="${DB_DATABASE:-database/database.sqlite}"
        if [ -f "$SQLITE_DB" ]; then
            cp "$SQLITE_DB" "$BACKUP_DIR/database_backup_$TIMESTAMP.sqlite"
            echo -e "${GREEN}   ✅ SQLite database backed up${NC}"
        fi
    fi
fi

echo ""

# Step 2: Install Composer Dependencies (Production)
echo -e "${YELLOW}📦 Step 2: Installing PHP dependencies for production...${NC}"

if [ ! -d "vendor" ]; then
    echo -e "${YELLOW}   Vendor directory not found. Installing Composer dependencies...${NC}"
    composer install --optimize-autoloader --no-dev --no-interaction
    
    if [ $? -ne 0 ]; then
        echo -e "${RED}   ❌ Composer install failed!${NC}"
        exit 1
    fi
    echo -e "${GREEN}   ✅ PHP dependencies installed${NC}"
else
    echo -e "${YELLOW}   Vendor directory exists. Updating dependencies...${NC}"
    composer install --optimize-autoloader --no-dev --no-interaction
    
    if [ $? -ne 0 ]; then
        echo -e "${RED}   ❌ Composer update failed!${NC}"
        exit 1
    fi
    echo -e "${GREEN}   ✅ PHP dependencies updated${NC}"
fi

echo ""

# Step 3: Build Frontend Assets
echo -e "${YELLOW}🔨 Step 3: Building frontend assets...${NC}"

if [ -f "package.json" ]; then
    if [ ! -d "node_modules" ]; then
        echo -e "${YELLOW}   Installing npm dependencies...${NC}"
        npm install
    fi
    
    echo -e "${YELLOW}   Building production assets...${NC}"
    npm run build
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}   ✅ Frontend assets built successfully${NC}"
    else
        echo -e "${RED}   ❌ Build failed!${NC}"
        exit 1
    fi
else
    echo -e "${YELLOW}   ⚠️  No package.json found, skipping build${NC}"
fi

echo ""

# Step 4: Ensure storage directories exist (with .gitkeep files)
echo -e "${YELLOW}📁 Step 4: Ensuring storage directories exist...${NC}"
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p storage/app/public/profile_pictures
mkdir -p bootstrap/cache
mkdir -p public/uploads/profile_pictures

# Create .gitkeep files so directories are included in zip
touch storage/framework/cache/data/.gitkeep
touch storage/framework/sessions/.gitkeep
touch storage/framework/views/.gitkeep
touch storage/logs/.gitkeep
echo -e "${GREEN}   ✅ Storage directories prepared${NC}"

echo ""

# Step 5: Clear caches
echo -e "${YELLOW}🧹 Step 5: Clearing caches...${NC}"
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
echo -e "${GREEN}   ✅ Caches cleared${NC}"

echo ""

# Step 6: Create deployment zip (INCLUDING vendor directory)
echo -e "${YELLOW}📦 Step 6: Creating deployment zip file (including vendor)...${NC}"

ZIP_NAME="ServiceManLaravel_Deployment_$(date +%Y%m%d_%H%M%S).zip"

echo -e "${YELLOW}   Creating zip file: $ZIP_NAME${NC}"
echo -e "${YELLOW}   ⚠️  This may take a few minutes (vendor directory is large)...${NC}"

# Create zip excluding unnecessary files BUT INCLUDING vendor
# Note: We exclude cache files but keep directory structure with .gitkeep
# IMPORTANT: Exclude bootstrap/cache/*.php files - they must be regenerated on server
zip -r "$ZIP_NAME" . \
    -x "*.git*" \
    -x "node_modules/*" \
    -x "bootstrap/cache/*.php" \
    -x "bootstrap/cache/packages.php" \
    -x "bootstrap/cache/services.php" \
    -x "storage/logs/*.log" \
    -x "storage/logs/laravel.log" \
    -x "storage/framework/cache/data/*" \
    -x "storage/framework/cache/data/*.php" \
    -x "storage/framework/sessions/*" \
    -x "storage/framework/sessions/sess_*" \
    -x "storage/framework/views/*.php" \
    -x "*.log" \
    -x ".env" \
    -x ".env.local" \
    -x ".env.*" \
    -x "!.env.example" \
    -x "backups/*" \
    -x "*.zip" \
    -x "test_*.php" \
    -x "debug_*.php" \
    -x "create_*.php" \
    -x "seed_*.php" \
    -x "fix_*.php" \
    -x "check_*.php" \
    -x "add_*.php" \
    -x "verify_*.php" \
    -x "setup_*.php" \
    -x "set_*.php" \
    -x "debug_*.html" \
    -x "test_*.json" \
    -x "test_*.html" \
    -x "*.sqlite" \
    -x "*.sqlite-journal" \
    -x "GITHUB_PUSH_CHECKLIST.md" \
    -x "CPANEL_FIX_500_ERROR.md" \
    -x "QUICK_FIX_500.md" \
    -x "CPANEL_FIX_CACHE_PATH.md" \
    -x "LOCAL_DEPLOYMENT_GUIDE.md" \
    > /dev/null 2>&1

if [ $? -eq 0 ]; then
    ZIP_SIZE=$(du -h "$ZIP_NAME" 2>/dev/null | cut -f1 || echo "unknown")
    echo -e "${GREEN}   ✅ Deployment zip created: $ZIP_NAME (Size: $ZIP_SIZE)${NC}"
    echo -e "${GREEN}   ✅ Vendor directory included - ready for upload!${NC}"
else
    echo -e "${RED}   ❌ Failed to create zip file${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}✅ Deployment package ready!${NC}"
echo ""
echo -e "${BLUE}📋 Summary:${NC}"
if [ -d "backups" ]; then
    echo -e "   • Database backup: backups/"
fi
echo -e "   • PHP dependencies (vendor): ✅ INCLUDED"
echo -e "   • Frontend built: public/build/"
echo -e "   • Deployment zip: ${ZIP_NAME}"
echo ""
echo -e "${YELLOW}📝 Next steps for cPanel:${NC}"
echo "   1. Upload $ZIP_NAME to cPanel via File Manager"
echo "   2. Extract in your subdomain folder (serviceman.sekimbi.com)"
echo "   3. Upload the .htaccess file to the root if not included"
echo "   4. Create/configure .env file with production settings"
echo "   5. Set file permissions: chmod -R 775 storage bootstrap/cache"
echo "   6. Import database backup if needed"
echo "   7. Create storage symlink: php artisan storage:link"
echo "   8. Visit your website - it should work! 🎉"
echo ""
echo -e "${GREEN}🎉 Ready for deployment!${NC}"
