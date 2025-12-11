@extends('layouts.app')

@section('title', 'Servicemen Management')

@push('styles')
<style>
    @keyframes approveGlow {
        0%, 100% { 
            box-shadow: 0 0 10px rgba(34, 197, 94, 0.5);
        }
        50% { 
            box-shadow: 0 0 20px rgba(34, 197, 94, 0.8), 0 0 30px rgba(34, 197, 94, 0.6);
        }
    }
    
    .approve-button {
        animation: approveGlow 2s ease-in-out infinite;
    }
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Servicemen Management</h1>
        <p class="text-gray-600">Filter, search, and manage all service providers</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 mb-6 sm:mb-8">
        <div class="bg-white rounded-lg sm:rounded-xl shadow-md sm:shadow-lg p-3 sm:p-6 border-t-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Total</p>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-blue-100 p-2 sm:p-3 rounded-lg">
                    <i class="fas fa-users text-blue-600 text-lg sm:text-xl lg:text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg sm:rounded-xl shadow-md sm:shadow-lg p-3 sm:p-6 border-t-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Approved</p>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">{{ $stats['approved'] }}</p>
                </div>
                <div class="bg-green-100 p-2 sm:p-3 rounded-lg">
                    <i class="fas fa-user-check text-green-600 text-lg sm:text-xl lg:text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg sm:rounded-xl shadow-md sm:shadow-lg p-3 sm:p-6 border-t-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Pending</p>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">{{ $stats['pending_approval'] }}</p>
                </div>
                <div class="bg-yellow-100 p-2 sm:p-3 rounded-lg">
                    <i class="fas fa-clock text-yellow-600 text-lg sm:text-xl lg:text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg sm:rounded-xl shadow-md sm:shadow-lg p-3 sm:p-6 border-t-4 border-purple-500 col-span-2 sm:col-span-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Available</p>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">{{ $stats['available'] }}</p>
                </div>
                <div class="bg-purple-100 p-2 sm:p-3 rounded-lg">
                    <i class="fas fa-check-circle text-purple-600 text-lg sm:text-xl lg:text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg sm:rounded-xl shadow-md sm:shadow-lg p-3 sm:p-6 border-t-4 border-teal-500 col-span-2 sm:col-span-1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Verified</p>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900">{{ $stats['verified'] }}</p>
                </div>
                <div class="bg-teal-100 p-2 sm:p-3 rounded-lg">
                    <i class="fas fa-shield-check text-teal-600 text-lg sm:text-xl lg:text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 mb-8">
        <form method="GET" action="{{ route('admin.servicemen') }}" class="space-y-4" id="filterForm">
            <!-- First Row: Search and Category -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Search -->
                <div class="sm:col-span-2 lg:col-span-1">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-search mr-2 text-blue-600"></i>Search
                    </label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Name, email, username, or skill..."
                           class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm sm:text-base">
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-tag mr-2 text-green-600"></i>Service Type
                    </label>
                    <select name="category" 
                            class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm sm:text-base bg-white">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Second Row: Skills -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-tools mr-2 text-indigo-600"></i>Skills
                </label>
                <select name="skill" 
                        class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm sm:text-base bg-white">
                    <option value="">All Skills</option>
                    @foreach($allSkills as $skill)
                        <option value="{{ $skill }}" {{ request('skill') === $skill ? 'selected' : '' }}>
                            {{ $skill }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Third Row: Status Filters -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Approval Status -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-user-check mr-2 text-purple-600"></i>Approval Status
                    </label>
                    <select name="approval_status" 
                            class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm sm:text-base bg-white">
                        <option value="">All</option>
                        <option value="approved" {{ request('approval_status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="pending" {{ request('approval_status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    </select>
                </div>

                <!-- Availability -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-toggle-on mr-2 text-teal-600"></i>Availability
                    </label>
                    <select name="availability" 
                            class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent text-sm sm:text-base bg-white">
                        <option value="">All</option>
                        <option value="available" {{ request('availability') === 'available' ? 'selected' : '' }}>Available</option>
                        <option value="unavailable" {{ request('availability') === 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                    </select>
                </div>

                <!-- Email Verification -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-shield-check mr-2 text-cyan-600"></i>Email Verified
                    </label>
                    <select name="verified" 
                            class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-cyan-500 focus:border-transparent text-sm sm:text-base bg-white">
                        <option value="">All</option>
                        <option value="yes" {{ request('verified') === 'yes' ? 'selected' : '' }}>Verified</option>
                        <option value="no" {{ request('verified') === 'no' ? 'selected' : '' }}>Not Verified</option>
                    </select>
                </div>
            </div>

            <!-- Fourth Row: Sort and Actions -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <!-- Sort By -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-sort mr-2 text-orange-600"></i>Sort By
                    </label>
                    <select name="sort_by" 
                            class="w-full px-4 py-2.5 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent text-sm sm:text-base bg-white">
                        <option value="ranking" {{ request('sort_by', 'ranking') === 'ranking' ? 'selected' : '' }}>Category Ranking</option>
                        <option value="rating" {{ request('sort_by') === 'rating' ? 'selected' : '' }}>Highest Rating</option>
                        <option value="jobs" {{ request('sort_by') === 'jobs' ? 'selected' : '' }}>Most Jobs</option>
                        <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Name (A-Z)</option>
                        <option value="latest" {{ request('sort_by') === 'latest' ? 'selected' : '' }}>Latest First</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-end gap-2 sm:gap-3">
                    <button type="submit" 
                            class="flex-1 px-4 sm:px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors text-sm sm:text-base shadow-md hover:shadow-lg">
                        <i class="fas fa-filter mr-2"></i><span class="hidden sm:inline">Apply</span><span class="sm:hidden">Filter</span>
                    </button>
                    <a href="{{ route('admin.servicemen') }}" 
                       class="px-4 sm:px-6 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors text-sm sm:text-base shadow-md hover:shadow-lg whitespace-nowrap">
                        <i class="fas fa-redo mr-2"></i>Reset
                    </a>
                </div>
            </div>

            <!-- Active Filters Display -->
            @if(request()->hasAny(['search', 'category', 'skill', 'approval_status', 'availability', 'verified']))
                <div class="pt-4 border-t border-gray-200">
                    <p class="text-xs text-gray-600 mb-2 font-semibold">Active Filters:</p>
                    <div class="flex flex-wrap gap-2">
                        @if(request('search'))
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-blue-100 text-blue-800">
                                Search: "{{ request('search') }}"
                                <a href="{{ route('admin.servicemen', array_merge(request()->all(), ['search' => null])) }}" class="ml-2 hover:text-blue-600">
                                    <i class="fas fa-times"></i>
                                </a>
                            </span>
                        @endif
                        @if(request('category'))
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-green-100 text-green-800">
                                Service: {{ $categories->firstWhere('id', request('category'))->name ?? 'N/A' }}
                                <a href="{{ route('admin.servicemen', array_merge(request()->all(), ['category' => null])) }}" class="ml-2 hover:text-green-600">
                                    <i class="fas fa-times"></i>
                                </a>
                            </span>
                        @endif
                        @if(request('skill'))
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-indigo-100 text-indigo-800">
                                Skill: {{ request('skill') }}
                                <a href="{{ route('admin.servicemen', array_merge(request()->all(), ['skill' => null])) }}" class="ml-2 hover:text-indigo-600">
                                    <i class="fas fa-times"></i>
                                </a>
                            </span>
                        @endif
                        @if(request('approval_status'))
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-purple-100 text-purple-800">
                                {{ ucfirst(request('approval_status')) }}
                                <a href="{{ route('admin.servicemen', array_merge(request()->all(), ['approval_status' => null])) }}" class="ml-2 hover:text-purple-600">
                                    <i class="fas fa-times"></i>
                                </a>
                            </span>
                        @endif
                        @if(request('availability'))
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-teal-100 text-teal-800">
                                {{ ucfirst(request('availability')) }}
                                <a href="{{ route('admin.servicemen', array_merge(request()->all(), ['availability' => null])) }}" class="ml-2 hover:text-teal-600">
                                    <i class="fas fa-times"></i>
                                </a>
                            </span>
                        @endif
                        @if(request('verified'))
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs bg-cyan-100 text-cyan-800">
                                {{ request('verified') === 'yes' ? 'Verified' : 'Not Verified' }}
                                <a href="{{ route('admin.servicemen', array_merge(request()->all(), ['verified' => null])) }}" class="ml-2 hover:text-cyan-600">
                                    <i class="fas fa-times"></i>
                                </a>
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        </form>
    </div>

    <!-- Servicemen Cards Grid -->
    @if($servicemen->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mb-6 sm:mb-8">
            @foreach($servicemen as $serviceman)
                @php
                    $profile = $serviceman->servicemanProfile;
                    $rating = $profile->rating ?? 0;
                    $jobs = $profile->total_jobs_completed ?? 0;
                    $category = $profile->category ?? null;
                @endphp
                
                <div class="bg-white rounded-lg sm:rounded-xl shadow-md sm:shadow-lg overflow-hidden border-2 border-gray-200 hover:border-blue-500 transition-all hover:shadow-xl">
                    <!-- Card Header -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-4 sm:p-6">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-3 sm:mb-4">
                            <div class="flex items-center space-x-2 sm:space-x-3 w-full sm:w-auto">
                                <img src="{{ $serviceman->profile_picture_url }}" 
                                     alt="{{ $serviceman->full_name }}"
                                     class="w-12 h-12 sm:w-16 sm:h-16 rounded-full border-2 sm:border-4 border-white object-cover shadow-lg flex-shrink-0">
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-base sm:text-lg font-bold text-white truncate">{{ $serviceman->full_name }}</h3>
                                    <p class="text-blue-100 text-xs sm:text-sm truncate">{{ $serviceman->email }}</p>
                                </div>
                            </div>
                            @if($serviceman->is_approved)
                                <span class="bg-green-500 text-white px-2 sm:px-3 py-1 rounded-full text-xs font-bold whitespace-nowrap self-start sm:self-auto">
                                    <i class="fas fa-check-circle mr-1"></i>Approved
                                </span>
                            @else
                                <span class="bg-yellow-500 text-white px-2 sm:px-3 py-1 rounded-full text-xs font-bold whitespace-nowrap self-start sm:self-auto">
                                    <i class="fas fa-clock mr-1"></i>Pending
                                </span>
                            @endif
                        </div>

                        <!-- Category Badge -->
                        @if($category)
                            @php
                                $rank = $profile->getCategoryRank();
                            @endphp
                            <div class="inline-flex items-center bg-white bg-opacity-90 px-3 py-1 rounded-full">
                                <i class="fas fa-tag mr-2 text-blue-600"></i>
                                <span class="text-gray-900 font-semibold text-sm">{{ $category->name }}</span>
                                @if($rank)
                                    <span class="ml-2 px-2 py-0.5 bg-purple-100 text-purple-800 rounded-full text-xs font-bold">#{{ $rank }}</span>
                                @endif
                            </div>
                        @else
                            <div class="inline-flex items-center bg-yellow-500 bg-opacity-90 px-3 py-1 rounded-full">
                                <i class="fas fa-exclamation-triangle mr-2 text-white"></i>
                                <span class="text-white font-semibold text-sm">No Category</span>
                            </div>
                        @endif
                    </div>

                    <!-- Card Body -->
                    <div class="p-4 sm:p-6">
                        <!-- Stats Row -->
                        <div class="grid grid-cols-2 gap-2 sm:gap-4 mb-3 sm:mb-4">
                            <div class="text-center bg-yellow-50 rounded-lg p-2 sm:p-3">
                                <div class="flex items-center justify-center mb-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= round($rating) ? 'text-yellow-400' : 'text-gray-300' }} text-xs sm:text-sm"></i>
                                    @endfor
                                </div>
                                <p class="text-xs text-gray-600 font-semibold">{{ number_format($rating, 1) }} Rating</p>
                            </div>
                            <div class="text-center bg-green-50 rounded-lg p-2 sm:p-3">
                                <i class="fas fa-briefcase text-green-600 text-xl sm:text-2xl mb-1"></i>
                                <p class="text-xs text-gray-600 font-semibold">{{ $jobs }} Jobs</p>
                            </div>
                        </div>
                        
                        <!-- Category Ranking -->
                        @if($category && $rating)
                            @php
                                $rank = $profile->getCategoryRank();
                            @endphp
                            @if($rank)
                                <div class="mb-3 sm:mb-4 text-center">
                                    <div class="inline-flex items-center bg-purple-100 rounded-lg px-3 py-2">
                                        <i class="fas fa-trophy text-purple-600 mr-2"></i>
                                        <span class="text-xs sm:text-sm font-bold text-purple-800">Rank #{{ $rank }} in {{ $category->name }}</span>
                                    </div>
                                </div>
                            @endif
                        @endif

                        <!-- Additional Info -->
                        <div class="space-y-1.5 sm:space-y-2 mb-3 sm:mb-4">
                            @if($profile->experience_years)
                                <div class="flex items-center text-xs sm:text-sm text-gray-600">
                                    <i class="fas fa-calendar-alt text-blue-600 mr-2 w-4 sm:w-5 flex-shrink-0"></i>
                                    <span class="truncate">{{ $profile->experience_years }} years experience</span>
                                </div>
                            @endif

                            @if($profile->hourly_rate)
                                <div class="flex items-center text-xs sm:text-sm text-gray-600">
                                    <i class="fas fa-money-bill-wave text-green-600 mr-2 w-4 sm:w-5 flex-shrink-0"></i>
                                    <span class="truncate">₦{{ number_format($profile->hourly_rate) }}/hr</span>
                                </div>
                            @endif

                            <div class="flex items-center text-xs sm:text-sm text-gray-600">
                                <i class="fas fa-toggle-{{ $profile->is_available ? 'on text-green-600' : 'off text-gray-400' }} mr-2 w-4 sm:w-5 flex-shrink-0"></i>
                                <span>{{ $profile->is_available ? 'Available' : 'Unavailable' }}</span>
                            </div>

                            <div class="flex items-center text-xs sm:text-sm text-gray-600">
                                <i class="fas fa-shield-{{ $serviceman->is_email_verified ? 'check text-green-600' : 'alt text-yellow-600' }} mr-2 w-4 sm:w-5 flex-shrink-0"></i>
                                <span class="truncate">{{ $serviceman->is_email_verified ? 'Email Verified' : 'Email Not Verified' }}</span>
                            </div>
                        </div>

                        <!-- Skills Preview -->
                        @if($profile->skills)
                            <div class="bg-gray-50 rounded-lg p-2 sm:p-3 mb-3 sm:mb-4">
                                <p class="text-xs font-semibold text-gray-700 mb-1">Skills:</p>
                                <div class="flex flex-wrap gap-1">
                                    @foreach(array_slice(explode(',', $profile->skills), 0, 3) as $skill)
                                        <span class="px-2 py-0.5 bg-white text-gray-700 rounded text-xs">{{ trim($skill) }}</span>
                                    @endforeach
                                    @if(count(explode(',', $profile->skills)) > 3)
                                        <span class="px-2 py-0.5 bg-white text-gray-500 rounded text-xs">+{{ count(explode(',', $profile->skills)) - 3 }} more</span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <!-- Bio Preview -->
                        @if($profile->bio)
                            <div class="bg-gray-50 rounded-lg p-2 sm:p-3 mb-3 sm:mb-4">
                                <p class="text-xs text-gray-600 line-clamp-2">{{ Str::limit($profile->bio, 80) }}</p>
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="flex flex-col sm:flex-row gap-2">
                            <a href="{{ route('servicemen.show', $serviceman) }}" 
                               class="flex-1 text-center px-3 sm:px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors text-xs sm:text-sm">
                                <i class="fas fa-eye mr-1"></i>View Profile
                            </a>
                            
                            @if($serviceman->is_approved)
                                <button onclick="toggleApproval({{ $serviceman->id }}, false, '{{ $serviceman->full_name }}')" 
                                        class="px-3 sm:px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors text-xs sm:text-sm"
                                        title="Revoke Approval">
                                    <i class="fas fa-user-times mr-1 sm:mr-0"></i><span class="sm:hidden">Revoke</span>
                                </button>
                            @else
                                <button onclick="toggleApproval({{ $serviceman->id }}, true, '{{ $serviceman->full_name }}')" 
                                        class="approve-button flex-1 sm:flex-none px-5 sm:px-7 py-3 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-extrabold rounded-xl transition-all text-base shadow-2xl hover:shadow-green-500/50 transform hover:scale-110 border-4 border-green-300 relative overflow-hidden group"
                                        title="✓ Click to Approve this Serviceman">
                                    <span class="relative z-10 flex items-center justify-center">
                                        <i class="fas fa-check-circle mr-2 text-xl animate-bounce"></i>
                                        <span class="font-extrabold text-base uppercase tracking-wide">✓ APPROVE</span>
                                    </span>
                                    <span class="absolute inset-0 bg-white opacity-0 group-hover:opacity-20 transition-opacity"></span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="bg-white rounded-lg sm:rounded-xl shadow-md sm:shadow-lg p-4 sm:p-6 overflow-x-auto">
            {{ $servicemen->links() }}
        </div>
    @else
        <div class="bg-white rounded-xl shadow-lg p-12 text-center">
            <i class="fas fa-users text-gray-400 text-6xl mb-4"></i>
            <h3 class="text-xl font-bold text-gray-900 mb-2">No Servicemen Found</h3>
            <p class="text-gray-600 mb-6">Try adjusting your filters to see more results.</p>
            <a href="{{ route('admin.servicemen') }}" 
               class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                <i class="fas fa-redo mr-2"></i>Clear Filters
            </a>
        </div>
    @endif
</div>

<script>
function toggleApproval(userId, approve, userName) {
    const action = approve ? 'approve' : 'revoke approval for';
    const confirmMessage = approve 
        ? `Are you sure you want to APPROVE ${userName}?\n\nThey will be able to login and accept jobs.`
        : `Are you sure you want to REVOKE APPROVAL for ${userName}?\n\nThey will be immediately logged out and unable to login until re-approved.`;
    
    if (confirm(confirmMessage)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = approve 
            ? `/admin/servicemen/${userId}/approve`
            : `/admin/servicemen/${userId}/revoke-approval`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);
        
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection

