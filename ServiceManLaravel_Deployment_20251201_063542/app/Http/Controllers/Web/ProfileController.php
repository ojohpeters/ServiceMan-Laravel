<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $user->load(['clientProfile', 'servicemanProfile']);
        
        return view('profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'username' => 'required|string|max:255|unique:users,username,' . $user->id,
            'phone_number' => 'nullable|string|max:20',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $updateData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'username' => $request->username,
            'phone_number' => $request->phone_number,
        ];

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile_picture && file_exists(public_path($user->profile_picture))) {
                unlink(public_path($user->profile_picture));
            }

            $file = $request->file('profile_picture');
            $filename = 'profile_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/profile_pictures'), $filename);
            $updateData['profile_picture'] = 'uploads/profile_pictures/' . $filename;
        }

        $user->update($updateData);

        return back()->with('success', 'Profile updated successfully!');
    }

    public function clientProfile()
    {
        $user = Auth::user();
        $profile = $user->clientProfile;
        
        return view('profile.client', compact('user', 'profile'));
    }

    public function updateClientProfile(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'phone_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $user->clientProfile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'phone_number' => $request->phone_number,
                'address' => $request->address,
            ]
        );

        return back()->with('success', 'Client profile updated successfully!');
    }

    public function servicemanProfile()
    {
        $user = Auth::user();
        $profile = $user->servicemanProfile;
        $categories = \App\Models\Category::all();
        
        return view('profile.serviceman', compact('user', 'profile', 'categories'));
    }

    public function updateServicemanProfile(Request $request)
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'category_id' => 'nullable|exists:categories,id',
            'bio' => 'nullable|string|max:1000',
            'experience_years' => 'nullable|integer|min:0|max:50',
            'skills' => 'nullable|string|max:1000',
            'is_available' => 'boolean',
            'profile_picture' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Update user basic info
        $updateData = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone_number' => $request->phone_number,
        ];

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old profile picture if exists
            if ($user->profile_picture && file_exists(public_path($user->profile_picture))) {
                unlink(public_path($user->profile_picture));
            }

            $file = $request->file('profile_picture');
            $filename = 'profile_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/profile_pictures'), $filename);
            $updateData['profile_picture'] = 'uploads/profile_pictures/' . $filename;
        }

        $user->update($updateData);

        // Update serviceman profile
        $user->servicemanProfile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'category_id' => $request->category_id,
                'bio' => $request->bio,
                'experience_years' => $request->experience_years,
                'skills' => $request->skills,
                'is_available' => $request->has('is_available'),
            ]
        );

        return back()->with('success', 'Serviceman profile updated successfully!');
    }

    public function showPublic(User $user)
    {
        if ($user->user_type !== 'SERVICEMAN') {
            abort(404);
        }

        $user->load(['servicemanProfile.category', 'ratingsReceived']);
        
        return view('servicemen.show', compact('user'));
    }
}

