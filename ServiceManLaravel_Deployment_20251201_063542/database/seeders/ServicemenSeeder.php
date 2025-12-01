<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Rating;
use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class ServicemenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all();
        $clients = User::where('user_type', 'CLIENT')->get();
        
        if ($categories->isEmpty() || $clients->isEmpty()) {
            $this->command->info('No categories or clients found. Please run other seeders first.');
            return;
        }

        $servicemenData = [
            // Electrical Services
            [
                'first_name' => 'James',
                'last_name' => 'Electrical',
                'username' => 'james_electrical',
                'email' => 'james@electrician.com',
                'category' => 'Electrical',
                'bio' => 'Licensed electrician with 15+ years experience in residential and commercial electrical work.',
                'experience_years' => 15,
                'skills' => 'Wiring, Circuit repair, Installation, Electrical troubleshooting',
                'rating' => 4.9,
                'total_jobs' => 245,
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Volt',
                'username' => 'sarah_volt',
                'email' => 'sarah@electrician.com',
                'category' => 'Electrical',
                'bio' => 'Professional electrician specializing in smart home installations and energy-efficient solutions.',
                'experience_years' => 8,
                'skills' => 'Smart home installation, Energy audits, LED lighting, Panel upgrades',
                'rating' => 4.7,
                'total_jobs' => 156,
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Power',
                'username' => 'david_power',
                'email' => 'david@electrician.com',
                'category' => 'Electrical',
                'bio' => 'Industrial electrician with expertise in high-voltage systems and emergency repairs.',
                'experience_years' => 12,
                'skills' => 'Industrial wiring, Emergency repairs, Generator installation, High-voltage work',
                'rating' => 4.5,
                'total_jobs' => 189,
            ],

            // Plumbing Services
            [
                'first_name' => 'Maria',
                'last_name' => 'Plumber',
                'username' => 'maria_plumber',
                'email' => 'maria@plumber.com',
                'category' => 'Plumbing',
                'bio' => 'Master plumber with 20+ years experience in residential and commercial plumbing.',
                'experience_years' => 20,
                'skills' => 'Pipe repair, Drain cleaning, Water heater installation, Bathroom remodeling',
                'rating' => 4.8,
                'total_jobs' => 312,
            ],
            [
                'first_name' => 'John',
                'last_name' => 'Pipe',
                'username' => 'john_pipe',
                'email' => 'john@plumber.com',
                'category' => 'Plumbing',
                'bio' => 'Emergency plumber available 24/7 for urgent plumbing issues.',
                'experience_years' => 10,
                'skills' => 'Emergency repairs, Leak detection, Pipe replacement, Water pressure issues',
                'rating' => 4.6,
                'total_jobs' => 203,
            ],
            [
                'first_name' => 'Lisa',
                'last_name' => 'Water',
                'username' => 'lisa_water',
                'email' => 'lisa@plumber.com',
                'category' => 'Plumbing',
                'bio' => 'Specialized in bathroom and kitchen plumbing installations and repairs.',
                'experience_years' => 6,
                'skills' => 'Bathroom remodeling, Kitchen plumbing, Fixture installation, Water filtration',
                'rating' => 4.4,
                'total_jobs' => 98,
            ],

            // HVAC Services
            [
                'first_name' => 'Robert',
                'last_name' => 'HVAC',
                'username' => 'robert_hvac',
                'email' => 'robert@hvac.com',
                'category' => 'HVAC',
                'bio' => 'Certified HVAC technician with expertise in heating, ventilation, and air conditioning systems.',
                'experience_years' => 18,
                'skills' => 'AC repair, Heating systems, Duct cleaning, Thermostat installation',
                'rating' => 4.9,
                'total_jobs' => 267,
            ],
            [
                'first_name' => 'Jennifer',
                'last_name' => 'Cool',
                'username' => 'jennifer_cool',
                'email' => 'jennifer@hvac.com',
                'category' => 'HVAC',
                'bio' => 'Energy-efficient HVAC specialist focused on modern cooling and heating solutions.',
                'experience_years' => 9,
                'skills' => 'Energy-efficient systems, Smart thermostats, Ductwork, Indoor air quality',
                'rating' => 4.7,
                'total_jobs' => 134,
            ],

            // General Maintenance
            [
                'first_name' => 'Michael',
                'last_name' => 'Handyman',
                'username' => 'michael_handyman',
                'email' => 'michael@handyman.com',
                'category' => 'General Maintenance',
                'bio' => 'Versatile handyman with skills in multiple trades and home improvement projects.',
                'experience_years' => 14,
                'skills' => 'General repairs, Painting, Carpentry, Appliance repair, Furniture assembly',
                'rating' => 4.5,
                'total_jobs' => 178,
            ],
            [
                'first_name' => 'Amanda',
                'last_name' => 'Fix',
                'username' => 'amanda_fix',
                'email' => 'amanda@handyman.com',
                'category' => 'General Maintenance',
                'bio' => 'Professional handywoman specializing in home maintenance and small repairs.',
                'experience_years' => 7,
                'skills' => 'Home maintenance, Small repairs, Cleaning, Organization, Safety checks',
                'rating' => 4.3,
                'total_jobs' => 89,
            ],
        ];

        foreach ($servicemenData as $data) {
            $category = $categories->where('name', $data['category'])->first();
            if (!$category) continue;

            // Create serviceman user
            $serviceman = User::create([
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make('ServicemanPass123!'),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'user_type' => 'SERVICEMAN',
                'is_email_verified' => true,
                'email_verified_at' => now(),
            ]);

            // Create serviceman profile
            $serviceman->servicemanProfile()->create([
                'category_id' => $category->id,
                'phone_number' => '+234' . rand(800000000, 999999999),
                'bio' => $data['bio'],
                'experience_years' => $data['experience_years'],
                'skills' => $data['skills'],
                'is_available' => true,
                'rating' => $data['rating'],
                'total_jobs_completed' => $data['total_jobs'],
            ]);

            // Create service requests and ratings for this serviceman
            $this->createServiceHistory($serviceman, $clients, $category, $data['total_jobs'], $data['rating']);

            $this->command->info("Created serviceman: {$serviceman->full_name} ({$data['category']})");
        }

        $this->command->info('Servicemen with ratings seeded successfully!');
    }

    private function createServiceHistory($serviceman, $clients, $category, $totalJobs, $averageRating)
    {
        // Create completed service requests
        for ($i = 0; $i < $totalJobs; $i++) {
            $client = $clients->random();
            $createdAt = Carbon::now()->subDays(rand(1, 365));

            $serviceRequest = ServiceRequest::create([
                'client_id' => $client->id,
                'serviceman_id' => $serviceman->id,
                'category_id' => $category->id,
                'booking_date' => $createdAt->copy()->subDays(rand(1, 7)),
                'is_emergency' => rand(0, 1) == 1,
                'auto_flagged_emergency' => false,
                'status' => 'COMPLETED',
                'initial_booking_fee' => rand(0, 1) == 1 ? 5000 : 2000,
                'serviceman_estimated_cost' => rand(5000, 50000),
                'admin_markup_percentage' => 10,
                'final_cost' => rand(5500, 55000),
                'client_address' => 'Sample address ' . rand(1, 100),
                'service_description' => 'Sample service description ' . rand(1, 100),
                'inspection_completed_at' => $createdAt->copy()->addHours(rand(1, 24)),
                'work_completed_at' => $createdAt->copy()->addDays(rand(1, 3)),
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addDays(rand(1, 3)),
            ]);

            // Create rating with some variation around the average
            $ratingVariation = rand(-20, 20) / 100; // ±0.2 variation
            $rating = max(1, min(5, $averageRating + $ratingVariation));
            
            Rating::create([
                'service_request_id' => $serviceRequest->id,
                'serviceman_id' => $serviceman->id,
                'client_id' => $client->id,
                'rating' => round($rating, 1),
                'review' => $this->getRandomReview(round($rating, 1)),
                'created_at' => $createdAt->copy()->addDays(rand(1, 3)),
            ]);
        }
    }

    private function getRandomReview($rating)
    {
        $reviews = [
            5 => [
                'Excellent work! Very professional and completed the job perfectly.',
                'Outstanding service! Highly recommended to anyone.',
                'Fantastic job! The serviceman was punctual and skilled.',
                'Perfect! Could not have asked for better service.',
                'Amazing work! Will definitely use this serviceman again.',
            ],
            4 => [
                'Very good service. Professional and efficient.',
                'Good work overall. Minor issues but completed well.',
                'Satisfied with the service. Would recommend.',
                'Quality work done in a timely manner.',
                'Good service with room for minor improvements.',
            ],
            3 => [
                'Average service. Job was completed but could be better.',
                'Okay work. Met expectations but nothing exceptional.',
                'Satisfactory service. Job done as requested.',
                'Standard service. Nothing to complain about.',
                'Fair work. Basic expectations were met.',
            ],
            2 => [
                'Below average service. Some issues with quality.',
                'Not very satisfied. Could have been better.',
                'Disappointing service. Expected more.',
                'Poor communication and service quality.',
                'Not recommended. Multiple issues encountered.',
            ],
            1 => [
                'Terrible service. Multiple problems and delays.',
                'Very poor work quality. Would not recommend.',
                'Awful experience. Service was unacceptable.',
                'Worst service ever. Complete waste of time.',
                'Horrible work. Major issues and poor communication.',
            ],
        ];

        $ratingKey = floor($rating);
        $availableReviews = $reviews[$ratingKey] ?? $reviews[3];
        return $availableReviews[array_rand($availableReviews)];
    }
}
