@extends('layouts.app')

@section('title', 'Dashboard - Client')

@push('styles')
<style>
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-slide-in {
        animation: slideInUp 0.4s ease-out;
    }
    
    .stat-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Welcome Header -->
        <div class="mb-8 animate-slide-in">
            <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 rounded-2xl shadow-2xl overflow-hidden">
                <div class="px-8 py-10">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center border-2 border-white/30">
                                    <i class="fas fa-user text-3xl text-white"></i>
                                </div>
                            </div>
                <div>
                                <h1 class="text-3xl md:text-4xl font-bold text-white">Welcome back, {{ Auth::user()->first_name }}!</h1>
                                <p class="text-blue-100 mt-2 text-lg">Manage your service requests and track progress</p>
                            </div>
                </div>
                        <div class="mt-6 md:mt-0">
                            <a href="{{ route('services') }}" class="inline-flex items-center px-6 py-3 bg-white text-blue-600 rounded-xl font-semibold hover:bg-blue-50 transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                                <i class="fas fa-search mr-2"></i>
                                Find Services
                            </a>
                        </div>
                    </div>
            </div>
        </div>
    </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Requests -->
            <div class="stat-card bg-white rounded-2xl shadow-lg overflow-hidden border-l-4 border-blue-500">
            <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Total Requests</p>
                            <p class="text-4xl font-bold text-gray-900">{{ $stats['totalRequests'] }}</p>
                            <p class="text-xs text-gray-500 mt-2">All time</p>
                        </div>
                        <div class="bg-gradient-to-br from-blue-100 to-blue-200 p-4 rounded-xl">
                            <i class="fas fa-clipboard-list text-3xl text-blue-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending -->
            <div class="stat-card bg-white rounded-2xl shadow-lg overflow-hidden border-l-4 border-yellow-500">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Pending</p>
                            <p class="text-4xl font-bold text-gray-900">{{ $stats['pendingRequests'] }}</p>
                            <p class="text-xs text-gray-500 mt-2">Awaiting action</p>
                        </div>
                        <div class="bg-gradient-to-br from-yellow-100 to-yellow-200 p-4 rounded-xl">
                            <i class="fas fa-clock text-3xl text-yellow-600"></i>
                        </div>
                </div>
            </div>
        </div>

            <!-- In Progress -->
            <div class="stat-card bg-white rounded-2xl shadow-lg overflow-hidden border-l-4 border-purple-500">
            <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">In Progress</p>
                            <p class="text-4xl font-bold text-gray-900">{{ $stats['inProgressRequests'] }}</p>
                            <p class="text-xs text-gray-500 mt-2">Active now</p>
                        </div>
                        <div class="bg-gradient-to-br from-purple-100 to-purple-200 p-4 rounded-xl">
                            <i class="fas fa-tools text-3xl text-purple-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Completed -->
            <div class="stat-card bg-white rounded-2xl shadow-lg overflow-hidden border-l-4 border-green-500">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-1">Completed</p>
                            <p class="text-4xl font-bold text-gray-900">{{ $stats['completedRequests'] }}</p>
                            <p class="text-xs text-gray-500 mt-2">Successfully done</p>
                        </div>
                        <div class="bg-gradient-to-br from-green-100 to-green-200 p-4 rounded-xl">
                            <i class="fas fa-check-circle text-3xl text-green-600"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Spending Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="stat-card bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl shadow-xl overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-blue-100 uppercase tracking-wider mb-2">Total Spending</p>
                            <p class="text-5xl font-bold text-white">₦{{ number_format($stats['totalSpent']) }}</p>
                            <p class="text-sm text-blue-200 mt-3">All time expenditure</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm p-5 rounded-2xl border border-white/20">
                            <i class="fas fa-wallet text-5xl text-white"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stat-card bg-gradient-to-br from-green-600 to-green-700 rounded-2xl shadow-xl overflow-hidden">
                <div class="p-8">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-semibold text-green-100 uppercase tracking-wider mb-2">This Month</p>
                            <p class="text-5xl font-bold text-white">₦{{ number_format($stats['thisMonthSpent']) }}</p>
                            <p class="text-sm text-green-200 mt-3">{{ now()->format('F Y') }}</p>
                        </div>
                        <div class="bg-white/10 backdrop-blur-sm p-5 rounded-2xl border border-white/20">
                            <i class="fas fa-calendar-alt text-5xl text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-2xl shadow-xl mb-8 overflow-hidden">
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-8 py-5 border-b border-gray-200">
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-bolt text-yellow-500 mr-3"></i>
                    Quick Actions
                </h3>
            </div>
            <div class="p-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <a href="{{ route('services') }}" class="group flex flex-col items-center justify-center p-6 bg-gradient-to-br from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200 rounded-2xl border-2 border-blue-200 hover:border-blue-300 transition-all transform hover:scale-105 shadow-md hover:shadow-lg">
                        <div class="bg-gradient-to-br from-blue-600 to-blue-700 p-4 rounded-xl mb-3 group-hover:scale-110 transition-transform shadow-lg">
                            <i class="fas fa-plus text-white text-2xl"></i>
                        </div>
                        <span class="text-sm font-bold text-gray-800">New Request</span>
                        <span class="text-xs text-gray-600 mt-1">Book a service</span>
                    </a>
                    
                    <a href="{{ route('service-requests.index') }}" class="group flex flex-col items-center justify-center p-6 bg-gradient-to-br from-purple-50 to-purple-100 hover:from-purple-100 hover:to-purple-200 rounded-2xl border-2 border-purple-200 hover:border-purple-300 transition-all transform hover:scale-105 shadow-md hover:shadow-lg">
                        <div class="bg-gradient-to-br from-purple-600 to-purple-700 p-4 rounded-xl mb-3 group-hover:scale-110 transition-transform shadow-lg">
                            <i class="fas fa-list text-white text-2xl"></i>
                        </div>
                        <span class="text-sm font-bold text-gray-800">All Requests</span>
                        <span class="text-xs text-gray-600 mt-1">View history</span>
                    </a>
                    
                    <a href="{{ route('notifications.index') }}" class="group flex flex-col items-center justify-center p-6 bg-gradient-to-br from-yellow-50 to-yellow-100 hover:from-yellow-100 hover:to-yellow-200 rounded-2xl border-2 border-yellow-200 hover:border-yellow-300 transition-all transform hover:scale-105 shadow-md hover:shadow-lg relative">
                        <div class="bg-gradient-to-br from-yellow-600 to-yellow-700 p-4 rounded-xl mb-3 group-hover:scale-110 transition-transform shadow-lg">
                            <i class="fas fa-bell text-white text-2xl"></i>
                    </div>
                        <span class="text-sm font-bold text-gray-800">Notifications</span>
                        <span class="text-xs text-gray-600 mt-1">Stay updated</span>
                        @php
                            $unreadCount = auth()->user()->notifications()->where('is_read', false)->count();
                        @endphp
                        @if($unreadCount > 0)
                            <span class="absolute top-2 right-2 bg-red-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center shadow-lg animate-pulse">
                                {{ $unreadCount }}
                            </span>
                        @endif
                    </a>
                    
                    <a href="{{ route('profile') }}" class="group flex flex-col items-center justify-center p-6 bg-gradient-to-br from-green-50 to-green-100 hover:from-green-100 hover:to-green-200 rounded-2xl border-2 border-green-200 hover:border-green-300 transition-all transform hover:scale-105 shadow-md hover:shadow-lg">
                        <div class="bg-gradient-to-br from-green-600 to-green-700 p-4 rounded-xl mb-3 group-hover:scale-110 transition-transform shadow-lg">
                            <i class="fas fa-user-cog text-white text-2xl"></i>
                    </div>
                        <span class="text-sm font-bold text-gray-800">Profile</span>
                        <span class="text-xs text-gray-600 mt-1">Manage account</span>
                    </a>
            </div>
        </div>
    </div>

        <!-- Recent Activity Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Service Requests -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 px-8 py-5 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-900 flex items-center">
                            <i class="fas fa-history text-blue-600 mr-3"></i>
                            Recent Service Requests
                        </h3>
                        <a href="{{ route('service-requests.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold hover:underline">
                            View All →
                        </a>
                    </div>
            </div>
            <div class="p-6">
                @forelse($recentRequests as $request)
                        <div class="group flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 sm:gap-4 p-4 hover:bg-gray-50 rounded-xl transition-all border-b border-gray-100 last:border-b-0">
                            <div class="flex items-start sm:items-center space-x-3 sm:space-x-4 flex-1 min-w-0">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center text-white font-bold shadow-md text-xs sm:text-sm">
                                        #{{ $request->id }}
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-semibold text-gray-900 truncate">{{ $request->category->name ?? 'Service Request' }}</h4>
                                    <p class="text-xs sm:text-sm text-gray-600 flex items-center mt-1 truncate">
                                        <i class="fas fa-tag text-gray-400 mr-1 sm:mr-2 text-xs"></i>
                                        {{ $request->category->name ?? 'Service' }}
                                    </p>
                                    <p class="text-xs text-gray-500 flex items-center mt-1">
                                        <i class="fas fa-clock text-gray-400 mr-1 sm:mr-2"></i>
                                        {{ $request->created_at->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center justify-between sm:justify-end space-x-2 sm:space-x-3 w-full sm:w-auto">
                                <span class="px-2 sm:px-3 py-1 sm:py-1.5 text-xs font-bold rounded-full shadow-sm whitespace-nowrap
                                    @if($request->status === 'PENDING_ADMIN_ASSIGNMENT' || $request->status === 'PENDING') bg-yellow-100 text-yellow-800 border border-yellow-200
                                    @elseif($request->status === 'IN_PROGRESS') bg-blue-100 text-blue-800 border border-blue-200
                                    @elseif($request->status === 'COMPLETED') bg-green-100 text-green-800 border border-green-200
                                    @elseif($request->status === 'SERVICEMAN_INSPECTED') bg-indigo-100 text-indigo-800 border border-indigo-200
                                    @elseif($request->status === 'AWAITING_PAYMENT' || $request->status === 'PAYMENT_CONFIRMED') bg-green-100 text-green-800 border border-green-200
                                    @else bg-gray-100 text-gray-800 border border-gray-200 @endif">
                                    {{ \App\Models\ServiceRequest::STATUS_CHOICES[$request->status] ?? ucfirst(str_replace('_', ' ', strtolower($request->status))) }}
                                </span>
                                <a href="{{ route('service-requests.show', $request) }}" class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 p-2 rounded-lg transition-all flex-shrink-0">
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                @empty
                        <div class="text-center py-12">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                                <i class="fas fa-clipboard-list text-3xl text-gray-400"></i>
                            </div>
                            <p class="text-gray-600 font-medium mb-2">No service requests yet</p>
                            <p class="text-gray-500 text-sm mb-4">Start by browsing our services</p>
                            <a href="{{ route('services') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl font-semibold hover:from-blue-700 hover:to-purple-700 transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                                <i class="fas fa-search mr-2"></i>
                            Browse Services
                        </a>
                    </div>
                @endforelse
                </div>
            </div>

            <!-- Recent Notifications -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-yellow-50 to-orange-50 px-8 py-5 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-900 flex items-center">
                            <i class="fas fa-bell text-yellow-600 mr-3"></i>
                            Recent Notifications
                        </h3>
                        <a href="{{ route('notifications.index') }}" class="text-yellow-600 hover:text-yellow-800 text-sm font-semibold hover:underline">
                            View All →
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    @forelse($notifications->where('user_id', auth()->id())->take(5) as $notification)
                        <div class="group flex items-start space-x-4 p-4 hover:bg-gray-50 rounded-xl transition-all border-b border-gray-100 last:border-b-0 {{ $notification->is_read ? '' : 'bg-blue-50/50' }}">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-full flex items-center justify-center shadow-md">
                                    <i class="fas fa-bell text-white"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 mb-1">{{ $notification->title }}</p>
                                <p class="text-xs text-gray-700 mb-2 line-clamp-2">{{ Str::limit($notification->message, 120) }}</p>
                                <div class="flex items-center space-x-3">
                                    <p class="text-xs text-gray-500 flex items-center">
                                        <i class="fas fa-clock mr-1"></i>
                                        {{ $notification->created_at->diffForHumans() }}
                                    </p>
                                    @if(!$notification->is_read)
                                        <span class="text-xs font-semibold text-blue-600 flex items-center">
                                            <span class="w-2 h-2 bg-blue-600 rounded-full mr-1 animate-pulse"></span>
                                            New
                                        </span>
                @endif
            </div>
        </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                                <i class="fas fa-bell text-3xl text-gray-400"></i>
                            </div>
                            <p class="text-gray-600 font-medium mb-2">No notifications yet</p>
                            <p class="text-gray-500 text-sm">You'll be notified about updates to your service requests</p>
                        </div>
                    @endforelse
                    </div>
            </div>
        </div>
    </div>
</div>
@endsection
