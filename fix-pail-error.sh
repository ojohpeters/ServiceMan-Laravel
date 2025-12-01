#!/bin/bash

# Quick Fix Script for Laravel Pail Error on cPanel
# Run this script on your server via Terminal

echo "🔧 Fixing Laravel Pail Service Provider Error..."
echo ""

cd ~/serviceman.sekimbi.com

# Delete cached service provider files
echo "📁 Removing cached service provider files..."
rm -f bootstrap/cache/packages.php
rm -f bootstrap/cache/services.php
rm -f bootstrap/cache/config.php
rm -f bootstrap/cache/routes-v7.php

echo "✅ Cached files deleted"

# Clear all caches
echo "🧹 Clearing Laravel caches..."
php artisan optimize:clear

# Regenerate service providers
echo "🔄 Regenerating service providers..."
php artisan package:discover

# Rebuild production caches
echo "⚡ Rebuilding production caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "✅ Fix complete! Your website should work now."
echo "🌐 Visit: https://serviceman.sekimbi.com"

