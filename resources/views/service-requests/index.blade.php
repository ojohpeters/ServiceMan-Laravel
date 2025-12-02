@extends('layouts.app')

@section('title', 'Service Requests')

@push('styles')
<style>
    .request-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .request-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="mb-4 md:mb-0">
                    <h1 class="text-4xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-clipboard-list mr-3 text-blue-600"></i>
                        Service Requests
                    </h1>
                    <p class="mt-2 text-gray-600 text-lg">
                        @if(auth()->user()->isClient())
                            Track your service requests and their progress
                        @elseif(auth()->user()->isServiceman())
                            Manage your assigned service requests
                        @else
                            Manage all service requests on the platform
                        @endif
                    </p>
                </div>
                @if(auth()->user()->isClient())
                    <a href="{{ route('services') }}" 
                       class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                        <i class="fas fa-plus mr-2"></i>New Request
                    </a>
                @endif
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6 bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-50 to-purple-50 px-8 py-4 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 flex items-center">
                    <i class="fas fa-filter mr-3 text-blue-600"></i>Filter & Search
                </h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label for="status_filter" class="block text-sm font-bold text-gray-900 mb-2">
                            <i class="fas fa-tasks mr-2 text-blue-600"></i>Status
                        </label>
                        <select id="status_filter" class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white transition-all">
                            <option value="">All Statuses</option>
                            @foreach(\App\Models\ServiceRequest::STATUS_CHOICES as $key => $label)
                                <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label for="category_filter" class="block text-sm font-bold text-gray-900 mb-2">
                            <i class="fas fa-tag mr-2 text-green-600"></i>Category
                        </label>
                        <select id="category_filter" class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white transition-all">
                            <option value="">All Categories</option>
                            @foreach(\App\Models\Category::all() as $category)
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="emergency_filter" class="block text-sm font-bold text-gray-900 mb-2">
                            <i class="fas fa-exclamation-circle mr-2 text-red-600"></i>Type
                        </label>
                        <select id="emergency_filter" class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white transition-all">
                            <option value="">All Types</option>
                            <option value="1" {{ request('emergency') == '1' ? 'selected' : '' }}>Emergency</option>
                            <option value="0" {{ request('emergency') == '0' ? 'selected' : '' }}>Normal</option>
                        </select>
                    </div>

                    <div class="flex items-end space-x-2">
                        <button onclick="applyFilters()" 
                                class="flex-1 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-3 px-4 rounded-xl transition-all shadow-md hover:shadow-lg">
                            <i class="fas fa-filter mr-2"></i>Apply
                        </button>
                        <button onclick="clearFilters()" 
                                class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-bold py-3 px-4 rounded-xl transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Service Requests Cards -->
        @if($serviceRequests->count() > 0)
            <div class="space-y-4">
                @foreach($serviceRequests as $request)
                    <div class="request-card bg-white rounded-2xl shadow-lg overflow-hidden border-l-4 {{ $request->is_emergency ? 'border-red-500' : 'border-blue-500' }}">
                        <!-- Card Header -->
                        <div class="p-4 sm:p-6">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 overflow-hidden">
                                <!-- Left Side: Request Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start space-x-3 mb-3">
                                        <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg flex-shrink-0">
                                            <span class="text-white font-bold text-sm sm:text-lg">#{{ $request->id }}</span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                                <h3 class="text-base sm:text-lg font-bold text-gray-900 break-words">{{ $request->category->name }}</h3>
                                                @if($request->is_emergency)
                                                    <span class="px-2 sm:px-3 py-1 bg-red-100 text-red-800 text-xs font-bold rounded-full border border-red-200 flex items-center whitespace-nowrap">
                                                        <i class="fas fa-exclamation-triangle mr-1"></i>Emergency
                                                    </span>
                                                @endif
                                            </div>
                                            <p class="text-xs sm:text-sm text-gray-600 line-clamp-2 break-words">{{ $request->service_description }}</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Quick Stats -->
                                    <div class="flex flex-wrap items-center gap-2 sm:gap-3 text-xs sm:text-sm">
                                        <span class="flex items-center text-gray-700 whitespace-nowrap">
                                            <i class="fas fa-calendar mr-1 sm:mr-2 text-blue-600 text-xs"></i>
                                            <strong class="mr-1 hidden sm:inline">Booking:</strong>
                                            <span>{{ $request->booking_date->format('M d, Y') }}</span>
                                        </span>
                                        <span class="flex items-center text-gray-700 whitespace-nowrap">
                                            <i class="fas fa-clock mr-1 sm:mr-2 text-green-600 text-xs"></i>
                                            <strong class="mr-1 hidden sm:inline">Created:</strong>
                                            <span>{{ $request->created_at->diffForHumans() }}</span>
                                        </span>
                                        @if($request->final_cost)
                                            <span class="flex items-center text-green-700 font-bold whitespace-nowrap">
                                                <i class="fas fa-money-bill-wave mr-1 sm:mr-2 text-xs"></i>
                                                ₦{{ number_format($request->final_cost) }}
                                            </span>
                                        @elseif($request->serviceman_estimated_cost)
                                            <span class="flex items-center text-yellow-700 font-bold">
                                                <i class="fas fa-calculator mr-1 sm:mr-2 text-xs"></i>
                                                <span class="hidden sm:inline">₦{{ number_format($request->serviceman_estimated_cost) }} (estimated)</span>
                                                <span class="sm:hidden">₦{{ number_format($request->serviceman_estimated_cost) }}</span>
                                            </span>
                                        @else
                                            <span class="flex items-center text-gray-700 font-bold whitespace-nowrap">
                                                <i class="fas fa-money-bill mr-1 sm:mr-2 text-xs"></i>
                                                <span class="hidden sm:inline">₦{{ number_format($request->initial_booking_fee) }} (booking fee)</span>
                                                <span class="sm:hidden">₦{{ number_format($request->initial_booking_fee) }}</span>
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <!-- Right Side: Status & Actions -->
                                <div class="flex flex-col sm:flex-row items-start sm:items-end space-y-2 sm:space-y-0 sm:space-x-3 ml-0 sm:ml-4 mt-4 sm:mt-0 w-full sm:w-auto">
                                    <!-- Status Badge - Full width on mobile -->
                                    <span class="px-3 sm:px-4 py-1.5 sm:py-2 text-xs sm:text-sm font-bold rounded-lg sm:rounded-xl shadow-sm whitespace-nowrap text-center sm:text-left {{ 
                                        $request->status === 'PENDING_ADMIN_ASSIGNMENT' ? 'bg-yellow-100 text-yellow-800 border border-yellow-200' :
                                        ($request->status === 'ASSIGNED_TO_SERVICEMAN' || $request->status === 'ASSIGNED' ? 'bg-blue-100 text-blue-800 border border-blue-200' :
                                        ($request->status === 'SERVICEMAN_INSPECTED' ? 'bg-indigo-100 text-indigo-800 border border-indigo-200' :
                                        ($request->status === 'IN_PROGRESS' ? 'bg-purple-100 text-purple-800 border border-purple-200' :
                                        ($request->status === 'COMPLETED' ? 'bg-green-100 text-green-800 border border-green-200' :
                                        ($request->status === 'AWAITING_PAYMENT' || $request->status === 'PAYMENT_CONFIRMED' ? 'bg-green-100 text-green-800 border border-green-200' :
                                        'bg-gray-100 text-gray-800 border border-gray-200')))))
                                    }}">
                                        {{ \App\Models\ServiceRequest::STATUS_CHOICES[$request->status] ?? str_replace('_', ' ', ucwords(strtolower($request->status))) }}
                                    </span>

                                </div>
                            </div>
                        </div>

                        <!-- Details Section -->
                        <div class="border-t border-gray-200">
                            <div class="p-6 bg-gray-50">
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                                    <!-- Client Info (for Admin & Serviceman) -->
                                    @if(auth()->user()->isAdmin() || auth()->user()->isServiceman())
                                        <div class="bg-white rounded-xl p-5 shadow-sm border border-blue-100">
                                            <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center">
                                                <i class="fas fa-user-circle mr-2 text-blue-600"></i>
                                                Client Information
                                            </h4>
                                            <div class="space-y-2 text-sm">
                                                <p><strong>Name:</strong> {{ $request->client->full_name }}</p>
                                                <p><strong>Email:</strong> <a href="mailto:{{ $request->client->email }}" class="text-blue-600 hover:underline">{{ $request->client->email }}</a></p>
                                                <p><strong>Phone:</strong> <a href="tel:{{ $request->client->phone_number }}" class="text-blue-600 hover:underline">{{ $request->client->phone_number }}</a></p>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Serviceman Info (for Admin & Client) -->
                                    @if((auth()->user()->isAdmin() || (auth()->user()->isClient() && $request->status !== 'PENDING_ADMIN_ASSIGNMENT')) && $request->serviceman)
                                        <div class="bg-white rounded-xl p-5 shadow-sm border border-green-100">
                                            <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center">
                                                <i class="fas fa-user-tie mr-2 text-green-600"></i>
                                                Primary Serviceman
                                            </h4>
                                            <div class="space-y-2 text-sm">
                                                <p><strong>Name:</strong> {{ $request->serviceman->full_name }}</p>
                                                <p><strong>Email:</strong> <a href="mailto:{{ $request->serviceman->email }}" class="text-blue-600 hover:underline">{{ $request->serviceman->email }}</a></p>
                                                <p><strong>Phone:</strong> <a href="tel:{{ $request->serviceman->phone_number }}" class="text-blue-600 hover:underline">{{ $request->serviceman->phone_number }}</a></p>
                                                @if($request->serviceman->servicemanProfile)
                                                    <p><strong>Experience:</strong> {{ $request->serviceman->servicemanProfile->experience_years ?? 0 }} years</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Backup Serviceman (if assigned) -->
                                    @if($request->backup_serviceman_id && $request->backupServiceman && auth()->user()->isAdmin())
                                        <div class="bg-white rounded-xl p-5 shadow-sm border border-orange-100">
                                            <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center">
                                                <i class="fas fa-shield-alt mr-2 text-orange-600"></i>
                                                Backup Serviceman
                                            </h4>
                                            <div class="space-y-2 text-sm">
                                                <p><strong>Name:</strong> {{ $request->backupServiceman->full_name }}</p>
                                                <p><strong>Email:</strong> <a href="mailto:{{ $request->backupServiceman->email }}" class="text-blue-600 hover:underline">{{ $request->backupServiceman->email }}</a></p>
                                                <p><strong>Phone:</strong> <a href="tel:{{ $request->backupServiceman->phone_number }}" class="text-blue-600 hover:underline">{{ $request->backupServiceman->phone_number }}</a></p>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Service Location -->
                                    <div class="bg-white rounded-xl p-5 shadow-sm border border-purple-100">
                                        <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center">
                                            <i class="fas fa-map-marker-alt mr-2 text-purple-600"></i>
                                            Service Location
                                        </h4>
                                        <p class="text-sm text-gray-700">{{ $request->client_address }}</p>
                                    </div>

                                    <!-- Payment Info -->
                                    <div class="bg-white rounded-xl p-5 shadow-sm border border-green-100">
                                        <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center">
                                            <i class="fas fa-money-bill-wave mr-2 text-green-600"></i>
                                            Payment Details
                                        </h4>
                                        <div class="space-y-2 text-sm">
                                            <p><strong>Booking Fee:</strong> ₦{{ number_format($request->initial_booking_fee) }}</p>
                                            @if($request->serviceman_estimated_cost)
                                                <p><strong>Serviceman Estimate:</strong> ₦{{ number_format($request->serviceman_estimated_cost) }}</p>
                                            @endif
                                            @if($request->final_cost)
                                                <p class="text-green-700 font-bold"><strong>Final Cost:</strong> ₦{{ number_format($request->final_cost) }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Additional Info -->
                                    <div class="bg-white rounded-xl p-5 shadow-sm border border-gray-100">
                                        <h4 class="text-sm font-bold text-gray-900 mb-3 flex items-center">
                                            <i class="fas fa-info-circle mr-2 text-gray-600"></i>
                                            Additional Info
                                        </h4>
                                        <div class="space-y-2 text-sm">
                                            <p><strong>Request Type:</strong> {{ $request->is_emergency ? 'Emergency' : 'Normal' }}</p>
                                            <p><strong>Created:</strong> {{ $request->created_at->format('M d, Y H:i A') }}</p>
                                            @if($request->updated_at != $request->created_at)
                                                <p><strong>Last Updated:</strong> {{ $request->updated_at->diffForHumans() }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <div class="flex flex-wrap gap-3">
                                        <a href="{{ route('service-requests.show', $request) }}" 
                                           class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold rounded-xl transition-all shadow-md hover:shadow-lg">
                                            <i class="fas fa-eye mr-2"></i>Full Details
                                        </a>

                                        @if(auth()->user()->isAdmin())
                                            @if($request->status === 'PENDING_ADMIN_ASSIGNMENT')
                                                <button onclick="window.location.href='{{ route('service-requests.show', $request) }}'" 
                                                        class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold rounded-xl transition-all shadow-md hover:shadow-lg">
                                                    <i class="fas fa-user-plus mr-2"></i>Assign Serviceman
                                                </button>
                                            @endif
                                            
                                            @if($request->status === 'SERVICEMAN_INSPECTED')
                                                <button onclick="window.location.href='{{ route('service-requests.show', $request) }}'" 
                                                        class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-bold rounded-xl transition-all shadow-md hover:shadow-lg">
                                                    <i class="fas fa-dollar-sign mr-2"></i>Set Final Cost
                                                </button>
                                            @endif
                                        @endif

                                        @if(auth()->user()->isServiceman() && ($request->serviceman_id === auth()->id() || $request->backup_serviceman_id === auth()->id()))
                                            @if($request->status === 'ASSIGNED_TO_SERVICEMAN')
                                                <a href="{{ route('service-requests.show', $request) }}" 
                                                   class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold rounded-xl transition-all shadow-md hover:shadow-lg">
                                                    <i class="fas fa-calculator mr-2"></i>Submit Estimate
                                                </a>
                                            @elseif($request->status === 'IN_PROGRESS')
                                                <a href="{{ route('service-requests.show', $request) }}" 
                                                   class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-bold rounded-xl transition-all shadow-md hover:shadow-lg">
                                                    <i class="fas fa-check-circle mr-2"></i>Mark Complete
                                                </a>
                                            @endif
                                        @endif
                                    </div>
                                </div>
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
            <div class="bg-white rounded-2xl shadow-xl p-12 text-center">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-gray-100 rounded-full mb-6">
                    <i class="fas fa-clipboard-list text-5xl text-gray-400"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-3">No service requests found</h3>
                <p class="text-gray-600 mb-8 text-lg">
                    @if(auth()->user()->isClient())
                        You haven't created any service requests yet.
                    @elseif(auth()->user()->isServiceman())
                        No service requests have been assigned to you yet.
                    @else
                        No service requests exist on the platform.
                    @endif
                </p>
                @if(auth()->user()->isClient())
                    <a href="{{ route('services') }}" 
                       class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                        <i class="fas fa-plus mr-2"></i>Create Your First Request
                    </a>
                @endif
            </div>
        @endif
    </div>
</div>

<script>
function applyFilters() {
    const status = document.getElementById('status_filter').value;
    const category = document.getElementById('category_filter').value;
    const emergency = document.getElementById('emergency_filter').value;
    
    const params = new URLSearchParams();
    if (status) params.append('status', status);
    if (category) params.append('category', category);
    if (emergency !== '') params.append('emergency', emergency);
    
    window.location.href = '{{ route("service-requests.index") }}?' + params.toString();
}

function clearFilters() {
    window.location.href = '{{ route("service-requests.index") }}';
}
</script>
@endsection
