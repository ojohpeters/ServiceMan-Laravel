@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Welcome Section -->
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
        <div class="p-6 bg-gradient-to-r from-purple-600 to-red-600 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Admin Dashboard</h1>
                    <p class="text-purple-100 mt-1">Manage the ServiceMan platform</p>
                </div>
                <div class="text-right">
                    <a href="{{ route('admin.analytics') }}" class="bg-white text-purple-600 px-4 py-2 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
                        <i class="fas fa-chart-bar mr-2"></i>View Analytics
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border-l-4 border-blue-500 transform hover:scale-105 transition-transform">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Total Users</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">{{ $stats['totalUsers'] }}</p>
                        <div class="flex items-center mt-2 space-x-2 text-xs">
                            <span class="bg-blue-100 text-blue-700 px-2 py-1 rounded font-medium">{{ $stats['total_clients'] }} Clients</span>
                            <span class="bg-purple-100 text-purple-700 px-2 py-1 rounded font-medium">{{ $stats['total_servicemen'] }} Pros</span>
                        </div>
                    </div>
                    <div class="bg-blue-100 p-4 rounded-xl">
                        <i class="fas fa-users text-3xl text-blue-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border-l-4 border-green-500 transform hover:scale-105 transition-transform">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Service Requests</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">{{ $stats['totalServiceRequests'] }}</p>
                        <p class="text-xs text-gray-500 mt-2">Platform activity</p>
                    </div>
                    <div class="bg-green-100 p-4 rounded-xl">
                        <i class="fas fa-clipboard-list text-3xl text-green-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border-l-4 border-orange-500 transform hover:scale-105 transition-transform">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Pending Review</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">{{ $stats['pendingRequests'] }}</p>
                        <p class="text-xs text-gray-500 mt-2">Needs assignment</p>
                    </div>
                    <div class="bg-orange-100 p-4 rounded-xl">
                        <i class="fas fa-exclamation-triangle text-3xl text-orange-600"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border-l-4 border-emerald-500 transform hover:scale-105 transition-transform">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Completed</p>
                        <p class="text-4xl font-bold text-gray-900 mt-2">{{ $stats['completedRequests'] }}</p>
                        <p class="text-xs text-gray-500 mt-2">Successfully finished</p>
                    </div>
                    <div class="bg-emerald-100 p-4 rounded-xl">
                        <i class="fas fa-check-double text-3xl text-emerald-600"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Service Requests -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Recent Service Requests</h3>
                    <a href="{{ route('admin.service-requests') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        View all
                    </a>
                </div>
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
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Users -->
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Recent Users</h3>
                    <a href="{{ route('admin.users') }}" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        View all
                    </a>
                </div>
            </div>
            <div class="p-6">
                @forelse($recentUsers as $user)
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-b-0">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-white text-sm"></i>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900">{{ $user->full_name }}</h4>
                                <p class="text-xs text-gray-500">{{ ucfirst(strtolower($user->user_type)) }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-400">{{ $user->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <i class="fas fa-users text-4xl text-gray-400 mb-4"></i>
                        <p class="text-gray-500">No users yet</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('admin.categories') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="flex-shrink-0">
                        <i class="fas fa-tags text-2xl text-blue-600"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-sm font-medium text-gray-900">Manage Categories</h4>
                        <p class="text-sm text-gray-500">Add, edit, or remove service categories</p>
                    </div>
                </a>

                <a href="{{ route('admin.service-requests') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clipboard-list text-2xl text-green-600"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-sm font-medium text-gray-900">Service Requests</h4>
                        <p class="text-sm text-gray-500">Review and manage service requests</p>
                    </div>
                </a>

                <a href="{{ route('admin.analytics') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                    <div class="flex-shrink-0">
                        <i class="fas fa-chart-bar text-2xl text-purple-600"></i>
                    </div>
                    <div class="ml-4">
                        <h4 class="text-sm font-medium text-gray-900">Analytics</h4>
                        <p class="text-sm text-gray-500">View platform statistics and insights</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
