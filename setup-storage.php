<?php

/**
 * ServiceMan Laravel - Storage Setup Script
 * 
 * Run this file directly via browser or command line to create required directories.
 * URL: https://serviceman.sekimbi.com/setup-storage.php
 * Or via SSH: php setup-storage.php
 */

$baseDir = __DIR__;

$directories = [
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
    'storage/app/public/profile_pictures',
    'bootstrap/cache',
    'public/uploads/profile_pictures',
];

echo "<pre>";
echo "🚀 Setting up storage directories...\n\n";

$created = 0;
foreach ($directories as $dir) {
    $path = $baseDir . '/' . $dir;
    if (!is_dir($path)) {
        if (mkdir($path, 0755, true)) {
            echo "✅ Created: $dir\n";
            $created++;
        } else {
            echo "❌ Failed to create: $dir\n";
        }
    } else {
        echo "✓ Already exists: $dir\n";
    }
}

// Create .gitkeep files
$gitkeepDirs = [
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
    'storage/logs',
];

foreach ($gitkeepDirs as $dir) {
    $gitkeep = $baseDir . '/' . $dir . '/.gitkeep';
    if (!file_exists($gitkeep)) {
        file_put_contents($gitkeep, '');
        echo "✅ Created .gitkeep in: $dir\n";
    }
}

echo "\n✅ Setup complete! Created $created new directories.\n\n";

// Try to set permissions (may need to be done manually)
echo "📝 Next steps:\n";
echo "1. Set permissions: chmod -R 775 storage bootstrap/cache public/uploads\n";
echo "2. Create storage symlink: php artisan storage:link\n";
echo "3. Clear cache: php artisan config:clear\n";
echo "4. Visit your website\n";

echo "</pre>";

