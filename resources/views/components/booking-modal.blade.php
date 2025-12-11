<!-- Modern Booking Modal -->
<div id="bookingModal" class="fixed inset-0 bg-black bg-opacity-60 backdrop-blur-sm hidden z-50 p-2 sm:p-4 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen py-4 sm:py-8">
        <div class="relative bg-white rounded-lg shadow-xl w-full max-w-3xl transform transition-all animate-fadeIn my-4 sm:my-8 max-h-[98vh] overflow-hidden flex flex-col">
        <!-- Header with Gradient -->
        <div class="bg-blue-600 px-6 sm:px-8 py-5 rounded-t-lg sticky top-0 z-10">
            <div class="flex justify-between items-center">
                <div class="flex-1">
                    <h3 class="text-xl sm:text-2xl font-bold text-white flex items-center">
                        <i class="fas fa-calendar-check mr-2 sm:mr-3"></i>Book Service
                    </h3>
                    <p class="text-blue-100 text-xs sm:text-sm mt-1">Complete your booking details</p>
                </div>
                <button onclick="closeBookingModal()" type="button" class="text-white hover:text-gray-200 transition-colors ml-4 flex-shrink-0">
                    <i class="fas fa-times text-xl sm:text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <form id="bookingForm" method="POST" action="{{ route('service-requests.store') }}" class="p-4 sm:p-6 md:p-8 flex-1 overflow-y-auto" onsubmit="return validateModalBookingDate()">
            @csrf
            <input type="hidden" id="modalServicemanId" name="serviceman_id" value="">
            <input type="hidden" id="modalCategoryId" name="category_id" value="">

            <!-- Selected Serviceman Card -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 sm:p-6 mb-4 sm:mb-6">
                <h4 class="font-bold text-gray-900 mb-3 sm:mb-4 flex items-center text-sm sm:text-base">
                    <i class="fas fa-user-tie mr-2 text-blue-600"></i>
                    Selected Professional
                </h4>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4">
                    <img id="modalServicemanImage" 
                         src="" 
                         alt="Serviceman" 
                         class="w-14 h-14 sm:w-16 sm:h-16 rounded-full object-cover border-4 border-blue-200 shadow-lg flex-shrink-0 mx-auto sm:mx-0">
                    <div class="flex-1 min-w-0 text-center sm:text-left w-full sm:w-auto">
                        <p class="font-bold text-base sm:text-lg text-gray-900 break-words sm:truncate" id="modalServicemanDetails">Loading...</p>
                        <p class="text-xs sm:text-sm text-gray-600 flex items-center justify-center sm:justify-start mt-1 break-words sm:truncate" id="modalServicemanCategory">
                            <i class="fas fa-tools mr-2"></i>Category
                        </p>
                        <div class="flex flex-col sm:flex-row items-center sm:items-center justify-center sm:justify-start mt-2 gap-2 flex-wrap">
                            <div class="flex items-center">
                                <div class="flex" id="modalServicemanRating">
                                    <!-- Stars will be inserted here -->
                                </div>
                                <span class="text-xs sm:text-sm text-gray-600 ml-2 whitespace-nowrap" id="modalServicemanStats">0 jobs</span>
                            </div>
                            <div id="modalAvailabilityStatus">
                                <!-- Availability badge will be inserted here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Availability Warning (shown when serviceman is busy) -->
            <div id="busyWarning" class="hidden bg-orange-50 border-l-4 border-orange-500 rounded-r-lg p-4 sm:p-5 mb-4 sm:mb-6">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-orange-600 text-xl sm:text-2xl mr-3 sm:mr-4 mt-1 flex-shrink-0"></i>
                    <div class="flex-1 min-w-0">
                        <h4 class="font-bold text-orange-900 mb-2 text-sm sm:text-base">⚠️ Serviceman Currently Busy</h4>
                        <p class="text-xs sm:text-sm text-orange-800 mb-3">
                            This serviceman is currently working on another job. Your request will be queued and processed once they become available.
                        </p>
                        <div id="alternativeServicemen" class="hidden">
                            <p class="text-sm font-bold text-gray-900 mb-2">
                                <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                                Available Alternatives in This Category:
                            </p>
                            <div id="alternativesList" class="space-y-2">
                                <!-- Alternative servicemen will be inserted here -->
                            </div>
                        </div>
                        <div id="noAlternatives" class="hidden">
                            <p class="text-sm font-bold text-gray-900 mb-2">
                                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                No Other Servicemen Available
                            </p>
                            <p class="text-sm text-gray-700">
                                Don't worry! Your request will be processed and the serviceman will be assigned to you as soon as they complete their current job. Quality work is guaranteed regardless of wait time.
                            </p>
                        </div>
                        <label class="flex items-center mt-4 cursor-pointer">
                            <input type="checkbox" id="acceptBusyServiceman" class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm font-semibold text-gray-900">
                                I understand and want to proceed with this serviceman
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Booking Fee Display -->
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 sm:p-6 mb-4 sm:mb-6">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex-1">
                        <h4 class="font-bold text-gray-900 mb-1 flex items-center text-sm sm:text-base">
                            <i class="fas fa-money-bill-wave mr-2 text-green-600"></i>
                            Booking Fee
                        </h4>
                        <p class="text-xs sm:text-sm text-gray-600" id="modalFeeType">Regular Service</p>
                    </div>
                    <div class="text-right flex-shrink-0">
                        <p class="text-2xl sm:text-4xl font-bold text-green-600" id="modalBookingFee">₦2,000</p>
                    </div>
                </div>
            </div>

            <!-- Form Fields -->
            <div class="space-y-4 sm:space-y-5">
                <!-- Service Date -->
                <div>
                    <label for="modalBookingDate" class="block text-xs sm:text-sm font-bold text-gray-900 mb-2">
                        <i class="fas fa-calendar mr-2 text-blue-600"></i>
                        Preferred Service Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" 
                           id="modalBookingDate" 
                           name="booking_date" 
                           required
                           min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                           class="w-full px-3 py-2 sm:px-4 sm:py-3 text-sm sm:text-base border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    <!-- Availability warning for modal -->
                    <div id="modalDateAvailabilityWarning" class="hidden mt-3 bg-red-50 border-l-4 border-red-500 rounded-r-lg p-3 sm:p-4">
                        <div class="flex items-start">
                            <i class="fas fa-exclamation-triangle text-red-600 text-lg sm:text-xl mr-3 mt-1"></i>
                            <div>
                                <h4 class="font-bold text-red-900 mb-1 text-sm sm:text-base">⚠️ Serviceman Unavailable</h4>
                                <p class="text-xs sm:text-sm text-red-800" id="modalAvailabilityWarningMessage">
                                    This serviceman is marked as busy on the selected date. Please choose a different date.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Emergency Checkbox -->
                <div class="bg-red-50 border border-red-200 rounded-xl p-3 sm:p-4">
                    <div class="flex items-start">
                        <input type="checkbox" 
                               id="modalIsEmergency" 
                               name="is_emergency" 
                               value="1"
                               class="h-4 w-4 sm:h-5 sm:w-5 text-red-600 focus:ring-red-500 border-gray-300 rounded mt-1 flex-shrink-0">
                        <label for="modalIsEmergency" class="ml-2 sm:ml-3">
                            <span class="block text-xs sm:text-sm font-bold text-gray-900">Emergency Service Request</span>
                            <span class="block text-xs text-red-600 mt-1">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Booking within 2 days or emergency flag adds ₦3,000
                            </span>
                        </label>
                    </div>
                </div>

                <!-- Service Address -->
                <div>
                    <label for="modalClientAddress" class="block text-xs sm:text-sm font-bold text-gray-900 mb-2">
                        <i class="fas fa-map-marker-alt mr-2 text-orange-600"></i>
                        Service Address <span class="text-red-500">*</span>
                    </label>
                    <textarea id="modalClientAddress" 
                              name="client_address" 
                              rows="3" 
                              required
                              maxlength="500"
                              placeholder="Enter complete address (max 500 characters)..."
                              class="w-full px-3 py-2 sm:px-4 sm:py-3 text-sm sm:text-base border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none"></textarea>
                    <p class="mt-1 text-xs text-gray-500">
                        <span id="modal-address-char-count">0</span>/500 characters
                    </p>
                </div>

                <!-- Service Description -->
                <div>
                    <label for="modalServiceDescription" class="block text-xs sm:text-sm font-bold text-gray-900 mb-2">
                        <i class="fas fa-file-alt mr-2 text-purple-600"></i>
                        Service Description <span class="text-red-500">*</span>
                    </label>
                    <textarea id="modalServiceDescription" 
                              name="service_description" 
                              rows="4" 
                              required
                              maxlength="1000"
                              placeholder="Describe the service you need in detail (max 1000 characters)..."
                              class="w-full px-3 py-2 sm:px-4 sm:py-3 text-sm sm:text-base border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none"></textarea>
                    <p class="mt-1 text-xs text-gray-500">
                        <span id="modal-description-char-count">0</span>/1000 characters
                    </p>
                </div>
            </div>

            <!-- Information Banner -->
            <div class="bg-blue-50 border-l-4 border-blue-500 rounded-r-xl p-3 sm:p-4 mt-4 sm:mt-6">
                <p class="text-xs sm:text-sm text-blue-900 font-semibold mb-2">
                    <i class="fas fa-info-circle mr-2"></i>What happens next?
                </p>
                <ul class="text-xs text-blue-800 space-y-1.5 ml-4 sm:ml-6">
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-600 mr-2 mt-0.5 flex-shrink-0"></i>
                        <span>You'll be redirected to secure payment (Paystack)</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-600 mr-2 mt-0.5 flex-shrink-0"></i>
                        <span>After payment, your service request will be created</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-600 mr-2 mt-0.5 flex-shrink-0"></i>
                        <span>Admin will confirm assignment & serviceman will contact you</span>
                    </li>
                </ul>
            </div>

            <!-- Modal Footer -->
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 mt-4 sm:mt-6 pt-4 sm:pt-6 border-t-2 border-gray-200 sticky bottom-0 bg-white">
                <button type="button" 
                        onclick="closeBookingModal()"
                        class="w-full sm:flex-1 px-4 py-3 sm:px-6 sm:py-4 text-sm sm:text-base bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold rounded-xl transition-all transform active:scale-95">
                    <i class="fas fa-times mr-2"></i>Cancel
                </button>
                <button type="submit"
                        id="modalSubmitButton"
                        class="w-full sm:flex-1 px-4 py-3 sm:px-6 sm:py-4 text-sm sm:text-base bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors">
                    <i class="fas fa-lock mr-2"></i>
                    <span id="modalPayText" class="whitespace-nowrap">Proceed to Payment - ₦2,000</span>
                </button>
            </div>
        </form>
        </div>
    </div>
</div>

<style>
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: scale(0.95);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.animate-fadeIn {
    animation: fadeIn 0.3s ease-out;
}
</style>

<script>
async function openBookingModal(servicemanData) {
    console.log('Opening booking modal for:', servicemanData);
    
    // Populate modal with serviceman data
    document.getElementById('modalServicemanId').value = servicemanData.id;
    document.getElementById('modalCategoryId').value = servicemanData.category_id;
    
    // Update serviceman details
    const fullName = servicemanData.full_name || servicemanData.username || 'Professional';
    document.getElementById('modalServicemanDetails').textContent = fullName;
    document.getElementById('modalServicemanCategory').innerHTML = `<i class="fas fa-tools mr-2"></i>${servicemanData.category_name || 'Service Professional'}`;
    
    // Update profile picture
    document.getElementById('modalServicemanImage').src = servicemanData.profile_picture_url || '/images/default-serviceman.jpg';
    
    // Update rating stars
    const rating = servicemanData.rating || 0;
    let starsHtml = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= Math.floor(rating)) {
            starsHtml += '<i class="fas fa-star text-yellow-400 text-sm"></i>';
        } else if (i === Math.ceil(rating) && rating % 1 !== 0) {
            starsHtml += '<i class="fas fa-star-half-alt text-yellow-400 text-sm"></i>';
        } else {
            starsHtml += '<i class="far fa-star text-gray-300 text-sm"></i>';
        }
    }
    document.getElementById('modalServicemanRating').innerHTML = starsHtml;
    document.getElementById('modalServicemanStats').textContent = `${rating.toFixed(1)} • ${servicemanData.total_jobs || servicemanData.total_jobs_completed || 0} jobs`;
    
    // Check availability status - check if busy today
    fetch(`/api/servicemen/${servicemanData.id}/check-availability?date=${new Date().toISOString().split('T')[0]}`)
        .then(response => response.json())
        .then(todayData => {
            const isAvailableToday = servicemanData.is_available !== false && !todayData.is_busy;
            
            // Update availability badge
            const availabilityBadge = isAvailableToday
                ? '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800"><i class="fas fa-check-circle mr-1"></i>Available Now</span>'
                : '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800"><i class="fas fa-clock mr-1"></i>Currently Busy</span>';
            document.getElementById('modalAvailabilityStatus').innerHTML = availabilityBadge;
            
            // If booking date is already set, check it
            const bookingDate = document.getElementById('modalBookingDate')?.value;
            if (bookingDate) {
                checkModalServicemanAvailability();
            }
        })
        .catch(error => {
            console.error('Error checking today availability:', error);
            // Fallback to original check
            const isAvailable = servicemanData.is_available !== false;
            const availabilityBadge = isAvailable
                ? '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800"><i class="fas fa-check-circle mr-1"></i>Available Now</span>'
                : '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800"><i class="fas fa-clock mr-1"></i>Currently Busy</span>';
            document.getElementById('modalAvailabilityStatus').innerHTML = availabilityBadge;
        });
    
    // Handle busy serviceman warning
    if (!isAvailable) {
        document.getElementById('busyWarning').classList.remove('hidden');
        
        // Fetch alternative servicemen
        try {
            const response = await fetch(`/api/categories/${servicemanData.category_id}/servicemen`);
            const data = await response.json();
            
            // Filter out the selected serviceman and only show available ones
            const alternatives = data.servicemen.filter(s => 
                s.id !== servicemanData.id && s.is_available
            );
            
            if (alternatives.length > 0) {
                document.getElementById('alternativeServicemen').classList.remove('hidden');
                document.getElementById('noAlternatives').classList.add('hidden');
                
                let alternativesHtml = '';
                alternatives.slice(0, 3).forEach(alt => {
                    alternativesHtml += `
                        <div class="flex items-center justify-between bg-white p-3 rounded-xl border-2 border-green-200 hover:border-green-400 transition-all cursor-pointer" onclick="switchToServiceman(${JSON.stringify(alt).replace(/"/g, '&quot;')})">
                            <div class="flex items-center space-x-3">
                                <img src="${alt.profile_picture_url || '/images/default-serviceman.jpg'}" 
                                     class="w-10 h-10 rounded-full object-cover border-2 border-green-200">
                                <div>
                                    <p class="font-semibold text-gray-900 text-sm">${alt.full_name}</p>
                                    <p class="text-xs text-gray-600">⭐ ${alt.rating || 0} • ${alt.total_jobs || 0} jobs</p>
                                </div>
                            </div>
                            <button type="button" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-lg text-xs font-bold transition-colors">
                                Select
                            </button>
                        </div>
                    `;
                });
                document.getElementById('alternativesList').innerHTML = alternativesHtml;
            } else {
                document.getElementById('alternativeServicemen').classList.add('hidden');
                document.getElementById('noAlternatives').classList.remove('hidden');
            }
        } catch (error) {
            console.error('Error fetching alternatives:', error);
            document.getElementById('alternativeServicemen').classList.add('hidden');
            document.getElementById('noAlternatives').classList.remove('hidden');
        }
        
        // Disable submit until checkbox is checked
        updateSubmitButton();
    } else {
        document.getElementById('busyWarning').classList.add('hidden');
    }
    
    // Show modal
    document.getElementById('bookingModal').classList.remove('hidden');
    
    // Initialize booking fee calculation
    updateBookingFee();
}

function switchToServiceman(servicemanData) {
    closeBookingModal();
    setTimeout(() => openBookingModal(servicemanData), 300);
}

function updateSubmitButton() {
    const submitButton = document.getElementById('modalSubmitButton');
    const busyWarning = document.getElementById('busyWarning');
    const acceptCheckbox = document.getElementById('acceptBusyServiceman');
    
    if (!busyWarning.classList.contains('hidden')) {
        // Serviceman is busy
        if (acceptCheckbox.checked) {
            submitButton.disabled = false;
            submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
        } else {
            submitButton.disabled = true;
            submitButton.classList.add('opacity-50', 'cursor-not-allowed');
        }
    } else {
        // Serviceman is available
        submitButton.disabled = false;
        submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
    }
}

function closeBookingModal() {
    document.getElementById('bookingModal').classList.add('hidden');
    document.getElementById('bookingForm').reset();
}

let isModalServicemanBusyOnSelectedDate = false;

function checkModalServicemanAvailability() {
    const servicemanId = document.getElementById('modalServicemanId')?.value;
    const bookingDate = document.getElementById('modalBookingDate')?.value;
    const warningDiv = document.getElementById('modalDateAvailabilityWarning');
    const warningMessage = document.getElementById('modalAvailabilityWarningMessage');
    const form = document.getElementById('bookingForm');
    const submitButton = form?.querySelector('button[type="submit"]');
    
    if (!servicemanId || !bookingDate) {
        if (warningDiv) warningDiv.classList.add('hidden');
        isModalServicemanBusyOnSelectedDate = false;
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
        }
        return;
    }
    
    // Call API to check availability
    fetch(`/api/servicemen/${servicemanId}/check-availability?date=${bookingDate}`)
        .then(response => response.json())
        .then(data => {
            if (data.is_busy || !data.is_available) {
                isModalServicemanBusyOnSelectedDate = true;
                if (warningDiv) {
                    warningDiv.classList.remove('hidden');
                    if (warningMessage) {
                        warningMessage.textContent = 
                            `⚠️ WARNING: This serviceman is marked as BUSY/UNAVAILABLE on ${data.date_formatted}. Please select a different date.`;
                    }
                }
                // Disable form submission
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.classList.add('opacity-50', 'cursor-not-allowed');
                }
            } else {
                isModalServicemanBusyOnSelectedDate = false;
                if (warningDiv) warningDiv.classList.add('hidden');
                if (submitButton) {
                    submitButton.disabled = false;
                    submitButton.classList.remove('opacity-50', 'cursor-not-allowed');
                }
            }
        })
        .catch(error => {
            console.error('Error checking availability:', error);
        });
}

function updateBookingFee() {
    const bookingDate = document.getElementById('modalBookingDate').value;
    const isEmergency = document.getElementById('modalIsEmergency').checked;
    
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
            document.getElementById('modalIsEmergency').checked = true;
            document.getElementById('modalIsEmergency').disabled = true;
            isAutoEmergency = true;
        } else {
            document.getElementById('modalIsEmergency').disabled = false;
        }
    }
    
    // Add emergency fee if checked manually (and not auto-emergency)
    if (isEmergency && !isAutoEmergency) {
        baseFee = 5000;
    }
    
    // Update UI
    document.getElementById('modalBookingFee').textContent = `₦${baseFee.toLocaleString()}`;
    document.getElementById('modalPayText').textContent = `Proceed to Payment - ₦${baseFee.toLocaleString()}`;
    
    // Update fee type display
    const feeTypeElement = document.getElementById('modalFeeType');
    if (baseFee >= 5000) {
        feeTypeElement.textContent = isAutoEmergency ? 'Emergency (Within 2 days)' : 'Emergency Service';
        feeTypeElement.className = 'text-sm font-semibold text-red-600';
    } else {
        feeTypeElement.textContent = 'Regular Service';
        feeTypeElement.className = 'text-sm font-semibold text-green-600';
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners for fee calculation
    const bookingDateField = document.getElementById('modalBookingDate');
    const emergencyCheckbox = document.getElementById('modalIsEmergency');
    const acceptBusyCheckbox = document.getElementById('acceptBusyServiceman');
    
    if (bookingDateField) {
        bookingDateField.addEventListener('change', function() {
            updateBookingFee();
            checkModalServicemanAvailability();
        });
    }
    
    if (emergencyCheckbox) {
        emergencyCheckbox.addEventListener('change', updateBookingFee);
    }
    
    if (acceptBusyCheckbox) {
        acceptBusyCheckbox.addEventListener('change', updateSubmitButton);
    }
    
    // Close modal when clicking outside
    const modal = document.getElementById('bookingModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeBookingModal();
            }
        });
    }
    
    // Character count for address
    const modalAddressField = document.getElementById('modalClientAddress');
    const modalAddressCount = document.getElementById('modal-address-char-count');
    if (modalAddressField && modalAddressCount) {
        modalAddressField.addEventListener('input', function() {
            modalAddressCount.textContent = this.value.length;
        });
    }
    
    // Character count for description
    const modalDescriptionField = document.getElementById('modalServiceDescription');
    const modalDescriptionCount = document.getElementById('modal-description-char-count');
    if (modalDescriptionField && modalDescriptionCount) {
        modalDescriptionField.addEventListener('input', function() {
            modalDescriptionCount.textContent = this.value.length;
        });
    }
});

function validateModalBookingDate() {
    if (isModalServicemanBusyOnSelectedDate) {
        const bookingDate = document.getElementById('modalBookingDate')?.value;
        if (bookingDate) {
            const date = new Date(bookingDate);
            const formattedDate = date.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            alert(`Cannot proceed: This serviceman is unavailable on ${formattedDate}. Please choose a different date.`);
        }
        return false;
    }
    return true;
}
</script>
