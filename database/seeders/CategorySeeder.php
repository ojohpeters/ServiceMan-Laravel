<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electrical',
                'description' => 'Electrical services and repairs including wiring, installations, and electrical maintenance',
                'icon_url' => 'https://example.com/icons/electrical.png',
                'is_active' => true
            ],
            [
                'name' => 'Plumbing',
                'description' => 'Plumbing services including pipe repairs, installations, and maintenance',
                'icon_url' => 'https://example.com/icons/plumbing.png',
                'is_active' => true
            ],
            [
                'name' => 'HVAC',
                'description' => 'Heating, Ventilation, and Air Conditioning services',
                'icon_url' => 'https://example.com/icons/hvac.png',
                'is_active' => true
            ],
            [
                'name' => 'Carpentry',
                'description' => 'Carpentry and woodworking services including furniture repair and installation',
                'icon_url' => 'https://example.com/icons/carpentry.png',
                'is_active' => true
            ],
            [
                'name' => 'Painting',
                'description' => 'Interior and exterior painting services',
                'icon_url' => 'https://example.com/icons/painting.png',
                'is_active' => true
            ],
            [
                'name' => 'Cleaning',
                'description' => 'House and office cleaning services',
                'icon_url' => 'https://example.com/icons/cleaning.png',
                'is_active' => true
            ],
            [
                'name' => 'Appliance Repair',
                'description' => 'Home appliance repair and maintenance services',
                'icon_url' => 'https://example.com/icons/appliance.png',
                'is_active' => true
            ],
            [
                'name' => 'Gardening',
                'description' => 'Landscaping and gardening services',
                'icon_url' => 'https://example.com/icons/gardening.png',
                'is_active' => true
            ]
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}