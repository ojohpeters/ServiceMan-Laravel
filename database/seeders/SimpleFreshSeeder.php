<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;
use App\Models\ServicemanProfile;
use App\Models\ClientProfile;

class SimpleFreshSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin User
        User::create([
            'username' => 'admin',
            'email' => 'admin@serviceman.com',
            'password' => Hash::make('password'),
            'user_type' => 'ADMIN',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'is_email_verified' => true,
            'email_verified_at' => now(),
        ]);

        // Create Categories
        $electrical = Category::create([
            'name' => 'Electrical Services',
            'description' => 'Professional electrical installation, repair, and maintenance services',
            'is_active' => true,
        ]);

        $plumbing = Category::create([
            'name' => 'Plumbing Services',
            'description' => 'Complete plumbing solutions including installation, repair, and maintenance',
            'is_active' => true,
        ]);

        $hvac = Category::create([
            'name' => 'HVAC Services',
            'description' => 'Heating, ventilation, and air conditioning services',
            'is_active' => true,
        ]);

        // Create Test Client
        $client = User::create([
            'username' => 'john_client',
            'email' => 'john@example.com',
            'password' => Hash::make('ClientPass123!'),
            'user_type' => 'CLIENT',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'is_email_verified' => true,
            'email_verified_at' => now(),
        ]);

        ClientProfile::create([
            'user_id' => $client->id,
            'phone_number' => '+2348012345678',
            'address' => 'Lagos, Nigeria',
            'preferred_contact_method' => 'phone',
        ]);

        // Create Test Servicemen
        $serviceman1 = User::create([
            'username' => 'alex_electrician',
            'email' => 'alex@electrician.com',
            'password' => Hash::make('ServicemanPass123!'),
            'user_type' => 'SERVICEMAN',
            'first_name' => 'Alex',
            'last_name' => 'Thompson',
            'is_email_verified' => true,
            'email_verified_at' => now(),
        ]);

        ServicemanProfile::create([
            'user_id' => $serviceman1->id,
            'category_id' => $electrical->id,
            'phone_number' => '+2348012345681',
            'bio' => 'Licensed electrician with 8 years of experience',
            'experience_years' => 8,
            'rating' => 4.9,
            'total_jobs_completed' => 156,
            'skills' => 'Electrical Installation, Wiring, Circuit Repair',
            'is_available' => true,
            'hourly_rate' => 3000,
        ]);

        $serviceman2 = User::create([
            'username' => 'robert_plumber',
            'email' => 'robert@plumber.com',
            'password' => Hash::make('ServicemanPass123!'),
            'user_type' => 'SERVICEMAN',
            'first_name' => 'Robert',
            'last_name' => 'Brown',
            'is_email_verified' => true,
            'email_verified_at' => now(),
        ]);

        ServicemanProfile::create([
            'user_id' => $serviceman2->id,
            'category_id' => $plumbing->id,
            'phone_number' => '+2348012345684',
            'bio' => 'Master plumber with extensive experience',
            'experience_years' => 10,
            'rating' => 4.6,
            'total_jobs_completed' => 187,
            'skills' => 'Pipe Installation, Drain Cleaning, Water Heater Repair',
            'is_available' => true,
            'hourly_rate' => 2500,
        ]);

        echo "✅ Fresh data seeded successfully!\n";
        echo "📧 Admin Login: admin@serviceman.com / password\n";
        echo "👥 Client Login: john@example.com / ClientPass123!\n";
        echo "🔧 Serviceman Logins: alex@electrician.com, robert@plumber.com / ServicemanPass123!\n";
    }
}
