<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Use TestDataSeeder for clean, documented test data
        // Comment out old seeders to avoid duplicates
        $this->call([
            TestDataSeeder::class,
            // CategorySeeder::class,
            // AdminUserSeeder::class,
            // ServicemanSeeder::class,
        ]);
    }
}