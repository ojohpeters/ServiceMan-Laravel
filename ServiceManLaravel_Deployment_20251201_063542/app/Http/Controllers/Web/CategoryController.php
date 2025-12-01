<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('servicemen')->get();
        return view('services.index', compact('categories'));
    }

    public function show(Category $category)
    {
        $servicemen = User::where('user_type', 'SERVICEMAN')
            ->whereHas('servicemanProfile', function($query) use ($category) {
                $query->where('category_id', $category->id)
                      ->where('is_available', true);
            })
            ->with(['servicemanProfile', 'ratingsReceived'])
            ->get()
            ->map(function($user) {
                $user->average_rating = $user->ratingsReceived->avg('rating') ?? 0;
                $user->total_ratings = $user->ratingsReceived->count();
                $user->total_jobs = $user->ratingsReceived->count();
                return $user;
            })
            ->sortByDesc('average_rating')
            ->sortByDesc('total_jobs');

        return view('services.category', compact('category', 'servicemen'));
    }

    public function getServicemen(Category $category, Request $request)
    {
        // Debug: Log the category being requested
        \Log::info("Getting servicemen for category: {$category->id} - {$category->name}");
        
        // Check if we should include unavailable servicemen (for backup selection)
        $includeUnavailable = $request->query('include_unavailable', false);
        
        // Ensure we only get actual servicemen with valid profiles
        $servicemen = User::where('user_type', 'SERVICEMAN')
            ->whereHas('servicemanProfile', function($query) use ($category, $includeUnavailable) {
                $query->where('category_id', $category->id);
                
                // Only filter by availability if not including unavailable
                if (!$includeUnavailable) {
                    $query->where('is_available', true);
                }
            })
            ->with(['servicemanProfile', 'ratingsReceived'])
            ->get()
            // Additional safety filter: ensure user_type matches
            ->filter(function($user) {
                return $user->user_type === 'SERVICEMAN' && $user->servicemanProfile !== null;
            });
        
        // Debug: Log how many servicemen were found
        \Log::info("Found {$servicemen->count()} servicemen for category {$category->id}");
        
        $mappedServicemen = $servicemen->map(function($user) {
                $fullName = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
                if (empty($fullName)) {
                    $fullName = $user->username ?? 'Unknown Professional';
                }
                
                // Get rating from serviceman profile or calculate from ratings
                $rating = $user->servicemanProfile->rating ?? 0;
                if ($rating == 0) {
                    $rating = round($user->ratingsReceived->avg('rating') ?? 0, 1);
                }
                
                // Get total jobs from serviceman profile or count ratings
                $totalJobs = $user->servicemanProfile->total_jobs_completed ?? $user->ratingsReceived->count();
                
                return [
                    'id' => $user->id,
                    'full_name' => $fullName,
                    'first_name' => $user->first_name ?? '',
                    'last_name' => $user->last_name ?? '',
                    'username' => $user->username ?? '',
                    'bio' => $user->servicemanProfile->bio ?? 'Professional service provider',
                    'experience_years' => $user->servicemanProfile->experience_years ?? 0,
                    'rating' => $rating,
                    'total_jobs' => $totalJobs,
                    'total_jobs_completed' => $totalJobs, // Add this for compatibility
                    'skills' => $user->servicemanProfile->skills ?? '',
                    'is_available' => $user->servicemanProfile->is_available ?? false,
                    'profile_picture_url' => $user->profile_picture_url ?? asset('images/default-serviceman.jpg'),
                ];
            })
            ->sortByDesc('rating')
            ->sortByDesc('total_jobs')
            ->values();

        // Debug: Log the final result
        \Log::info("Returning " . $mappedServicemen->count() . " servicemen for category {$category->id}");

        return response()->json([
            'servicemen' => $mappedServicemen
        ]);
    }
}

