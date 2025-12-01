<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
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
                'password' => Hash::make('AdminPass123!'),
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
                'password' => Hash::make('ClientPass123!'),
                'user_type' => 'CLIENT',
                'first_name' => 'Test',
                'last_name' => 'Client',
                'is_email_verified' => true,
                'email_verified_at' => now()
            ]
        );

        // Create client profile
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
                'password' => Hash::make('ServicemanPass123!'),
                'user_type' => 'SERVICEMAN',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'is_email_verified' => true,
                'email_verified_at' => now()
            ]
        );

        // Create serviceman profile
        if (!$serviceman->servicemanProfile) {
            $serviceman->servicemanProfile()->create([
                'phone_number' => '+2348012345679',
                'experience_years' => '3-5',
                'skills' => 'Electrical repairs, HVAC maintenance, Plumbing',
                'is_available' => true,
                'hourly_rate' => 5000
            ]);
        }

        echo "Test users created successfully!\n";
        echo "Admin: admin@serviceman.com / AdminPass123!\n";
        echo "Client: client@test.com / ClientPass123!\n";
        echo "Serviceman: serviceman@test.com / ServicemanPass123!\n";
    }
}
