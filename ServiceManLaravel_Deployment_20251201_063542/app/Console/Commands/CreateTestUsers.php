<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateTestUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:create-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create test users for development';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating test users...');

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

        $this->info('✅ Admin user created: admin@serviceman.com / AdminPass123!');

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

        $this->info('✅ Client user created: client@test.com / ClientPass123!');

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

        $this->info('✅ Serviceman user created: serviceman@test.com / ServicemanPass123!');

        $this->info('');
        // Update all existing users to be verified
        User::where('is_email_verified', false)->update([
            'is_email_verified' => true,
            'email_verified_at' => now()
        ]);

        $this->info('🎉 All test users created successfully!');
        $this->info('You can now login with any of these credentials on the login page.');
    }
}
