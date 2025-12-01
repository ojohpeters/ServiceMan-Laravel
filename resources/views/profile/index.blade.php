@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Profile</h1>
        <p class="text-gray-600">Manage your account information</p>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-6 bg-green-50 border-l-4 border-green-400 p-4 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-400 mr-3"></i>
                <p class="text-sm text-green-700">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-400 p-4 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-exclamation-circle text-red-400 mr-3"></i>
                <p class="text-sm text-red-700">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    @if(session('info'))
        <div class="mb-6 bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg">
            <div class="flex items-center">
                <i class="fas fa-info-circle text-blue-400 mr-3"></i>
                <p class="text-sm text-blue-700">{{ session('info') }}</p>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Information -->
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-medium text-gray-900">Basic Information</h2>
                    <button onclick="editBasicInfo()" class="text-blue-600 hover:text-blue-800 font-medium">
                        Edit
                    </button>
                </div>
                
                <div id="basicInfoView" class="space-y-4">
                    <div class="flex items-center space-x-4">
                        <img src="{{ $user->profile_picture_url }}" 
                             alt="{{ $user->full_name }}" 
                             class="w-20 h-20 rounded-full object-cover border-4 border-purple-200 shadow-lg">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ $user->full_name }}</h3>
                            <p class="text-gray-600">{{ ucfirst(strtolower($user->user_type)) }}</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Username</p>
                            <p class="text-gray-900">{{ $user->username }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Email</p>
                            <p class="text-gray-900">{{ $user->email }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">First Name</p>
                            <p class="text-gray-900">{{ $user->first_name }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500">Last Name</p>
                            <p class="text-gray-900">{{ $user->last_name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Edit Form -->
                <form id="basicInfoForm" method="POST" action="{{ route('profile.update') }}" class="hidden space-y-4">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700">First Name</label>
                            <input type="text" id="first_name" name="first_name" value="{{ $user->first_name }}" required 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
                            <input type="text" id="last_name" name="last_name" value="{{ $user->last_name }}" required 
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                    </div>
                    
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" id="username" name="username" value="{{ $user->username }}" required 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email" value="{{ $user->email }}" required 
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                    </div>
                    
                    <div class="flex items-center justify-end space-x-4">
                        <button type="button" onclick="cancelEdit()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-400 transition-colors">
                            Cancel
                        </button>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Profile Type Specific Information -->
            @if($user->isClient() && $user->clientProfile)
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-medium text-gray-900">Client Profile</h2>
                        <a href="{{ route('profile.client') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                            Manage
                        </a>
                    </div>
                    
                    <div class="space-y-4">
                        @if($user->clientProfile->phone_number)
                            <div>
                                <p class="text-sm font-medium text-gray-500">Phone Number</p>
                                <p class="text-gray-900">{{ $user->clientProfile->phone_number }}</p>
                            </div>
                        @endif
                        
                        @if($user->clientProfile->address)
                            <div>
                                <p class="text-sm font-medium text-gray-500">Address</p>
                                <p class="text-gray-900">{{ $user->clientProfile->address }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            @if($user->isServiceman() && $user->servicemanProfile)
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-medium text-gray-900">Serviceman Profile</h2>
                        <a href="{{ route('profile.serviceman') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                            Manage
                        </a>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Service Category</p>
                            <p class="text-gray-900">{{ $user->servicemanProfile->category->name ?? 'Not specified' }}</p>
                        </div>
                        
                        @if($user->servicemanProfile->phone_number)
                            <div>
                                <p class="text-sm font-medium text-gray-500">Phone Number</p>
                                <p class="text-gray-900">{{ $user->servicemanProfile->phone_number }}</p>
                            </div>
                        @endif
                        
                        @if($user->servicemanProfile->experience_years)
                            <div>
                                <p class="text-sm font-medium text-gray-500">Experience</p>
                                <p class="text-gray-900">{{ $user->servicemanProfile->experience_years }} years</p>
                            </div>
                        @endif
                        
                        
                        <div>
                            <p class="text-sm font-medium text-gray-500">Availability</p>
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $user->servicemanProfile->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $user->servicemanProfile->is_available ? 'Available' : 'Busy' }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Quick Actions</h3>
                <div class="space-y-3">
                    @if($user->isClient())
                        <a href="{{ route('services') }}" class="block w-full bg-blue-600 text-white text-center py-2 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors">
                            New Service Request
                        </a>
                        <a href="{{ route('service-requests.index') }}" class="block w-full bg-gray-100 text-gray-700 text-center py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                            View Requests
                        </a>
                    @endif
                    
                    @if($user->isServiceman())
                        <a href="{{ route('service-requests.index') }}" class="block w-full bg-green-600 text-white text-center py-2 px-4 rounded-lg font-medium hover:bg-green-700 transition-colors">
                            View Assignments
                        </a>
                        <a href="{{ route('profile.serviceman') }}" class="block w-full bg-gray-100 text-gray-700 text-center py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition-colors">
                            Update Profile
                        </a>
                    @endif
                    
                    @if($user->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="block w-full bg-purple-600 text-white text-center py-2 px-4 rounded-lg font-medium hover:bg-purple-700 transition-colors">
                            Admin Dashboard
                        </a>
                    @endif
                </div>
            </div>

            <!-- Email Verification Alert -->
            @if(!$user->is_email_verified)
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg mb-6">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-400 text-xl"></i>
                        </div>
                        <div class="ml-3 flex-1">
                            <h3 class="text-sm font-medium text-yellow-800 mb-2">
                                Email Verification Required
                            </h3>
                            <p class="text-sm text-yellow-700 mb-3">
                                Please verify your email address to access all features. Check your inbox at <strong>{{ $user->email }}</strong> for the verification link. If you didn't receive it or it expired, click the button below to resend.
                            </p>
                            <form action="{{ route('verification.resend') }}" method="POST" id="resendVerificationForm">
                                @csrf
                                <button type="submit" 
                                        class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                        id="resendVerificationBtn">
                                    <i class="fas fa-envelope mr-2"></i>
                                    <span id="resendText">Resend Verification Email</span>
                                    <span id="resendLoading" class="hidden">
                                        <i class="fas fa-spinner fa-spin mr-2"></i>
                                        Sending...
                                    </span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Account Status -->
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Account Status</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Email Verified</span>
                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $user->is_email_verified ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $user->is_email_verified ? 'Verified' : 'Not Verified' }}
                        </span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Account Type</span>
                        <span class="text-sm font-medium text-gray-900">{{ ucfirst(strtolower($user->user_type)) }}</span>
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Member Since</span>
                        <span class="text-sm text-gray-900">{{ $user->created_at->format('M Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function editBasicInfo() {
    document.getElementById('basicInfoView').classList.add('hidden');
    document.getElementById('basicInfoForm').classList.remove('hidden');
}

function cancelEdit() {
    document.getElementById('basicInfoView').classList.remove('hidden');
    document.getElementById('basicInfoForm').classList.add('hidden');
}

// Handle resend verification email with loading state
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('resendVerificationForm');
    const btn = document.getElementById('resendVerificationBtn');
    const text = document.getElementById('resendText');
    const loading = document.getElementById('resendLoading');
    
    if (form && btn) {
        form.addEventListener('submit', function(e) {
            // Show loading state immediately
            btn.disabled = true;
            if (text) text.classList.add('hidden');
            if (loading) loading.classList.remove('hidden');
            
            // Let the form submit normally - Laravel will handle the response
            // The loading state will be reset on page reload
        });
    }
});
</script>
@endpush
@endsection
