@extends('layouts.app')

@section('title', 'Pending Servicemen - Category Assignment')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-orange-600 to-red-600 bg-clip-text text-transparent">
                        Pending Servicemen Approval
                    </h1>
                    <p class="text-gray-600 mt-2">Review and assign categories to new serviceman registrations</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <span class="px-4 py-2 bg-orange-100 text-orange-700 rounded-full font-medium">
                        {{ $pendingServicemen->total() }} Pending
                    </span>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-2xl shadow-md animate-fadeIn">
                <div class="flex items-center">
                    <i class="fas fa-check-circle text-green-500 mr-3 text-xl"></i>
                    <p class="text-sm text-green-700 font-medium">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-2xl shadow-md">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3 text-xl"></i>
                    <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        <!-- Pending Servicemen Grid -->
        @if($pendingServicemen->count() > 0)
            <div class="grid grid-cols-1 gap-6 mb-6">
                @foreach($pendingServicemen as $serviceman)
                    <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-shadow overflow-hidden border-l-4 border-orange-500">
                        <div class="p-6">
                            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                                <!-- Serviceman Info -->
                                <div class="flex-1">
                                    <!-- Header -->
                                    <div class="flex items-start space-x-4 mb-4">
                                        <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-red-500 rounded-2xl flex items-center justify-center shadow-md flex-shrink-0">
                                            <i class="fas fa-user-clock text-white text-2xl"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-bold text-gray-900">{{ $serviceman->full_name }}</h3>
                                            <p class="text-sm text-gray-600">{{ $serviceman->username ?? 'N/A' }}</p>
                                            <div class="flex items-center mt-1 space-x-3">
                                                <span class="text-xs text-gray-500">
                                                    <i class="fas fa-envelope mr-1"></i>{{ $serviceman->email }}
                                                </span>
                                                @if($serviceman->servicemanProfile->phone_number)
                                                    <span class="text-xs text-gray-500">
                                                        <i class="fas fa-phone mr-1"></i>{{ $serviceman->servicemanProfile->phone_number }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Profile Details Grid -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        @if($serviceman->servicemanProfile->experience_years)
                                            <div class="flex items-start space-x-3">
                                                <div class="bg-blue-100 p-2 rounded-lg">
                                                    <i class="fas fa-calendar-alt text-blue-600"></i>
                                                </div>
                                                <div>
                                                    <p class="text-xs text-gray-500 font-semibold uppercase">Experience</p>
                                                    <p class="text-sm font-medium text-gray-900">{{ $serviceman->servicemanProfile->experience_years }} years</p>
                                                </div>
                                            </div>
                                        @endif

                                        @if($serviceman->servicemanProfile->hourly_rate)
                                            <div class="flex items-start space-x-3">
                                                <div class="bg-green-100 p-2 rounded-lg">
                                                    <i class="fas fa-money-bill-wave text-green-600"></i>
                                                </div>
                                                <div>
                                                    <p class="text-xs text-gray-500 font-semibold uppercase">Hourly Rate</p>
                                                    <p class="text-sm font-medium text-gray-900">₦{{ number_format($serviceman->servicemanProfile->hourly_rate) }}/hour</p>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="flex items-start space-x-3">
                                            <div class="bg-purple-100 p-2 rounded-lg">
                                                <i class="fas fa-clock text-purple-600"></i>
                                            </div>
                                            <div>
                                                <p class="text-xs text-gray-500 font-semibold uppercase">Registered</p>
                                                <p class="text-sm font-medium text-gray-900">{{ $serviceman->created_at->diffForHumans() }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bio -->
                                    @if($serviceman->servicemanProfile->bio)
                                        <div class="bg-gray-50 rounded-xl p-4 mb-4">
                                            <p class="text-xs text-gray-500 font-semibold mb-2">
                                                <i class="fas fa-quote-left mr-1"></i>Bio
                                            </p>
                                            <p class="text-sm text-gray-700">{{ $serviceman->servicemanProfile->bio }}</p>
                                        </div>
                                    @endif

                                    <!-- Skills -->
                                    @if($serviceman->servicemanProfile->skills)
                                        <div class="bg-purple-50 rounded-xl p-4">
                                            <p class="text-xs text-gray-500 font-semibold mb-2">
                                                <i class="fas fa-wrench mr-1"></i>Skills
                                            </p>
                                            <div class="flex flex-wrap gap-2">
                                                @foreach(explode(',', $serviceman->servicemanProfile->skills) as $skill)
                                                    <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">
                                                        {{ trim($skill) }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Action Section -->
                                <div class="flex-shrink-0 lg:min-w-[280px]">
                                    <div class="bg-gradient-to-br from-orange-50 to-red-50 border-2 border-orange-200 rounded-2xl p-6">
                                        <h4 class="font-bold text-gray-900 mb-4 flex items-center">
                                            <i class="fas fa-tasks mr-2 text-orange-600"></i>
                                            Assign Category
                                        </h4>
                                        
                                        <form action="{{ route('admin.servicemen.assign-category', $serviceman) }}" method="POST">
                                            @csrf
                                            <div class="space-y-4">
                                                <div>
                                                    <label for="category_{{ $serviceman->id }}" class="block text-sm font-bold text-gray-700 mb-2">
                                                        Select Category <span class="text-red-500">*</span>
                                                    </label>
                                                    <select id="category_{{ $serviceman->id }}" 
                                                            name="category_id" 
                                                            required
                                                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all">
                                                        <option value="">Choose a category...</option>
                                                        @foreach($categories as $category)
                                                            <option value="{{ $category->id }}">
                                                                {{ $category->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <button type="submit" 
                                                        class="w-full flex items-center justify-center px-6 py-3 bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-700 hover:to-red-700 text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                                                    <i class="fas fa-check-circle mr-2"></i>
                                                    Approve & Assign
                                                </button>
                                            </div>
                                        </form>

                                        <div class="mt-4 pt-4 border-t border-orange-200">
                                            <p class="text-xs text-gray-600">
                                                <i class="fas fa-info-circle mr-1 text-orange-600"></i>
                                                The serviceman will be notified when category is assigned and their profile will become active.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $pendingServicemen->links() }}
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check-circle text-green-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">All Caught Up!</h3>
                <p class="text-gray-500 mb-6">No servicemen waiting for category assignment.</p>
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-xl transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                </a>
            </div>
        @endif
    </div>
</div>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fadeIn {
    animation: fadeIn 0.5s ease-out;
}
</style>
@endsection

