@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Category Requests</h1>
            <p class="text-gray-600 mt-2">Review and approve serviceman category requests</p>
        </div>
    </div>

    <!-- Category Requests Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Pending Category Requests</h2>
        </div>
        
        @if($categoryRequests->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Serviceman</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category Request</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Requested</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($categoryRequests as $request)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                            <i class="fas fa-user text-white text-sm"></i>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $request->serviceman->full_name ?? 'Unknown' }}</div>
                                            <div class="text-sm text-gray-500">{{ $request->serviceman->email ?? '' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $request->category_name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900 max-w-xs truncate">
                                        {{ $request->description ?? 'No description provided' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $statusColors = [
                                            'PENDING' => 'bg-yellow-100 text-yellow-800',
                                            'APPROVED' => 'bg-green-100 text-green-800',
                                            'REJECTED' => 'bg-red-100 text-red-800',
                                        ];
                                        $statusColor = $statusColors[$request->status] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                        {{ $request->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $request->created_at->format('M d, Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    @if($request->status === 'PENDING')
                                        <div class="flex space-x-2">
                                            <button onclick="openApproveModal({{ $request->id }}, '{{ $request->category_name }}', '{{ $request->serviceman->full_name ?? 'Unknown' }}')" 
                                                    class="text-green-600 hover:text-green-900">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button onclick="openRejectModal({{ $request->id }}, '{{ $request->category_name }}', '{{ $request->serviceman->full_name ?? 'Unknown' }}')" 
                                                    class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    @else
                                        <div class="text-sm text-gray-500">
                                            Processed by: {{ $request->processedBy->full_name ?? 'Admin' }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $categoryRequests->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-clipboard-list text-gray-400 text-6xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No category requests found</h3>
                <p class="text-gray-500 mb-6">Category requests will appear here when servicemen request new categories.</p>
            </div>
        @endif
    </div>
</div>

<!-- Approve Category Request Modal -->
<div id="approveModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Approve Category Request</h3>
            <button onclick="closeApproveModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="approveForm" method="POST" class="mt-4">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Request Details</label>
                    <div class="bg-gray-50 p-3 rounded-md">
                        <p class="text-sm"><strong>Serviceman:</strong> <span id="modalServicemanName"></span></p>
                        <p class="text-sm"><strong>Category:</strong> <span id="modalCategoryName"></span></p>
                    </div>
                </div>
                
                <div>
                    <label for="approveNotes" class="block text-sm font-medium text-gray-700 mb-2">Admin Notes (Optional)</label>
                    <textarea id="approveNotes" name="admin_notes" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Add any notes about the approval..."></textarea>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                <button type="button" onclick="closeApproveModal()"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Cancel
                </button>
                <button type="submit" name="action" value="approve"
                        class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500">
                    Approve Category
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Reject Category Request Modal -->
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Reject Category Request</h3>
            <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <form id="rejectForm" method="POST" class="mt-4">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Request Details</label>
                    <div class="bg-gray-50 p-3 rounded-md">
                        <p class="text-sm"><strong>Serviceman:</strong> <span id="rejectServicemanName"></span></p>
                        <p class="text-sm"><strong>Category:</strong> <span id="rejectCategoryName"></span></p>
                    </div>
                </div>
                
                <div>
                    <label for="rejectNotes" class="block text-sm font-medium text-gray-700 mb-2">Rejection Reason *</label>
                    <textarea id="rejectNotes" name="admin_notes" rows="3" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Please provide a reason for rejecting this category request..."></textarea>
                </div>
            </div>
            
            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t">
                <button type="button" onclick="closeRejectModal()"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Cancel
                </button>
                <button type="submit" name="action" value="reject"
                        class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                    Reject Request
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentRequestId = null;

function openApproveModal(requestId, categoryName, servicemanName) {
    currentRequestId = requestId;
    
    document.getElementById('modalServicemanName').textContent = servicemanName;
    document.getElementById('modalCategoryName').textContent = categoryName;
    document.getElementById('approveForm').action = `/admin/category-requests/${requestId}/handle`;
    
    document.getElementById('approveModal').classList.remove('hidden');
}

function closeApproveModal() {
    document.getElementById('approveModal').classList.add('hidden');
    document.getElementById('approveForm').reset();
}

function openRejectModal(requestId, categoryName, servicemanName) {
    currentRequestId = requestId;
    
    document.getElementById('rejectServicemanName').textContent = servicemanName;
    document.getElementById('rejectCategoryName').textContent = categoryName;
    document.getElementById('rejectForm').action = `/admin/category-requests/${requestId}/handle`;
    
    document.getElementById('rejectModal').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('rejectForm').reset();
}

// Close modals when clicking outside
document.getElementById('approveModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeApproveModal();
    }
});

document.getElementById('rejectModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});
</script>
@endsection
