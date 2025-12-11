@extends('layouts.app')

@section('title', 'Service Request #' . $serviceRequest->id)

@push('styles')
<style>
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translate(-50%, -20px);
        }
        to {
            opacity: 1;
            transform: translate(-50%, 0);
        }
    }
    
    @keyframes fadeOutUp {
        from {
            opacity: 1;
            transform: translate(-50%, 0);
        }
        to {
            opacity: 0;
            transform: translate(-50%, -20px);
        }
    }
    
    .animate-fade-in-down {
        animation: fadeInDown 0.3s ease-out;
    }
    
    .animate-fade-out-up {
        animation: fadeOutUp 0.3s ease-in;
    }
</style>
@endpush

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Service Request #{{ $serviceRequest->id }}</h1>
                    <p class="mt-2 text-sm sm:text-base text-gray-600">{{ $statusMessage }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2 sm:gap-4">
                    <span class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-full whitespace-nowrap {{ 
                        $serviceRequest->is_emergency ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800' 
                    }}">
                        {{ $serviceRequest->is_emergency ? 'Emergency' : 'Normal' }}
                    </span>
                    <span class="px-3 py-1.5 text-xs sm:text-sm font-medium rounded-full whitespace-nowrap bg-gray-100 text-gray-800">
                        {{ \App\Models\ServiceRequest::STATUS_CHOICES[$serviceRequest->status] ?? str_replace('_', ' ', ucwords(strtolower($serviceRequest->status))) }}
                    </span>
                </div>
            </div>
            
            @php
                $isAssignedServiceman = auth()->user()->isServiceman() && 
                    ((int)$serviceRequest->serviceman_id === (int)auth()->id() || ((int)$serviceRequest->backup_serviceman_id === (int)auth()->id() && $serviceRequest->backup_serviceman_id));
            @endphp
            @if($isAssignedServiceman && $serviceRequest->status === 'ASSIGNED_TO_SERVICEMAN')
                <!-- Prominent Alert for Serviceman -->
                <div class="mt-6 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-xl shadow-2xl p-6 border-l-4 border-yellow-400 animate-pulse">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center mb-3">
                                <i class="fas fa-exclamation-triangle text-yellow-400 text-2xl mr-3"></i>
                                <h2 class="text-xl font-bold">Action Required: Submit Your Cost Estimate</h2>
                            </div>
                            <p class="text-blue-100 mb-4">
                                You have been assigned to this job. Please review the service details and submit your cost estimate to proceed.
                            </p>
                            <button type="button" onclick="toggleEstimateForm()" class="bg-white text-blue-600 hover:bg-blue-50 font-bold py-3 px-6 rounded-lg transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                                <i class="fas fa-calculator mr-2"></i>Submit Estimate Now
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Service Details -->
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Service Details</h2>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Service Category</h3>
                            <p class="text-gray-900">{{ $serviceRequest->category->name }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Booking Date</h3>
                            <p class="text-gray-900">{{ $serviceRequest->booking_date->format('M d, Y') }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Service Address</h3>
                            <p class="text-gray-900">{{ $serviceRequest->client_address }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <h3 class="text-sm font-medium text-gray-500 mb-2">Service Description</h3>
                            <p class="text-gray-900">{{ $serviceRequest->service_description }}</p>
                        </div>
                    </div>
                </div>

                <!-- Pricing Information -->
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Pricing Information</h2>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center py-2 border-b border-gray-200">
                            <span class="text-gray-600">Booking Fee</span>
                            <span class="font-semibold text-gray-900">₦{{ number_format($serviceRequest->initial_booking_fee) }}</span>
                        </div>
                        
                        @if($serviceRequest->serviceman_estimated_cost)
                            @if(auth()->user()->isAdmin() || auth()->user()->isServiceman())
                                <!-- Show breakdown for admin and serviceman -->
                            <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                <span class="text-gray-600">Serviceman Estimate</span>
                                <span class="font-semibold text-gray-900">₦{{ number_format($serviceRequest->serviceman_estimated_cost) }}</span>
                            </div>
                            
                            @if($serviceRequest->final_cost)
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <span class="text-gray-600">Admin Markup ({{ $serviceRequest->admin_markup_percentage }}%)</span>
                                    <span class="font-semibold text-gray-900">₦{{ number_format($serviceRequest->serviceman_estimated_cost * ($serviceRequest->admin_markup_percentage / 100)) }}</span>
                                </div>
                                @endif
                            @endif
                                
                            @if($serviceRequest->final_cost)
                                <div class="flex justify-between items-center py-3 bg-blue-50 rounded-lg px-4">
                                    <span class="text-lg font-semibold text-blue-900">Final Cost</span>
                                    <span class="text-lg font-bold text-blue-900">₦{{ number_format($serviceRequest->final_cost) }}</span>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Status Actions -->
                @if(!empty($nextSteps))
                    <div class="bg-white shadow-lg rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Next Steps</h2>
                        <div class="space-y-3">
                            @foreach($nextSteps as $step)
                                <div class="flex items-center">
                                    <i class="fas fa-arrow-right text-blue-600 mr-3"></i>
                                    <span class="text-gray-700">{{ $step }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Negotiations (Feature Disabled) -->
                {{-- @if($serviceRequest->negotiations->count() > 0)
                    <div class="bg-white shadow-lg rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Price Negotiations</h2>
                        <div class="space-y-4">
                            @foreach($serviceRequest->negotiations as $negotiation)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="text-sm font-medium text-gray-500">
                                            {{ $negotiation->proposedBy->full_name }} - {{ $negotiation->created_at->format('M d, Y H:i') }}
                                        </span>
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ 
                                            $negotiation->status === 'ACCEPTED' ? 'bg-green-100 text-green-800' : 
                                            ($negotiation->status === 'REJECTED' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')
                                        }}">
                                            {{ $negotiation->status }}
                                        </span>
                                    </div>
                                    <p class="text-gray-700 mb-2">{{ $negotiation->message }}</p>
                                    <p class="text-lg font-semibold text-blue-600">₦{{ number_format($negotiation->proposed_amount) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif --}}

                <!-- Rating Section -->
                @if($serviceRequest->status === 'COMPLETED' && !$serviceRequest->rating && auth()->user()->isClient())
                    <div class="bg-white shadow-lg rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Rate Your Experience</h2>
                        <form action="{{ route('service-requests.rate', $serviceRequest) }}" method="POST">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                                    <div class="flex space-x-2">
                                        @for($i = 1; $i <= 5; $i++)
                                            <input type="radio" id="rating_{{ $i }}" name="rating" value="{{ $i }}" class="sr-only">
                                            <label for="rating_{{ $i }}" class="cursor-pointer text-2xl text-gray-300 hover:text-yellow-400 rating-star">
                                                <i class="fas fa-star"></i>
                                            </label>
                                        @endfor
                                    </div>
                                </div>
                                <div>
                                    <label for="review" class="block text-sm font-medium text-gray-700 mb-2">Review (Optional)</label>
                                    <textarea id="review" name="review" rows="3" 
                                              placeholder="Share your experience with this serviceman..."
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                                </div>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-lg transition-colors">
                                    Submit Rating
                                </button>
                            </div>
                        </form>
                    </div>
                @endif

                <!-- Existing Rating -->
                @if($serviceRequest->rating)
                    <div class="bg-white shadow-lg rounded-lg p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Your Rating</h2>
                        <div class="flex items-center space-x-2 mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="fas fa-star {{ $i <= $serviceRequest->rating->rating ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                            @endfor
                            <span class="text-gray-600">({{ $serviceRequest->rating->rating }}/5)</span>
                        </div>
                        @if($serviceRequest->rating->review)
                            <p class="text-gray-700">{{ $serviceRequest->rating->review }}</p>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Client Information (for Serviceman & Admin) -->
                @if(auth()->user()->isServiceman() || auth()->user()->isAdmin())
                    <div class="bg-white shadow-lg rounded-2xl p-6 border-l-4 border-blue-500">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-user-circle mr-3 text-blue-600 text-xl"></i>
                            Client Information
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <img src="{{ $serviceRequest->client->profile_picture_url }}" 
                                     alt="{{ $serviceRequest->client->full_name }}" 
                                     class="w-16 h-16 rounded-full object-cover border-4 border-blue-200 shadow-lg">
                                <div class="flex-1">
                                    <p class="font-bold text-gray-900 text-lg">{{ $serviceRequest->client->full_name }}</p>
                                    <p class="text-sm text-gray-600">Client</p>
                                </div>
                            </div>
                            <div class="space-y-2 pt-3 border-t border-gray-200">
                                <div class="flex items-center space-x-2 text-sm">
                                    <i class="fas fa-envelope text-blue-600 w-5"></i>
                                    <a href="mailto:{{ $serviceRequest->client->email }}" class="text-blue-600 hover:underline font-medium">
                                        {{ $serviceRequest->client->email }}
                                    </a>
                                </div>
                                <div class="flex items-center space-x-2 text-sm">
                                    <i class="fas fa-phone text-green-600 w-5"></i>
                                    <a href="tel:{{ $serviceRequest->client->phone_number }}" class="text-green-600 hover:underline font-medium">
                                        {{ $serviceRequest->client->phone_number }}
                                    </a>
                                </div>
                                <div class="flex items-center space-x-2 text-sm">
                                    <i class="fas fa-map-marker-alt text-red-600 w-5"></i>
                                    <span class="text-gray-700">{{ $serviceRequest->client_address }}</span>
                                </div>
                            </div>
                            <div class="pt-3 border-t border-gray-200">
                                <a href="tel:{{ $serviceRequest->client->phone_number }}" 
                                   class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors">
                                    <i class="fas fa-phone mr-2"></i>Call Client
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Primary Serviceman Information (for Client & Admin) -->
                @if($serviceRequest->serviceman && (auth()->user()->isAdmin() || (auth()->user()->isClient() && $serviceRequest->status !== 'PENDING_ADMIN_ASSIGNMENT')))
                    <div class="bg-white shadow-lg rounded-2xl p-6 border-l-4 border-green-500">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-user-tie mr-3 text-green-600 text-xl"></i>
                            Primary Serviceman
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <img src="{{ $serviceRequest->serviceman->profile_picture_url }}" 
                                     alt="{{ $serviceRequest->serviceman->full_name }}" 
                                     class="w-16 h-16 rounded-full object-cover border-4 border-green-200 shadow-lg">
                                <div class="flex-1">
                                    <p class="font-bold text-gray-900 text-lg">{{ $serviceRequest->serviceman->full_name }}</p>
                                    <p class="text-sm text-gray-600">{{ $serviceRequest->serviceman->servicemanProfile->category->name ?? 'Service Provider' }}</p>
                                    @if($serviceRequest->serviceman->servicemanProfile)
                                        <div class="flex items-center mt-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star text-xs {{ $i <= ($serviceRequest->serviceman->servicemanProfile->rating ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                            @endfor
                                            <span class="text-xs text-gray-600 ml-1">({{ $serviceRequest->serviceman->servicemanProfile->rating ?? 0 }})</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="space-y-2 pt-3 border-t border-gray-200">
                                <div class="flex items-center space-x-2 text-sm">
                                    <i class="fas fa-envelope text-blue-600 w-5"></i>
                                    <a href="mailto:{{ $serviceRequest->serviceman->email }}" class="text-blue-600 hover:underline font-medium">
                                        {{ $serviceRequest->serviceman->email }}
                                    </a>
                                </div>
                                <div class="flex items-center space-x-2 text-sm">
                                    <i class="fas fa-phone text-green-600 w-5"></i>
                                    <a href="tel:{{ $serviceRequest->serviceman->phone_number }}" class="text-green-600 hover:underline font-medium">
                                        {{ $serviceRequest->serviceman->phone_number }}
                                    </a>
                                </div>
                                @if($serviceRequest->serviceman->servicemanProfile)
                                    <div class="flex items-center space-x-2 text-sm">
                                        <i class="fas fa-briefcase text-purple-600 w-5"></i>
                                        <span class="text-gray-700">{{ $serviceRequest->serviceman->servicemanProfile->experience_years ?? 0 }} years experience</span>
                                    </div>
                                @endif
                            </div>
                            <div class="pt-3 border-t border-gray-200">
                                <a href="tel:{{ $serviceRequest->serviceman->phone_number }}" 
                                   class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors">
                                    <i class="fas fa-phone mr-2"></i>Call Serviceman
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Backup Serviceman Information (for Admin only) -->
                @php
                    $backupServiceman = null;
                    if ($serviceRequest->backup_serviceman_id) {
                        $backupServiceman = \App\Models\User::with('servicemanProfile.category')->find($serviceRequest->backup_serviceman_id);
                    }
                @endphp
                @if($backupServiceman && auth()->user()->isAdmin())
                    <div class="bg-white shadow-lg rounded-2xl p-6 border-l-4 border-orange-500">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-shield-alt mr-3 text-orange-600 text-xl"></i>
                            Backup Serviceman
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center space-x-3">
                                <img src="{{ $backupServiceman->profile_picture_url }}" 
                                     alt="{{ $backupServiceman->full_name }}" 
                                     class="w-16 h-16 rounded-full object-cover border-4 border-orange-200 shadow-lg">
                                <div class="flex-1">
                                    <p class="font-bold text-gray-900 text-lg">{{ $backupServiceman->full_name }}</p>
                                    <p class="text-sm text-gray-600">{{ $backupServiceman->servicemanProfile->category->name ?? 'Service Provider' }}</p>
                                    @if($backupServiceman->servicemanProfile)
                                        <div class="flex items-center mt-1">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star text-xs {{ $i <= ($backupServiceman->servicemanProfile->rating ?? 0) ? 'text-yellow-400' : 'text-gray-300' }}"></i>
                                            @endfor
                                            <span class="text-xs text-gray-600 ml-1">({{ $backupServiceman->servicemanProfile->rating ?? 0 }})</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="space-y-2 pt-3 border-t border-gray-200">
                                <div class="flex items-center space-x-2 text-sm">
                                    <i class="fas fa-envelope text-blue-600 w-5"></i>
                                    <a href="mailto:{{ $backupServiceman->email }}" class="text-blue-600 hover:underline font-medium">
                                        {{ $backupServiceman->email }}
                                    </a>
                                </div>
                                <div class="flex items-center space-x-2 text-sm">
                                    <i class="fas fa-phone text-green-600 w-5"></i>
                                    <a href="tel:{{ $backupServiceman->phone_number }}" class="text-green-600 hover:underline font-medium">
                                        {{ $backupServiceman->phone_number }}
                                    </a>
                                </div>
                                @if($backupServiceman->servicemanProfile)
                                    <div class="flex items-center space-x-2 text-sm">
                                        <i class="fas fa-briefcase text-purple-600 w-5"></i>
                                        <span class="text-gray-700">{{ $backupServiceman->servicemanProfile->experience_years ?? 0 }} years experience</span>
                                    </div>
                                    @if($backupServiceman->servicemanProfile->total_jobs_completed ?? 0 > 0)
                                        <div class="flex items-center space-x-2 text-sm">
                                            <i class="fas fa-check-circle text-green-600 w-5"></i>
                                            <span class="text-gray-700">{{ $backupServiceman->servicemanProfile->total_jobs_completed ?? 0 }} jobs completed</span>
                                        </div>
                                    @endif
                                @endif
                            </div>
                            <div class="pt-3 border-t border-gray-200">
                                <div class="bg-orange-50 border border-orange-200 rounded-lg p-3 mb-3">
                                    <p class="text-xs text-orange-800">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        <strong>Backup Role:</strong> This serviceman will step in if the primary serviceman becomes unavailable.
                                    </p>
                                </div>
                                <a href="tel:{{ $backupServiceman->phone_number }}" 
                                   class="w-full inline-flex items-center justify-center px-4 py-2.5 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg transition-colors">
                                    <i class="fas fa-phone mr-2"></i>Call Backup Serviceman
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Client Actions -->
                @if(auth()->user()->isClient() && $serviceRequest->client_id === auth()->id())
                    <div class="bg-white shadow-lg rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
                        <div class="space-y-3">
                            @php
                                $hasPaidBookingFee = $serviceRequest->payments()->where('payment_type', 'INITIAL_BOOKING')->where('status', 'SUCCESSFUL')->exists();
                            @endphp

                            @if($serviceRequest->status === 'PENDING_ADMIN_ASSIGNMENT' && !$hasPaidBookingFee)
                                <!-- Pay Booking Fee -->
                                <a href="{{ route('paystack.initialize') }}?service_request={{ $serviceRequest->id }}&type=INITIAL_BOOKING&amount={{ $serviceRequest->initial_booking_fee }}" 
                                   class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors text-center block">
                                    <i class="fas fa-credit-card mr-2"></i>Pay Booking Fee (₦{{ number_format($serviceRequest->initial_booking_fee) }})
                                </a>
                                
                                <a href="{{ route('service-requests.edit', $serviceRequest) }}" 
                                   class="w-full bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded-lg transition-colors text-center block">
                                    <i class="fas fa-edit mr-2"></i>Edit Request
                                </a>
                            @elseif($serviceRequest->status === 'PENDING_ADMIN_ASSIGNMENT' && $hasPaidBookingFee)
                                <!-- Booking fee paid, waiting for serviceman assignment -->
                                <div class="w-full bg-gray-400 text-white font-medium py-2 px-4 rounded-lg text-center">
                                    <i class="fas fa-clock mr-2"></i>Waiting for Serviceman Assignment
                                </div>
                                <p class="text-sm text-gray-600 text-center">Your booking fee has been paid. We're assigning a serviceman to your request.</p>
                            @elseif($serviceRequest->status === 'ASSIGNED_TO_SERVICEMAN' && $serviceRequest->serviceman_id)
                                <!-- Serviceman assigned, waiting for inspection -->
                                @if($serviceRequest->accepted_at)
                                    <div class="w-full bg-blue-600 text-white font-medium py-2 px-4 rounded-lg text-center">
                                        <i class="fas fa-search mr-2"></i>Serviceman Inspecting Location
                                    </div>
                                    <p class="text-sm text-gray-600 text-center">Your serviceman {{ $serviceRequest->serviceman->full_name }} has accepted the assignment and is inspecting the location. They will provide a cost estimate soon.</p>
                                @else
                                    <div class="w-full bg-yellow-600 text-white font-medium py-2 px-4 rounded-lg text-center">
                                        <i class="fas fa-clock mr-2"></i>Waiting for Serviceman Acceptance
                                    </div>
                                    <p class="text-sm text-gray-600 text-center">Your serviceman {{ $serviceRequest->serviceman->full_name ?? 'has been assigned' }} has been assigned. They will accept the assignment and inspect the location soon.</p>
                                @endif
                            @elseif($serviceRequest->status === 'SERVICEMAN_INSPECTED')
                                <!-- Serviceman has inspected, waiting for admin to set final cost -->
                                <div class="w-full bg-blue-600 text-white font-medium py-2 px-4 rounded-lg text-center">
                                    <i class="fas fa-hourglass-half mr-2"></i>Processing
                                </div>
                                <p class="text-sm text-gray-600 text-center">Your request is being processed. You'll be notified once the cost estimate is ready.</p>
                            @elseif($serviceRequest->status === 'AWAITING_CLIENT_APPROVAL')
                                <!-- Final cost ready, client can accept or negotiate -->
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-3">
                                    <h4 class="font-semibold text-green-800 mb-2">Final Cost Estimate</h4>
                                    <p class="text-3xl font-bold text-green-600">₦{{ number_format($serviceRequest->final_cost) }}</p>
                                </div>
                                
                                <form action="{{ route('service-requests.accept-cost', $serviceRequest) }}" method="POST" class="mb-3">
                                    @csrf
                                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                                        <i class="fas fa-check mr-2"></i>Accept Cost & Pay
                                    </button>
                                </form>
                                
                                {{-- Negotiate Price button removed - Feature disabled --}}
                            @elseif($serviceRequest->status === 'NEGOTIATING')
                                {{-- Negotiation status - Feature disabled --}}
                                {{-- <div class="w-full bg-blue-500 text-white font-medium py-2 px-4 rounded-lg text-center">
                                    <i class="fas fa-clock mr-2"></i>Awaiting Admin Review
                                </div>
                                <p class="text-sm text-gray-600 text-center">The admin is reviewing the pricing for this service request.</p> --}}
                            @elseif($serviceRequest->status === 'AWAITING_PAYMENT')
                                <!-- Final payment ready -->
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-3">
                                    <h4 class="font-semibold text-blue-800 mb-2">Final Payment</h4>
                                    <p class="text-2xl font-bold text-blue-600">₦{{ number_format($serviceRequest->final_cost) }}</p>
                                </div>
                                
                                <form action="{{ route('paystack.initialize') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="service_request" value="{{ $serviceRequest->id }}">
                                    <input type="hidden" name="type" value="FINAL_PAYMENT">
                                    <input type="hidden" name="amount" value="{{ $serviceRequest->final_cost }}">
                                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                                    <i class="fas fa-credit-card mr-2"></i>Pay Final Amount
                                    </button>
                                </form>
                            @elseif($serviceRequest->status === 'PAYMENT_CONFIRMED')
                                <!-- Payment confirmed, waiting for admin to notify serviceman -->
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-3">
                                    <h4 class="font-semibold text-green-800 mb-2">✅ Payment Confirmed</h4>
                                    <p class="text-sm text-green-600">Your payment has been confirmed! Our admin team is coordinating with the serviceman to begin work.</p>
                                </div>
                            @elseif($serviceRequest->status === 'IN_PROGRESS')
                                <!-- Work in progress -->
                                <div class="w-full bg-blue-400 text-white font-medium py-2 px-4 rounded-lg text-center">
                                    <i class="fas fa-tools mr-2"></i>Work in Progress
                                </div>
                                <p class="text-sm text-gray-600 text-center">Your serviceman is currently working on your request.</p>
                            @elseif($serviceRequest->status === 'COMPLETED')
                                <!-- Work completed -->
                                <div class="w-full bg-green-400 text-white font-medium py-2 px-4 rounded-lg text-center">
                                    <i class="fas fa-check-circle mr-2"></i>Work Completed
                                </div>
                                <p class="text-sm text-gray-600 text-center">Your service has been completed successfully!</p>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Serviceman Actions -->
                @php
                    $isAssignedServiceman = auth()->user()->isServiceman() && 
                        ((int)$serviceRequest->serviceman_id === (int)auth()->id() || 
                         ($serviceRequest->backup_serviceman_id && (int)$serviceRequest->backup_serviceman_id === (int)auth()->id()));
                @endphp
                @if($isAssignedServiceman)
                    <div class="bg-white shadow-lg rounded-2xl p-6 border-l-4 border-blue-500">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-tools mr-2 text-blue-600"></i>
                            Your Actions
                        </h3>
                        <div class="space-y-3">
                            @if($serviceRequest->status === 'ASSIGNED_TO_SERVICEMAN')
                                @if(!$serviceRequest->accepted_at)
                                    <!-- Accept/Decline Assignment -->
                                    <div class="bg-yellow-50 border-2 border-yellow-300 rounded-lg p-4 mb-3">
                                        <p class="text-sm font-bold text-yellow-900 mb-2 flex items-center">
                                            <i class="fas fa-question-circle mr-2 text-lg"></i>Assignment Received
                                        </p>
                                        <p class="text-sm text-yellow-800 mb-4">You have been assigned to this job. Please accept or decline the assignment.</p>
                                        <div class="grid grid-cols-2 gap-3">
                                            <form action="{{ route('service-requests.accept', $serviceRequest) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition-all shadow-md hover:shadow-lg">
                                                    <i class="fas fa-check mr-2"></i>Accept
                                                </button>
                                            </form>
                                            <button type="button" onclick="toggleDeclineForm()" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded-lg transition-all shadow-md hover:shadow-lg">
                                                <i class="fas fa-times mr-2"></i>Decline
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <!-- Decline Form (Hidden) -->
                                    <div id="declineForm" class="hidden bg-red-50 border-2 border-red-300 rounded-lg p-4 mb-3">
                                        <p class="text-sm font-bold text-red-900 mb-2">Decline Assignment</p>
                                        <form action="{{ route('service-requests.decline', $serviceRequest) }}" method="POST">
                                            @csrf
                                            <div class="mb-3">
                                                <label class="block text-sm font-semibold text-gray-700 mb-1">Reason (Optional)</label>
                                                <textarea name="decline_reason" rows="3" maxlength="500" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500"></textarea>
                                            </div>
                                            <div class="flex gap-2">
                                                <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg">
                                                    <i class="fas fa-times mr-2"></i>Confirm Decline
                                                </button>
                                                <button type="button" onclick="toggleDeclineForm()" class="flex-1 bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg">
                                                    Cancel
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @else
                                    <!-- Already Accepted - Show Estimate Form -->
                                    <div class="bg-blue-50 border-2 border-blue-300 rounded-lg p-4 mb-3">
                                        <p class="text-sm font-bold text-blue-900 mb-2 flex items-center">
                                            <i class="fas fa-exclamation-circle mr-2 text-lg"></i>Action Required - Submit Your Estimate
                                        </p>
                                        <p class="text-sm text-blue-800 mb-4">You have accepted this assignment. Please inspect the location and submit your cost estimate to proceed.</p>
                                        <button type="button" onclick="toggleEstimateForm()" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-6 rounded-lg transition-all shadow-lg hover:shadow-xl transform hover:scale-105 text-lg">
                                            <i class="fas fa-calculator mr-2 text-xl"></i>Submit Inspection & Cost Estimate
                                        </button>
                                    </div>
                                @endif
                            @elseif($serviceRequest->status === 'PAYMENT_CONFIRMED')
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-3">
                                    <p class="text-sm font-semibold text-yellow-900 mb-2">
                                        <i class="fas fa-clock mr-2"></i>Waiting for Authorization
                                    </p>
                                    <p class="text-xs text-yellow-800">Payment has been confirmed. Waiting for admin to authorize work to begin.</p>
                                </div>
                            @elseif($serviceRequest->status === 'IN_PROGRESS')
                                <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-3">
                                    <p class="text-sm font-semibold text-green-900 mb-2">
                                        <i class="fas fa-tools mr-2"></i>Work in Progress
                                    </p>
                                    <p class="text-xs text-green-800 mb-3">You're currently working on this service request. Mark it as completed when done.</p>
                                    <button onclick="toggleCompletionForm()" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-lg transition-colors shadow-md hover:shadow-lg">
                                        <i class="fas fa-check-circle mr-2"></i>Mark Work as Completed
                                    </button>
                                </div>
                            @elseif($serviceRequest->status === 'WORK_COMPLETED')
                                <div class="bg-teal-50 border border-teal-200 rounded-lg p-4">
                                    <p class="text-sm font-semibold text-teal-900 mb-2">
                                        <i class="fas fa-check-double mr-2"></i>Work Completed
                                    </p>
                                    <p class="text-xs text-teal-800">Your work has been marked as completed. Waiting for admin verification.</p>
                                </div>
                            @else
                                <div class="bg-yellow-50 border-2 border-yellow-300 rounded-lg p-4">
                                    <p class="text-sm font-semibold text-yellow-900 mb-2">
                                        <i class="fas fa-info-circle mr-2"></i>Current Status: {{ \App\Models\ServiceRequest::STATUS_CHOICES[$serviceRequest->status] ?? str_replace('_', ' ', $serviceRequest->status) }}
                                    </p>
                                    <p class="text-xs text-yellow-800">
                                        @if($serviceRequest->status === 'PENDING_ADMIN_ASSIGNMENT')
                                            This request is waiting for admin to assign a serviceman.
                                        @elseif($serviceRequest->status === 'SERVICEMAN_INSPECTED')
                                            You have already submitted your estimate. Waiting for admin to set final cost.
                                        @elseif($serviceRequest->status === 'AWAITING_CLIENT_APPROVAL' || $serviceRequest->status === 'AWAITING_PAYMENT')
                                            Waiting for client to approve and pay the final cost.
                                        @else
                                            No actions required at this time. Check back later for updates.
                                        @endif
                                    </p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Admin Control Panel -->
                @if(auth()->user()->isAdmin())
                    <div class="bg-purple-50 shadow-md rounded-lg overflow-hidden border border-purple-200">
                        <div class="bg-purple-600 px-6 py-4">
                            <h3 class="text-lg font-bold text-white flex items-center">
                                <i class="fas fa-shield-alt mr-2"></i>Admin Control Panel
                            </h3>
                            <p class="text-xs text-purple-100 mt-1">Manage this service request</p>
                        </div>
                        
                        <div class="p-6 space-y-4">
                            <!-- Current Status Display -->
                            <div class="bg-white rounded-xl p-4 border-2 border-gray-200">
                                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Current Status</p>
                                <div class="flex items-center justify-between">
                                    <span class="px-4 py-2 rounded-lg text-sm font-bold {{ 
                                        $serviceRequest->status === 'PENDING_ADMIN_ASSIGNMENT' ? 'bg-yellow-100 text-yellow-800' :
                                        ($serviceRequest->status === 'ASSIGNED_TO_SERVICEMAN' ? 'bg-blue-100 text-blue-800' :
                                        ($serviceRequest->status === 'SERVICEMAN_INSPECTED' ? 'bg-purple-100 text-purple-800' :
                                        ($serviceRequest->status === 'AWAITING_CLIENT_APPROVAL' ? 'bg-orange-100 text-orange-800' :
                                        ($serviceRequest->status === 'NEGOTIATING' ? 'bg-red-100 text-red-800' :
                                        ($serviceRequest->status === 'PAYMENT_CONFIRMED' ? 'bg-green-100 text-green-800' :
                                        ($serviceRequest->status === 'IN_PROGRESS' ? 'bg-indigo-100 text-indigo-800' :
                                        ($serviceRequest->status === 'WORK_COMPLETED' ? 'bg-teal-100 text-teal-800' :
                                        ($serviceRequest->status === 'COMPLETED' ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-800'))))))))
                                    }}">
                                        {{ str_replace('_', ' ', $serviceRequest->status) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Actions Based on Status -->
                            @if($serviceRequest->status === 'PENDING_ADMIN_ASSIGNMENT')
                                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-r-lg">
                                    <p class="text-sm font-semibold text-yellow-900 mb-2">
                                        <i class="fas fa-exclamation-triangle mr-2"></i>Action Required: Assign Servicemen
                                    </p>
                                    <p class="text-xs text-yellow-700 mb-3">This request needs primary and optional backup serviceman assignment.</p>
                                    <button onclick="showAssignModal({{ $serviceRequest->id }}, '{{ $serviceRequest->client->full_name }}', '{{ $serviceRequest->serviceman->full_name ?? 'Not assigned' }}', {{ $serviceRequest->category_id }})" 
                                            class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-3 px-4 rounded-lg transition-all shadow-md hover:shadow-lg">
                                        <i class="fas fa-user-plus mr-2"></i>Assign Servicemen Now
                                    </button>
                                </div>

                            @elseif($serviceRequest->status === 'SERVICEMAN_INSPECTED')
                                <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded-r-lg">
                                    <p class="text-sm font-semibold text-purple-900 mb-2">
                                        <i class="fas fa-calculator mr-2"></i>Cost Estimate Ready for Review
                                    </p>
                                    <div class="bg-white rounded-lg p-3 mb-3 border border-purple-200">
                                        <p class="text-xs text-gray-600">Serviceman's Estimate</p>
                                        <p class="text-2xl font-bold text-gray-900">₦{{ number_format($serviceRequest->serviceman_estimated_cost) }}</p>
                                        @if($serviceRequest->inspection_notes)
                                            <p class="text-xs text-gray-600 mt-2 italic">"{{ $serviceRequest->inspection_notes }}"</p>
                                        @endif
                                    </div>
                                    <button onclick="openSetCostModal({{ $serviceRequest->id }}, {{ $serviceRequest->serviceman_estimated_cost }})"
                                            class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-4 rounded-lg transition-all shadow-md hover:shadow-lg">
                                        <i class="fas fa-dollar-sign mr-2"></i>Set Final Cost (Add Markup)
                                    </button>
                                </div>

                            @elseif($serviceRequest->status === 'PAYMENT_CONFIRMED')
                                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg">
                                    <p class="text-sm font-semibold text-green-900 mb-2">
                                        <i class="fas fa-money-check-alt mr-2"></i>Payment Received - Authorize Work
                                    </p>
                                    <p class="text-xs text-green-700 mb-3">Final payment confirmed. Notify serviceman to begin work.</p>
                                    <form action="{{ route('admin.service-requests.notify-start', $serviceRequest) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition-all shadow-md hover:shadow-lg">
                                            <i class="fas fa-play-circle mr-2"></i>Authorize & Notify Serviceman
                                        </button>
                                    </form>
                                </div>

                            @elseif($serviceRequest->status === 'WORK_COMPLETED')
                                <div class="bg-teal-50 border-l-4 border-teal-500 p-4 rounded-r-lg">
                                    <p class="text-sm font-semibold text-teal-900 mb-2">
                                        <i class="fas fa-clipboard-check mr-2"></i>Work Completed - Verify & Close
                                    </p>
                                    <div class="bg-white border border-teal-200 rounded-lg p-3 mb-3">
                                        <p class="text-xs font-semibold text-gray-600 mb-1">Serviceman's Notes:</p>
                                        <p class="text-sm text-gray-800">{{ $serviceRequest->completion_notes }}</p>
                                    </div>
                                    <form action="{{ route('admin.service-requests.confirm-completion', $serviceRequest) }}" method="POST">
                                    @csrf
                                        <div class="mb-3">
                                            <label for="admin_notes" class="block text-xs font-semibold text-gray-700 mb-2">Admin Verification Notes (Optional)</label>
                                            <textarea id="admin_notes" name="admin_notes" rows="2"
                                                      placeholder="Add verification notes or comments..."
                                                      class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500"></textarea>
                                        </div>
                                        <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white font-semibold py-3 px-4 rounded-lg transition-all shadow-md hover:shadow-lg">
                                            <i class="fas fa-check-double mr-2"></i>Verify & Close Request
                                    </button>
                                </form>
                                </div>

                            @elseif($serviceRequest->status === 'NEGOTIATING')
                                <div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded-r-lg">
                                    <p class="text-sm font-semibold text-orange-900 mb-2">
                                        {{-- Price Negotiation In Progress - Feature disabled --}}
                                        {{-- <i class="fas fa-handshake mr-2"></i>Price Negotiation In Progress --}}
                                    </p>
                                    {{-- @if($serviceRequest->negotiations->count() > 0)
                                        @php $latestNegotiation = $serviceRequest->negotiations->sortByDesc('created_at')->first(); @endphp
                                        <div class="bg-white rounded-lg p-3 mb-3 border border-orange-200">
                                            <div class="flex justify-between items-center mb-2">
                                                <span class="text-xs text-gray-600">Original Cost</span>
                                                <span class="text-sm font-bold text-gray-900">₦{{ number_format($serviceRequest->final_cost) }}</span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <span class="text-xs text-gray-600">Client Proposed</span>
                                                <span class="text-sm font-bold text-orange-600">₦{{ number_format($latestNegotiation->proposed_amount) }}</span>
                                            </div>
                                            <p class="text-xs text-gray-600 mt-2 italic">"{{ $latestNegotiation->reason }}"</p>
                                        </div>
                                        <a href="{{ route('admin.service-requests') }}" class="block w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-3 px-4 rounded-lg transition-all shadow-md hover:shadow-lg text-center">
                                            <i class="fas fa-gavel mr-2"></i>Review Negotiation
                                        </a>
                                    @endif --}}
                                </div>

                            @elseif($serviceRequest->status === 'COMPLETED')
                                <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg text-center">
                                    <i class="fas fa-check-circle text-4xl text-emerald-600 mb-3"></i>
                                    <p class="text-sm font-semibold text-emerald-900">Request Completed</p>
                                    <p class="text-xs text-emerald-700 mt-1">This service request has been successfully completed.</p>
                                </div>

                            @else
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                                    <i class="fas fa-info-circle text-2xl text-blue-600 mb-2"></i>
                                    <p class="text-sm text-blue-900">No admin action required at this stage</p>
                                    <p class="text-xs text-blue-700 mt-1">Status: {{ str_replace('_', ' ', $serviceRequest->status) }}</p>
                                </div>
                            @endif

                            <!-- Change Serviceman (Available when serviceman is assigned) -->
                            @if($serviceRequest->serviceman_id && $serviceRequest->serviceman)
                                <div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded-r-lg mt-4">
                                    <p class="text-sm font-semibold text-orange-900 mb-2">
                                        <i class="fas fa-user-exchange mr-2"></i>Change Serviceman
                                    </p>
                                    <p class="text-xs text-orange-700 mb-3">Need to reassign this request to a different serviceman? You can change both primary and backup servicemen.</p>
                                    <button onclick="showChangeServicemanModal({{ $serviceRequest->id }}, '{{ $serviceRequest->serviceman->full_name }}', {{ $serviceRequest->category_id }}, {{ $serviceRequest->serviceman_id }}, {{ $serviceRequest->backup_serviceman_id ?? 'null' }})" 
                                            class="w-full bg-orange-600 hover:bg-orange-700 text-white font-semibold py-3 px-4 rounded-lg transition-all shadow-md hover:shadow-lg">
                                        <i class="fas fa-exchange-alt mr-2"></i>Change Serviceman
                                    </button>
                                </div>
                            @endif

                            <!-- Quick Admin Tools -->
                            <div class="border-t border-gray-200 pt-4 mt-4">
                                <p class="text-xs font-semibold text-gray-600 uppercase mb-3">Quick Tools</p>
                                <div class="grid grid-cols-2 gap-2">
                                    <a href="{{ route('admin.service-requests') }}" class="flex items-center justify-center px-3 py-2 bg-purple-100 text-purple-700 rounded-lg text-xs font-medium hover:bg-purple-200 transition-colors">
                                        <i class="fas fa-list mr-1"></i>All Requests
                                    </a>
                                    <a href="{{ route('admin.users') }}" class="flex items-center justify-center px-3 py-2 bg-blue-100 text-blue-700 rounded-lg text-xs font-medium hover:bg-blue-200 transition-colors">
                                        <i class="fas fa-users mr-1"></i>Manage Users
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Timeline -->
                <div class="bg-white shadow-lg rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h3>
                    <div class="space-y-4">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Request Created</p>
                                <p class="text-xs text-gray-500">{{ $serviceRequest->created_at->format('M d, Y H:i') }}</p>
                            </div>
                        </div>
                        
                        @if($serviceRequest->serviceman)
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-blue-500 rounded-full mr-3"></div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Serviceman Assigned</p>
                                    <p class="text-xs text-gray-500">{{ $serviceRequest->updated_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                        @endif
                        
                        @if($serviceRequest->inspection_completed_at)
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3"></div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Inspection Completed</p>
                                    <p class="text-xs text-gray-500">{{ $serviceRequest->inspection_completed_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                        @endif
                        
                        @if($serviceRequest->work_completed_at)
                            <div class="flex items-center">
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Work Completed</p>
                                    <p class="text-xs text-gray-500">{{ $serviceRequest->work_completed_at->format('M d, Y H:i') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Negotiation Modal -->
<!-- Estimate Modal -->
<div id="estimateModal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm hidden z-50 overflow-y-auto p-4">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6 transform transition-all">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900 flex items-center">
                    <i class="fas fa-calculator mr-2 text-blue-600"></i>
                    Submit Cost Estimate
                </h3>
                <button type="button" onclick="toggleEstimateForm()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form action="{{ route('service-requests.submit-estimate', $serviceRequest) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="serviceman_estimated_cost" class="block text-sm font-semibold text-gray-900 mb-2">
                            Estimated Cost (₦) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               id="serviceman_estimated_cost" 
                               name="serviceman_estimated_cost" 
                               required
                               min="0"
                               step="0.01"
                               placeholder="Enter your cost estimate..."
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg font-semibold">
                        <p class="mt-1 text-xs text-gray-500">Enter the total cost for this service</p>
                    </div>
                    <div>
                        <label for="inspection_notes" class="block text-sm font-semibold text-gray-900 mb-2">
                            Inspection Notes <span class="text-gray-500">(Optional)</span>
                        </label>
                        <textarea id="inspection_notes" 
                                  name="inspection_notes" 
                                  rows="4"
                                  maxlength="1000"
                                  placeholder="Add any notes from your inspection (e.g., materials needed, time required, special conditions)..."
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"></textarea>
                        <p class="mt-1 text-xs text-gray-500">
                            <span id="notes-char-count">0</span>/1000 characters
                        </p>
                    </div>
                    <div class="flex space-x-3 pt-4">
                        <button type="button" 
                                onclick="toggleEstimateForm()" 
                                class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-3 px-4 rounded-lg transition-colors">
                            <i class="fas fa-times mr-2"></i>Cancel
                        </button>
                        <button type="submit" 
                                class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition-colors shadow-lg hover:shadow-xl">
                            <i class="fas fa-check mr-2"></i>Submit Estimate
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Mark Complete Modal -->
<div id="completionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Mark Work as Completed</h3>
            <form action="{{ route('service-requests.mark-complete', $serviceRequest) }}" method="POST">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="completion_notes" class="block text-sm font-medium text-gray-700 mb-2">Completion Notes *</label>
                        <textarea id="completion_notes" name="completion_notes" rows="4" required
                                  placeholder="Describe the work completed, any issues resolved, recommendations, etc..."
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                        <p class="text-xs text-gray-500 mt-1">This message will be reviewed by admin and shared with the client</p>
                    </div>
                    <div class="flex space-x-3">
                        <button type="button" onclick="toggleCompletionForm()" 
                                class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="flex-1 bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                            Mark as Completed
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Admin Set Final Cost Modal -->
<div id="setCostModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-900">
                    <i class="fas fa-calculator mr-2 text-purple-600"></i>Set Final Cost
                </h3>
                <button onclick="closeSetCostModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <form id="setCostForm" method="POST">
                @csrf
                <!-- Hidden field for final_cost that will be calculated -->
                <input type="hidden" id="final_cost_input" name="final_cost" value="0">
                
                <div class="space-y-4">
                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600">Serviceman's Estimate</span>
                            <span class="text-lg font-bold text-gray-900" id="servicemanEstimate">₦0</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Admin Markup (10%)</span>
                            <span class="text-lg font-bold text-purple-600" id="adminMarkup">₦0</span>
                        </div>
                        <div class="border-t border-purple-300 my-2"></div>
                        <div class="flex justify-between items-center">
                            <span class="text-base font-semibold text-gray-900">Final Cost to Client</span>
                            <span class="text-2xl font-bold text-purple-600" id="finalCostDisplay">₦0</span>
                        </div>
                    </div>

                    <div>
                        <label for="admin_markup_percentage" class="block text-sm font-semibold text-gray-700 mb-2">
                            Admin Markup Percentage (%)
                        </label>
                        <input type="number" 
                               id="admin_markup_percentage" 
                               name="admin_markup_percentage" 
                               value="10" 
                               min="0" 
                               max="50" 
                               step="0.1"
                               oninput="calculateFinalCost()"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Adjust the markup percentage (0-50%)</p>
                    </div>

                    <div>
                        <label for="admin_notes_cost" class="block text-sm font-semibold text-gray-700 mb-2">
                            Notes for Client (Optional)
                        </label>
                        <textarea id="admin_notes_cost" 
                                  name="admin_notes" 
                                  rows="3"
                                  placeholder="Add any notes or explanations about the cost..."
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent"></textarea>
                    </div>

                    <div class="flex space-x-3 pt-4">
                        <button type="button" onclick="closeSetCostModal()" 
                                class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-3 px-4 rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                            <i class="fas fa-check mr-2"></i>Set Final Cost
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showComingSoonMessage(event) {
    event.preventDefault();
    event.stopPropagation();
    
    // Create toast notification
    const toast = document.createElement('div');
    toast.className = 'fixed top-20 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white px-6 py-4 rounded-lg shadow-2xl z-50 animate-fade-in-down';
    toast.innerHTML = `
        <div class="flex items-center space-x-3">
            <i class="fas fa-hourglass-half text-yellow-400 text-xl"></i>
            <div>
                <p class="font-semibold">💬 Negotiate Price - Coming Soon</p>
                <p class="text-sm text-gray-300">This feature is under review by management</p>
            </div>
        </div>
    `;
    
    document.body.appendChild(toast);
    
    // Remove after 4 seconds
    setTimeout(() => {
        toast.classList.add('animate-fade-out-up');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

function toggleNegotiationForm() {
    // Disabled - show coming soon message instead
    showComingSoonMessage(event);
    return false;
}

function toggleDeclineForm() {
    const form = document.getElementById('declineForm');
    if (form) {
        form.classList.toggle('hidden');
    }
}

function toggleEstimateForm() {
    const modal = document.getElementById('estimateModal');
    if (modal) {
        if (modal.classList.contains('hidden')) {
            modal.classList.remove('hidden');
            // Focus on the cost input when modal opens
            setTimeout(() => {
                const costInput = document.getElementById('serviceman_estimated_cost');
                if (costInput) {
                    costInput.focus();
                }
            }, 100);
        } else {
            modal.classList.add('hidden');
        }
    }
}

// Character count for inspection notes
document.addEventListener('DOMContentLoaded', function() {
    const notesTextarea = document.getElementById('inspection_notes');
    const notesCount = document.getElementById('notes-char-count');
    a
    if (notesTextarea && notesCount) {
        notesTextarea.addEventListener('input', function() {
            notesCount.textContent = this.value.length;
        });
    }
});

function toggleCompletionForm() {
    const modal = document.getElementById('completionModal');
    if (modal) {
        modal.classList.toggle('hidden');
    }
}

let currentServicemanEstimate = 0;

function openSetCostModal(requestId, servicemanEstimate) {
    currentServicemanEstimate = servicemanEstimate;
    const modal = document.getElementById('setCostModal');
    const form = document.getElementById('setCostForm');
    
    form.action = `/admin/service-requests/${requestId}/set-final-cost`;
    
    // Update display
    document.getElementById('servicemanEstimate').textContent = '₦' + servicemanEstimate.toLocaleString();
    
    // Calculate initial values
    calculateFinalCost();
    
    modal.classList.remove('hidden');
}

function closeSetCostModal() {
    const modal = document.getElementById('setCostModal');
    modal.classList.add('hidden');
}

function calculateFinalCost() {
    const markupPercentage = parseFloat(document.getElementById('admin_markup_percentage').value) || 10;
    const markup = currentServicemanEstimate * (markupPercentage / 100);
    const finalCost = currentServicemanEstimate + markup;
    
    // Update display
    document.getElementById('adminMarkup').textContent = '₦' + markup.toLocaleString('en-NG', {maximumFractionDigits: 2});
    document.getElementById('finalCostDisplay').textContent = '₦' + finalCost.toLocaleString('en-NG', {maximumFractionDigits: 2});
    
    // Update hidden field with the calculated final cost
    document.getElementById('final_cost_input').value = finalCost;
}

// Rating stars functionality
document.addEventListener('DOMContentLoaded', function() {
    const ratingStars = document.querySelectorAll('.rating-star');
    const ratingInputs = document.querySelectorAll('input[name="rating"]');
    
    // Only initialize rating functionality if rating stars exist
    if (ratingStars.length === 0) {
        return;
    }
    
    ratingStars.forEach((star, index) => {
        star.addEventListener('click', function() {
            const rating = index + 1;
            const ratingInput = document.querySelector(`input[value="${rating}"]`);
            if (ratingInput) {
                ratingInput.checked = true;
            }
            updateStarDisplay(rating);
        });
        
        star.addEventListener('mouseenter', function() {
            const rating = index + 1;
            updateStarDisplay(rating);
        });
    });
    
    const ratingStar = document.querySelector('.rating-star');
    if (ratingStar) {
        const ratingContainer = ratingStar.parentElement;
        ratingContainer.addEventListener('mouseleave', function() {
            const checkedRating = document.querySelector('input[name="rating"]:checked');
            if (checkedRating) {
                updateStarDisplay(parseInt(checkedRating.value));
            } else {
                updateStarDisplay(0);
            }
        });
    }
    
    function updateStarDisplay(rating) {
        ratingStars.forEach((star, index) => {
            if (index < rating) {
                star.classList.add('text-yellow-400');
                star.classList.remove('text-gray-300');
            } else {
                star.classList.add('text-gray-300');
                star.classList.remove('text-yellow-400');
            }
        });
    }
});

// Admin Assign Serviceman Modal Functions
function showAssignModal(requestId, clientName, servicemanName, categoryId) {
    console.log('Opening assign modal for request:', requestId);
    
    // Update modal content
    document.getElementById('modalClientName').textContent = clientName;
    document.getElementById('modalServicemanName').textContent = servicemanName;
    
    // Form action is already set on the server side
    const form = document.getElementById('assignForm');
    console.log('Form will submit to:', form.action);
    
    // Backup servicemen are already pre-loaded on page load
    const backupSelect = document.getElementById('backupServicemanSelect');
    console.log('Backup options available:', backupSelect ? backupSelect.options.length - 1 : 0);
    
    // Show modal
    document.getElementById('assignModal').classList.remove('hidden');
}

function closeAssignModal() {
    document.getElementById('assignModal').classList.add('hidden');
    document.getElementById('assignForm').reset();
    resetModalToAssignMode();
}

function showRejectionMode() {
    console.log('Switching to rejection mode');
    
    // Hide assign fields
    document.getElementById('adminMessageSection').classList.add('hidden');
    document.getElementById('backupServicemanSelect').closest('div').classList.add('hidden');
    
    // Show rejection field and enable it
    const rejectionSection = document.getElementById('rejectionReasonSection');
    const rejectionTextarea = document.getElementById('rejectionReason');
    rejectionSection.classList.remove('hidden');
    rejectionTextarea.disabled = false; // Enable for submission
    
    // Hide normal buttons, show rejection buttons
    document.getElementById('normalButtons').classList.add('hidden');
    document.getElementById('rejectionButtons').classList.remove('hidden');
}

function hideRejectionMode() {
    console.log('Switching back to assign mode');
    
    // Show assign fields
    document.getElementById('adminMessageSection').classList.remove('hidden');
    document.getElementById('backupServicemanSelect').closest('div').classList.remove('hidden');
    
    // Hide rejection field and disable it so it doesn't submit
    const rejectionSection = document.getElementById('rejectionReasonSection');
    const rejectionTextarea = document.getElementById('rejectionReason');
    rejectionSection.classList.add('hidden');
    rejectionTextarea.disabled = true; // Disable so it won't be submitted
    rejectionTextarea.value = ''; // Clear value
    
    // Show normal buttons, hide rejection buttons
    document.getElementById('normalButtons').classList.remove('hidden');
    document.getElementById('rejectionButtons').classList.add('hidden');
}

function resetModalToAssignMode() {
    hideRejectionMode();
}

// Close modal when clicking outside
document.getElementById('assignModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeAssignModal();
    }
});

// Add form submission logging and validation
document.getElementById('assignForm')?.addEventListener('submit', function(e) {
    console.log('Form submitting!');
    console.log('Action:', this.action);
    console.log('Method:', this.method);
    
    const formData = new FormData(this);
    const action = formData.get('action');
    console.log('Action type:', action);
    
    // Validate rejection reason if rejecting
    if (action === 'reject') {
        const rejectionReason = document.getElementById('rejectionReason').value.trim();
        if (!rejectionReason) {
            e.preventDefault();
            alert('⚠️ Please provide a rejection reason before confirming.');
            document.getElementById('rejectionReason').focus();
            return false;
        }
        console.log('Rejection reason provided:', rejectionReason);
    }
    
    console.log('Form validation passed, submitting...');
    // Let it submit normally
});

// Change Serviceman Modal Functions
function showChangeServicemanModal(requestId, currentServicemanName, categoryId, currentServicemanId, currentBackupId) {
    document.getElementById('changeServicemanModal').classList.remove('hidden');
    
    // Update modal content
    document.getElementById('changeModalCurrentServiceman').textContent = currentServicemanName;
    
    // Form action is already set in the HTML
    
    // Clear any previous selections
    document.getElementById('newServicemanSelect').value = '';
    document.getElementById('newBackupServicemanSelect').value = '';
    document.getElementById('reassignmentReason').value = '';
    
    // Show modal
    document.body.style.overflow = 'hidden';
}

function closeChangeServicemanModal() {
    document.getElementById('changeServicemanModal').classList.add('hidden');
    document.getElementById('changeForm').reset();
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside
document.getElementById('changeServicemanModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeChangeServicemanModal();
    }
});
</script>

<!-- Change Serviceman Modal -->
<div id="changeServicemanModal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-6 border w-11/12 md:w-3/4 lg:w-1/2 shadow-2xl rounded-2xl bg-white">
        <div class="flex justify-between items-center pb-4 border-b-2 border-gray-200">
            <h3 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-exchange-alt mr-2"></i>Change Serviceman
            </h3>
            <button onclick="closeChangeServicemanModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        
        <form id="changeForm" method="POST" action="{{ route('admin.service-requests.change-serviceman', $serviceRequest) }}" class="mt-6">
            @csrf
            <div class="space-y-5">
                <!-- Current Assignment Info -->
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-5">
                    <label class="block text-sm font-bold text-gray-900 mb-3">
                        <i class="fas fa-info-circle mr-2 text-orange-600"></i>Current Assignment
                    </label>
                    <div class="space-y-2">
                        <p class="text-sm"><strong>Current Primary Serviceman:</strong> <span id="changeModalCurrentServiceman" class="text-orange-700 font-semibold"></span></p>
                        <p class="text-xs text-gray-600 mt-2 bg-orange-100 rounded-lg p-2">
                            <i class="fas fa-lightbulb mr-1"></i>
                            Select a new primary serviceman. You can also assign an optional backup serviceman.
                        </p>
                    </div>
                </div>
                
                <!-- New Primary Serviceman -->
                <div>
                    <label for="newServicemanSelect" class="block text-sm font-bold text-gray-900 mb-2">
                        <i class="fas fa-user-check text-blue-500 mr-2"></i>
                        New Primary Serviceman <span class="text-red-500">*</span>
                    </label>
                    <select id="newServicemanSelect" 
                            name="new_serviceman_id"
                            required
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all">
                        <option value="">Select new serviceman...</option>
                        @foreach($availableServicemen ?? [] as $serviceman)
                            <option value="{{ $serviceman['id'] }}">
                                {{ $serviceman['full_name'] }} - 
                                {{ $serviceman['experience_years'] }} years exp - 
                                Rating: {{ number_format($serviceman['rating'], 1) }}
                                @if($serviceman['id'] == ($serviceRequest->serviceman_id ?? 0))
                                    (Currently Assigned)
                                @elseif(!$serviceman['is_available'])
                                    (Currently Busy)
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Select the new primary serviceman for this request.
                    </p>
                </div>
                
                <!-- New Backup Serviceman (Optional) -->
                <div>
                    <label for="newBackupServicemanSelect" class="block text-sm font-bold text-gray-900 mb-2">
                        <i class="fas fa-user-shield text-purple-500 mr-2"></i>
                        New Backup Serviceman (Optional)
                    </label>
                    <select id="newBackupServicemanSelect" 
                            name="backup_serviceman_id"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all">
                        <option value="">No backup serviceman (optional)</option>
                        @foreach($availableServicemen ?? [] as $serviceman)
                            <option value="{{ $serviceman['id'] }}">
                                {{ $serviceman['full_name'] }} - 
                                {{ $serviceman['experience_years'] }} years exp - 
                                Rating: {{ number_format($serviceman['rating'], 1) }}
                                @if(!$serviceman['is_available'])
                                    (Currently Busy)
                                @endif
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Optionally select a backup serviceman as a standby.
                    </p>
                </div>
                
                <!-- Reason for Change (Optional) -->
                <div>
                    <label for="reassignmentReason" class="block text-sm font-bold text-gray-900 mb-2">
                        <i class="fas fa-comment-dots text-gray-500 mr-2"></i>
                        Reason for Change (Optional)
                    </label>
                    <textarea id="reassignmentReason" 
                              name="reassignment_reason" 
                              rows="3"
                              placeholder="Provide a reason for changing the serviceman (this will be included in notifications)..."
                              class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent transition-all resize-none"></textarea>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3 mt-8 pt-6 border-t-2 border-gray-200">
                <button type="button" 
                        onclick="closeChangeServicemanModal()"
                        class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold rounded-xl transition-all transform hover:scale-105">
                    <i class="fas fa-times mr-2"></i>Cancel
                </button>
                <button type="submit" 
                        class="flex-1 px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white font-semibold rounded-lg transition-colors shadow-lg hover:shadow-xl">
                    <i class="fas fa-exchange-alt mr-2"></i>Change Serviceman
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Assign Serviceman Modal -->
<div id="assignModal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-6 border w-11/12 md:w-3/4 lg:w-1/2 shadow-2xl rounded-2xl bg-white">
        <div class="flex justify-between items-center pb-4 border-b-2 border-gray-200">
            <h3 class="text-2xl font-bold text-gray-900">
                <i class="fas fa-user-check mr-2"></i>Confirm Serviceman Assignment
            </h3>
            <button onclick="closeAssignModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-2xl"></i>
            </button>
        </div>
        
        <form id="assignForm" method="POST" action="{{ route('admin.service-requests.assign-serviceman', $serviceRequest) }}" class="mt-6">
            @csrf
            <div class="space-y-5">
                <!-- Service Request Details -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-5">
                    <label class="block text-sm font-bold text-gray-900 mb-3">
                        <i class="fas fa-info-circle mr-2 text-blue-600"></i>Service Request Details
                    </label>
                    <div class="space-y-2">
                        <p class="text-sm"><strong>Client:</strong> <span id="modalClientName" class="text-gray-700"></span></p>
                        <p class="text-sm flex items-center">
                            <i class="fas fa-user-check text-blue-600 mr-2"></i>
                            <strong>Chosen Serviceman (Primary):</strong> 
                            <span id="modalServicemanName" class="text-blue-600 font-semibold ml-2"></span>
                        </p>
                        <p class="text-xs text-gray-600 mt-2 bg-blue-100 rounded-lg p-2">
                            <i class="fas fa-lightbulb mr-1"></i>
                            This serviceman was selected by the client and will receive the service request notification.
                        </p>
                    </div>
                </div>
                
                <!-- Backup Serviceman -->
                <div>
                    <label for="backupServicemanSelect" class="block text-sm font-bold text-gray-900 mb-2">
                        <i class="fas fa-user-shield text-orange-500 mr-2"></i>
                        Backup/Standby Serviceman (Optional)
                    </label>
                    <select id="backupServicemanSelect" 
                            name="backup_serviceman_id"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                        <option value="">No backup serviceman (optional)</option>
                        @foreach($availableServicemen ?? [] as $serviceman)
                            @if($serviceman['id'] != $serviceRequest->serviceman_id)
                                <option value="{{ $serviceman['id'] }}">
                                    {{ $serviceman['full_name'] }} - 
                                    {{ $serviceman['experience_years'] }} years exp - 
                                    Rating: {{ number_format($serviceman['rating'], 1) }}
                                    @if(!$serviceman['is_available'])
                                        (Currently Busy)
                                    @endif
                                </option>
                            @endif
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Select a backup serviceman to standby in case the primary serviceman becomes unavailable.
                    </p>
                    @if(count($availableServicemen ?? []) <= 1)
                        <p class="mt-2 text-xs text-orange-600 bg-orange-50 border border-orange-200 rounded-lg p-2">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            No other servicemen available in this category for backup assignment.
                        </p>
                    @endif
                </div>
                
                <!-- Admin Message -->
                <div id="adminMessageSection">
                    <label for="adminMessage" class="block text-sm font-bold text-gray-900 mb-2">
                        <i class="fas fa-comment-dots text-green-500 mr-2"></i>
                        Special Instructions (Optional)
                    </label>
                    <textarea id="adminMessage" 
                              name="message" 
                              rows="3"
                              placeholder="Add any special instructions or notes for the servicemen..."
                              class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all resize-none"></textarea>
                </div>

                <!-- Rejection Reason (hidden by default, only included when rejecting) -->
                <div id="rejectionReasonSection" class="hidden">
                    <label for="rejectionReason" class="block text-sm font-bold text-gray-900 mb-2">
                        <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                        Rejection Reason <span class="text-red-500">*</span>
                    </label>
                    <textarea id="rejectionReason" 
                              name="rejection_reason"
                              rows="4"
                              disabled
                              placeholder="Please provide a clear reason for rejecting this request (e.g., serviceman unavailable, out of service area, insufficient information, etc.)..."
                              class="w-full px-4 py-3 border-2 border-red-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent transition-all resize-none"></textarea>
                    <p class="mt-2 text-xs text-red-600">
                        <i class="fas fa-info-circle mr-1"></i>
                        The client will receive this reason in their notification. Please be clear and professional.
                    </p>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div id="normalButtons" class="flex flex-col sm:flex-row gap-3 mt-8 pt-6 border-t-2 border-gray-200">
                <button type="button" 
                        onclick="closeAssignModal()"
                        class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold rounded-xl transition-all transform hover:scale-105">
                    <i class="fas fa-times mr-2"></i>Cancel
                </button>
                <button type="button" 
                        onclick="showRejectionMode()"
                        class="flex-1 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                    <i class="fas fa-ban mr-2"></i>Reject Request
                </button>
                <button type="submit" 
                        name="action" 
                        value="assign"
                        class="flex-1 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors">
                    <i class="fas fa-check-circle mr-2"></i>Confirm & Assign
                </button>
            </div>

            <!-- Rejection Mode Buttons (Hidden by default) -->
            <div id="rejectionButtons" class="hidden flex flex-col sm:flex-row gap-3 mt-8 pt-6 border-t-2 border-gray-200">
                <button type="button" 
                        onclick="hideRejectionMode()"
                        class="flex-1 px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white font-bold rounded-xl transition-all transform hover:scale-105 shadow-md">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Assign
                </button>
                <button type="submit" 
                        name="action" 
                        value="reject"
                        class="flex-1 px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors">
                    <i class="fas fa-times-circle mr-2"></i>Confirm Rejection
                </button>
            </div>
        </form>
    </div>
</div>

@endsection