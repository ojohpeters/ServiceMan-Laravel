<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ServicemanProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SkillsController extends Controller
{
    /**
     * Get common skills from existing servicemen, organized by category
     */
    public function getCommonSkills(Request $request)
    {
        $categoryId = $request->query('category_id');
        
        // Get all skills from servicemen in database
        $allSkills = ServicemanProfile::whereNotNull('skills')
            ->where('skills', '!=', '')
            ->when($categoryId, function($query) use ($categoryId) {
                return $query->where('category_id', $categoryId);
            })
            ->pluck('skills')
            ->flatMap(function($skillsString) {
                return array_map('trim', explode(',', $skillsString));
            })
            ->filter()
            ->countBy()
            ->sortDesc()
            ->take(20)
            ->keys()
            ->values();

        // Predefined common skills
        $commonSkills = [
            'Customer Service', 
            'Time Management', 
            'Problem Solving', 
            'Communication',
            'Attention to Detail', 
            'Safety Compliance', 
            'Quality Assurance',
            'Emergency Response'
        ];

        // Category-specific skills
        $categorySkills = $this->getCategorySpecificSkills($categoryId);

        return response()->json([
            'common' => $commonSkills,
            'category_specific' => $categorySkills,
            'from_database' => $allSkills->toArray()
        ]);
    }

    private function getCategorySpecificSkills($categoryId)
    {
        $skillsMap = [
            '1' => [ // Electrical
                'Wiring & Rewiring', 'Circuit Installation', 'Lighting Systems', 
                'Electrical Troubleshooting', 'Panel Upgrades', 'Generator Installation',
                'Smart Home Wiring', 'Solar Installation', 'Emergency Electrical Repairs'
            ],
            '2' => [ // Plumbing
                'Pipe Installation', 'Leak Repair', 'Drain Cleaning', 
                'Water Heater Service', 'Bathroom Fitting', 'Kitchen Plumbing',
                'Sewer Line Work', 'Gas Line Installation', 'Emergency Plumbing'
            ],
            '3' => [ // HVAC
                'AC Installation', 'AC Repair', 'Heating Systems', 
                'Ventilation', 'Duct Cleaning', 'Thermostat Installation',
                'Refrigerant Charging', 'Preventive Maintenance', 'System Diagnostics'
            ],
            '4' => [ // Carpentry
                'Furniture Making', 'Door & Window Installation', 'Cabinet Installation',
                'Deck Building', 'Trim Work', 'Custom Woodwork',
                'Furniture Repair', 'Wood Finishing', 'Laminate Flooring'
            ],
            '5' => [ // Painting
                'Interior Painting', 'Exterior Painting', 'Texture Application',
                'Wallpaper Installation', 'Color Consultation', 'Surface Preparation',
                'Decorative Finishes', 'Spray Painting'
            ]
        ];

        return $skillsMap[$categoryId] ?? [];
    }
}
