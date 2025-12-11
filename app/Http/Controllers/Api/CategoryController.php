<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::active()
            ->withCount([
                'servicemen' => function($query) {
                    $query->where('is_available', true);
                }
            ])
            ->get()
            ->map(function($category) {
                // Calculate average rating for servicemen in this category
                $servicemenWithRatings = User::where('user_type', 'SERVICEMAN')
                    ->whereHas('servicemanProfile', function($query) use ($category) {
                        $query->where('category_id', $category->id)
                              ->where('is_available', true);
                    })
                    ->with('servicemanProfile')
                    ->get()
                    ->map(function($user) {
                        return $user->servicemanProfile->rating ?? 0;
                    })
                    ->filter(function($rating) {
                        return $rating > 0;
                    });
                
                $avgRating = $servicemenWithRatings->count() > 0 
                    ? round($servicemenWithRatings->avg(), 1) 
                    : 0;
                
                // Ensure servicemen_count is an integer
                $servicemenCount = (int) ($category->servicemen_count ?? 0);
                
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                    'icon_url' => $category->icon_url,
                    'is_active' => $category->is_active,
                    'servicemen_count' => $servicemenCount,
                    'average_rating' => $avgRating,
                    'created_at' => $category->created_at,
                    'updated_at' => $category->updated_at,
                ];
            });
        
        return response()->json([
            'data' => $categories,
            'success' => true
        ]);
    }

    public function show($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:categories',
            'description' => 'required|string',
            'icon_url' => 'nullable|url'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $category = Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'icon_url' => $request->icon_url,
            'is_active' => true
        ]);

        return response()->json($category, 201);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $category = Category::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:categories,name,' . $id,
            'description' => 'required|string',
            'icon_url' => 'nullable|url',
            'is_active' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $category->update($request->only(['name', 'description', 'icon_url', 'is_active']));

        return response()->json($category);
    }

    public function destroy($id)
    {
        $user = Auth::user();
        
        if (!$user->isAdmin()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $category = Category::findOrFail($id);
        $category->update(['is_active' => false]);

        return response()->json(['message' => 'Category deactivated successfully']);
    }

    public function getServicemenByCategory($categoryId, Request $request)
    {
        $category = Category::findOrFail($categoryId);
        
        $perPage = min($request->get('per_page', 15), 50); // Max 50 per page
        
        $servicemen = User::where('user_type', 'SERVICEMAN')
            ->whereHas('servicemanProfile', function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId)
                      ->where('is_available', true);
            })
            ->with(['servicemanProfile.category', 'ratingsReceived'])
            ->join('serviceman_profiles', 'users.id', '=', 'serviceman_profiles.user_id')
            ->orderByDesc('serviceman_profiles.rating')
            ->orderByDesc('serviceman_profiles.total_jobs_completed')
            ->select('users.*')
            ->paginate($perPage);

        // Calculate rank based on pagination offset
        $rankOffset = ($servicemen->currentPage() - 1) * $servicemen->perPage();
        
        $data = $servicemen->map(function ($serviceman, $index) use ($rankOffset) {
            $profile = $serviceman->servicemanProfile;
            $currentRank = $rankOffset + $index + 1;
            
            return [
                'id' => $serviceman->id,
                'user' => [
                    'first_name' => $serviceman->first_name,
                    'last_name' => $serviceman->last_name,
                    'username' => $serviceman->username,
                    'email' => $serviceman->email,
                    'profile_picture' => $serviceman->profile_picture,
                ],
                'serviceman_profile' => [
                    'category_id' => $profile->category_id,
                    'rating' => $profile->rating ?? 0,
                    'total_jobs_completed' => $profile->total_jobs_completed ?? 0,
                    'experience_years' => $profile->experience_years,
                    'skills' => $profile->skills,
                    'bio' => $profile->bio,
                    'hourly_rate' => $profile->hourly_rate,
                ],
                'rank' => $currentRank,
                'category_rank' => $profile->getCategoryRank(),
            ];
        });

        return response()->json([
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'description' => $category->description,
            ],
            'servicemen' => $data,
            'pagination' => [
                'current_page' => $servicemen->currentPage(),
                'last_page' => $servicemen->lastPage(),
                'per_page' => $servicemen->perPage(),
                'total' => $servicemen->total(),
            ],
        ]);
    }
}