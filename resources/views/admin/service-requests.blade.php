@extends('layouts.app')

@section('title', 'Service Requests Management')

@push('styles')
<style>
    .request-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .request-card:hover {
        transform: translateY(-4px);
    }
    
    @keyframes slideIn {
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
        animation: slideIn 0.4s ease-out;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8 animate-slide-in">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
                    <h1 class="text-4xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-tasks mr-3 text-blue-600"></i>
                        Service Requests
                    </h1>
                    <p class="text-gray-600 mt-2 text-lg">Manage and oversee all service requests</p>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center space-x-2">
                        <span class="px-4 py-2 bg-white shadow-md border-2 border-purple-200 text-purple-700 rounded-xl font-bold text-sm">
                            <i class="fas fa-clipboard-list mr-2"></i>{{ $serviceRequests->total() }} Total
                        </span>
                        <span class="px-4 py-2 bg-white shadow-md border-2 border-yellow-200 text-yellow-700 rounded-xl font-bold text-sm">
                            <i class="fas fa-clock mr-2"></i>{{ $serviceRequests->where('status', 'PENDING_ADMIN_ASSIGNMENT')->count() }} Pending
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6 bg-white rounded-lg shadow-md border border-gray-200 overflow-hidden animate-slide-in">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 flex items-center">
                    <i class="fas fa-filter mr-3 text-blue-600"></i>Filter & Search
                </h3>
            </div>
            <div class="p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Status Filter -->
                    <div>
                        <label for="status" class="block text-sm font-bold text-gray-900 mb-2">
                            <i class="fas fa-info-circle mr-2 text-blue-600"></i>Status
                        </label>
                        <select id="status" name="status" 
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white transition-all">
                            <option value="">All Statuses</option>
                            <option value="PENDING_ADMIN_ASSIGNMENT" {{ request('status') == 'PENDING_ADMIN_ASSIGNMENT' ? 'selected' : '' }}>Pending Assignment</option>
                            <option value="ASSIGNED_TO_SERVICEMAN" {{ request('status') == 'ASSIGNED_TO_SERVICEMAN' ? 'selected' : '' }}>Assigned</option>
                            <option value="SERVICEMAN_INSPECTED" {{ request('status') == 'SERVICEMAN_INSPECTED' ? 'selected' : '' }}>Inspected</option>
                            <option value="AWAITING_CLIENT_APPROVAL" {{ request('status') == 'AWAITING_CLIENT_APPROVAL' ? 'selected' : '' }}>Awaiting Approval</option>
                            <option value="NEGOTIATING" {{ request('status') == 'NEGOTIATING' ? 'selected' : '' }}>Negotiating</option>
                            <option value="AWAITING_PAYMENT" {{ request('status') == 'AWAITING_PAYMENT' ? 'selected' : '' }}>Awaiting Payment</option>
                            <option value="PAYMENT_CONFIRMED" {{ request('status') == 'PAYMENT_CONFIRMED' ? 'selected' : '' }}>Payment Confirmed</option>
                            <option value="IN_PROGRESS" {{ request('status') == 'IN_PROGRESS' ? 'selected' : '' }}>In Progress</option>
                            <option value="COMPLETED" {{ request('status') == 'COMPLETED' ? 'selected' : '' }}>Completed</option>
                            <option value="CANCELLED" {{ request('status') == 'CANCELLED' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <!-- Category Filter -->
                    <div>
                        <label for="category" class="block text-sm font-bold text-gray-900 mb-2">
                            <i class="fas fa-tag mr-2 text-green-600"></i>Category
                        </label>
                        <select id="category" name="category"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white transition-all">
                            <option value="">All Categories</option>
                            @foreach(\App\Models\Category::all() as $category)
                                <option value="{{ $category->id }}" {{ request('category') == (string)$category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Payment Status Filter -->
                    <div>
                        <label for="paid" class="block text-sm font-bold text-gray-900 mb-2">
                            <i class="fas fa-dollar-sign mr-2 text-purple-600"></i>Payment
                        </label>
                        <select id="paid" name="paid"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white transition-all">
                            <option value="">All</option>
                            <option value="1" {{ request('paid') == '1' ? 'selected' : '' }}>Paid</option>
                            <option value="0" {{ request('paid') == '0' ? 'selected' : '' }}>Unpaid</option>
                        </select>
    </div>

                    <!-- Actions -->
                    <div class="flex items-end space-x-2">
                        <button type="submit" 
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition-colors">
                            <i class="fas fa-search mr-2"></i>Search
                        </button>
                        <a href="{{ route('admin.service-requests') }}"
                           class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 px-4 rounded-xl transition-colors">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        @if($serviceRequests->count() > 0)
            <!-- Service Requests Grid -->
            <div class="space-y-6">
                @foreach($serviceRequests as $index => $request)
                    @php
                        $statusColors = [
                            'PENDING_ADMIN_ASSIGNMENT' => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-400', 'text' => 'text-yellow-800', 'badge' => 'bg-yellow-100', 'icon' => 'fa-clock', 'iconColor' => 'text-yellow-600'],
                            'ASSIGNED_TO_SERVICEMAN' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-400', 'text' => 'text-blue-800', 'badge' => 'bg-blue-100', 'icon' => 'fa-user-check', 'iconColor' => 'text-blue-600'],
                            'SERVICEMAN_INSPECTED' => ['bg' => 'bg-purple-50', 'border' => 'border-purple-400', 'text' => 'text-purple-800', 'badge' => 'bg-purple-100', 'icon' => 'fa-search', 'iconColor' => 'text-purple-600'],
                            'AWAITING_CLIENT_APPROVAL' => ['bg' => 'bg-orange-50', 'border' => 'border-orange-400', 'text' => 'text-orange-800', 'badge' => 'bg-orange-100', 'icon' => 'fa-hourglass-half', 'iconColor' => 'text-orange-600'],
                            'PAYMENT_CONFIRMED' => ['bg' => 'bg-green-50', 'border' => 'border-green-400', 'text' => 'text-green-800', 'badge' => 'bg-green-100', 'icon' => 'fa-check-circle', 'iconColor' => 'text-green-600'],
                            'IN_PROGRESS' => ['bg' => 'bg-indigo-50', 'border' => 'border-indigo-400', 'text' => 'text-indigo-800', 'badge' => 'bg-indigo-100', 'icon' => 'fa-cog', 'iconColor' => 'text-indigo-600'],
                            'WORK_COMPLETED' => ['bg' => 'bg-teal-50', 'border' => 'border-teal-400', 'text' => 'text-teal-800', 'badge' => 'bg-teal-100', 'icon' => 'fa-check-double', 'iconColor' => 'text-teal-600'],
                            'COMPLETED' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-400', 'text' => 'text-emerald-800', 'badge' => 'bg-emerald-100', 'icon' => 'fa-trophy', 'iconColor' => 'text-emerald-600'],
                            'CANCELLED' => ['bg' => 'bg-gray-50', 'border' => 'border-gray-400', 'text' => 'text-gray-800', 'badge' => 'bg-gray-100', 'icon' => 'fa-times-circle', 'iconColor' => 'text-gray-600'],
                        ];
                        $colors = $statusColors[$request->status] ?? ['bg' => 'bg-gray-50', 'border' => 'border-gray-400', 'text' => 'text-gray-800', 'badge' => 'bg-gray-100', 'icon' => 'fa-question-circle', 'iconColor' => 'text-gray-600'];
                        $hasPaidBookingFee = $request->payments()->where('payment_type', 'INITIAL_BOOKING')->where('status', 'SUCCESSFUL')->exists();
                    @endphp
                    
                    <div class="request-card bg-white rounded-lg shadow-md hover:shadow-lg overflow-hidden border-l-4 {{ $colors['border'] }} animate-slide-in" 
                         style="animation-delay: {{ $index * 0.1 }}s">
                        <div class="p-6">
                            <!-- Header Row -->
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
                                <!-- Left: ID and Title -->
                                <div class="flex items-center space-x-4">
                                    <div class="w-16 h-16 bg-blue-600 rounded-lg flex items-center justify-center shadow-md flex-shrink-0">
                                        <div class="text-center">
                                            <div class="text-white font-bold text-xl leading-none">#{{ $request->id }}</div>
                                            <div class="text-white text-[10px] opacity-80">REQ</div>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-xl font-bold text-gray-900 truncate">{{ $request->category->name ?? 'Service Request' }}</h3>
                                        <div class="flex items-center flex-wrap gap-2 mt-1">
                                            @if($request->is_emergency)
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200 animate-pulse">
                                                    <i class="fas fa-exclamation-triangle mr-1"></i>EMERGENCY
                                                </span>
                                            @endif
                                            <span class="text-sm text-gray-600">
                                                <i class="fas fa-clock mr-1"></i>{{ $request->created_at->diffForHumans() }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right: Status Badge -->
                                <div class="flex items-center space-x-3 flex-shrink-0">
                                    <span class="inline-flex items-center px-4 py-2.5 rounded-xl text-sm font-bold {{ $colors['badge'] }} {{ $colors['text'] }} shadow-md border-2 border-current border-opacity-20">
                                        <i class="fas {{ $colors['icon'] }} mr-2"></i>
                                        {{ ucwords(strtolower(str_replace('_', ' ', $request->status))) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Info Grid -->
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <!-- Client Info -->
                                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-bold text-blue-900 uppercase tracking-wide">Client</p>
                                            <p class="text-sm font-bold text-gray-900 truncate">{{ $request->client->full_name ?? 'Unknown' }}</p>
                                        </div>
                                    </div>
                                    <p class="text-xs text-gray-600 truncate">
                                        <i class="fas fa-envelope mr-1"></i>{{ $request->client->email ?? 'N/A' }}
                                    </p>
                                    <p class="text-xs text-gray-600">
                                        <i class="fas fa-phone mr-1"></i>{{ $request->client->phone_number ?? 'N/A' }}
                                    </p>
                                </div>

                                <!-- Serviceman Info -->
                                <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-user-tie text-white"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-bold text-green-900 uppercase tracking-wide">Serviceman</p>
                                            @if($request->serviceman)
                                                <p class="text-sm font-bold text-gray-900 truncate">{{ $request->serviceman->full_name }}</p>
                                            @else
                                                <p class="text-sm font-bold text-gray-400 italic">Not Assigned</p>
                                            @endif
                                        </div>
                                    </div>
                                    @if($request->serviceman)
                                        <p class="text-xs text-gray-600 truncate">
                                            <i class="fas fa-envelope mr-1"></i>{{ $request->serviceman->email }}
                                        </p>
                                        <p class="text-xs text-gray-600">
                                            <i class="fas fa-phone mr-1"></i>{{ $request->serviceman->phone_number }}
                                        </p>
                                        @endif
                                </div>

                                <!-- Schedule & Payment Info -->
                                <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-calendar text-white"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-xs font-bold text-purple-900 uppercase tracking-wide">Schedule</p>
                                            <p class="text-sm font-bold text-gray-900">{{ $request->booking_date->format('M d, Y') }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        @if($hasPaidBookingFee)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-500 text-white shadow-sm">
                                                <i class="fas fa-check-circle mr-1"></i>Paid
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-500 text-white shadow-sm">
                                                <i class="fas fa-times-circle mr-1"></i>Unpaid
                                            </span>
                                        @endif
                                        @if($request->final_cost)
                                            <span class="text-sm font-bold text-purple-900">₦{{ number_format($request->final_cost) }}</span>
                                        @elseif($request->serviceman_estimated_cost)
                                            <span class="text-sm font-bold text-gray-600">₦{{ number_format($request->serviceman_estimated_cost) }}</span>
                                        @else
                                            <span class="text-sm font-bold text-gray-500">₦{{ number_format($request->initial_booking_fee) }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            @if($request->service_description)
                                <div class="bg-gray-50 rounded-xl p-4 mb-6 border border-gray-200">
                                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">
                                        <i class="fas fa-file-alt mr-2"></i>Description
                                    </p>
                                    <p class="text-sm text-gray-700 leading-relaxed">{{ Str::limit($request->service_description, 150) }}</p>
                                </div>
                            @endif

                            <!-- Actions -->
                            <div class="flex flex-wrap gap-3 pt-4 border-t-2 border-gray-100">
                                <a href="{{ route('service-requests.show', $request) }}" 
                                   class="flex-1 min-w-[200px] flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold transition-colors">
                                    <i class="fas fa-eye mr-2"></i>View Full Details
                                </a>

                                @if($request->status === 'PAYMENT_CONFIRMED')
                                    <form action="{{ route('admin.service-requests.notify-start', $request) }}" method="POST" class="flex-1 min-w-[200px]">
                                        @csrf
                                        <button type="submit" class="w-full flex items-center justify-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-lg font-semibold transition-colors">
                                            <i class="fas fa-play-circle mr-2"></i>Start Work
                                        </button>
                                    </form>
                                @endif

                                @if($request->status === 'WORK_COMPLETED')
                                    <a href="{{ route('service-requests.show', $request) }}#admin-actions"
                                       class="flex-1 min-w-[200px] flex items-center justify-center px-6 py-3 bg-teal-600 hover:bg-teal-700 text-white rounded-lg font-semibold transition-colors">
                                        <i class="fas fa-check-double mr-2"></i>Verify Completion
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                        @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="mt-8">
                {{ $serviceRequests->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-2xl shadow-xl p-12 text-center animate-slide-in">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-gray-100 rounded-full mb-6">
                    <i class="fas fa-clipboard-list text-5xl text-gray-400"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-3">No Service Requests Found</h3>
                <p class="text-gray-600 mb-6 text-lg">
                    @if(request()->hasAny(['status', 'category', 'paid']))
                        No requests match your current filters. Try adjusting your search criteria.
                    @else
                        Service requests will appear here when clients make bookings.
                    @endif
                </p>
                @if(request()->hasAny(['status', 'category', 'paid']))
                    <a href="{{ route('admin.service-requests') }}"
                       class="inline-flex items-center px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-colors">
                        <i class="fas fa-times mr-2"></i>Clear Filters
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>
@endsection
