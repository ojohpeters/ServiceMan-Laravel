@extends('layouts.app')

@section('title', 'Servicemen Management')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Servicemen Management</h1>
        <p class="text-gray-600">Filter, search, and manage all service providers</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Total</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>
                <div class="bg-blue-100 p-3 rounded-lg">
                    <i class="fas fa-users text-blue-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Approved</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['approved'] }}</p>
                </div>
                <div class="bg-green-100 p-3 rounded-lg">
                    <i class="fas fa-user-check text-green-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Pending</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['pending_approval'] }}</p>
                </div>
                <div class="bg-yellow-100 p-3 rounded-lg">
                    <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-purple-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Available</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['available'] }}</p>
                </div>
                <div class="bg-purple-100 p-3 rounded-lg">
                    <i class="fas fa-check-circle text-purple-600 text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6 border-t-4 border-teal-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Verified</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $stats['verified'] }}</p>
                </div>
                <div class="bg-teal-100 p-3 rounded-lg">
                    <i class="fas fa-shield-check text-teal-600 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
        <form method="GET" action="{{ route('admin.servicemen') }}" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Search -->
                <div class="lg:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-search mr-2 text-blue-600"></i>Search
                    </label>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Name, email, or username..."
                           class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-tag mr-2 text-green-600"></i>Service Type
                    </label>
                    <select name="category" 
                            class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Approval Status -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-user-check mr-2 text-purple-600"></i>Approval Status
                    </label>
                    <select name="approval_status" 
                            class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
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
                            class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent">
                        <option value="">All</option>
                        <option value="available" {{ request('availability') === 'available' ? 'selected' : '' }}>Available</option>
                        <option value="unavailable" {{ request('availability') === 'unavailable' ? 'selected' : '' }}>Unavailable</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Sort By -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-sort mr-2 text-orange-600"></i>Sort By
                    </label>
                    <select name="sort_by" 
                            class="w-full px-4 py-2 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent">
                        <option value="latest" {{ request('sort_by') === 'latest' ? 'selected' : '' }}>Latest First</option>
                        <option value="rating" {{ request('sort_by') === 'rating' ? 'selected' : '' }}>Highest Rating</option>
                        <option value="jobs" {{ request('sort_by') === 'jobs' ? 'selected' : '' }}>Most Jobs</option>
                        <option value="name" {{ request('sort_by') === 'name' ? 'selected' : '' }}>Name (A-Z)</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-end gap-3">
                    <button type="submit" 
                            class="flex-1 px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                        <i class="fas fa-filter mr-2"></i>Apply Filters
                    </button>
                    <a href="{{ route('admin.servicemen') }}" 
                       class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors">
                        <i class="fas fa-redo mr-2"></i>Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Servicemen Cards Grid -->
    @if($servicemen->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            @foreach($servicemen as $serviceman)
                @php
                    $profile = $serviceman->servicemanProfile;
                    $rating = $profile->rating ?? 0;
                    $jobs = $profile->total_jobs_completed ?? 0;
                    $category = $profile->category ?? null;
                @endphp
                
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border-2 border-gray-200 hover:border-blue-500 transition-all hover:shadow-xl">
                    <!-- Card Header -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <img src="{{ $serviceman->profile_picture_url }}" 
                                     alt="{{ $serviceman->full_name }}"
                                     class="w-16 h-16 rounded-full border-4 border-white object-cover shadow-lg">
                                <div>
                                    <h3 class="text-lg font-bold text-white">{{ $serviceman->full_name }}</h3>
                                    <p class="text-blue-100 text-sm">{{ $serviceman->email }}</p>
                                </div>
                            </div>
                            @if($serviceman->is_approved)
                                <span class="bg-green-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                                    <i class="fas fa-check-circle mr-1"></i>Approved
                                </span>
                            @else
                                <span class="bg-yellow-500 text-white px-3 py-1 rounded-full text-xs font-bold">
                                    <i class="fas fa-clock mr-1"></i>Pending
                                </span>
                            @endif
                        </div>

                        <!-- Category Badge -->
                        @if($category)
                            <div class="inline-flex items-center bg-white bg-opacity-20 px-3 py-1 rounded-full">
                                <i class="fas fa-tag mr-2 text-white"></i>
                                <span class="text-white font-semibold text-sm">{{ $category->name }}</span>
                            </div>
                        @else
                            <div class="inline-flex items-center bg-yellow-500 bg-opacity-80 px-3 py-1 rounded-full">
                                <i class="fas fa-exclamation-triangle mr-2 text-white"></i>
                                <span class="text-white font-semibold text-sm">No Category</span>
                            </div>
                        @endif
                    </div>

                    <!-- Card Body -->
                    <div class="p-6">
                        <!-- Stats Row -->
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="text-center bg-yellow-50 rounded-lg p-3">
                                <div class="flex items-center justify-center mb-1">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star {{ $i <= round($rating) ? 'text-yellow-400' : 'text-gray-300' }} text-sm"></i>
                                    @endfor
                                </div>
                                <p class="text-xs text-gray-600 font-semibold">{{ number_format($rating, 1) }} Rating</p>
                            </div>
                            <div class="text-center bg-green-50 rounded-lg p-3">
                                <i class="fas fa-briefcase text-green-600 text-2xl mb-1"></i>
                                <p class="text-xs text-gray-600 font-semibold">{{ $jobs }} Jobs</p>
                            </div>
                        </div>

                        <!-- Additional Info -->
                        <div class="space-y-2 mb-4">
                            @if($profile->experience_years)
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-calendar-alt text-blue-600 mr-2 w-5"></i>
                                    <span>{{ $profile->experience_years }} years experience</span>
                                </div>
                            @endif

                            @if($profile->hourly_rate)
                                <div class="flex items-center text-sm text-gray-600">
                                    <i class="fas fa-money-bill-wave text-green-600 mr-2 w-5"></i>
                                    <span>₦{{ number_format($profile->hourly_rate) }}/hr</span>
                                </div>
                            @endif

                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-toggle-{{ $profile->is_available ? 'on text-green-600' : 'off text-gray-400' }} mr-2 w-5"></i>
                                <span>{{ $profile->is_available ? 'Available' : 'Unavailable' }}</span>
                            </div>

                            <div class="flex items-center text-sm text-gray-600">
                                <i class="fas fa-shield-{{ $serviceman->is_email_verified ? 'check text-green-600' : 'alt text-yellow-600' }} mr-2 w-5"></i>
                                <span>{{ $serviceman->is_email_verified ? 'Email Verified' : 'Email Not Verified' }}</span>
                            </div>
                        </div>

                        <!-- Bio Preview -->
                        @if($profile->bio)
                            <div class="bg-gray-50 rounded-lg p-3 mb-4">
                                <p class="text-xs text-gray-600 line-clamp-2">{{ Str::limit($profile->bio, 100) }}</p>
                            </div>
                        @endif

                        <!-- Action Buttons -->
                        <div class="flex gap-2">
                            <a href="{{ route('servicemen.show', $serviceman) }}" 
                               class="flex-1 text-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors text-sm">
                                <i class="fas fa-eye mr-1"></i>View Profile
                            </a>
                            
                            @if($serviceman->is_approved)
                                <button onclick="toggleApproval({{ $serviceman->id }}, false, '{{ $serviceman->full_name }}')" 
                                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition-colors text-sm"
                                        title="Revoke Approval">
                                    <i class="fas fa-user-times"></i>
                                </button>
                            @else
                                <button onclick="toggleApproval({{ $serviceman->id }}, true, '{{ $serviceman->full_name }}')" 
                                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors text-sm"
                                        title="Approve">
                                    <i class="fas fa-user-check"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="bg-white rounded-xl shadow-lg p-6">
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

