@extends('layouts.app')

@section('title', 'Complete Booking Payment')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-purple-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-xl">
                <i class="fas fa-credit-card text-white text-3xl"></i>
            </div>
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2">
                Complete Your Booking
            </h1>
            <p class="text-gray-600">Pay the booking fee to confirm your service request</p>
        </div>

        @if(session('info'))
            <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg shadow-md">
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-blue-500 mr-3"></i>
                    <p class="text-sm text-blue-700">{{ session('info') }}</p>
                </div>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Booking Summary -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-4">
                        <h2 class="text-xl font-bold text-white">
                            <i class="fas fa-clipboard-check mr-2"></i>Booking Summary
                        </h2>
                    </div>

                    <div class="p-6 space-y-4">
                        <!-- Service Details -->
                        <div class="flex items-start space-x-4">
                            <div class="bg-blue-100 p-3 rounded-xl">
                                <i class="fas fa-concierge-bell text-2xl text-blue-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-500">Service Category</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $pendingBooking['category_name'] }}</p>
                            </div>
                        </div>

                        <!-- Serviceman -->
                        <div class="flex items-start space-x-4">
                            <div class="bg-purple-100 p-3 rounded-xl">
                                <i class="fas fa-user-tie text-2xl text-purple-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-500">Chosen Serviceman</p>
                                <p class="text-lg font-semibold text-gray-900">{{ $pendingBooking['serviceman_name'] }}</p>
                            </div>
                        </div>

                        <!-- Booking Date -->
                        <div class="flex items-start space-x-4">
                            <div class="bg-green-100 p-3 rounded-xl">
                                <i class="fas fa-calendar-alt text-2xl text-green-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-500">Booking Date</p>
                                <p class="text-lg font-semibold text-gray-900">{{ \Carbon\Carbon::parse($pendingBooking['booking_date'])->format('F d, Y') }}</p>
                                @if($pendingBooking['is_emergency'])
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 mt-1">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>Emergency Service
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="flex items-start space-x-4">
                            <div class="bg-orange-100 p-3 rounded-xl">
                                <i class="fas fa-map-marker-alt text-2xl text-orange-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-500">Service Location</p>
                                <p class="text-base text-gray-900">{{ $pendingBooking['client_address'] }}</p>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="flex items-start space-x-4">
                            <div class="bg-indigo-100 p-3 rounded-xl">
                                <i class="fas fa-align-left text-2xl text-indigo-600"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-gray-500">Service Description</p>
                                <p class="text-base text-gray-900">{{ $pendingBooking['service_description'] }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Card -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden sticky top-20">
                    <div class="bg-gradient-to-br from-green-500 to-emerald-600 px-6 py-4">
                        <h3 class="text-lg font-bold text-white">
                            <i class="fas fa-money-bill-wave mr-2"></i>Payment Details
                        </h3>
                    </div>

                    <div class="p-6">
                        <div class="mb-6">
                            <p class="text-sm text-gray-500 mb-2">Booking Fee</p>
                            <p class="text-4xl font-bold text-gray-900">₦{{ number_format($pendingBooking['booking_fee']) }}</p>
                            @if($pendingBooking['is_emergency'])
                                <p class="text-xs text-red-600 mt-2">
                                    <i class="fas fa-info-circle mr-1"></i>Emergency service fee
                                </p>
                            @else
                                <p class="text-xs text-gray-500 mt-2">
                                    <i class="fas fa-info-circle mr-1"></i>Standard booking fee
                                </p>
                            @endif
                        </div>

                        <div class="bg-blue-50 rounded-lg p-4 mb-6 border border-blue-200">
                            <p class="text-xs font-semibold text-blue-900 mb-2">
                                <i class="fas fa-shield-alt mr-1"></i>What happens next?
                            </p>
                            <ul class="text-xs text-blue-800 space-y-2">
                                <li class="flex items-start">
                                    <i class="fas fa-check text-green-600 mr-2 mt-0.5"></i>
                                    <span>Payment secured with Paystack</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check text-green-600 mr-2 mt-0.5"></i>
                                    <span>Service request created</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check text-green-600 mr-2 mt-0.5"></i>
                                    <span>Admin assigns serviceman</span>
                                </li>
                                <li class="flex items-start">
                                    <i class="fas fa-check text-green-600 mr-2 mt-0.5"></i>
                                    <span>Serviceman contacts you</span>
                                </li>
                            </ul>
                        </div>

                        <form action="{{ route('paystack.initialize') }}" method="POST">
                            @csrf
                            <input type="hidden" name="service_request" value="pending">
                            <input type="hidden" name="type" value="INITIAL_BOOKING">
                            <input type="hidden" name="amount" value="{{ $pendingBooking['booking_fee'] }}">
                            
                            <button type="submit" 
                                    class="w-full flex justify-center items-center py-4 px-4 border border-transparent rounded-xl shadow-lg text-base font-bold text-white bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all transform hover:scale-105">
                                <i class="fas fa-lock mr-2"></i>
                                Pay ₦{{ number_format($pendingBooking['booking_fee']) }} Now
                            </button>
                        </form>

                        <p class="text-xs text-center text-gray-500 mt-4">
                            <i class="fas fa-lock mr-1"></i>
                            Secure payment powered by Paystack
                        </p>

                        <a href="{{ route('services') }}" class="block text-center text-sm text-gray-600 hover:text-gray-900 mt-4">
                            <i class="fas fa-arrow-left mr-1"></i>Cancel and go back
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

