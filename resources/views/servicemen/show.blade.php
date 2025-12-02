@extends('layouts.app')

@section('title', $user->full_name . ' - ServiceMan')

@section('content')
<div class="max-w-4xl mx-auto py-4 sm:py-6 px-4 sm:px-6 lg:px-8 overflow-x-hidden">
    <!-- Admin Controls (Only visible to admins) -->
    @auth
        @if(auth()->user()->isAdmin())
            <div class="bg-gradient-to-r from-red-50 to-orange-50 border-l-4 border-red-500 rounded-lg shadow-lg p-6 mb-6">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2 flex items-center">
                            <i class="fas fa-shield-alt text-red-600 mr-2"></i>
                            Admin Controls
                        </h3>
                        <div class="flex flex-wrap gap-3">
                            <!-- Approval Status -->
                            @if($user->is_approved)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-2"></i>Approved
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock mr-2"></i>Pending Approval
                                </span>
                            @endif
                            
                            <!-- Email Verification Status -->
                            @if($user->is_email_verified)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                                    <i class="fas fa-envelope-check mr-2"></i>Email Verified
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                                    <i class="fas fa-envelope-times mr-2"></i>Email Not Verified
                                </span>
                            @endif
                            
                            <!-- Category Status -->
                            @if($user->servicemanProfile->category)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-purple-100 text-purple-800">
                                    <i class="fas fa-tag mr-2"></i>{{ $user->servicemanProfile->category->name }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">
                                    <i class="fas fa-exclamation-triangle mr-2"></i>No Category Assigned
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex gap-3 flex-wrap">
                        @if($user->is_approved)
                            <form method="POST" action="{{ route('admin.servicemen.revoke-approval', $user) }}" 
                                  onsubmit="return confirm('Are you sure you want to REVOKE approval for {{ $user->full_name }}? They will be immediately logged out and unable to login until re-approved.');">
                                @csrf
                                <button type="submit" 
                                        class="inline-flex items-center px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors shadow-md hover:shadow-lg">
                                    <i class="fas fa-user-times mr-2"></i>Revoke Approval
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.servicemen.approve', $user) }}" 
                                  onsubmit="return confirm('Are you sure you want to APPROVE {{ $user->full_name }}? They will be able to login and accept jobs.');">
                                @csrf
                                <button type="submit" 
                                        class="inline-flex items-center px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors shadow-md hover:shadow-lg">
                                    <i class="fas fa-user-check mr-2"></i>Approve Serviceman
                                </button>
                            </form>
                        @endif
                        
                        @if(!$user->servicemanProfile->category)
                            <a href="{{ route('admin.pending-servicemen') }}" 
                               class="inline-flex items-center px-5 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors shadow-md hover:shadow-lg">
                                <i class="fas fa-tag mr-2"></i>Assign Category
                            </a>
                        @endif
                        
                        <a href="{{ route('admin.servicemen') }}" 
                           class="inline-flex items-center px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors shadow-md hover:shadow-lg">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Servicemen
                        </a>
                    </div>
                </div>
            </div>
        @endif
    @endauth
    
    <!-- Profile Header -->
    <div class="bg-white rounded-lg shadow-sm p-4 sm:p-6 lg:p-8 mb-6">
        <div class="flex flex-col lg:flex-row items-start lg:items-center gap-4 lg:gap-6">
            <!-- Profile Picture -->
            <div class="flex-shrink-0 mx-auto lg:mx-0">
                <img src="{{ $user->profile_picture_url }}" 
                     alt="{{ $user->full_name }}" 
                     class="w-24 h-24 sm:w-32 sm:h-32 rounded-full object-cover border-4 border-blue-100 shadow-lg">
            </div>
            
            <!-- Profile Info -->
            <div class="flex-1 min-w-0 text-center lg:text-left w-full">
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 break-words">{{ $user->full_name }}</h1>
                <p class="text-base sm:text-lg text-gray-600 mt-1">{{ $user->servicemanProfile->category->name ?? 'Professional' }}</p>
                
                @if($user->ratingsReceived->count() > 0)
                    <div class="flex items-center justify-center lg:justify-start mt-2 flex-wrap gap-2">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star {{ $i <= $user->ratingsReceived->avg('rating') ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                        @endfor
                        <span class="text-sm text-gray-600">
                            {{ number_format($user->ratingsReceived->avg('rating'), 1) }} ({{ $user->ratingsReceived->count() }} reviews)
                        </span>
                    </div>
                @else
                    <p class="text-gray-500 mt-2 text-sm">No reviews yet</p>
                @endif
                
                <div class="flex flex-wrap items-center justify-center lg:justify-start mt-4 gap-2">
                    <span class="px-3 py-1 text-xs sm:text-sm font-medium rounded-full {{ $user->servicemanProfile->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $user->servicemanProfile->is_available ? 'Available' : 'Busy' }}
                    </span>
                    
                    @if($user->servicemanProfile->experience_years)
                        <span class="text-xs sm:text-sm text-gray-600">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            {{ $user->servicemanProfile->experience_years }} years experience
                        </span>
                    @endif
                </div>
            </div>
            
            <!-- Action Button - Full width on mobile, positioned on desktop -->
            <div class="w-full lg:w-auto lg:flex-shrink-0 lg:text-right mt-4 lg:mt-0">
            @auth
                @if(auth()->user()->isAdmin())
                    <!-- Admin viewing - show quick actions -->
                    <div class="flex flex-col gap-2">
                        @if($user->is_approved)
                            <span class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-green-100 text-green-800 rounded-lg text-sm font-semibold">
                                <i class="fas fa-check-circle mr-2"></i>Approved
                            </span>
                        @else
                            <span class="w-full sm:w-auto inline-flex items-center justify-center px-4 py-2 bg-yellow-100 text-yellow-800 rounded-lg text-sm font-semibold">
                                <i class="fas fa-clock mr-2"></i>Pending Approval
                            </span>
                        @endif
                        <p class="text-xs text-gray-500 text-center">Use admin controls above for actions</p>
                    </div>
                @elseif(auth()->user()->isClient())
                    @if($user->servicemanProfile->is_available)
                        <a href="{{ route('service-requests.create') }}?serviceman_id={{ $user->id }}" 
                           class="w-full sm:w-auto block sm:inline-block text-center bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg whitespace-nowrap">
                            <i class="fas fa-calendar-check mr-2"></i>Book Now
                        </a>
                    @else
                        <button disabled class="w-full sm:w-auto block sm:inline-block text-center bg-gray-400 text-white px-6 py-3 rounded-lg font-semibold cursor-not-allowed whitespace-nowrap">
                            <i class="fas fa-ban mr-2"></i>Currently Unavailable
                        </button>
                    @endif
                @else
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 sm:p-4">
                        <p class="text-xs sm:text-sm text-yellow-800 text-center sm:text-left">
                            <i class="fas fa-info-circle mr-2"></i>Only clients can book services
                        </p>
                    </div>
                @endif
            @else
                <a href="{{ route('login') }}?redirect={{ urlencode(url()->current()) }}" 
                   class="w-full sm:w-auto block sm:inline-block text-center bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg whitespace-nowrap">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login to Book
                </a>
            @endauth
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- About -->
            @if($user->servicemanProfile->bio)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">About</h2>
                    <p class="text-gray-700">{{ $user->servicemanProfile->bio }}</p>
                </div>
            @endif

            <!-- Skills -->
            @if($user->servicemanProfile->skills)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Skills & Expertise</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach(explode(',', $user->servicemanProfile->skills) as $skill)
                            <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                                {{ trim($skill) }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Reviews -->
            @if($user->ratingsReceived->count() > 0)
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Reviews</h2>
                    <div class="space-y-4">
                        @foreach($user->ratingsReceived->take(5) as $rating)
                            <div class="border-b border-gray-200 pb-4 last:border-b-0">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= $rating->rating ? 'text-yellow-400' : 'text-gray-300' }} text-sm"></i>
                                        @endfor
                                        <span class="ml-2 text-sm text-gray-600">{{ $rating->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                                @if($rating->comment)
                                    <p class="text-gray-700">{{ $rating->comment }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">

            <!-- Service Category -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Service Category</h3>
                <div class="flex items-center">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                        <i class="fas fa-{{ $user->servicemanProfile->category->icon ?? 'tools' }} text-blue-600"></i>
                    </div>
                    <span class="text-gray-700">{{ $user->servicemanProfile->category->name ?? 'Professional' }}</span>
                </div>
            </div>

            <!-- Availability -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Availability</h3>
                <div class="flex items-center">
                    <div class="w-3 h-3 rounded-full {{ $user->servicemanProfile->is_available ? 'bg-green-500' : 'bg-red-500' }} mr-3"></div>
                    <span class="text-gray-700">
                        {{ $user->servicemanProfile->is_available ? 'Currently Available' : 'Currently Busy' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
