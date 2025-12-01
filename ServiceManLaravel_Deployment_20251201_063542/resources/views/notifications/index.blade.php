@extends('layouts.app')

@section('title', 'Notifications - ServiceMan')

@section('content')
<div class="min-h-screen bg-gray-50 py-6 sm:py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 sm:mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Notifications</h1>
                    <p class="text-sm sm:text-base text-gray-600 mt-1">Stay updated with your service requests and account activity</p>
                </div>
                @if($notifications->count() > 0)
                    <div class="flex items-center gap-3">
                        <button onclick="markAllAsRead()" 
                                class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-colors text-sm sm:text-base">
                            <i class="fas fa-check-double mr-2"></i>
                            Mark All Read
                        </button>
                        <span class="text-sm text-gray-500 whitespace-nowrap">{{ $notifications->total() }} total</span>
                    </div>
                @endif
            </div>
        </div>

        @if($notifications->count() > 0)
            <!-- Notifications List -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="divide-y divide-gray-200">
                    @foreach($notifications as $notification)
                        <div class="notification-item p-4 sm:p-6 hover:bg-gray-50 transition-colors {{ !$notification->is_read ? 'bg-blue-50 border-l-4 border-blue-500' : '' }}"
                             data-notification-id="{{ $notification->id }}">
                            <div class="flex items-start space-x-4">
                                <!-- Icon -->
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-md {{ 
                                        str_contains($notification->type, 'PAYMENT') ? 'bg-gradient-to-br from-green-500 to-emerald-600' : 
                                        (str_contains($notification->type, 'RATING') ? 'bg-gradient-to-br from-yellow-500 to-orange-600' : 
                                        (str_contains($notification->type, 'COMPLETED') ? 'bg-gradient-to-br from-blue-500 to-indigo-600' : 
                                        (str_contains($notification->type, 'EMERGENCY') ? 'bg-gradient-to-br from-red-500 to-pink-600' : 
                                        (str_contains($notification->type, 'ASSIGNMENT') ? 'bg-gradient-to-br from-purple-500 to-purple-700' : 'bg-gradient-to-br from-gray-500 to-gray-700'))))
                                    }}">
                                        @if(str_contains($notification->type, 'PAYMENT'))
                                            <i class="fas fa-dollar-sign text-white text-lg"></i>
                                        @elseif(str_contains($notification->type, 'RATING'))
                                            <i class="fas fa-star text-white text-lg"></i>
                                        @elseif(str_contains($notification->type, 'COMPLETED'))
                                            <i class="fas fa-check-circle text-white text-lg"></i>
                                        @elseif(str_contains($notification->type, 'EMERGENCY'))
                                            <i class="fas fa-exclamation-triangle text-white text-lg"></i>
                                        @elseif(str_contains($notification->type, 'ASSIGNMENT'))
                                            <i class="fas fa-user-plus text-white text-lg"></i>
                                        @else
                                            <i class="fas fa-bell text-white text-lg"></i>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2 mb-1">
                                                <h3 class="text-base sm:text-lg font-bold text-gray-900">{{ $notification->title }}</h3>
                                                @if(!$notification->is_read)
                                                    <span class="flex-shrink-0 w-2 h-2 bg-blue-600 rounded-full"></span>
                                                @endif
                                            </div>
                                            <p class="text-sm sm:text-base text-gray-600 mb-2">{{ $notification->message }}</p>
                                            <div class="flex flex-wrap items-center gap-2 sm:gap-4 mt-3">
                                                <span class="text-xs sm:text-sm text-gray-500 flex items-center">
                                                    <i class="fas fa-clock mr-1"></i>
                                                    {{ $notification->created_at->diffForHumans() }}
                                                </span>
                                                @if($notification->serviceRequest)
                                                    <a href="{{ route('service-requests.show', $notification->serviceRequest) }}" 
                                                       class="text-xs sm:text-sm text-blue-600 hover:text-blue-800 font-semibold flex items-center">
                                                        <i class="fas fa-external-link-alt mr-1"></i>
                                                        View Request #{{ $notification->serviceRequest->id }}
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                        @if(!$notification->is_read)
                                            <button onclick="markAsRead({{ $notification->id }})" 
                                                    class="flex-shrink-0 text-xs sm:text-sm text-blue-600 hover:text-blue-800 font-semibold whitespace-nowrap">
                                                Mark as read
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            
            <!-- Pagination -->
            @if($notifications->hasPages())
                <div class="mt-6 flex justify-center">
                    {{ $notifications->links() }}
                </div>
            @endif
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="text-center py-12 sm:py-16 px-4">
                    <div class="w-24 h-24 bg-gradient-to-br from-blue-100 to-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-bell-slash text-5xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">No notifications</h3>
                    <p class="text-gray-600 mb-6 max-w-md mx-auto">You're all caught up! New notifications will appear here when you have updates on your service requests or account activity.</p>
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-xl transition-all shadow-md hover:shadow-lg">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<script>
function markAsRead(notificationId) {
    fetch(`/notifications/${notificationId}/mark-read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    }).then(response => {
        if (response.ok) {
            const notificationItem = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notificationItem) {
                notificationItem.classList.remove('bg-blue-50', 'border-l-4', 'border-blue-500');
                const unreadDot = notificationItem.querySelector('.bg-blue-600');
                if (unreadDot) {
                    unreadDot.remove();
                }
                const markReadButton = notificationItem.querySelector('button[onclick*="markAsRead"]');
                if (markReadButton) {
                    markReadButton.remove();
                }
            }
            // Update notification count if badge exists
            if (typeof updateNotificationCount === 'function') {
                updateNotificationCount();
            }
        }
    }).catch(error => {
        console.error('Error marking notification as read:', error);
        alert('Failed to mark notification as read. Please try again.');
    });
}

function markAllAsRead() {
    if (!confirm('Mark all notifications as read?')) {
        return;
    }
    
    fetch('/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    }).then(response => {
        if (response.ok) {
            window.location.reload();
        }
    }).catch(error => {
        console.error('Error marking all notifications as read:', error);
        alert('Failed to mark all notifications as read. Please try again.');
    });
}
</script>
@endsection
