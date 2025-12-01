@extends('layouts.app')

@section('title', 'Pending Servicemen Approval')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 flex items-center">
                <i class="fas fa-user-check mr-3 text-blue-600"></i>
                Pending Servicemen Approval
            </h1>
            <p class="text-gray-600 mt-2">Review and approve serviceman registrations</p>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-6 py-4 rounded-lg">
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-6 py-4 rounded-lg">
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        <!-- Pending Servicemen Grid -->
        @if($pendingServicemen->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($pendingServicemen as $serviceman)
                    <div class="bg-white rounded-2xl shadow-lg p-6 border-l-4 border-yellow-500">
                        <!-- Profile Picture & Info -->
                        <div class="flex items-center space-x-4 mb-4">
                            <img src="{{ $serviceman->profile_picture_url }}" 
                                 alt="{{ $serviceman->full_name }}" 
                                 class="w-16 h-16 rounded-full object-cover border-4 border-yellow-100 shadow-md">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900">{{ $serviceman->full_name }}</h3>
                                <p class="text-sm text-gray-600">{{ $serviceman->username }}</p>
                                <span class="inline-block mt-1 px-2 py-0.5 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-clock mr-1"></i>Pending Approval
                                </span>
                            </div>
                        </div>

                        <!-- Contact Info -->
                        <div class="space-y-2 mb-4 pb-4 border-b border-gray-200">
                            <div class="flex items-center text-sm">
                                <i class="fas fa-envelope text-blue-600 w-5 mr-2"></i>
                                <span class="text-gray-700 truncate">{{ $serviceman->email }}</span>
                            </div>
                            @if($serviceman->phone_number)
                                <div class="flex items-center text-sm">
                                    <i class="fas fa-phone text-green-600 w-5 mr-2"></i>
                                    <span class="text-gray-700">{{ $serviceman->phone_number }}</span>
                                </div>
                            @endif
                            <div class="flex items-center text-sm">
                                <i class="fas fa-calendar text-purple-600 w-5 mr-2"></i>
                                <span class="text-gray-700">Registered {{ $serviceman->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        <!-- Profile Details -->
                        @if($serviceman->servicemanProfile)
                            <div class="space-y-2 mb-4">
                                @if($serviceman->servicemanProfile->category)
                                    <div class="flex items-center text-sm">
                                        <i class="fas fa-tag text-orange-600 w-5 mr-2"></i>
                                        <span class="font-semibold text-gray-900">{{ $serviceman->servicemanProfile->category->name }}</span>
                                    </div>
                                @endif
                                @if($serviceman->servicemanProfile->experience_years)
                                    <div class="flex items-center text-sm">
                                        <i class="fas fa-briefcase text-blue-600 w-5 mr-2"></i>
                                        <span class="text-gray-700">{{ $serviceman->servicemanProfile->experience_years }} years experience</span>
                                    </div>
                                @endif
                                @if($serviceman->servicemanProfile->bio)
                                    <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                                        <p class="text-xs font-semibold text-gray-700 mb-1">Bio:</p>
                                        <p class="text-sm text-gray-600 line-clamp-3">{{ $serviceman->servicemanProfile->bio }}</p>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Email Verification Status -->
                        <div class="mb-4">
                            @if($serviceman->is_email_verified)
                                <span class="inline-flex items-center text-xs font-semibold text-green-700 bg-green-100 px-2 py-1 rounded-full">
                                    <i class="fas fa-check-circle mr-1"></i>Email Verified
                                </span>
                            @else
                                <span class="inline-flex items-center text-xs font-semibold text-red-700 bg-red-100 px-2 py-1 rounded-full">
                                    <i class="fas fa-times-circle mr-1"></i>Email Not Verified
                                </span>
                            @endif
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex space-x-2 pt-4 border-t border-gray-200">
                            <!-- Approve Button -->
                            <form method="POST" action="{{ route('admin.servicemen.approve', $serviceman) }}" class="flex-1">
                                @csrf
                                <button type="submit" 
                                        class="w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white px-4 py-2 rounded-lg font-bold transition-all shadow-md hover:shadow-lg">
                                    <i class="fas fa-check mr-2"></i>Approve
                                </button>
                            </form>

                            <!-- Reject Button -->
                            <button onclick="showRejectModal({{ $serviceman->id }}, '{{ $serviceman->full_name }}')"
                                    class="flex-1 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white px-4 py-2 rounded-lg font-bold transition-all shadow-md hover:shadow-lg">
                                <i class="fas fa-times mr-2"></i>Reject
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-16 bg-white rounded-2xl shadow-lg">
                <i class="fas fa-user-check text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No Pending Servicemen</h3>
                <p class="text-gray-600">All serviceman registrations have been reviewed.</p>
            </div>
        @endif
    </div>
</div>

<!-- Rejection Modal -->
<div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full mx-4">
        <h3 class="text-2xl font-bold text-gray-900 mb-4">
            <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
            Reject Serviceman
        </h3>
        <p class="text-gray-700 mb-6">
            Are you sure you want to reject <strong id="servicemanName"></strong>? 
            This will delete their account and send them a notification.
        </p>

        <form id="rejectForm" method="POST">
            @csrf
            <div class="mb-6">
                <label for="rejection_reason" class="block text-sm font-bold text-gray-900 mb-2">
                    Rejection Reason <span class="text-red-500">*</span>
                </label>
                <textarea id="rejection_reason" 
                          name="rejection_reason" 
                          rows="4" 
                          required
                          placeholder="Please provide a clear reason for rejection..."
                          class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-4 focus:ring-red-300 focus:border-red-500 transition-all resize-none"></textarea>
            </div>

            <div class="flex space-x-3">
                <button type="button" 
                        onclick="hideRejectModal()"
                        class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-700 px-6 py-3 rounded-xl font-bold transition-all">
                    Cancel
                </button>
                <button type="submit" 
                        class="flex-1 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-lg hover:shadow-xl">
                    <i class="fas fa-times mr-2"></i>Reject
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function showRejectModal(servicemanId, servicemanName) {
        document.getElementById('servicemanName').textContent = servicemanName;
        document.getElementById('rejectForm').action = `/admin/servicemen/${servicemanId}/reject`;
        document.getElementById('rejectModal').classList.remove('hidden');
        document.getElementById('rejectModal').classList.add('flex');
    }

    function hideRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
        document.getElementById('rejectModal').classList.remove('flex');
        document.getElementById('rejection_reason').value = '';
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideRejectModal();
        }
    });

    // Close modal when clicking outside
    document.getElementById('rejectModal').addEventListener('click', function(e) {
        if (e.target === this) {
            hideRejectModal();
        }
    });
</script>
@endpush
@endsection

