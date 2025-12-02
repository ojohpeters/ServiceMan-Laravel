#!/bin/bash

# ServiceMan Laravel - Complete cPanel Deployment Package
# This script builds assets and creates a zip that extracts cleanly to cPanel

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}🚀 ServiceMan Laravel - cPanel Deployment Package Builder${NC}"
echo ""

# Get the project root directory
PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_ROOT"

# Step 1: Install Composer Dependencies (Production)
echo -e "${YELLOW}📦 Step 1: Installing PHP dependencies for production...${NC}"
if ! composer install --optimize-autoloader --no-dev --no-interaction; then
    echo -e "${RED}   ❌ Composer install failed!${NC}"
    exit 1
fi
echo -e "${GREEN}   ✅ PHP dependencies installed${NC}"
echo ""

# Step 2: Build Frontend Assets
echo -e "${YELLOW}🔨 Step 2: Building frontend assets...${NC}"
if [ -f "package.json" ]; then
    if [ ! -d "node_modules" ]; then
        echo -e "${YELLOW}   Installing npm dependencies...${NC}"
        npm install
    fi
    
    if ! npm run build; then
        echo -e "${RED}   ❌ Build failed!${NC}"
        exit 1
    fi
    echo -e "${GREEN}   ✅ Frontend assets built successfully${NC}"
else
    echo -e "${YELLOW}   ⚠️  No package.json found, skipping build${NC}"
fi
echo ""

# Step 3: Ensure Storage Directories Exist
echo -e "${YELLOW}📁 Step 3: Preparing storage directories...${NC}"
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
touch storage/app/public/profile_pictures/.gitkeep
touch public/uploads/profile_pictures/.gitkeep
echo -e "${GREEN}   ✅ Storage directories prepared${NC}"
echo ""

# Step 4: Clear Caches
echo -e "${YELLOW}🧹 Step 4: Clearing Laravel caches...${NC}"
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true

# Remove bootstrap cache PHP files (they'll be regenerated on server)
rm -f bootstrap/cache/packages.php
rm -f bootstrap/cache/services.php
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/routes-v7.php
rm -f bootstrap/cache/routes.php
echo -e "${GREEN}   ✅ Caches cleared${NC}"
echo ""

# Step 5: Create Deployment Zip
echo -e "${YELLOW}📦 Step 5: Creating deployment zip file...${NC}"
ZIP_NAME="serviceman.sekimbi.com.zip"

# Remove old zip if exists
if [ -f "$ZIP_NAME" ]; then
    rm -f "$ZIP_NAME"
    echo -e "${YELLOW}   Removed old zip file${NC}"
fi

echo -e "${YELLOW}   Creating: $ZIP_NAME${NC}"
echo -e "${YELLOW}   ⚠️  This may take a few minutes (vendor directory is large)...${NC}"

# Create zip file with all necessary files
# The zip will maintain the directory structure so when extracted, files go to the right place
zip -r "$ZIP_NAME" . \
    -x "*.git*" \
    -x "*.gitignore" \
    -x "node_modules/*" \
    -x "bootstrap/cache/*.php" \
    -x "bootstrap/cache/packages.php" \
    -x "bootstrap/cache/services.php" \
    -x "bootstrap/cache/config.php" \
    -x "bootstrap/cache/routes*.php" \
    -x "storage/logs/*.log" \
    -x "storage/logs/laravel.log" \
    -x "storage/framework/cache/data/*" \
    -x "storage/framework/cache/data/*.php" \
    -x "!storage/framework/cache/data/.gitkeep" \
    -x "storage/framework/sessions/*" \
    -x "storage/framework/sessions/sess_*" \
    -x "!storage/framework/sessions/.gitkeep" \
    -x "storage/framework/views/*.php" \
    -x "!storage/framework/views/.gitkeep" \
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
    -x "*.md" \
    -x "WHATSAPP_*.txt" \
    -x "*.sh" \
    -x "!deploy-to-cpanel.sh" \
    -x "ServiceManLaravel_Deployment_*" \
    > /dev/null 2>&1

if [ $? -eq 0 ]; then
    ZIP_SIZE=$(du -h "$ZIP_NAME" 2>/dev/null | cut -f1 || echo "unknown")
    echo -e "${GREEN}   ✅ Deployment zip created: $ZIP_NAME (Size: $ZIP_SIZE)${NC}"
else
    echo -e "${RED}   ❌ Failed to create zip file${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}✅ Deployment package ready!${NC}"
echo ""
echo -e "${BLUE}📋 Package Summary:${NC}"
echo -e "   • PHP dependencies (vendor): ✅ INCLUDED"
echo -e "   • Frontend assets (public/build): ✅ BUILT & INCLUDED"
echo -e "   • Storage directories: ✅ PREPARED"
echo -e "   • Bootstrap cache: ❌ EXCLUDED (will regenerate on server)"
echo -e "   • File name: ${ZIP_NAME}"
echo ""
echo -e "${YELLOW}📝 cPanel Deployment Instructions:${NC}"
echo "   1. Log into cPanel File Manager"
echo "   2. Navigate to: serviceman.sekimbi.com"
echo "   3. Select ALL existing files/folders and DELETE them"
echo "   4. Upload the zip file: $ZIP_NAME"
echo "   5. Right-click the zip file → Extract"
echo "   6. The files will extract directly into the current directory"
echo "   7. After extraction, DELETE the zip file"
echo ""
echo -e "${YELLOW}📝 Post-Extraction Steps:${NC}"
echo "   1. Create/upload .env file with production settings"
echo "   2. Set file permissions (via Terminal or File Manager):"
echo "      chmod -R 775 storage bootstrap/cache"
echo "   3. Create storage symlink (via Terminal):"
echo "      php artisan storage:link"
echo "   4. Clear and cache config (via Terminal):"
echo "      php artisan config:clear"
echo "      php artisan config:cache"
echo "      php artisan route:cache"
echo "      php artisan view:cache"
echo "   5. Ensure document root points to: serviceman.sekimbi.com/public"
echo ""
echo -e "${GREEN}🎉 Ready for deployment!${NC}"

