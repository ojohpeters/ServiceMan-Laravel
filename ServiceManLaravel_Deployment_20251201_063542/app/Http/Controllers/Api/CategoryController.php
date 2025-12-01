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
        $categories = Category::active()->get();
        return response()->json($categories);
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

    public function getServicemenByCategory($categoryId)
    {
        $category = Category::findOrFail($categoryId);
        
        $servicemen = User::where('user_type', 'SERVICEMAN')
            ->whereHas('servicemanProfile', function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId)
                      ->where('is_available', true);
            })
            ->with('servicemanProfile')
            ->get();

        $data = $servicemen->map(function ($serviceman) {
            $profile = $serviceman->servicemanProfile;
            return [
                'id' => $serviceman->id,
                'user' => [
                    'first_name' => $serviceman->first_name,
                    'last_name' => $serviceman->last_name,
                    'username' => $serviceman->username
                ],
                'rating' => $profile->rating ?? 0,
                'total_jobs_completed' => $profile->total_jobs_completed ?? 0,
                'bio' => $profile->bio,
                'experience_years' => $profile->experience_years,
                'is_available' => $profile->is_available,
                'hourly_rate' => $profile->hourly_rate,
                'skills' => $profile->skills
            ];
        });

        return response()->json($data);
    }
}