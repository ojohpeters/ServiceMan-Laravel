@extends('layouts.app')

@section('title', 'Custom Service Requests')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                        Custom Service Requests
                    </h1>
                    <p class="text-gray-600 mt-2">Review and manage serviceman requests for new service categories</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <div class="flex items-center space-x-2 text-sm">
                        <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full font-medium">
                            {{ $customRequests->total() }} Total
                        </span>
                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full font-medium">
                            {{ $customRequests->where('status', 'PENDING')->count() }} Pending
                        </span>
                    </div>
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

        <!-- Custom Service Requests Grid -->
        @if($customRequests->count() > 0)
            <div class="grid grid-cols-1 gap-6 mb-6">
                @foreach($customRequests as $customRequest)
                    @php
                        $statusColors = [
                            'PENDING' => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-500', 'text' => 'text-yellow-800', 'badge' => 'bg-yellow-100'],
                            'APPROVED' => ['bg' => 'bg-green-50', 'border' => 'border-green-500', 'text' => 'text-green-800', 'badge' => 'bg-green-100'],
                            'REJECTED' => ['bg' => 'bg-red-50', 'border' => 'border-red-500', 'text' => 'text-red-800', 'badge' => 'bg-red-100'],
                        ];
                        $colors = $statusColors[$customRequest->status] ?? $statusColors['PENDING'];
                    @endphp
                    
                    <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-shadow overflow-hidden border-l-4 {{ $colors['border'] }}">
                        <div class="p-6">
                            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                                <!-- Request Info -->
                                <div class="flex-1">
                                    <!-- Header -->
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex items-start space-x-4">
                                            <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center shadow-md">
                                                <i class="fas fa-lightbulb text-white text-2xl"></i>
                                            </div>
                                            <div>
                                                <h3 class="text-xl font-bold text-gray-900">{{ $customRequest->service_name }}</h3>
                                                <p class="text-sm text-gray-600">
                                                    Requested by {{ $customRequest->serviceman->full_name }}
                                                </p>
                                                <p class="text-xs text-gray-500 mt-1">
                                                    {{ $customRequest->created_at->format('M d, Y \a\t h:i A') }}
                                                </p>
                                            </div>
                                        </div>
                                        <span class="px-4 py-2 rounded-full text-sm font-bold {{ $colors['badge'] }} {{ $colors['text'] }} shadow-sm">
                                            {{ $customRequest->status }}
                                        </span>
                                    </div>

                                    <!-- Serviceman Info -->
                                    <div class="bg-blue-50 rounded-xl p-4 mb-4">
                                        <p class="text-xs font-semibold text-gray-500 mb-2">
                                            <i class="fas fa-user mr-1"></i>Serviceman Details
                                        </p>
                                        <div class="grid grid-cols-2 gap-3 text-sm">
                                            <div>
                                                <span class="text-gray-600">Email:</span>
                                                <span class="font-medium text-gray-900 ml-1">{{ $customRequest->serviceman->email }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-600">Phone:</span>
                                                <span class="font-medium text-gray-900 ml-1">{{ $customRequest->serviceman->servicemanProfile->phone_number ?? 'N/A' }}</span>
                                            </div>
                                            @if($customRequest->serviceman->servicemanProfile)
                                                <div>
                                                    <span class="text-gray-600">Experience:</span>
                                                    <span class="font-medium text-gray-900 ml-1">{{ $customRequest->serviceman->servicemanProfile->experience_years ?? 0 }} years</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-600">Current Rating:</span>
                                                    <span class="font-medium text-gray-900 ml-1">{{ $customRequest->serviceman->servicemanProfile->rating ?? 0 }} ⭐</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <div class="bg-gray-50 rounded-xl p-4 mb-4">
                                        <p class="text-xs font-semibold text-gray-500 mb-2">
                                            <i class="fas fa-align-left mr-1"></i>Service Description
                                        </p>
                                        <p class="text-sm text-gray-700">{{ $customRequest->service_description }}</p>
                                    </div>

                                    <!-- Additional Info -->
                                    @if($customRequest->why_needed || $customRequest->target_market)
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            @if($customRequest->why_needed)
                                                <div class="bg-orange-50 rounded-xl p-4">
                                                    <p class="text-xs font-semibold text-gray-500 mb-2">
                                                        <i class="fas fa-question-circle mr-1"></i>Why Needed
                                                    </p>
                                                    <p class="text-sm text-gray-700">{{ $customRequest->why_needed }}</p>
                                                </div>
                                            @endif

                                            @if($customRequest->target_market)
                                                <div class="bg-purple-50 rounded-xl p-4">
                                                    <p class="text-xs font-semibold text-gray-500 mb-2">
                                                        <i class="fas fa-users mr-1"></i>Target Market
                                                    </p>
                                                    <p class="text-sm text-gray-700">{{ $customRequest->target_market }}</p>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    <!-- Admin Response (if processed) -->
                                    @if($customRequest->admin_response && $customRequest->status !== 'PENDING')
                                        <div class="border-t border-gray-200 pt-4 mt-4">
                                            <div class="{{ $customRequest->status === 'APPROVED' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }} border rounded-xl p-4">
                                                <p class="text-xs font-semibold text-gray-500 mb-2">Admin Response</p>
                                                <p class="text-sm text-gray-800">{{ $customRequest->admin_response }}</p>
                                                @if($customRequest->reviewer)
                                                    <p class="text-xs text-gray-500 mt-2">
                                                        By {{ $customRequest->reviewer->full_name }} • 
                                                        {{ $customRequest->approved_at ? $customRequest->approved_at->format('M d, Y') : $customRequest->rejected_at->format('M d, Y') }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Action Section (Only for Pending) -->
                                @if($customRequest->status === 'PENDING')
                                    <div class="flex-shrink-0 lg:min-w-[320px]">
                                        <div class="bg-gradient-to-br from-purple-50 to-pink-50 border-2 border-purple-200 rounded-2xl p-6">
                                            <h4 class="font-bold text-gray-900 mb-4 flex items-center">
                                                <i class="fas fa-tasks mr-2 text-purple-600"></i>
                                                Review & Respond
                                            </h4>
                                            
                                            <form action="{{ route('admin.custom-service-requests.handle', $customRequest) }}" method="POST" id="customForm{{ $customRequest->id }}">
                                                @csrf
                                                <div class="space-y-4">
                                                    <!-- Action Type (Hidden, controlled by buttons) -->
                                                    <input type="hidden" name="action" id="action{{ $customRequest->id }}" value="">

                                                    <!-- Category Selection (for approval) -->
                                                    <div id="categorySection{{ $customRequest->id }}">
                                                        <label for="category_{{ $customRequest->id }}" class="block text-sm font-bold text-gray-700 mb-2">
                                                            Assign to Category
                                                        </label>
                                                        <select id="category_{{ $customRequest->id }}" 
                                                                name="category_id"
                                                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                                                            <option value="">Create new or select existing...</option>
                                                            @foreach($categories as $category)
                                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <p class="mt-1 text-xs text-gray-500">
                                                            Select existing category or create new one
                                                        </p>
                                                    </div>

                                                    <!-- Admin Response -->
                                                    <div>
                                                        <label for="response{{ $customRequest->id }}" class="block text-sm font-bold text-gray-700 mb-2">
                                                            Your Response <span class="text-red-500">*</span>
                                                        </label>
                                                        <textarea id="response{{ $customRequest->id }}" 
                                                                  name="admin_response" 
                                                                  rows="4"
                                                                  required
                                                                  placeholder="Provide detailed feedback..."
                                                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all resize-none"></textarea>
                                                        <p class="mt-1 text-xs text-gray-500">
                                                            <i class="fas fa-info-circle mr-1"></i>
                                                            Serviceman will receive this message
                                                        </p>
                                                    </div>

                                                    <!-- Action Buttons -->
                                                    <div class="grid grid-cols-2 gap-3 pt-4">
                                                        <button type="button" 
                                                                onclick="submitCustomForm({{ $customRequest->id }}, 'reject')"
                                                                class="flex items-center justify-center px-4 py-3 bg-red-600 hover:bg-red-700 text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-xl">
                                                            <i class="fas fa-times-circle mr-2"></i>Reject
                                                        </button>
                                                        <button type="button" 
                                                                onclick="submitCustomForm({{ $customRequest->id }}, 'approve')"
                                                                class="flex items-center justify-center px-4 py-3 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold rounded-xl transition-all shadow-lg hover:shadow-xl">
                                                            <i class="fas fa-check-circle mr-2"></i>Approve
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>

                                            <div class="mt-4 pt-4 border-t border-purple-200">
                                                <p class="text-xs text-gray-600">
                                                    <i class="fas fa-info-circle mr-1 text-purple-600"></i>
                                                    Serviceman will receive a notification with your decision and message.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $customRequests->links() }}
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-lg p-12 text-center">
                <div class="w-20 h-20 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-clipboard-check text-purple-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No Custom Service Requests</h3>
                <p class="text-gray-500 mb-6">No servicemen have requested custom services yet.</p>
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

<script>
function submitCustomForm(requestId, action) {
    const form = document.getElementById('customForm' + requestId);
    const actionInput = document.getElementById('action' + requestId);
    const responseField = document.getElementById('response' + requestId);
    const categoryField = document.getElementById('category_' + requestId);
    
    // Validate response
    if (!responseField.value.trim()) {
        alert('Please provide a response message');
        responseField.focus();
        return;
    }
    
    // For approval, validate category selection
    if (action === 'approve') {
        if (!categoryField.value) {
            alert('Please select a category for this service');
            categoryField.focus();
            return;
        }
    }
    
    actionInput.value = action;
    form.submit();
}
</script>
@endsection

