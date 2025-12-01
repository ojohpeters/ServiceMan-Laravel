<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\Hash;

class DefaultUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::create([
            'username' => 'admin',
            'email' => 'admin@serviceman.com',
            'password' => Hash::make('AdminPass123!'),
            'first_name' => 'Admin',
            'last_name' => 'User',
            'user_type' => 'ADMIN',
            'is_email_verified' => true,
            'email_verified_at' => now(),
        ]);

        // Create Client Users
        $client1 = User::create([
            'username' => 'john_doe',
            'email' => 'john@example.com',
            'password' => Hash::make('ClientPass123!'),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'user_type' => 'CLIENT',
            'is_email_verified' => true,
            'email_verified_at' => now(),
        ]);

        $client1->clientProfile()->create([
            'phone_number' => '+2348012345678',
            'address' => '123 Main Street, Lagos, Nigeria',
        ]);

        $client2 = User::create([
            'username' => 'jane_smith',
            'email' => 'jane@example.com',
            'password' => Hash::make('ClientPass123!'),
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'user_type' => 'CLIENT',
            'is_email_verified' => true,
            'email_verified_at' => now(),
        ]);

        $client2->clientProfile()->create([
            'phone_number' => '+2348023456789',
            'address' => '456 Oak Avenue, Abuja, Nigeria',
        ]);

        // Create Serviceman Users
        $categories = Category::all();
        
        if ($categories->count() > 0) {
            // Electrical Serviceman
            $electrician = User::create([
                'username' => 'mike_electrician',
                'email' => 'mike@electrician.com',
                'password' => Hash::make('ServicemanPass123!'),
                'first_name' => 'Mike',
                'last_name' => 'Johnson',
                'user_type' => 'SERVICEMAN',
                'is_email_verified' => true,
                'email_verified_at' => now(),
            ]);

            $electrician->servicemanProfile()->create([
                'category_id' => $categories->where('name', 'like', '%electrical%')->first()?->id ?? $categories->first()->id,
                'phone_number' => '+2348034567890',
                'bio' => 'Professional electrician with 8 years of experience in residential and commercial electrical work.',
                'experience_years' => 8,
                'skills' => 'Electrical wiring, Circuit repair, Installation, Safety inspections, Power outage fixes',
                'is_available' => true,
                'hourly_rate' => 5000,
            ]);

            // Plumbing Serviceman
            $plumber = User::create([
                'username' => 'david_plumber',
                'email' => 'david@plumber.com',
                'password' => Hash::make('ServicemanPass123!'),
                'first_name' => 'David',
                'last_name' => 'Wilson',
                'user_type' => 'SERVICEMAN',
                'is_email_verified' => true,
                'email_verified_at' => now(),
            ]);

            $plumber->servicemanProfile()->create([
                'category_id' => $categories->where('name', 'like', '%plumbing%')->first()?->id ?? $categories->first()->id,
                'phone_number' => '+2348045678901',
                'bio' => 'Licensed plumber specializing in pipe repairs, drain cleaning, and water heater services.',
                'experience_years' => 6,
                'skills' => 'Pipe repairs, Drain cleaning, Faucet installation, Water heater service, Leak detection',
                'is_available' => true,
                'hourly_rate' => 4500,
            ]);

            // HVAC Serviceman
            $hvacTech = User::create([
                'username' => 'robert_hvac',
                'email' => 'robert@hvac.com',
                'password' => Hash::make('ServicemanPass123!'),
                'first_name' => 'Robert',
                'last_name' => 'Brown',
                'user_type' => 'SERVICEMAN',
                'is_email_verified' => true,
                'email_verified_at' => now(),
            ]);

            $hvacTech->servicemanProfile()->create([
                'category_id' => $categories->where('name', 'like', '%hvac%')->first()?->id ?? $categories->first()->id,
                'phone_number' => '+2348056789012',
                'bio' => 'HVAC specialist with expertise in air conditioning, heating, and ventilation systems.',
                'experience_years' => 10,
                'skills' => 'AC installation, Heating repair, Duct cleaning, Maintenance, System diagnostics',
                'is_available' => true,
                'hourly_rate' => 6000,
            ]);

            // General Repair Serviceman
            $handyman = User::create([
                'username' => 'alex_handyman',
                'email' => 'alex@handyman.com',
                'password' => Hash::make('ServicemanPass123!'),
                'first_name' => 'Alex',
                'last_name' => 'Davis',
                'user_type' => 'SERVICEMAN',
                'is_email_verified' => true,
                'email_verified_at' => now(),
            ]);

            $handyman->servicemanProfile()->create([
                'category_id' => $categories->where('name', 'like', '%general%')->first()?->id ?? $categories->first()->id,
                'phone_number' => '+2348067890123',
                'bio' => 'Versatile handyman offering a wide range of home repair and maintenance services.',
                'experience_years' => 5,
                'skills' => 'General repairs, Furniture assembly, Painting, Minor carpentry, Home maintenance',
                'is_available' => true,
                'hourly_rate' => 3500,
            ]);
        }

        $this->command->info('Default users created successfully!');
        $this->command->info('');
        $this->command->info('=== LOGIN CREDENTIALS ===');
        $this->command->info('');
        $this->command->info('ADMIN USER:');
        $this->command->info('Email: admin@serviceman.com');
        $this->command->info('Password: AdminPass123!');
        $this->command->info('');
        $this->command->info('CLIENT USERS:');
        $this->command->info('Email: john@example.com | Password: ClientPass123!');
        $this->command->info('Email: jane@example.com | Password: ClientPass123!');
        $this->command->info('');
        $this->command->info('SERVICEMAN USERS:');
        $this->command->info('Email: mike@electrician.com | Password: ServicemanPass123!');
        $this->command->info('Email: david@plumber.com | Password: ServicemanPass123!');
        $this->command->info('Email: robert@hvac.com | Password: ServicemanPass123!');
        $this->command->info('Email: alex@handyman.com | Password: ServicemanPass123!');
        $this->command->info('');
    }
}
