<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@serviceman.com'],
            [
                'username' => 'admin',
                'password' => bcrypt('password'),
                'user_type' => 'ADMIN',
                'first_name' => 'Admin',
                'last_name' => 'User',
                'is_email_verified' => true,
                'email_verified_at' => now()
            ]
        );

        // Create test client
        $client = User::firstOrCreate(
            ['email' => 'client@test.com'],
            [
                'username' => 'client_test',
                'password' => bcrypt('ClientPass123!'),
                'user_type' => 'CLIENT',
                'first_name' => 'Test',
                'last_name' => 'Client',
                'is_email_verified' => true,
                'email_verified_at' => now()
            ]
        );

        // Create client profile if it doesn't exist
        if (!$client->clientProfile) {
            $client->clientProfile()->create([
                'phone_number' => '+2348012345678',
                'address' => '123 Test Street, Lagos, Nigeria'
            ]);
        }

        // Create test serviceman
        $serviceman = User::firstOrCreate(
            ['email' => 'serviceman@test.com'],
            [
                'username' => 'serviceman_test',
                'password' => bcrypt('ServicemanPass123!'),
                'user_type' => 'SERVICEMAN',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'is_email_verified' => true,
                'email_verified_at' => now()
            ]
        );

        // Create serviceman profile if it doesn't exist
        if (!$serviceman->servicemanProfile) {
            $serviceman->servicemanProfile()->create([
                'phone_number' => '+2348012345679',
                'years_of_experience' => 5,
                'bio' => 'Experienced electrical and HVAC technician with expertise in repairs and maintenance.',
                'is_available' => true,
                'category_id' => 1 // Assuming Electrical category has ID 1
            ]);
        }

        echo "Users seeded successfully!\n";
    }
}