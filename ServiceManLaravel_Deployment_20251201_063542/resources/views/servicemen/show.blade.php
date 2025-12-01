@extends('layouts.app')

@section('title', $user->full_name . ' - ServiceMan')

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Profile Header -->
    <div class="bg-white rounded-lg shadow-sm p-8 mb-6">
        <div class="flex items-center space-x-6">
            <img src="{{ $user->profile_picture_url }}" 
                 alt="{{ $user->full_name }}" 
                 class="w-32 h-32 rounded-full object-cover border-4 border-blue-100 shadow-lg">
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-900">{{ $user->full_name }}</h1>
                <p class="text-lg text-gray-600">{{ $user->servicemanProfile->category->name ?? 'Professional' }}</p>
                
                @if($user->ratingsReceived->count() > 0)
                    <div class="flex items-center mt-2">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star {{ $i <= $user->ratingsReceived->avg('rating') ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                        @endfor
                        <span class="ml-2 text-gray-600">
                            {{ number_format($user->ratingsReceived->avg('rating'), 1) }} ({{ $user->ratingsReceived->count() }} reviews)
                        </span>
                    </div>
                @else
                    <p class="text-gray-500 mt-2">No reviews yet</p>
                @endif
                
                <div class="flex items-center mt-4 space-x-4">
                    <span class="px-3 py-1 text-sm font-medium rounded-full {{ $user->servicemanProfile->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ $user->servicemanProfile->is_available ? 'Available' : 'Busy' }}
                    </span>
                    
                    @if($user->servicemanProfile->experience_years)
                        <span class="text-sm text-gray-600">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            {{ $user->servicemanProfile->experience_years }} years experience
                        </span>
                    @endif
                    
                </div>
            </div>
            
            <div class="text-right">
                @auth
                    @if(auth()->user()->isClient())
                        @if($user->servicemanProfile->is_available)
                            <a href="{{ route('service-requests.create') }}?serviceman_id={{ $user->id }}" 
                               class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg inline-flex items-center">
                                <i class="fas fa-calendar-check mr-2"></i>Book Now
                            </a>
                        @else
                            <button disabled class="bg-gray-400 text-white px-6 py-3 rounded-lg font-semibold cursor-not-allowed inline-flex items-center">
                                <i class="fas fa-ban mr-2"></i>Currently Unavailable
                            </button>
                        @endif
                    @else
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <p class="text-sm text-yellow-800">
                                <i class="fas fa-info-circle mr-2"></i>Only clients can book services
                            </p>
                        </div>
                    @endif
                @else
                    <a href="{{ route('login') }}?redirect={{ urlencode(url()->current()) }}" 
                       class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-3 rounded-lg font-semibold transition-all shadow-md hover:shadow-lg inline-flex items-center">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login to Book
                    </a>
                @endauth
            </div>
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
