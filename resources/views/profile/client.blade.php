@extends('layouts.app')

@section('title', 'Client Profile')

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Client Profile</h1>
                <p class="text-gray-600">Manage your contact information and preferences</p>
            </div>
            <a href="{{ route('profile') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                <i class="fas fa-arrow-left mr-2"></i>Back to Profile
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i>Please fix the following errors:
            <ul class="mt-2 list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Profile Form -->
    <div class="bg-white shadow-sm rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Contact Information</h2>
        </div>
        
        <form method="POST" action="{{ route('profile.client.update') }}" class="p-6 space-y-6">
            @csrf
            @method('PUT')
            
            <!-- Phone Number -->
            <div>
                <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                    Phone Number
                </label>
                <input type="tel" id="phone_number" name="phone_number" 
                       value="{{ old('phone_number', $profile->phone_number ?? '') }}"
                       placeholder="Enter your contact number"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
            </div>

            <!-- Address -->
            <div>
                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                    Address
                </label>
                <textarea id="address" name="address" rows="3" 
                          placeholder="Enter your address for service location reference..."
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('address', $profile->address ?? '') }}</textarea>
                <p class="mt-1 text-sm text-gray-500">This helps servicemen know your service location</p>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('profile') }}" 
                   class="bg-gray-300 text-gray-700 px-6 py-2 rounded-lg font-medium hover:bg-gray-400 transition-colors">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>Save Profile
                </button>
            </div>
        </form>
    </div>

    <!-- Current Profile Summary -->
    @if($profile)
    <div class="mt-8 bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Current Contact Information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h4 class="text-sm font-medium text-gray-500 mb-2">Phone Number</h4>
                <p class="text-gray-900">{{ $profile->phone_number ?: 'Not provided' }}</p>
            </div>
            <div>
                <h4 class="text-sm font-medium text-gray-500 mb-2">Address</h4>
                <p class="text-gray-900">{{ $profile->address ?: 'Not provided' }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Quick Actions -->
    <div class="mt-8 bg-white shadow-sm rounded-lg p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="{{ route('services') }}" 
               class="flex items-center justify-center p-4 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-plus mr-2"></i>Request New Service
            </a>
            <a href="{{ route('service-requests.index') }}" 
               class="flex items-center justify-center p-4 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                <i class="fas fa-list mr-2"></i>View My Requests
            </a>
        </div>
    </div>
</div>
@endsection
