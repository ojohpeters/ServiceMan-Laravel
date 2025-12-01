#!/bin/bash

# Quick Cache Clearing Script for cPanel
# Run this in cPanel Terminal

echo "🧹 Clearing all Laravel caches..."
echo ""

cd ~/serviceman.sekimbi.com

echo "1️⃣  Clearing optimized caches..."
php artisan optimize:clear

echo "2️⃣  Clearing config cache..."
php artisan config:clear

echo "3️⃣  Clearing route cache..."
php artisan route:clear

echo "4️⃣  Clearing view cache..."
php artisan view:clear

echo "5️⃣  Clearing application cache..."
php artisan cache:clear

echo "6️⃣  Clearing compiled files..."
php artisan clear-compiled

echo "7️⃣  Rebuilding optimized caches..."
php artisan optimize

echo ""
echo "✅ All caches cleared and rebuilt!"
echo "🌐 Now clear your browser cache (Ctrl+Shift+Delete) or use Incognito mode"

