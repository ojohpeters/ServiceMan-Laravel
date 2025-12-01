@extends('layouts.app')

@section('title', $category->name . ' Services - ServiceMan')
@section('description', 'Find skilled ' . strtolower($category->name) . ' professionals in your area.')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Category Header -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
        <div class="flex items-center mb-4">
            <div class="w-16 h-16 bg-blue-100 rounded-lg flex items-center justify-center mr-6">
                <i class="fas fa-{{ $category->icon ?? 'tools' }} text-blue-600 text-2xl"></i>
            </div>
            <div>
                <h1 class="text-3xl font-bold text-gray-900">{{ $category->name }} Services</h1>
                <p class="text-gray-600 mt-1">{{ $servicemen->count() }} professionals available</p>
            </div>
        </div>
        
        @if($category->description)
            <p class="text-gray-700">{{ $category->description }}</p>
        @endif
    </div>

    <!-- Servicemen Grid -->
    @if($servicemen->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($servicemen as $serviceman)
                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow p-6 border border-gray-200">
                    <div class="text-center mb-4">
                        <img src="{{ $serviceman->profile_picture_url }}" 
                             alt="{{ $serviceman->full_name }}" 
                             class="w-24 h-24 rounded-full object-cover mx-auto mb-4 border-4 border-blue-100 shadow-md">
                        <h3 class="text-xl font-semibold text-gray-900">{{ $serviceman->first_name }} {{ $serviceman->last_name }}</h3>
                        <p class="text-sm text-gray-500">{{ $serviceman->servicemanProfile->category->name ?? 'Professional' }}</p>
                    </div>

                    <!-- Rating -->
                    @if($serviceman->ratingsReceived->count() > 0)
                        <div class="flex items-center justify-center mb-4">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= $serviceman->ratingsReceived->avg('rating') ? 'text-yellow-400' : 'text-gray-300' }} text-sm"></i>
                            @endfor
                            <span class="ml-2 text-sm text-gray-600">
                                ({{ $serviceman->ratingsReceived->count() }} reviews)
                            </span>
                        </div>
                    @else
                        <div class="text-center mb-4">
                            <span class="text-sm text-gray-500">No reviews yet</span>
                        </div>
                    @endif

                    <!-- Profile Info -->
                    <div class="space-y-2 mb-4">
                        @if($serviceman->servicemanProfile->experience_years)
                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-calendar-alt mr-2 text-blue-600"></i>
                                {{ $serviceman->servicemanProfile->experience_years }} years experience
                            </div>
                        @endif
                        
                        
                        <div class="flex items-center justify-center text-sm">
                            <span class="inline-flex items-center px-3 py-1 text-xs font-bold rounded-full {{ $serviceman->servicemanProfile->is_available ? 'bg-green-100 text-green-800' : 'bg-orange-100 text-orange-800' }}">
                                <i class="fas {{ $serviceman->servicemanProfile->is_available ? 'fa-check-circle' : 'fa-clock' }} mr-1"></i>
                                {{ $serviceman->servicemanProfile->is_available ? 'Available Now' : 'Currently Busy' }}
                            </span>
                        </div>
                    </div>

                    <!-- Bio -->
                    @if($serviceman->servicemanProfile->bio)
                        <p class="text-sm text-gray-600 mb-4 line-clamp-3">{{ $serviceman->servicemanProfile->bio }}</p>
                    @endif

                    <!-- Skills -->
                    @if($serviceman->servicemanProfile->skills)
                        <div class="mb-4">
                            <p class="text-sm font-medium text-gray-700 mb-2">Skills:</p>
                            <div class="flex flex-wrap gap-1">
                                @foreach(explode(',', $serviceman->servicemanProfile->skills) as $skill)
                                    <span class="px-2 py-1 text-xs bg-gray-100 text-gray-700 rounded">{{ trim($skill) }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Actions -->
                    <div class="flex space-x-2">
                        <a href="{{ route('servicemen.show', $serviceman) }}" class="flex-1 bg-blue-600 text-white text-center py-2 px-4 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors">
                            View Profile
                        </a>
                        @auth
                            @if(auth()->user()->isClient())
                                <button onclick="openBookingModal({
                                    id: {{ $serviceman->id }},
                                    full_name: '{{ $serviceman->first_name }} {{ $serviceman->last_name }}',
                                    username: '{{ $serviceman->username }}',
                                    rating: {{ $serviceman->servicemanProfile->rating ?? 0 }},
                                    total_jobs: {{ $serviceman->servicemanProfile->total_jobs_completed ?? 0 }},
                                    category_id: {{ $serviceman->servicemanProfile->category_id }},
                                    category_name: '{{ $serviceman->servicemanProfile->category->name ?? '' }}',
                                    bio: '{{ $serviceman->servicemanProfile->bio ?? '' }}',
                                    is_available: {{ $serviceman->servicemanProfile->is_available ? 'true' : 'false' }},
                                    profile_picture_url: '{{ $serviceman->profile_picture_url }}'
                                })" class="flex-1 bg-green-600 text-white text-center py-2 px-4 rounded-lg text-sm font-medium hover:bg-green-700 transition-colors">
                                    Book Now
                                </button>
                            @else
                                <button disabled class="flex-1 bg-gray-400 text-white text-center py-2 px-4 rounded-lg text-sm font-medium cursor-not-allowed" title="Only clients can book services">
                                    <i class="fas fa-lock mr-1"></i>Clients Only
                                </button>
                            @endif
                        @else
                            <a href="{{ route('login') }}" class="flex-1 bg-green-600 text-white text-center py-2 px-4 rounded-lg text-sm font-medium hover:bg-green-700 transition-colors">
                                Book Now
                            </a>
                        @endauth
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-12">
            <i class="fas fa-user-times text-4xl text-gray-400 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No professionals available</h3>
            <p class="text-gray-500 mb-6">There are currently no {{ strtolower($category->name) }} professionals available in this category.</p>
            <a href="{{ route('services') }}" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition-colors">
                Browse Other Services
            </a>
        </div>
    @endif

    <!-- Back to Services -->
    <div class="mt-8 text-center">
        <a href="{{ route('services') }}" class="text-blue-600 hover:text-blue-800 font-medium">
            <i class="fas fa-arrow-left mr-2"></i>Back to All Services
        </a>
    </div>
</div>

<!-- Include Booking Modal -->
<x-booking-modal />
@endsection
