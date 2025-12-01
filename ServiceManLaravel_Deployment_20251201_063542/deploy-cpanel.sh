#!/bin/bash

# ServiceMan Laravel - cPanel Deployment Script
# Run this script after uploading files to cPanel via SSH

echo "🚀 Starting ServiceMan Laravel Deployment..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if .env exists
if [ ! -f .env ]; then
    echo -e "${YELLOW}⚠️  .env file not found. Creating from .env.example...${NC}"
    if [ -f .env.example ]; then
        cp .env.example .env
        echo -e "${GREEN}✅ .env file created. Please configure it with your settings.${NC}"
    else
        echo -e "${RED}❌ .env.example not found!${NC}"
        exit 1
    fi
fi

# Install/Update Composer dependencies
echo -e "${YELLOW}📦 Installing PHP dependencies...${NC}"
composer install --optimize-autoloader --no-dev --no-interaction

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Composer install failed!${NC}"
    exit 1
fi
echo -e "${GREEN}✅ PHP dependencies installed${NC}"

# Install/Update NPM dependencies
echo -e "${YELLOW}📦 Installing Node.js dependencies...${NC}"
npm install --production

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ NPM install failed!${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Node.js dependencies installed${NC}"

# Build frontend assets
echo -e "${YELLOW}🔨 Building frontend assets...${NC}"
npm run build

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Build failed!${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Frontend assets built${NC}"

# Generate application key if not set
echo -e "${YELLOW}🔑 Checking application key...${NC}"
php artisan key:generate --force

# Generate JWT secret if not set
echo -e "${YELLOW}🔐 Checking JWT secret...${NC}"
php artisan jwt:secret --force 2>/dev/null || echo "JWT secret already set or jwt package not installed"

# Create storage directories
echo -e "${YELLOW}📁 Creating storage directories...${NC}"
mkdir -p storage/app/public/profile_pictures
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p public/uploads/profile_pictures
echo -e "${GREEN}✅ Storage directories created${NC}"

# Create storage symlink
echo -e "${YELLOW}🔗 Creating storage symlink...${NC}"
php artisan storage:link
echo -e "${GREEN}✅ Storage symlink created${NC}"

# Set permissions
echo -e "${YELLOW}🔒 Setting file permissions...${NC}"
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache public/uploads
chmod +x artisan
echo -e "${GREEN}✅ Permissions set${NC}"

# Run migrations
echo -e "${YELLOW}🗄️  Running database migrations...${NC}"
php artisan migrate --force

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Migration failed! Check your database configuration.${NC}"
    exit 1
fi
echo -e "${GREEN}✅ Migrations completed${NC}"

# Clear all caches
echo -e "${YELLOW}🧹 Clearing caches...${NC}"
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
echo -e "${GREEN}✅ Caches cleared${NC}"

# Cache for production
echo -e "${YELLOW}⚡ Caching for production...${NC}"
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo -e "${GREEN}✅ Production caches created${NC}"

# Create default images directory if it doesn't exist
if [ ! -d "public/images" ]; then
    echo -e "${YELLOW}🖼️  Creating images directory...${NC}"
    mkdir -p public/images
    echo -e "${GREEN}✅ Images directory created${NC}"
    echo -e "${YELLOW}⚠️  Please add default profile pictures to public/images/${NC}"
fi

echo ""
echo -e "${GREEN}✅ Deployment completed successfully!${NC}"
echo ""
echo -e "${YELLOW}📋 Next steps:${NC}"
echo "1. Configure your .env file with production settings"
echo "2. Run seeders if needed: php artisan db:seed --class=CategorySeeder"
echo "3. Test your application at your domain"
echo "4. Check that uploads directory has write permissions: chmod -R 775 public/uploads"
echo ""
echo -e "${GREEN}🎉 Happy hosting!${NC}"

