<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ClientProfile;
use App\Models\ServicemanProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function getCurrentUser()
    {
        $user = Auth::user();
        $user->load(['clientProfile', 'servicemanProfile.category']);
        
        return response()->json($user);
    }

    public function getClientProfile()
    {
        $user = Auth::user();
        
        if (!$user->isClient()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $profile = $user->clientProfile;
        
        if (!$profile) {
            $profile = $user->clientProfile()->create([
                'phone_number' => '',
                'address' => ''
            ]);
        }

        return response()->json($profile);
    }

    public function updateClientProfile(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isClient()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string'
        ]);

        // Also allow updating user basic info
        $userValidator = Validator::make($request->all(), [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'username' => 'nullable|string|max:255|unique:users,username,' . $user->id
        ]);

        if ($userValidator->fails()) {
            return response()->json(['errors' => $userValidator->errors()], 400);
        }

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Update user basic info if provided
        $user->update($request->only(['first_name', 'last_name', 'email', 'username']));

        $profile = $user->clientProfile;
        
        if (!$profile) {
            $profile = $user->clientProfile()->create([
                'phone_number' => $request->phone_number ?? '',
                'address' => $request->address ?? ''
            ]);
        } else {
            $profile->update($request->only(['phone_number', 'address']));
        }

        // Reload user with updated data
        $user->load('clientProfile');

        return response()->json($user);
    }

    public function getServicemanProfile()
    {
        $user = Auth::user();
        
        if (!$user->isServiceman()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $profile = $user->servicemanProfile;
        
        if (!$profile) {
            $profile = $user->servicemanProfile()->create([
                'phone_number' => '',
                'bio' => '',
                'is_available' => true
            ]);
        }

        $profile->load('category');
        
        return response()->json($profile);
    }

    public function updateServicemanProfile(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isServiceman()) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $validator = Validator::make($request->all(), [
            'category_id' => 'nullable|exists:categories,id',
            'phone_number' => 'nullable|string|max:20',
            'bio' => 'nullable|string',
            'experience_years' => 'nullable|string',
            'skills' => 'nullable|string',
            'hourly_rate' => 'nullable|numeric|min:0',
            'is_available' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Also allow updating user basic info
        $userValidator = Validator::make($request->all(), [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'username' => 'nullable|string|max:255|unique:users,username,' . $user->id
        ]);

        if ($userValidator->fails()) {
            return response()->json(['errors' => $userValidator->errors()], 400);
        }

        // Update user basic info if provided
        $user->update($request->only(['first_name', 'last_name', 'email', 'username']));

        $profile = $user->servicemanProfile;
        
        if (!$profile) {
            $profile = $user->servicemanProfile()->create([
                'category_id' => $request->category_id,
                'phone_number' => $request->phone_number ?? '',
                'bio' => $request->bio ?? '',
                'experience_years' => $request->experience_years,
                'skills' => $request->skills,
                'hourly_rate' => $request->hourly_rate,
                'is_available' => $request->is_available ?? true
            ]);
        } else {
            $profile->update($request->only([
                'category_id', 'phone_number', 'bio', 
                'experience_years', 'skills', 'hourly_rate', 'is_available'
            ]));
        }

        $profile->load('category');
        
        // Reload user with updated data
        $user->load(['servicemanProfile.category']);
        
        return response()->json($user);
    }

    public function getPublicServicemanProfile($userId)
    {
        $user = User::findOrFail($userId);
        
        if (!$user->isServiceman()) {
            return response()->json(['error' => 'User is not a serviceman'], 404);
        }

        $profile = $user->servicemanProfile;
        
        if (!$profile) {
            return response()->json(['error' => 'Serviceman profile not found'], 404);
        }

        $profile->load('category');
        
        // Return public information only
        return response()->json([
            'user_id' => $user->id,
            'full_name' => $user->getFullNameAttribute(),
            'category' => $profile->category,
            'rating' => $profile->rating,
            'total_jobs_completed' => $profile->total_jobs_completed,
            'bio' => $profile->bio,
            'years_of_experience' => $profile->years_of_experience,
            'is_available' => $profile->is_available,
            'created_at' => $profile->created_at,
            'updated_at' => $profile->updated_at
        ]);
    }

    public function createTestServicemen(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|exists:categories,id'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $categoryId = $request->category_id;
        $category = \App\Models\Category::findOrFail($categoryId);

        $testServicemen = [
            [
                'username' => 'john_electrician',
                'email' => 'john@example.com',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'bio' => 'Experienced electrician with 10+ years in residential and commercial electrical work.',
                'years_of_experience' => 10,
                'phone_number' => '+2348012345678'
            ],
            [
                'username' => 'jane_electrician',
                'email' => 'jane@example.com',
                'first_name' => 'Jane',
                'last_name' => 'Doe',
                'bio' => 'Professional electrician specializing in smart home installations and electrical repairs.',
                'years_of_experience' => 8,
                'phone_number' => '+2348012345679'
            ],
            [
                'username' => 'mike_electrician',
                'email' => 'mike@example.com',
                'first_name' => 'Mike',
                'last_name' => 'Johnson',
                'bio' => 'Licensed electrician with expertise in industrial electrical systems and emergency repairs.',
                'years_of_experience' => 12,
                'phone_number' => '+2348012345680'
            ]
        ];

        $createdServicemen = [];
        
        foreach ($testServicemen as $index => $servicemanData) {
            // Check if user already exists
            if (User::where('email', $servicemanData['email'])->exists()) {
                continue;
            }

            // Create user
            $user = User::create([
                'username' => $servicemanData['username'],
                'email' => $servicemanData['email'],
                'password' => bcrypt('TestPass123!'),
                'user_type' => 'SERVICEMAN',
                'first_name' => $servicemanData['first_name'],
                'last_name' => $servicemanData['last_name'],
                'is_email_verified' => true
            ]);

            // Create serviceman profile
            $profile = $user->servicemanProfile()->create([
                'category_id' => $categoryId,
                'bio' => $servicemanData['bio'],
                'years_of_experience' => $servicemanData['years_of_experience'],
                'phone_number' => $servicemanData['phone_number'],
                'rating' => 4.5 + ($index * 0.1),
                'total_jobs_completed' => 20 + ($index * 5),
                'is_available' => true
            ]);

            $createdServicemen[] = [
                'id' => $user->id,
                'full_name' => $user->getFullNameAttribute(),
                'email' => $user->email,
                'rating' => $profile->rating,
                'total_jobs_completed' => $profile->total_jobs_completed
            ];
        }

        return response()->json([
            'message' => "Created " . count($createdServicemen) . " servicemen for category '{$category->name}'",
            'servicemen' => $createdServicemen
        ]);
    }
}