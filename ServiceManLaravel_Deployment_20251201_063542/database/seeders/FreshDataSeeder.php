<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;
use App\Models\ServicemanProfile;
use App\Models\ClientProfile;
use App\Models\Rating;

class FreshDataSeeder extends Seeder
{
    public function run(): void
    {
        // Clean existing data
        $this->command->info('🧹 Cleaning existing data...');
        
        // Delete in correct order to avoid foreign key constraints
        Rating::truncate();
        ServicemanProfile::truncate();
        ClientProfile::truncate();
        User::where('user_type', '!=', 'ADMIN')->delete();
        Category::truncate();

        // Create Categories
        $this->command->info('📂 Creating categories...');
        $categories = [
            [
                'name' => 'Electrical Services',
                'description' => 'Professional electrical installation, repair, and maintenance services',
                'is_active' => true,
            ],
            [
                'name' => 'Plumbing Services',
                'description' => 'Complete plumbing solutions including installation, repair, and maintenance',
                'is_active' => true,
            ],
            [
                'name' => 'HVAC Services',
                'description' => 'Heating, ventilation, and air conditioning services',
                'is_active' => true,
            ],
            [
                'name' => 'Handyman Services',
                'description' => 'General repair and maintenance services for your home',
                'is_active' => true,
            ],
            [
                'name' => 'Carpentry Services',
                'description' => 'Custom woodwork, furniture repair, and carpentry solutions',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }

        // Create Admin User
        $this->command->info('👑 Creating admin user...');
        $admin = User::create([
            'username' => 'admin',
            'email' => 'admin@serviceman.com',
            'password' => Hash::make('password'),
            'user_type' => 'ADMIN',
            'first_name' => 'Admin',
            'last_name' => 'User',
            'is_email_verified' => true,
            'email_verified_at' => now(),
        ]);

        // Create Test Clients
        $this->command->info('👥 Creating test clients...');
        $clients = [
            [
                'username' => 'john_client',
                'email' => 'john@example.com',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'phone' => '+2348012345678',
            ],
            [
                'username' => 'jane_client',
                'email' => 'jane@example.com',
                'first_name' => 'Jane',
                'last_name' => 'Smith',
                'phone' => '+2348012345679',
            ],
            [
                'username' => 'mike_client',
                'email' => 'mike@example.com',
                'first_name' => 'Mike',
                'last_name' => 'Johnson',
                'phone' => '+2348012345680',
            ],
        ];

        foreach ($clients as $clientData) {
            $user = User::create([
                'username' => $clientData['username'],
                'email' => $clientData['email'],
                'password' => Hash::make('ClientPass123!'),
                'user_type' => 'CLIENT',
                'first_name' => $clientData['first_name'],
                'last_name' => $clientData['last_name'],
                'is_email_verified' => true,
                'email_verified_at' => now(),
            ]);

            ClientProfile::create([
                'user_id' => $user->id,
                'phone_number' => $clientData['phone'],
                'address' => 'Lagos, Nigeria',
                'preferred_contact_method' => 'phone',
            ]);
        }

        // Create Test Servicemen with various ratings
        $this->command->info('🔧 Creating test servicemen...');
        $servicemen = [
            // Electrical Services
            [
                'username' => 'alex_electrician',
                'email' => 'alex@electrician.com',
                'first_name' => 'Alex',
                'last_name' => 'Thompson',
                'phone' => '+2348012345681',
                'category' => 'Electrical Services',
                'bio' => 'Licensed electrician with 8 years of experience in residential and commercial electrical work.',
                'experience_years' => 8,
                'rating' => 4.9,
                'total_jobs_completed' => 156,
                'skills' => 'Electrical Installation, Wiring, Circuit Repair, Panel Upgrades',
            ],
            [
                'username' => 'david_electrician',
                'email' => 'david@electrician.com',
                'first_name' => 'David',
                'last_name' => 'Wilson',
                'phone' => '+2348012345682',
                'category' => 'Electrical Services',
                'bio' => 'Expert electrician specializing in smart home installations and energy-efficient solutions.',
                'experience_years' => 6,
                'rating' => 4.7,
                'total_jobs_completed' => 89,
                'skills' => 'Smart Home Installation, Energy Audits, LED Lighting, Generator Installation',
            ],
            [
                'username' => 'betty_electrician',
                'email' => 'betty@electrician.com',
                'first_name' => 'Betty',
                'last_name' => 'White',
                'phone' => '+2348012345683',
                'category' => 'Electrical Services',
                'bio' => 'Professional electrical contractor with focus on safety and quality workmanship.',
                'experience_years' => 12,
                'rating' => 4.8,
                'total_jobs_completed' => 234,
                'skills' => 'Electrical Safety, Code Compliance, Emergency Repairs, Commercial Electrical',
            ],

            // Plumbing Services
            [
                'username' => 'robert_plumber',
                'email' => 'robert@plumber.com',
                'first_name' => 'Robert',
                'last_name' => 'Brown',
                'phone' => '+2348012345684',
                'category' => 'Plumbing Services',
                'bio' => 'Master plumber with extensive experience in residential and commercial plumbing systems.',
                'experience_years' => 10,
                'rating' => 4.6,
                'total_jobs_completed' => 187,
                'skills' => 'Pipe Installation, Drain Cleaning, Water Heater Repair, Bathroom Remodeling',
            ],
            [
                'username' => 'sarah_plumber',
                'email' => 'sarah@plumber.com',
                'first_name' => 'Sarah',
                'last_name' => 'Davis',
                'phone' => '+2348012345685',
                'category' => 'Plumbing Services',
                'bio' => 'Certified plumber specializing in modern plumbing solutions and water conservation.',
                'experience_years' => 7,
                'rating' => 4.9,
                'total_jobs_completed' => 123,
                'skills' => 'Water Conservation, Modern Fixtures, Leak Detection, Pipe Replacement',
            ],

            // HVAC Services
            [
                'username' => 'michael_hvac',
                'email' => 'michael@hvac.com',
                'first_name' => 'Michael',
                'last_name' => 'Garcia',
                'phone' => '+2348012345686',
                'category' => 'HVAC Services',
                'bio' => 'HVAC technician with expertise in installation, maintenance, and repair of heating and cooling systems.',
                'experience_years' => 9,
                'rating' => 4.5,
                'total_jobs_completed' => 145,
                'skills' => 'AC Installation, Heating Systems, Ductwork, Energy Efficiency',
            ],
            [
                'username' => 'lisa_hvac',
                'email' => 'lisa@hvac.com',
                'first_name' => 'Lisa',
                'last_name' => 'Martinez',
                'phone' => '+2348012345687',
                'category' => 'HVAC Services',
                'bio' => 'Professional HVAC specialist focused on indoor air quality and energy-efficient solutions.',
                'experience_years' => 5,
                'rating' => 4.8,
                'total_jobs_completed' => 78,
                'skills' => 'Air Quality, HVAC Maintenance, Thermostat Installation, System Optimization',
            ],

            // Handyman Services
            [
                'username' => 'james_handyman',
                'email' => 'james@handyman.com',
                'first_name' => 'James',
                'last_name' => 'Anderson',
                'phone' => '+2348012345688',
                'category' => 'Handyman Services',
                'bio' => 'Versatile handyman offering comprehensive home repair and maintenance services.',
                'experience_years' => 6,
                'rating' => 4.4,
                'total_jobs_completed' => 98,
                'skills' => 'General Repairs, Furniture Assembly, Painting, Minor Renovations',
            ],
            [
                'username' => 'emma_handyman',
                'email' => 'emma@handyman.com',
                'first_name' => 'Emma',
                'last_name' => 'Taylor',
                'phone' => '+2348012345689',
                'category' => 'Handyman Services',
                'bio' => 'Skilled handywoman specializing in home improvement and repair projects.',
                'experience_years' => 4,
                'rating' => 4.7,
                'total_jobs_completed' => 67,
                'skills' => 'Home Improvement, Furniture Repair, Wall Mounting, Small Projects',
            ],

            // Carpentry Services
            [
                'username' => 'william_carpenter',
                'email' => 'william@carpenter.com',
                'first_name' => 'William',
                'last_name' => 'Clark',
                'phone' => '+2348012345690',
                'category' => 'Carpentry Services',
                'bio' => 'Master carpenter with expertise in custom woodwork and furniture restoration.',
                'experience_years' => 15,
                'rating' => 4.9,
                'total_jobs_completed' => 201,
                'skills' => 'Custom Furniture, Cabinet Making, Wood Restoration, Precision Carpentry',
            ],
            [
                'username' => 'olivia_carpenter',
                'email' => 'olivia@carpenter.com',
                'first_name' => 'Olivia',
                'last_name' => 'Lewis',
                'phone' => '+2348012345691',
                'category' => 'Carpentry Services',
                'bio' => 'Professional carpenter specializing in modern and traditional woodworking techniques.',
                'experience_years' => 8,
                'rating' => 4.6,
                'total_jobs_completed' => 112,
                'skills' => 'Modern Carpentry, Furniture Design, Wood Finishing, Custom Shelving',
            ],
        ];

        foreach ($servicemen as $servicemanData) {
            $user = User::create([
                'username' => $servicemanData['username'],
                'email' => $servicemanData['email'],
                'password' => Hash::make('ServicemanPass123!'),
                'user_type' => 'SERVICEMAN',
                'first_name' => $servicemanData['first_name'],
                'last_name' => $servicemanData['last_name'],
                'is_email_verified' => true,
                'email_verified_at' => now(),
            ]);

            $category = Category::where('name', $servicemanData['category'])->first();

            ServicemanProfile::create([
                'user_id' => $user->id,
                'category_id' => $category->id,
                'phone_number' => $servicemanData['phone'],
                'bio' => $servicemanData['bio'],
                'experience_years' => $servicemanData['experience_years'],
                'rating' => $servicemanData['rating'],
                'total_jobs_completed' => $servicemanData['total_jobs_completed'],
                'skills' => $servicemanData['skills'],
                'is_available' => true,
                'hourly_rate' => rand(2000, 5000), // Random hourly rate between ₦2,000 - ₦5,000
            ]);

            // Create some sample ratings for each serviceman
            $this->createSampleRatings($user, $servicemanData['rating'], $servicemanData['total_jobs_completed']);
        }

        $this->command->info('✅ Fresh data seeded successfully!');
        $this->command->info('📧 Admin Login: admin@serviceman.com / password');
        $this->command->info('👥 Client Logins: john@example.com, jane@example.com, mike@example.com / ClientPass123!');
        $this->command->info('🔧 Serviceman Logins: Various emails / ServicemanPass123!');
    }

    private function createSampleRatings($serviceman, $averageRating, $totalJobs)
    {
        $clients = User::where('user_type', 'CLIENT')->get();
        
        // Create ratings to match the average rating
        $ratingsToCreate = min($totalJobs, 20); // Limit to 20 ratings max
        
        for ($i = 0; $i < $ratingsToCreate; $i++) {
            $client = $clients->random();
            
            // Generate rating close to the average
            $rating = $this->generateRatingNearAverage($averageRating);
            
            Rating::create([
                'service_request_id' => null, // We'll leave this null for now
                'serviceman_id' => $serviceman->id,
                'client_id' => $client->id,
                'rating' => $rating,
                'comment' => $this->generateRandomComment($rating),
            ]);
        }
    }

    private function generateRatingNearAverage($averageRating)
    {
        // Generate rating within 0.5 of the average
        $min = max(1, $averageRating - 0.5);
        $max = min(5, $averageRating + 0.5);
        
        return round(rand($min * 10, $max * 10) / 10, 1);
    }

    private function generateRandomComment($rating)
    {
        $comments = [
            5 => [
                'Excellent work! Highly recommended.',
                'Outstanding service and professionalism.',
                'Perfect job, will definitely hire again.',
                'Amazing work quality and attention to detail.',
            ],
            4 => [
                'Very good work, satisfied with the service.',
                'Professional and efficient service.',
                'Good quality work, would recommend.',
                'Solid work, minor issues but overall satisfied.',
            ],
            3 => [
                'Average work, met expectations.',
                'Decent service, room for improvement.',
                'Okay work, nothing exceptional.',
                'Satisfactory service, could be better.',
            ],
            2 => [
                'Below expectations, some issues.',
                'Not great, had some problems.',
                'Disappointing work quality.',
                'Several issues, not recommended.',
            ],
            1 => [
                'Terrible service, avoid at all costs.',
                'Worst experience, complete waste of money.',
                'Unprofessional and poor work quality.',
                'Extremely disappointed, would not recommend.',
            ],
        ];

        $ratingKey = floor($rating);
        $availableComments = $comments[$ratingKey] ?? $comments[3];
        
        return $availableComments[array_rand($availableComments)];
    }
}