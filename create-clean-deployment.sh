#!/bin/bash

# Clean Deployment Package for cPanel
# Creates a fresh, complete deployment zip with name: serviceman.sekimbi.com.zip

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}🧹 Creating Clean Deployment Package...${NC}"
echo ""

# Step 1: Install Composer Dependencies (Production)
echo -e "${YELLOW}📦 Step 1: Installing PHP dependencies for production...${NC}"
composer install --optimize-autoloader --no-dev --no-interaction
echo -e "${GREEN}   ✅ PHP dependencies installed${NC}"
echo ""

# Step 2: Build Frontend Assets
echo -e "${YELLOW}🔨 Step 2: Building frontend assets...${NC}"
if [ -f "package.json" ]; then
    if [ ! -d "node_modules" ]; then
        echo -e "${YELLOW}   Installing npm dependencies...${NC}"
        npm install
    fi
    npm run build
    echo -e "${GREEN}   ✅ Frontend assets built${NC}"
else
    echo -e "${YELLOW}   ⚠️  No package.json found${NC}"
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

# Create .gitkeep files
touch storage/framework/cache/data/.gitkeep
touch storage/framework/sessions/.gitkeep
touch storage/framework/views/.gitkeep
touch storage/logs/.gitkeep
echo -e "${GREEN}   ✅ Storage directories prepared${NC}"
echo ""

# Step 4: Clear ALL Caches (including bootstrap cache)
echo -e "${YELLOW}🧹 Step 4: Clearing all caches...${NC}"
php artisan optimize:clear 2>/dev/null || true
rm -f bootstrap/cache/packages.php
rm -f bootstrap/cache/services.php
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/routes-v7.php
echo -e "${GREEN}   ✅ All caches cleared${NC}"
echo ""

# Step 5: Create Clean Deployment Zip
echo -e "${YELLOW}📦 Step 5: Creating clean deployment zip...${NC}"
ZIP_NAME="serviceman.sekimbi.com.zip"

# Remove old zip if exists
if [ -f "$ZIP_NAME" ]; then
    rm -f "$ZIP_NAME"
    echo -e "${YELLOW}   Removed old zip file${NC}"
fi

echo -e "${YELLOW}   Creating: $ZIP_NAME${NC}"
echo -e "${YELLOW}   ⚠️  This may take a few minutes (includes vendor directory)...${NC}"

# Create zip - EXCLUDE bootstrap cache PHP files (they'll be regenerated on server)
zip -r "$ZIP_NAME" . \
    -x "*.git*" \
    -x "node_modules/*" \
    -x "bootstrap/cache/*.php" \
    -x "storage/logs/*.log" \
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
    -x "*.md" \
    -x "fix-*.sh" \
    -x "backup-*.sh" \
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
echo -e "${GREEN}✅ Clean deployment package ready!${NC}"
echo ""
echo -e "${BLUE}📋 Package Contents:${NC}"
echo -e "   • Vendor directory: ✅ INCLUDED"
echo -e "   • Frontend assets: ✅ BUILT"
echo -e "   • Storage directories: ✅ PREPARED"
echo -e "   • Bootstrap cache: ❌ EXCLUDED (will regenerate on server)"
echo -e "   • File: ${ZIP_NAME}"
echo ""
echo -e "${YELLOW}📝 Deployment Steps:${NC}"
echo "   1. Delete everything in /serviceman.sekimbi.com/ on cPanel"
echo "   2. Upload and extract: $ZIP_NAME"
echo "   3. Configure .env file"
echo "   4. Run on server: php artisan optimize"
echo "   5. Set permissions: chmod -R 775 storage bootstrap/cache"
echo "   6. Done! 🎉"
echo ""

