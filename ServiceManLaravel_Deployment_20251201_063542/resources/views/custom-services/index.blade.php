@extends('layouts.app')

@section('title', 'My Custom Service Requests')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-3xl font-bold bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                        My Custom Service Requests
                    </h1>
                    <p class="text-gray-600 mt-2">Track your custom service category requests</p>
                </div>
                <div class="mt-4 md:mt-0">
                    <a href="{{ route('custom-services.create') }}" 
                       class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-bold rounded-xl shadow-lg hover:shadow-xl transition-all transform hover:scale-105">
                        <i class="fas fa-plus mr-2"></i>Request New Service
                    </a>
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

        <!-- Custom Service Requests -->
        @if($customRequests->count() > 0)
            <div class="grid grid-cols-1 gap-6 mb-6">
                @foreach($customRequests as $request)
                    @php
                        $statusColors = [
                            'PENDING' => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-500', 'text' => 'text-yellow-800', 'badge' => 'bg-yellow-100'],
                            'APPROVED' => ['bg' => 'bg-green-50', 'border' => 'border-green-500', 'text' => 'text-green-800', 'badge' => 'bg-green-100'],
                            'REJECTED' => ['bg' => 'bg-red-50', 'border' => 'border-red-500', 'text' => 'text-red-800', 'badge' => 'bg-red-100'],
                        ];
                        $colors = $statusColors[$request->status] ?? $statusColors['PENDING'];
                    @endphp
                    
                    <div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-shadow overflow-hidden border-l-4 {{ $colors['border'] }}">
                        <div class="p-6">
                            <!-- Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3 mb-2">
                                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-500 rounded-xl flex items-center justify-center shadow-md">
                                            <i class="fas fa-lightbulb text-white text-xl"></i>
                                        </div>
                                        <div>
                                            <h3 class="text-xl font-bold text-gray-900">{{ $request->service_name }}</h3>
                                            <p class="text-xs text-gray-500">Submitted {{ $request->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                </div>
                                <span class="px-4 py-2 rounded-full text-sm font-bold {{ $colors['badge'] }} {{ $colors['text'] }} shadow-sm">
                                    {{ $request->status }}
                                </span>
                            </div>

                            <!-- Description -->
                            <div class="bg-gray-50 rounded-xl p-4 mb-4">
                                <p class="text-xs font-semibold text-gray-500 mb-2">
                                    <i class="fas fa-file-alt mr-1"></i>Service Description
                                </p>
                                <p class="text-sm text-gray-700">{{ $request->service_description }}</p>
                            </div>

                            <!-- Optional Fields -->
                            @if($request->why_needed || $request->target_market)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    @if($request->why_needed)
                                        <div class="bg-blue-50 rounded-xl p-4">
                                            <p class="text-xs font-semibold text-gray-500 mb-2">
                                                <i class="fas fa-question-circle mr-1"></i>Why Needed
                                            </p>
                                            <p class="text-sm text-gray-700">{{ $request->why_needed }}</p>
                                        </div>
                                    @endif

                                    @if($request->target_market)
                                        <div class="bg-purple-50 rounded-xl p-4">
                                            <p class="text-xs font-semibold text-gray-500 mb-2">
                                                <i class="fas fa-users mr-1"></i>Target Market
                                            </p>
                                            <p class="text-sm text-gray-700">{{ $request->target_market }}</p>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            <!-- Admin Response -->
                            @if($request->admin_response)
                                <div class="border-t border-gray-200 pt-4 mt-4">
                                    @if($request->status === 'APPROVED')
                                        <div class="bg-green-50 border-l-4 border-green-500 rounded-r-xl p-4">
                                            <p class="text-sm font-bold text-green-900 mb-2">
                                                <i class="fas fa-check-circle mr-2"></i>✅ Approved & Added as "{{ $request->category->name }}"
                                            </p>
                                            <p class="text-sm text-green-800">{{ $request->admin_response }}</p>
                                            <p class="text-xs text-green-700 mt-3">
                                                <i class="fas fa-lightbulb mr-1"></i>
                                                You can now apply for this category on your profile!
                                            </p>
                                            <a href="{{ route('profile.serviceman') }}" 
                                               class="inline-flex items-center mt-3 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                                                <i class="fas fa-user-edit mr-2"></i>Update Profile
                                            </a>
                                        </div>
                                    @elseif($request->status === 'REJECTED')
                                        <div class="bg-red-50 border-l-4 border-red-500 rounded-r-xl p-4">
                                            <p class="text-sm font-bold text-red-900 mb-2">
                                                <i class="fas fa-times-circle mr-2"></i>❌ Not Approved
                                            </p>
                                            <p class="text-sm text-red-800"><strong>Reason:</strong> {{ $request->admin_response }}</p>
                                            <p class="text-xs text-red-700 mt-3">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                You can submit a new request with more details or contact support.
                                            </p>
                                        </div>
                                    @endif
                                    @if($request->reviewer)
                                        <p class="text-xs text-gray-500 mt-2">
                                            Reviewed by {{ $request->reviewer->full_name }} on {{ $request->approved_at ? $request->approved_at->format('M d, Y') : $request->rejected_at->format('M d, Y') }}
                                        </p>
                                    @endif
                                </div>
                            @else
                                <!-- Pending Status -->
                                @if($request->status === 'PENDING')
                                    <div class="bg-yellow-50 border-l-4 border-yellow-500 rounded-r-xl p-4">
                                        <p class="text-sm font-semibold text-yellow-900 flex items-center">
                                            <i class="fas fa-clock mr-2"></i>
                                            Pending Admin Review
                                        </p>
                                        <p class="text-xs text-yellow-700 mt-1">
                                            Your request is being reviewed. You'll receive a notification when admin responds.
                                        </p>
                                    </div>
                                @endif
                            @endif
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
                    <i class="fas fa-lightbulb text-purple-600 text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No Custom Service Requests</h3>
                <p class="text-gray-500 mb-6">You haven't requested any custom services yet.</p>
                <a href="{{ route('custom-services.create') }}" 
                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-bold rounded-xl shadow-lg transition-all">
                    <i class="fas fa-plus mr-2"></i>Request Custom Service
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

