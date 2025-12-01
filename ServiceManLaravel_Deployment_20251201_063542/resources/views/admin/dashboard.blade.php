@extends('layouts.app')

@section('title', 'Admin Dashboard')

@push('styles')
<style>
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .animate-fade-in-up {
        animation: fadeInUp 0.5s ease-out;
    }
    
    .stat-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    .pulse-badge {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    
    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: .7;
        }
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 animate-fade-in-up">
            <div class="bg-blue-600 rounded-lg shadow-md overflow-hidden">
                <div class="px-8 py-10">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-full flex items-center justify-center border-2 border-white/30">
                                <i class="fas fa-shield-alt text-3xl text-white"></i>
                            </div>
                            <div>
                                <h1 class="text-3xl md:text-4xl font-bold text-white">Admin Control Center</h1>
                                <p class="text-indigo-100 mt-2 text-lg">Manage service requests, users, and platform analytics</p>
                            </div>
                        </div>
                        <div class="hidden md:flex items-center space-x-2">
                            <div class="text-right bg-white/10 backdrop-blur-sm px-4 py-2 rounded-xl border border-white/20">
                                <p class="text-white/80 text-xs font-medium">Current Time</p>
                                <p class="text-white text-lg font-bold">{{ now()->format('H:i A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
            <div class="bg-gray-50 px-8 py-5 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-bolt text-yellow-500 mr-3"></i>
                    Quick Actions
                </h2>
            </div>
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Manage Requests -->
                    <a href="{{ route('admin.service-requests') }}" 
                       class="group flex items-center p-5 bg-gradient-to-br from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200 rounded-2xl border-2 border-blue-200 hover:border-blue-300 transition-all transform hover:scale-105 shadow-md hover:shadow-lg">
                        <div class="bg-gradient-to-br from-blue-600 to-blue-700 p-4 rounded-xl mr-4 group-hover:scale-110 transition-transform shadow-lg">
                            <i class="fas fa-clipboard-list text-white text-2xl"></i>
                        </div>
                        <div class="flex-1">
                            <span class="text-sm font-bold text-gray-900 block">Manage Requests</span>
                            <span class="text-xs text-gray-600">View all service requests</span>
                        </div>
                    </a>
                    
                    <!-- Manage Categories -->
                    <a href="{{ route('admin.categories') }}" 
                       class="group flex items-center p-5 bg-gradient-to-br from-green-50 to-green-100 hover:from-green-100 hover:to-green-200 rounded-2xl border-2 border-green-200 hover:border-green-300 transition-all transform hover:scale-105 shadow-md hover:shadow-lg">
                        <div class="bg-gradient-to-br from-green-600 to-green-700 p-4 rounded-xl mr-4 group-hover:scale-110 transition-transform shadow-lg">
                            <i class="fas fa-tags text-white text-2xl"></i>
                        </div>
                        <div class="flex-1">
                            <span class="text-sm font-bold text-gray-900 block">Manage Categories</span>
                            <span class="text-xs text-gray-600">Add or edit categories</span>
                        </div>
                    </a>
                    
                    <!-- Manage Users -->
                    <a href="{{ route('admin.users') }}" 
                       class="group flex items-center p-5 bg-gradient-to-br from-purple-50 to-purple-100 hover:from-purple-100 hover:to-purple-200 rounded-2xl border-2 border-purple-200 hover:border-purple-300 transition-all transform hover:scale-105 shadow-md hover:shadow-lg">
                        <div class="bg-gradient-to-br from-purple-600 to-purple-700 p-4 rounded-xl mr-4 group-hover:scale-110 transition-transform shadow-lg">
                            <i class="fas fa-users text-white text-2xl"></i>
                        </div>
                        <div class="flex-1">
                            <span class="text-sm font-bold text-gray-900 block">Manage Users</span>
                            <span class="text-xs text-gray-600">View clients & servicemen</span>
                        </div>
                    </a>
                    
                    <!-- Manage Testimonials -->
                    <a href="{{ route('admin.testimonials') }}" 
                       class="group flex items-center p-5 bg-gradient-to-br from-yellow-50 to-yellow-100 hover:from-yellow-100 hover:to-yellow-200 rounded-2xl border-2 border-yellow-200 hover:border-yellow-300 transition-all transform hover:scale-105 shadow-md hover:shadow-lg">
                        <div class="bg-gradient-to-br from-yellow-500 to-yellow-600 p-4 rounded-xl mr-4 group-hover:scale-110 transition-transform shadow-lg">
                            <i class="fas fa-star text-white text-2xl"></i>
                        </div>
                        <div class="flex-1">
                            <span class="text-sm font-bold text-gray-900 block">Testimonials</span>
                            <span class="text-xs text-gray-600">Control landing page reviews</span>
                        </div>
                    </a>
                    
                    <!-- Approve Servicemen -->
                    <a href="{{ route('admin.servicemen-approval') }}" 
                       class="group flex items-center justify-between p-5 bg-gradient-to-br from-yellow-50 to-yellow-100 hover:from-yellow-100 hover:to-yellow-200 rounded-2xl border-2 border-yellow-200 hover:border-yellow-300 transition-all transform hover:scale-105 shadow-md hover:shadow-lg relative">
                        <div class="flex items-center flex-1">
                            <div class="bg-gradient-to-br from-yellow-600 to-yellow-700 p-4 rounded-xl mr-4 group-hover:scale-110 transition-transform shadow-lg">
                                <i class="fas fa-user-check text-white text-2xl"></i>
                            </div>
                            <div class="flex-1">
                                <span class="text-sm font-bold text-gray-900 block">Approve Servicemen</span>
                                <span class="text-xs text-gray-600">Review registrations</span>
                            </div>
                        </div>
                        @php
                            $pendingApprovalCount = \App\Models\User::where('user_type', 'SERVICEMAN')
                                ->where('is_approved', false)->count();
                        @endphp
                        @if($pendingApprovalCount > 0)
                            <span class="pulse-badge absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-8 h-8 flex items-center justify-center shadow-lg border-2 border-white animate-pulse">
                                {{ $pendingApprovalCount }}
                            </span>
                        @endif
                    </a>
                    
                    <!-- Pending Servicemen (Category Assignment) -->
                    <a href="{{ route('admin.pending-servicemen') }}" 
                       class="group flex items-center justify-between p-5 bg-gradient-to-br from-orange-50 to-orange-100 hover:from-orange-100 hover:to-orange-200 rounded-2xl border-2 border-orange-200 hover:border-orange-300 transition-all transform hover:scale-105 shadow-md hover:shadow-lg relative">
                        <div class="flex items-center flex-1">
                            <div class="bg-gradient-to-br from-orange-600 to-orange-700 p-4 rounded-xl mr-4 group-hover:scale-110 transition-transform shadow-lg">
                                <i class="fas fa-user-clock text-white text-2xl"></i>
                            </div>
                            <div class="flex-1">
                                <span class="text-sm font-bold text-gray-900 block">Assign Categories</span>
                                <span class="text-xs text-gray-600">Pending servicemen</span>
                            </div>
                        </div>
                        @php
                            $pendingCount = \App\Models\User::where('user_type', 'SERVICEMAN')
                                ->whereHas('servicemanProfile', function($q) {
                                    $q->whereNull('category_id');
                                })->count();
                        @endphp
                        @if($pendingCount > 0)
                            <span class="pulse-badge absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-8 h-8 flex items-center justify-center shadow-lg border-2 border-white">
                                {{ $pendingCount }}
                            </span>
                        @endif
                    </a>
                    
                    <!-- Custom Service Requests -->
                    <a href="{{ route('admin.custom-service-requests') }}" 
                       class="group flex items-center justify-between p-5 bg-gradient-to-br from-pink-50 to-pink-100 hover:from-pink-100 hover:to-pink-200 rounded-2xl border-2 border-pink-200 hover:border-pink-300 transition-all transform hover:scale-105 shadow-md hover:shadow-lg relative">
                        <div class="flex items-center flex-1">
                            <div class="bg-gradient-to-br from-pink-600 to-pink-700 p-4 rounded-xl mr-4 group-hover:scale-110 transition-transform shadow-lg">
                                <i class="fas fa-lightbulb text-white text-2xl"></i>
                            </div>
                            <div class="flex-1">
                                <span class="text-sm font-bold text-gray-900 block">Custom Services</span>
                                <span class="text-xs text-gray-600">Review new requests</span>
                            </div>
                        </div>
                        @php
                            $customRequestsCount = \App\Models\CustomServiceRequest::where('status', 'PENDING')->count();
                        @endphp
                        @if($customRequestsCount > 0)
                            <span class="pulse-badge absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold rounded-full w-8 h-8 flex items-center justify-center shadow-lg border-2 border-white">
                                {{ $customRequestsCount }}
                            </span>
                        @endif
                    </a>
                    
                    <!-- View Analytics -->
                    <a href="{{ route('admin.analytics') }}" 
                       class="group flex items-center p-5 bg-gradient-to-br from-yellow-50 to-yellow-100 hover:from-yellow-100 hover:to-yellow-200 rounded-2xl border-2 border-yellow-200 hover:border-yellow-300 transition-all transform hover:scale-105 shadow-md hover:shadow-lg">
                        <div class="bg-gradient-to-br from-yellow-600 to-yellow-700 p-4 rounded-xl mr-4 group-hover:scale-110 transition-transform shadow-lg">
                            <i class="fas fa-chart-bar text-white text-2xl"></i>
                        </div>
                        <div class="flex-1">
                            <span class="text-sm font-bold text-gray-900 block">View Analytics</span>
                            <span class="text-xs text-gray-600">Platform statistics</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-6 mb-8">
            <!-- Total Requests -->
            <div class="stat-card bg-white rounded-2xl shadow-lg overflow-hidden border-t-4 border-blue-500">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-gradient-to-br from-blue-100 to-blue-200 p-3 rounded-xl">
                        <i class="fas fa-clipboard-list text-2xl text-blue-600"></i>
                    </div>
                    </div>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Total Requests</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['total_requests'] }}</p>
                </div>
            </div>

            <!-- Pending Assignment -->
            <div class="stat-card bg-white rounded-2xl shadow-lg overflow-hidden border-t-4 border-yellow-500 relative">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-gradient-to-br from-yellow-100 to-yellow-200 p-3 rounded-xl">
                        <i class="fas fa-clock text-2xl text-yellow-600"></i>
                    </div>
                        @if($stats['pending_assignment'] > 0)
                            <span class="pulse-badge absolute top-2 right-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                                Action Required
                            </span>
                        @endif
                    </div>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Pending Assignment</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['pending_assignment'] }}</p>
                </div>
            </div>

            <!-- In Progress -->
            <div class="stat-card bg-white rounded-2xl shadow-lg overflow-hidden border-t-4 border-green-500">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-gradient-to-br from-green-100 to-green-200 p-3 rounded-xl">
                        <i class="fas fa-tools text-2xl text-green-600"></i>
                    </div>
                    </div>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">In Progress</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['in_progress'] }}</p>
                </div>
            </div>

            <!-- Completed -->
            <div class="stat-card bg-white rounded-2xl shadow-lg overflow-hidden border-t-4 border-purple-500">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-gradient-to-br from-purple-100 to-purple-200 p-3 rounded-xl">
                            <i class="fas fa-check-circle text-2xl text-purple-600"></i>
                    </div>
                    </div>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Completed</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['completed'] }}</p>
                </div>
            </div>

            <!-- Emergency Requests -->
            <div class="stat-card bg-white rounded-2xl shadow-lg overflow-hidden border-t-4 border-red-500">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-gradient-to-br from-red-100 to-red-200 p-3 rounded-xl">
                        <i class="fas fa-exclamation-triangle text-2xl text-red-600"></i>
                    </div>
                    </div>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Emergency</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['emergency_requests'] }}</p>
                </div>
            </div>

            <!-- Total Revenue -->
            <div class="stat-card bg-gradient-to-br from-green-600 to-green-700 rounded-2xl shadow-lg overflow-hidden">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl border border-white/30">
                            <i class="fas fa-money-bill-wave text-2xl text-white"></i>
                    </div>
                    </div>
                    <p class="text-sm font-semibold text-green-100 uppercase tracking-wide mb-1">Total Revenue</p>
                    <p class="text-3xl font-bold text-white">₦{{ number_format($stats['total_revenue']) }}</p>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid lg:grid-cols-2 gap-8 mb-8">
            <!-- Recent Service Requests -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-8 py-5 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center">
                            <i class="fas fa-list text-blue-600 mr-3"></i>
                            Recent Service Requests
                        </h2>
                        <a href="{{ route('admin.service-requests') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold hover:underline">
                            View All →
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @forelse($recentRequests as $request)
                            <div class="group flex items-center justify-between p-4 hover:bg-gray-50 rounded-xl transition-all border border-gray-200 hover:border-blue-300 hover:shadow-md">
                                <div class="flex items-center space-x-4 flex-1">
                                        <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center text-white font-bold shadow-md">
                                            #{{ $request->id }}
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 truncate">
                                                {{ $request->category->name }}
                                            </p>
                                        <p class="text-xs text-gray-600 flex items-center mt-1">
                                            <i class="fas fa-user text-gray-400 mr-2"></i>
                                                {{ $request->client->full_name }}
                                            </p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    @if($request->is_emergency)
                                        <span class="px-2 py-1 text-xs font-bold rounded-full bg-red-100 text-red-800 border border-red-200">
                                            Emergency
                                    </span>
                                    @endif
                                    <span class="px-2 py-1 text-xs font-bold rounded-full bg-gray-100 text-gray-800 border border-gray-200">
                                        {{ ucfirst(str_replace('_', ' ', strtolower($request->status))) }}
                                    </span>
                                    <a href="{{ route('service-requests.show', $request) }}" class="text-blue-600 hover:text-blue-800 hover:bg-blue-50 p-2 rounded-lg transition-all">
                                        <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-12">
                                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                                    <i class="fas fa-clipboard-list text-3xl text-gray-400"></i>
                                </div>
                                <p class="text-gray-600 font-medium">No recent service requests</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Pending Negotiations (Disabled Feature) -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden opacity-60">
                <div class="bg-gradient-to-r from-yellow-50 to-orange-50 px-8 py-5 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center">
                            <i class="fas fa-comments text-yellow-600 mr-3"></i>
                            Pending Negotiations
                        </h2>
                        <span class="text-xs font-semibold text-gray-600 bg-gray-200 px-3 py-1 rounded-full">
                            Coming Soon
                        </span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="text-center py-12">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-4">
                            <i class="fas fa-hourglass-half text-3xl text-gray-400"></i>
                                </div>
                        <p class="text-gray-600 font-medium mb-2">Feature Under Review</p>
                        <p class="text-gray-500 text-sm">Negotiation functionality is currently being reviewed by management</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
