#!/bin/bash

# ServiceMan Laravel - Storage Setup Script
# Run this script after extracting files on cPanel

echo "🚀 Setting up storage directories..."

# Create storage directories
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p storage/app/public/profile_pictures
mkdir -p bootstrap/cache
mkdir -p public/uploads/profile_pictures

# Set permissions
chmod -R 775 storage bootstrap/cache public/uploads
chmod -R 755 public

# Create .gitkeep files to preserve directories
touch storage/framework/cache/data/.gitkeep
touch storage/framework/sessions/.gitkeep
touch storage/framework/views/.gitkeep
touch storage/logs/.gitkeep

echo "✅ Storage directories created!"

# Create storage symlink
if command -v php &> /dev/null; then
    php artisan storage:link 2>/dev/null || echo "⚠️  Could not create storage symlink (may already exist)"
fi

echo "✅ Setup complete!"
echo ""
echo "Next steps:"
echo "1. Configure .env file"
echo "2. Run: php artisan config:cache"
echo "3. Visit your website"

