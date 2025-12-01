@extends('layouts.app')

@section('title', 'Dashboard - Serviceman')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Welcome Section -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
        <div class="p-6 bg-gradient-to-r from-green-600 to-blue-600 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Welcome back, {{ Auth::user()->first_name }}!</h1>
                    <p class="text-green-100 mt-1">Manage your service requests and grow your business</p>
                </div>
                <div class="text-right">
                    <a href="{{ route('profile.serviceman') }}" class="bg-white text-green-600 px-4 py-2 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                        <i class="fas fa-user-edit mr-2"></i>Update Profile
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Status -->
    @if($profile)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 bg-gradient-to-r from-green-600 to-blue-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">{{ Auth::user()->full_name }}</h3>
                            <p class="text-sm text-gray-500">{{ $profile->category->name ?? 'Professional' }}</p>
                            <div class="flex items-center mt-1">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $profile->is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $profile->is_available ? 'Available' : 'Busy' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">{{ $profile->experience_years ?? 0 }} years experience</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border-l-4 border-blue-500 transform hover:scale-105 transition-transform">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Total Jobs</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">{{ $stats['totalRequests'] }}</p>
                    </div>
                    <div class="bg-blue-100 p-4 rounded-xl">
                        <i class="fas fa-clipboard-list text-3xl text-blue-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border-l-4 border-yellow-500 transform hover:scale-105 transition-transform">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide">New Assignments</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">{{ $stats['pendingRequests'] }}</p>
                    </div>
                    <div class="bg-yellow-100 p-4 rounded-xl">
                        <i class="fas fa-bell text-3xl text-yellow-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border-l-4 border-purple-500 transform hover:scale-105 transition-transform">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Active Jobs</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">{{ $stats['inProgressRequests'] }}</p>
                    </div>
                    <div class="bg-purple-100 p-4 rounded-xl">
                        <i class="fas fa-tools text-3xl text-purple-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border-l-4 border-green-500 transform hover:scale-105 transition-transform">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Completed</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">{{ $stats['completedRequests'] }}</p>
                    </div>
                    <div class="bg-green-100 p-4 rounded-xl">
                        <i class="fas fa-check-circle text-3xl text-green-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Earnings & Performance Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border-l-4 border-green-500">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Total Earnings</p>
                        <p class="text-2xl font-bold text-gray-900">₦{{ number_format($stats['totalEarnings']) }}</p>
                        <p class="text-xs text-gray-500 mt-1">All completed jobs</p>
                    </div>
                    <div class="bg-green-100 p-4 rounded-full">
                        <i class="fas fa-money-bill-wave text-3xl text-green-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border-l-4 border-blue-500">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">This Month</p>
                        <p class="text-2xl font-bold text-gray-900">₦{{ number_format($stats['thisMonthEarnings']) }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ now()->format('F Y') }}</p>
                    </div>
                    <div class="bg-blue-100 p-4 rounded-full">
                        <i class="fas fa-calendar-check text-3xl text-blue-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border-l-4 border-yellow-500">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 mb-1">Average Rating</p>
                        <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['averageRating'], 1) }}/5.0</p>
                        <div class="flex items-center mt-1">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star text-xs {{ $i <= $stats['averageRating'] ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                            @endfor
                        </div>
                    </div>
                    <div class="bg-yellow-100 p-4 rounded-full">
                        <i class="fas fa-star text-3xl text-yellow-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl mb-6">
        <div class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ route('service-requests.index') }}" class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200 rounded-xl border border-blue-200 transition-all group">
                    <div class="bg-blue-600 p-3 rounded-full mb-2 group-hover:scale-110 transition-transform">
                        <i class="fas fa-tasks text-white text-xl"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-700">My Jobs</span>
                </a>
                
                <a href="{{ route('profile.serviceman') }}" class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-purple-50 to-purple-100 hover:from-purple-100 hover:to-purple-200 rounded-xl border border-purple-200 transition-all group">
                    <div class="bg-purple-600 p-3 rounded-full mb-2 group-hover:scale-110 transition-transform">
                        <i class="fas fa-user-edit text-white text-xl"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Update Profile</span>
                </a>
                
                <a href="{{ route('notifications.index') }}" class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-yellow-50 to-yellow-100 hover:from-yellow-100 hover:to-yellow-200 rounded-xl border border-yellow-200 transition-all group">
                    <div class="bg-yellow-600 p-3 rounded-full mb-2 group-hover:scale-110 transition-transform">
                        <i class="fas fa-bell text-white text-xl"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Notifications</span>
                </a>
                
                <a href="{{ route('custom-services.index') }}" class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-pink-50 to-pink-100 hover:from-pink-100 hover:to-pink-200 rounded-xl border border-pink-200 transition-all group">
                    <div class="bg-pink-600 p-3 rounded-full mb-2 group-hover:scale-110 transition-transform">
                        <i class="fas fa-lightbulb text-white text-xl"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Custom Services</span>
                </a>
                
                <a href="{{ route('profile') }}" class="flex flex-col items-center justify-center p-4 bg-gradient-to-br from-green-50 to-green-100 hover:from-green-100 hover:to-green-200 rounded-xl border border-green-200 transition-all group">
                    <div class="bg-green-600 p-3 rounded-full mb-2 group-hover:scale-110 transition-transform">
                        <i class="fas fa-cog text-white text-xl"></i>
                    </div>
                    <span class="text-sm font-medium text-gray-700">Settings</span>
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Service Requests -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Recent Service Requests</h3>
            </div>
            <div class="p-6">
                @forelse($recentRequests as $request)
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-b-0">
                        <div class="flex-1">
                            <h4 class="text-sm font-medium text-gray-900">{{ $request->title }}</h4>
                            <p class="text-sm text-gray-500">{{ $request->client->full_name ?? 'Client' }}</p>
                            <p class="text-xs text-gray-400">{{ $request->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="px-2 py-1 text-xs font-medium rounded-full 
                                @if($request->status === 'PENDING') bg-yellow-100 text-yellow-800
                                @elseif($request->status === 'IN_PROGRESS') bg-blue-100 text-blue-800
                                @elseif($request->status === 'COMPLETED') bg-green-100 text-green-800
                                @else bg-gray-100 text-gray-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', strtolower($request->status))) }}
                            </span>
                            <a href="{{ route('service-requests.show', $request) }}" class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <i class="fas fa-clipboard-list text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-500">No service requests yet</p>
                        <p class="text-sm text-gray-400">Complete your profile to start receiving requests</p>
                    </div>
                @endforelse
                
                @if($recentRequests->count() > 0)
                    <div class="mt-4 text-center">
                        <a href="{{ route('service-requests.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                            View all requests <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Notifications -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Recent Notifications</h3>
                    <a href="{{ route('notifications.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        View all
                    </a>
                </div>
            </div>
            <div class="p-6">
                @forelse($notifications->where('user_id', auth()->id()) as $notification)
                    <div class="flex items-start space-x-3 py-3 border-b border-gray-100 last:border-b-0">
                        <div class="flex-shrink-0">
                            <i class="fas fa-bell text-blue-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900">{{ $notification->title }}</p>
                            <p class="text-xs text-gray-600 mt-1">{{ Str::limit($notification->message, 100) }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <i class="fas fa-bell text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-500">No new notifications</p>
                        <p class="text-sm text-gray-400 mt-1">You'll be notified when you receive new service requests</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
