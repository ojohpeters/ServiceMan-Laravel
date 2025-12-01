<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\ServicemanProfile;
use App\Models\ClientProfile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting Test Data Seeding...');
        $this->command->newLine();

        // Read test credentials
        $credentialsPath = base_path('test_credentials.json');
        if (!file_exists($credentialsPath)) {
            $this->command->error('❌ test_credentials.json not found!');
            return;
        }

        $credentials = json_decode(file_get_contents($credentialsPath), true);

        // Create Categories
        $this->command->info('📂 Creating Categories...');
        $categoryMap = [];
        foreach ($credentials['categories'] as $categoryData) {
            $category = Category::firstOrCreate(
                ['name' => $categoryData['name']],
                [
                    'description' => $categoryData['description'],
                    'is_active' => $categoryData['is_active'],
                ]
            );
            $categoryMap[$categoryData['name']] = $category->id;
            $this->command->line("  ✓ {$category->name}");
        }
        $this->command->newLine();

        // Create Admin
        $this->command->info('👤 Creating Admin User...');
        $adminData = $credentials['admin'];
        $admin = User::create([
            'username' => $adminData['username'],
            'email' => $adminData['email'],
            'password' => Hash::make($adminData['password']),
            'phone_number' => $adminData['phone'],
            'first_name' => $adminData['first_name'],
            'last_name' => $adminData['last_name'],
            'user_type' => 'ADMIN',
            'is_email_verified' => true,
            'email_verified_at' => now(),
            'email_verification_token' => null,
        ]);
        $this->command->line("  ✓ Admin: {$admin->email}");
        $this->command->newLine();

        // Create Clients
        $this->command->info('👥 Creating Client Users...');
        foreach ($credentials['clients'] as $clientData) {
            $client = User::create([
                'username' => $clientData['username'],
                'email' => $clientData['email'],
                'password' => Hash::make($clientData['password']),
                'phone_number' => $clientData['phone'],
                'first_name' => $clientData['first_name'],
                'last_name' => $clientData['last_name'],
                'user_type' => 'CLIENT',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'email_verification_token' => null,
            ]);

            // Create client profile
            ClientProfile::create([
                'user_id' => $client->id,
                'address' => $clientData['address'],
            ]);

            $this->command->line("  ✓ Client: {$client->full_name} ({$client->email})");
        }
        $this->command->newLine();

        // Create Servicemen
        $this->command->info('🔧 Creating Serviceman Users...');
        foreach ($credentials['servicemen'] as $servicemanData) {
            $serviceman = User::create([
                'username' => $servicemanData['username'],
                'email' => $servicemanData['email'],
                'password' => Hash::make($servicemanData['password']),
                'phone_number' => $servicemanData['phone'],
                'first_name' => $servicemanData['first_name'],
                'last_name' => $servicemanData['last_name'],
                'user_type' => 'SERVICEMAN',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'email_verification_token' => null,
            ]);

            // Create serviceman profile
            $categoryId = $categoryMap[$servicemanData['category']] ?? null;
            if ($categoryId) {
                ServicemanProfile::create([
                    'user_id' => $serviceman->id,
                    'category_id' => $categoryId,
                    'bio' => $servicemanData['bio'],
                    'experience_years' => $servicemanData['experience_years'],
                    'skills' => $servicemanData['skills'],
                    'rating' => $servicemanData['rating'],
                    'total_jobs_completed' => $servicemanData['total_jobs_completed'],
                    'hourly_rate' => $servicemanData['hourly_rate'],
                    'is_available' => $servicemanData['is_available'],
                    'phone_number' => $servicemanData['phone'],
                ]);

                $this->command->line("  ✓ Serviceman: {$serviceman->full_name} ({$servicemanData['category']}) - {$serviceman->email}");
            } else {
                $this->command->warn("  ⚠ Could not create profile for {$serviceman->full_name} - category not found");
            }
        }
        $this->command->newLine();

        // Summary
        $this->command->info('✅ Test Data Seeding Completed!');
        $this->command->newLine();
        $this->command->line('📊 Summary:');
        $this->command->line('  • Categories: ' . Category::count());
        $this->command->line('  • Admin: 1');
        $this->command->line('  • Clients: ' . User::where('user_type', 'CLIENT')->count());
        $this->command->line('  • Servicemen: ' . User::where('user_type', 'SERVICEMAN')->count());
        $this->command->line('  • Serviceman Profiles: ' . ServicemanProfile::count());
        $this->command->newLine();
        $this->command->info('🔑 Quick Login Credentials:');
        $this->command->line('  Admin:      ' . $credentials['admin']['email'] . ' / ' . $credentials['admin']['password']);
        $this->command->line('  Client:     ' . $credentials['clients'][0]['email'] . ' / ' . $credentials['clients'][0]['password']);
        $this->command->line('  Serviceman: ' . $credentials['servicemen'][0]['email'] . ' / ' . $credentials['servicemen'][0]['password']);
    }
}
