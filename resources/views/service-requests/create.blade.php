@extends('layouts.app')

@section('title', 'Book Service')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 rounded-3xl flex items-center justify-center mx-auto mb-4 shadow-2xl animate-bounce-slow">
                <i class="fas fa-calendar-check text-white text-3xl"></i>
            </div>
            <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 bg-clip-text text-transparent mb-2">
                @if($selectedServiceman)
                    Book {{ $selectedServiceman->full_name }}
                @else
                    Book a Service
                @endif
            </h1>
            <p class="text-gray-600 text-lg">
                @if($selectedServiceman)
                    Complete your booking with this trusted professional
                @else
                    Choose a service category and professional to get started
                @endif
            </p>
        </div>

        @if(session('error'))
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-2xl shadow-md animate-shake">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-circle text-red-500 mr-3 text-xl"></i>
                    <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-2xl shadow-md">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-red-500 mr-3 text-xl mt-1"></i>
                    <div>
                        <p class="text-sm font-bold text-red-700 mb-2">Please fix the following errors:</p>
                        <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <!-- Main Form Card -->
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
            <!-- Card Header -->
            <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 px-8 py-6">
                <h2 class="text-2xl font-bold text-white flex items-center">
                    <i class="fas fa-clipboard-list mr-3"></i>Booking Details
                </h2>
                <p class="text-blue-100 text-sm mt-1">Fill in the information below to proceed</p>
            </div>

            <form action="{{ route('service-requests.store') }}" method="POST" class="p-8">
                @csrf

                @if($selectedServiceman)
                    <!-- Pre-selected Serviceman Card -->
                    <div class="bg-gradient-to-br from-blue-50 to-purple-50 border-2 border-blue-200 rounded-2xl p-6 mb-8">
                        <h3 class="font-bold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-user-check mr-2 text-blue-600"></i>
                            Your Selected Professional
                        </h3>
                        <div class="flex items-center space-x-6">
                            <div class="w-20 h-20 bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl flex items-center justify-center shadow-xl flex-shrink-0">
                                <i class="fas fa-user text-white text-3xl"></i>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-xl font-bold text-gray-900">{{ $selectedServiceman->full_name }}</h4>
                                <p class="text-sm text-gray-600 flex items-center mt-1">
                                    <i class="fas fa-tools mr-2"></i>
                                    {{ $selectedCategory->name }} • {{ $selectedServiceman->servicemanProfile->experience_years ?? 0 }} years experience
                                </p>
                                <div class="flex items-center mt-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        @if($i <= floor($selectedServiceman->servicemanProfile->rating ?? 0))
                                            <i class="fas fa-star text-yellow-400"></i>
                                        @elseif($i == ceil($selectedServiceman->servicemanProfile->rating ?? 0) && ($selectedServiceman->servicemanProfile->rating ?? 0) % 1 != 0)
                                            <i class="fas fa-star-half-alt text-yellow-400"></i>
                                        @else
                                            <i class="far fa-star text-gray-300"></i>
                                        @endif
                                    @endfor
                                    <span class="text-sm text-gray-600 ml-2">
                                        {{ number_format($selectedServiceman->servicemanProfile->rating ?? 0, 1) }} 
                                        • {{ $selectedServiceman->servicemanProfile->total_jobs_completed ?? 0 }} jobs completed
                                    </span>
                                </div>
                                @if($selectedServiceman->servicemanProfile->bio)
                                    <p class="text-sm text-gray-700 mt-3 bg-white rounded-lg p-3 border border-gray-200">
                                        "{{ $selectedServiceman->servicemanProfile->bio }}"
                                    </p>
                                @endif
                            </div>
                        </div>
                        
                        <input type="hidden" name="serviceman_id" value="{{ $selectedServiceman->id }}">
                        <input type="hidden" name="category_id" value="{{ $selectedCategory->id }}">
                    </div>
                @else
                    <!-- Category and Serviceman Selection -->
                    <div class="space-y-6 mb-8">
                        <!-- Service Category Selection -->
                        <div>
                            <label for="category_id" class="block text-sm font-bold text-gray-900 mb-2">
                                <i class="fas fa-list-ul mr-2 text-blue-600"></i>
                                Service Category <span class="text-red-500">*</span>
                            </label>
                            <select id="category_id" name="category_id" required
                                    class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                                <option value="">Select a service category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Serviceman Selection (loaded via AJAX) -->
                        <div id="serviceman-selection" class="hidden">
                            <label class="block text-sm font-bold text-gray-900 mb-2">
                                <i class="fas fa-user-tie mr-2 text-purple-600"></i>
                                Choose Your Serviceman <span class="text-red-500">*</span>
                            </label>
                            <div id="servicemen-list" class="space-y-3">
                                <!-- Servicemen will be loaded here via AJAX -->
                            </div>
                            @error('serviceman_id')
                                <p class="mt-1 text-sm text-red-600 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                @endif

                <!-- Booking Fee Display -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-200 rounded-2xl p-6 mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="font-bold text-gray-900 mb-1 flex items-center">
                                <i class="fas fa-money-bill-wave mr-2 text-green-600"></i>
                                Booking Fee
                            </h4>
                            <p class="text-sm text-gray-600" id="feeType">Regular Service</p>
                        </div>
                        <div class="text-right">
                            <p class="text-4xl font-bold text-green-600" id="bookingFee">₦2,000</p>
                        </div>
                    </div>
                </div>

                <!-- Form Fields -->
                <div class="space-y-6">
                    <!-- Booking Date -->
                    <div>
                        <label for="booking_date" class="block text-sm font-bold text-gray-900 mb-2">
                            <i class="fas fa-calendar mr-2 text-blue-600"></i>
                            Preferred Service Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" 
                               id="booking_date" 
                               name="booking_date" 
                               required
                               value="{{ old('booking_date') }}"
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                        @error('booking_date')
                            <p class="mt-1 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Emergency Checkbox -->
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                        <div class="flex items-start">
                            <input type="checkbox" 
                                   id="is_emergency" 
                                   name="is_emergency" 
                                   value="1"
                                   {{ old('is_emergency') ? 'checked' : '' }}
                                   class="h-5 w-5 text-red-600 focus:ring-red-500 border-gray-300 rounded mt-1">
                            <label for="is_emergency" class="ml-3">
                                <span class="block text-sm font-bold text-gray-900">Emergency Service Request</span>
                                <span class="block text-xs text-red-600 mt-1">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Booking within 2 days or emergency flag adds ₦3,000 to the booking fee
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Service Address -->
                    <div>
                        <label for="client_address" class="block text-sm font-bold text-gray-900 mb-2">
                            <i class="fas fa-map-marker-alt mr-2 text-orange-600"></i>
                            Service Address <span class="text-red-500">*</span>
                        </label>
                        <textarea id="client_address" 
                                  name="client_address" 
                                  rows="3" 
                                  required
                                  maxlength="500"
                                  placeholder="Enter the complete address where service is needed (max 500 characters)..."
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none">{{ old('client_address') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">
                            <span id="address-char-count">0</span>/500 characters
                        </p>
                        @error('client_address')
                            <p class="mt-1 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Service Description -->
                    <div>
                        <label for="service_description" class="block text-sm font-bold text-gray-900 mb-2">
                            <i class="fas fa-file-alt mr-2 text-purple-600"></i>
                            Service Description <span class="text-red-500">*</span>
                        </label>
                        <textarea id="service_description" 
                                  name="service_description" 
                                  rows="5" 
                                  required
                                  maxlength="1000"
                                  placeholder="Describe the service you need in detail (e.g., leaking pipe in bathroom, electrical outlet not working, etc.) - max 1000 characters..."
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none">{{ old('service_description') }}</textarea>
                        <p class="mt-1 text-xs text-gray-500">
                            <span id="description-char-count">0</span>/1000 characters
                        </p>
                        @error('service_description')
                            <p class="mt-1 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <!-- Information Banner -->
                <div class="bg-blue-50 border-l-4 border-blue-500 rounded-r-xl p-4 mt-8">
                    <p class="text-sm text-blue-900 font-semibold mb-2">
                        <i class="fas fa-info-circle mr-2"></i>What happens next?
                    </p>
                    <ul class="text-xs text-blue-800 space-y-2 ml-6">
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-600 mr-2 mt-0.5"></i>
                            <span>You'll be redirected to secure payment (Paystack)</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-600 mr-2 mt-0.5"></i>
                            <span>After payment, your service request will be created</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-green-600 mr-2 mt-0.5"></i>
                            <span>Admin will confirm assignment & serviceman will contact you</span>
                        </li>
                    </ul>
                </div>

                <!-- Form Actions -->
                <div class="flex flex-col sm:flex-row gap-4 mt-8 pt-6 border-t-2 border-gray-200">
                    <a href="{{ $selectedServiceman ? route('servicemen.show', $selectedServiceman) : route('services') }}" 
                       class="flex-1 flex items-center justify-center px-6 py-4 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold rounded-xl transition-all transform hover:scale-105">
                        <i class="fas fa-arrow-left mr-2"></i>Back
                    </a>
                    <button type="submit"
                            class="flex-1 flex items-center justify-center px-6 py-4 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-xl transform hover:scale-105">
                        <i class="fas fa-lock mr-2"></i>
                        Proceed to Payment - <span id="paymentAmount">₦2,000</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
@keyframes bounce-slow {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

.animate-bounce-slow {
    animation: bounce-slow 2s ease-in-out infinite;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-10px); }
    75% { transform: translateX(10px); }
}

.animate-shake {
    animation: shake 0.5s ease-in-out;
}
</style>

<script>
// Update booking fee based on date and emergency checkbox
function updateBookingFee() {
    const bookingDate = document.getElementById('booking_date').value;
    const isEmergency = document.getElementById('is_emergency').checked;
    
    let baseFee = 2000; // Regular booking fee
    let isAutoEmergency = false;
    
    // Check if date is within 2 days (emergency auto-detection)
    if (bookingDate) {
        const selectedDate = new Date(bookingDate);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        selectedDate.setHours(0, 0, 0, 0);
        const diffTime = selectedDate - today;
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        
        if (diffDays <= 2) {
            baseFee = 5000; // Emergency booking fee
            document.getElementById('is_emergency').checked = true;
            document.getElementById('is_emergency').disabled = true;
            isAutoEmergency = true;
        } else {
            document.getElementById('is_emergency').disabled = false;
        }
    }
    
    // Add emergency fee if checked manually (and not auto-emergency)
    if (isEmergency && !isAutoEmergency) {
        baseFee = 5000;
    }
    
    // Update UI
    document.getElementById('bookingFee').textContent = `₦${baseFee.toLocaleString()}`;
    document.getElementById('paymentAmount').textContent = `₦${baseFee.toLocaleString()}`;
    
    // Update fee type display
    const feeTypeElement = document.getElementById('feeType');
    if (baseFee >= 5000) {
        feeTypeElement.textContent = isAutoEmergency ? 'Emergency (Within 2 days)' : 'Emergency Service';
        feeTypeElement.className = 'text-sm font-semibold text-red-600';
    } else {
        feeTypeElement.textContent = 'Regular Service';
        feeTypeElement.className = 'text-sm font-semibold text-green-600';
    }
}

// Load servicemen when category is selected (if not pre-selected)
document.getElementById('category_id')?.addEventListener('change', function() {
    const categoryId = this.value;
    if (!categoryId) {
        document.getElementById('serviceman-selection').classList.add('hidden');
        return;
    }
    
    // Show loading state
    const servicemanSelection = document.getElementById('serviceman-selection');
    const servicemenList = document.getElementById('servicemen-list');
    
    servicemanSelection.classList.remove('hidden');
    servicemenList.innerHTML = '<p class="text-gray-500 text-center py-4"><i class="fas fa-spinner fa-spin mr-2"></i>Loading servicemen...</p>';
    
    // Fetch servicemen for this category
    fetch(`/api/categories/${categoryId}/servicemen`)
        .then(response => response.json())
        .then(data => {
            if (data.servicemen && data.servicemen.length > 0) {
                let html = '';
                data.servicemen.forEach(serviceman => {
                    html += `
                        <div class="bg-white border-2 border-gray-200 hover:border-blue-500 rounded-xl p-4 cursor-pointer transition-all transform hover:scale-102" onclick="selectServiceman(${serviceman.id})">
                            <div class="flex items-center space-x-4">
                                <div class="w-14 h-14 bg-gradient-to-br from-blue-600 to-purple-600 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-user text-white text-xl"></i>
                                </div>
                                <div class="flex-1">
                                    <h5 class="font-bold text-gray-900">${serviceman.full_name}</h5>
                                    <p class="text-sm text-gray-600">${serviceman.experience_years || 0} years experience</p>
                                    <div class="flex items-center mt-1">
                                        <span class="text-xs text-yellow-600">★ ${serviceman.rating || 0}</span>
                                        <span class="text-xs text-gray-500 ml-2">${serviceman.total_jobs_completed || 0} jobs</span>
                                    </div>
                                </div>
                                <input type="radio" name="serviceman_id" value="${serviceman.id}" required class="h-5 w-5 text-blue-600">
                            </div>
                        </div>
                    `;
                });
                servicemenList.innerHTML = html;
            } else {
                servicemenList.innerHTML = '<p class="text-gray-500 text-center py-4">No servicemen available for this category</p>';
            }
        })
        .catch(error => {
            console.error('Error loading servicemen:', error);
            servicemenList.innerHTML = '<p class="text-red-500 text-center py-4">Error loading servicemen. Please try again.</p>';
        });
});

function selectServiceman(servicemanId) {
    document.querySelector(`input[value="${servicemanId}"]`).checked = true;
}

// Add event listeners
document.addEventListener('DOMContentLoaded', function() {
    const bookingDateField = document.getElementById('booking_date');
    const emergencyCheckbox = document.getElementById('is_emergency');
    
    if (bookingDateField) {
        bookingDateField.addEventListener('change', updateBookingFee);
    }
    
    if (emergencyCheckbox) {
        emergencyCheckbox.addEventListener('change', updateBookingFee);
    }
    
    // Initial fee calculation
    updateBookingFee();
    
    // Character count for address
    const addressField = document.getElementById('client_address');
    const addressCount = document.getElementById('address-char-count');
    if (addressField && addressCount) {
        addressField.addEventListener('input', function() {
            addressCount.textContent = this.value.length;
        });
        // Set initial count
        if (addressField.value) {
            addressCount.textContent = addressField.value.length;
        }
    }
    
    // Character count for description
    const descriptionField = document.getElementById('service_description');
    const descriptionCount = document.getElementById('description-char-count');
    if (descriptionField && descriptionCount) {
        descriptionField.addEventListener('input', function() {
            descriptionCount.textContent = this.value.length;
        });
        // Set initial count
        if (descriptionField.value) {
            descriptionCount.textContent = descriptionField.value.length;
        }
    }
});
</script>
@endsection
