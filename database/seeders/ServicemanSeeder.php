<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;

class ServicemanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get categories
        $electrical = Category::where('name', 'Electrical')->first();
        $plumbing = Category::where('name', 'Plumbing')->first();
        $hvac = Category::where('name', 'HVAC')->first();
        $carpentry = Category::where('name', 'Carpentry')->first();
        $painting = Category::where('name', 'Painting')->first();
        $cleaning = Category::where('name', 'Cleaning')->first();
        $appliance = Category::where('name', 'Appliance Repair')->first();
        $gardening = Category::where('name', 'Gardening')->first();

        // Electrical Servicemen
        $electricalServicemen = [
            [
                'username' => 'electrical_mike',
                'email' => 'mike.electrical@serviceman.com',
                'first_name' => 'Michael',
                'last_name' => 'Johnson',
                'phone_number' => '+2348012345001',
                'bio' => 'Licensed electrical engineer with 8 years of experience in residential and commercial electrical work. Specializes in wiring, installations, and electrical repairs.',
                'skills' => 'Electrical wiring, Panel installations, Circuit repairs, LED lighting, Generator maintenance',
                'years_of_experience' => 8,
                'hourly_rate' => 7500,
                'is_available' => true,
                'category_id' => $electrical->id
            ],
            [
                'username' => 'sparky_david',
                'email' => 'david.sparky@serviceman.com',
                'first_name' => 'David',
                'last_name' => 'Williams',
                'phone_number' => '+2348012345002',
                'bio' => 'Expert electrician with 12 years of experience. Certified in industrial electrical work and home automation systems.',
                'skills' => 'Industrial electrical, Home automation, Smart home systems, Electrical troubleshooting, Safety inspections',
                'years_of_experience' => 12,
                'hourly_rate' => 9000,
                'is_available' => true,
                'category_id' => $electrical->id
            ],
            [
                'username' => 'power_sarah',
                'email' => 'sarah.power@serviceman.com',
                'first_name' => 'Sarah',
                'last_name' => 'Brown',
                'phone_number' => '+2348012345003',
                'bio' => 'Professional electrical technician specializing in emergency repairs and preventive maintenance. Available 24/7 for urgent electrical issues.',
                'skills' => 'Emergency repairs, Preventive maintenance, Electrical diagnostics, Power restoration, Safety compliance',
                'years_of_experience' => 6,
                'hourly_rate' => 6500,
                'is_available' => true,
                'category_id' => $electrical->id
            ]
        ];

        // Plumbing Servicemen
        $plumbingServicemen = [
            [
                'username' => 'pipe_master_james',
                'email' => 'james.pipe@serviceman.com',
                'first_name' => 'James',
                'last_name' => 'Davis',
                'phone_number' => '+2348012345004',
                'bio' => 'Master plumber with 15 years of experience. Expert in complex plumbing systems, water heater installations, and drain cleaning.',
                'skills' => 'Pipe installation, Water heater repair, Drain cleaning, Toilet repair, Leak detection',
                'years_of_experience' => 15,
                'hourly_rate' => 8000,
                'is_available' => true,
                'category_id' => $plumbing->id
            ],
            [
                'username' => 'aqua_lisa',
                'email' => 'lisa.aqua@serviceman.com',
                'first_name' => 'Lisa',
                'last_name' => 'Wilson',
                'phone_number' => '+2348012345005',
                'bio' => 'Licensed plumber specializing in residential plumbing and bathroom renovations. Known for clean, efficient work.',
                'skills' => 'Bathroom renovation, Pipe repair, Faucet installation, Water pressure issues, Bathroom fixtures',
                'years_of_experience' => 7,
                'hourly_rate' => 6000,
                'is_available' => true,
                'category_id' => $plumbing->id
            ],
            [
                'username' => 'flow_robert',
                'email' => 'robert.flow@serviceman.com',
                'first_name' => 'Robert',
                'last_name' => 'Miller',
                'phone_number' => '+2348012345006',
                'bio' => 'Emergency plumber available 24/7. Quick response time and expert in urgent plumbing repairs and water damage prevention.',
                'skills' => 'Emergency repairs, Water damage prevention, Burst pipe repair, Sewer line cleaning, Flood cleanup',
                'years_of_experience' => 10,
                'hourly_rate' => 8500,
                'is_available' => true,
                'category_id' => $plumbing->id
            ]
        ];

        // HVAC Servicemen
        $hvacServicemen = [
            [
                'username' => 'cool_master_steve',
                'email' => 'steve.cool@serviceman.com',
                'first_name' => 'Steve',
                'last_name' => 'Anderson',
                'phone_number' => '+2348012345007',
                'bio' => 'HVAC specialist with 11 years of experience in air conditioning, heating, and ventilation systems. EPA certified.',
                'skills' => 'AC installation, Heating repair, Ventilation systems, Refrigerant handling, HVAC maintenance',
                'years_of_experience' => 11,
                'hourly_rate' => 8500,
                'is_available' => true,
                'category_id' => $hvac->id
            ],
            [
                'username' => 'climate_mary',
                'email' => 'mary.climate@serviceman.com',
                'first_name' => 'Mary',
                'last_name' => 'Taylor',
                'phone_number' => '+2348012345008',
                'bio' => 'Certified HVAC technician specializing in energy-efficient systems and smart home climate control solutions.',
                'skills' => 'Energy-efficient systems, Smart thermostats, Duct cleaning, System optimization, Climate control',
                'years_of_experience' => 9,
                'hourly_rate' => 7500,
                'is_available' => true,
                'category_id' => $hvac->id
            ]
        ];

        // Carpentry Servicemen
        $carpentryServicemen = [
            [
                'username' => 'wood_craftsman_tom',
                'email' => 'tom.wood@serviceman.com',
                'first_name' => 'Thomas',
                'last_name' => 'Jackson',
                'phone_number' => '+2348012345009',
                'bio' => 'Master carpenter with 13 years of experience in custom furniture, home renovations, and structural repairs.',
                'skills' => 'Custom furniture, Cabinet making, Structural repairs, Flooring installation, Wood finishing',
                'years_of_experience' => 13,
                'hourly_rate' => 7000,
                'is_available' => true,
                'category_id' => $carpentry->id
            ],
            [
                'username' => 'build_betty',
                'email' => 'betty.build@serviceman.com',
                'first_name' => 'Betty',
                'last_name' => 'White',
                'phone_number' => '+2348012345010',
                'bio' => 'Professional carpenter specializing in home improvement projects, deck building, and interior finishing work.',
                'skills' => 'Deck building, Interior finishing, Trim work, Door installation, Window frames',
                'years_of_experience' => 8,
                'hourly_rate' => 6000,
                'is_available' => true,
                'category_id' => $carpentry->id
            ]
        ];

        // Painting Servicemen
        $paintingServicemen = [
            [
                'username' => 'color_master_john',
                'email' => 'john.color@serviceman.com',
                'first_name' => 'John',
                'last_name' => 'Harris',
                'phone_number' => '+2348012345011',
                'bio' => 'Professional painter with 10 years of experience in residential and commercial painting. Expert in color matching and decorative finishes.',
                'skills' => 'Interior painting, Exterior painting, Color matching, Decorative finishes, Surface preparation',
                'years_of_experience' => 10,
                'hourly_rate' => 5500,
                'is_available' => true,
                'category_id' => $painting->id
            ],
            [
                'username' => 'brush_jenny',
                'email' => 'jenny.brush@serviceman.com',
                'first_name' => 'Jennifer',
                'last_name' => 'Clark',
                'phone_number' => '+2348012345012',
                'bio' => 'Skilled painter specializing in interior design projects, murals, and specialty painting techniques.',
                'skills' => 'Interior design painting, Murals, Specialty techniques, Faux finishing, Wallpaper installation',
                'years_of_experience' => 6,
                'hourly_rate' => 5000,
                'is_available' => true,
                'category_id' => $painting->id
            ]
        ];

        // Cleaning Servicemen
        $cleaningServicemen = [
            [
                'username' => 'clean_pro_maria',
                'email' => 'maria.clean@serviceman.com',
                'first_name' => 'Maria',
                'last_name' => 'Garcia',
                'phone_number' => '+2348012345013',
                'bio' => 'Professional cleaning specialist with 8 years of experience. Certified in deep cleaning and sanitization services.',
                'skills' => 'Deep cleaning, Sanitization, Office cleaning, Post-construction cleanup, Window cleaning',
                'years_of_experience' => 8,
                'hourly_rate' => 3500,
                'is_available' => true,
                'category_id' => $cleaning->id
            ],
            [
                'username' => 'spotless_kevin',
                'email' => 'kevin.spotless@serviceman.com',
                'first_name' => 'Kevin',
                'last_name' => 'Martinez',
                'phone_number' => '+2348012345014',
                'bio' => 'Reliable cleaning professional specializing in residential cleaning and move-in/move-out services.',
                'skills' => 'Residential cleaning, Move-in/move-out, Carpet cleaning, Upholstery cleaning, Regular maintenance',
                'years_of_experience' => 5,
                'hourly_rate' => 3000,
                'is_available' => true,
                'category_id' => $cleaning->id
            ]
        ];

        // Appliance Repair Servicemen
        $applianceServicemen = [
            [
                'username' => 'fix_master_alex',
                'email' => 'alex.fix@serviceman.com',
                'first_name' => 'Alex',
                'last_name' => 'Rodriguez',
                'phone_number' => '+2348012345015',
                'bio' => 'Certified appliance repair technician with 12 years of experience. Expert in all major appliance brands and models.',
                'skills' => 'Refrigerator repair, Washing machine repair, Dishwasher repair, Oven repair, Small appliances',
                'years_of_experience' => 12,
                'hourly_rate' => 6000,
                'is_available' => true,
                'category_id' => $appliance->id
            ],
            [
                'username' => 'appliance_anna',
                'email' => 'anna.appliance@serviceman.com',
                'first_name' => 'Anna',
                'last_name' => 'Lee',
                'phone_number' => '+2348012345016',
                'bio' => 'Professional appliance technician specializing in modern smart appliances and energy-efficient equipment.',
                'skills' => 'Smart appliances, Energy-efficient equipment, Preventive maintenance, Warranty repairs, Troubleshooting',
                'years_of_experience' => 7,
                'hourly_rate' => 5500,
                'is_available' => true,
                'category_id' => $appliance->id
            ]
        ];

        // Gardening Servicemen
        $gardeningServicemen = [
            [
                'username' => 'green_thumb_carlos',
                'email' => 'carlos.green@serviceman.com',
                'first_name' => 'Carlos',
                'last_name' => 'Lopez',
                'phone_number' => '+2348012345017',
                'bio' => 'Professional landscaper and gardener with 9 years of experience. Expert in landscape design and garden maintenance.',
                'skills' => 'Landscape design, Garden maintenance, Tree trimming, Lawn care, Irrigation systems',
                'years_of_experience' => 9,
                'hourly_rate' => 4500,
                'is_available' => true,
                'category_id' => $gardening->id
            ],
            [
                'username' => 'bloom_sophia',
                'email' => 'sophia.bloom@serviceman.com',
                'first_name' => 'Sophia',
                'last_name' => 'Nguyen',
                'phone_number' => '+2348012345018',
                'bio' => 'Horticulturist specializing in plant care, garden design, and sustainable gardening practices.',
                'skills' => 'Plant care, Garden design, Sustainable gardening, Pest control, Seasonal planting',
                'years_of_experience' => 6,
                'hourly_rate' => 4000,
                'is_available' => true,
                'category_id' => $gardening->id
            ]
        ];

        // Combine all servicemen
        $allServicemen = array_merge(
            $electricalServicemen,
            $plumbingServicemen,
            $hvacServicemen,
            $carpentryServicemen,
            $paintingServicemen,
            $cleaningServicemen,
            $applianceServicemen,
            $gardeningServicemen
        );

        // Create servicemen
        foreach ($allServicemen as $servicemanData) {
            $user = User::firstOrCreate(
                ['email' => $servicemanData['email']],
                [
                    'username' => $servicemanData['username'],
                    'password' => bcrypt('ServicemanPass123!'),
                    'user_type' => 'SERVICEMAN',
                    'first_name' => $servicemanData['first_name'],
                    'last_name' => $servicemanData['last_name'],
                    'is_email_verified' => true,
                    'email_verified_at' => now()
                ]
            );

            // Create serviceman profile if it doesn't exist
            if (!$user->servicemanProfile) {
                $user->servicemanProfile()->create([
                    'phone_number' => $servicemanData['phone_number'],
                    'years_of_experience' => $servicemanData['years_of_experience'],
                    'bio' => $servicemanData['bio'],
                    'skills' => $servicemanData['skills'],
                    'is_available' => $servicemanData['is_available'],
                    'hourly_rate' => $servicemanData['hourly_rate'],
                    'category_id' => $servicemanData['category_id']
                ]);
            }
        }

        echo "Created " . count($allServicemen) . " servicemen across all categories!\n";
    }
}

