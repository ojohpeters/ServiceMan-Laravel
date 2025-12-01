@if(auth()->check())
    @php
        try {
            if (auth()->user()->isAdmin()) {
                // Admin sees all notifications (including those with user_id = null)
                $notifications = \App\Models\AppNotification::where(function($query) {
                    $query->where('user_id', auth()->id())
                          ->orWhereNull('user_id');
                })->unread()->latest()->limit(10)->get();
            } else {
                // Regular users only see their own notifications
                $notifications = \App\Models\AppNotification::where('user_id', auth()->id())
                    ->unread()
                    ->latest()
                    ->limit(10)
                    ->get();
            }
        } catch (\Exception $e) {
            $notifications = collect([]);
        }
    @endphp
    
    <!-- Notification Bell Button -->
    <div class="relative">
        <!-- Desktop: Opens panel -->
        <button onclick="handleNotificationClick()" 
                type="button"
                class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500 rounded-lg transition-colors touch-manipulation"
                aria-label="Open notifications">
            <i class="fas fa-bell text-xl pointer-events-none"></i>
            @if($notifications->count() > 0)
                <span class="notification-badge absolute -top-1 -right-1 bg-red-600 text-white text-xs rounded-full min-w-[20px] h-5 px-1.5 flex items-center justify-center font-bold shadow-lg border-2 border-white animate-pulse pointer-events-none">
                    {{ $notifications->count() }}
                </span>
            @endif
        </button>
    </div>

    <!-- Notifications Panel Overlay -->
    <div id="notifications-overlay" 
         class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 hidden transition-opacity duration-300"
         style="display: none; opacity: 0;"
         onclick="closeNotificationsPanel()">
    </div>

    <!-- Notifications Panel (Slide-in from right) -->
    <div id="notifications-panel" 
         class="fixed top-0 right-0 h-full w-full sm:w-[450px] lg:w-[500px] bg-white shadow-2xl z-[60] transform translate-x-full transition-transform duration-300 ease-in-out overflow-y-auto flex flex-col">
        
        <!-- Panel Header -->
        <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 px-6 py-5 flex-shrink-0">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-bell text-white text-lg"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">Notifications</h2>
                        @if($notifications->count() > 0)
                            <p class="text-blue-100 text-sm">{{ $notifications->count() }} unread notification{{ $notifications->count() > 1 ? 's' : '' }}</p>
                        @else
                            <p class="text-blue-100 text-sm">All caught up!</p>
                        @endif
                    </div>
                </div>
                <button onclick="closeNotificationsPanel()" 
                        class="text-white hover:bg-white hover:bg-opacity-20 p-2 rounded-lg transition-colors">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
        </div>

        @if($notifications->count() > 0)
            <!-- Notifications List -->
            <div class="flex-1 overflow-y-auto bg-gray-50">
                <div class="divide-y divide-gray-200">
                    @foreach($notifications as $notification)
                        <div class="notification-item bg-white hover:bg-gradient-to-r hover:from-blue-50 hover:to-purple-50 transition-all duration-200 border-l-4 {{ 
                            str_contains($notification->type, 'PAYMENT') ? 'border-green-500' : 
                            (str_contains($notification->type, 'RATING') ? 'border-yellow-500' : 
                            (str_contains($notification->type, 'COMPLETED') ? 'border-blue-500' : 
                            (str_contains($notification->type, 'EMERGENCY') ? 'border-red-500' : 'border-purple-500')))
                        }}">
                            <a href="{{ route('notifications.index') }}" 
                               class="block p-5 group"
                               onclick="markAsRead({{ $notification->id }})">
                                <div class="flex items-start space-x-4">
                                    <!-- Icon -->
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-md {{ 
                                            str_contains($notification->type, 'PAYMENT') ? 'bg-gradient-to-br from-green-500 to-emerald-600' : 
                                            (str_contains($notification->type, 'RATING') ? 'bg-gradient-to-br from-yellow-500 to-orange-600' : 
                                            (str_contains($notification->type, 'COMPLETED') ? 'bg-gradient-to-br from-blue-500 to-indigo-600' : 
                                            (str_contains($notification->type, 'EMERGENCY') ? 'bg-gradient-to-br from-red-500 to-pink-600' : 'bg-gradient-to-br from-purple-500 to-purple-700')))
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
                                        <div class="flex items-start justify-between mb-1">
                                            <h4 class="text-sm font-bold text-gray-900 line-clamp-1 group-hover:text-blue-600 transition-colors">
                                                {{ $notification->title }}
                                            </h4>
                                            <span class="flex-shrink-0 ml-2 w-2 h-2 bg-blue-600 rounded-full"></span>
                                        </div>
                                        <p class="text-sm text-gray-600 line-clamp-2 mb-2">{{ $notification->message }}</p>
                                        <div class="flex items-center justify-between">
                                            <p class="text-xs text-gray-500 flex items-center">
                                                <i class="fas fa-clock mr-1"></i>
                                                {{ $notification->created_at->diffForHumans() }}
                                            </p>
                                            @if($notification->service_request_id)
                                                <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded-full font-semibold">
                                                    Request #{{ $notification->service_request_id }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Panel Footer -->
            <div class="flex-shrink-0 bg-white border-t-2 border-gray-200 p-4">
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('notifications.index') }}" 
                       class="flex-1 flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold rounded-xl transition-all shadow-md hover:shadow-lg">
                        <i class="fas fa-list mr-2"></i>
                        View All Notifications
                    </a>
                    <button onclick="markAllAsRead()" 
                            class="flex items-center justify-center px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-bold rounded-xl transition-colors">
                        <i class="fas fa-check-double mr-2"></i>
                        Mark All Read
                    </button>
                </div>
            </div>
        @else
            <!-- Empty State -->
            <div class="flex-1 flex flex-col items-center justify-center p-8">
                <div class="w-24 h-24 bg-gradient-to-br from-blue-100 to-purple-100 rounded-full flex items-center justify-center mb-6">
                    <i class="fas fa-bell-slash text-5xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">No notifications</h3>
                <p class="text-gray-600 text-center mb-6">You're all caught up! New notifications will appear here.</p>
                <a href="{{ route('notifications.index') }}" 
                   class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold rounded-xl transition-all shadow-md hover:shadow-lg">
                    <i class="fas fa-history mr-2"></i>
                    View History
                </a>
            </div>
        @endif
    </div>
@endif

<style>
@keyframes slideInRight {
    from {
        transform: translateX(100%);
    }
    to {
        transform: translateX(0);
    }
}

@keyframes slideOutRight {
    from {
        transform: translateX(0);
    }
    to {
        transform: translateX(100%);
    }
}

.notification-item {
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script>
// Check if mobile device
function isMobileDevice() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || window.innerWidth < 768;
}

// Handle notification click - redirect on mobile, open panel on desktop
function handleNotificationClick() {
    if (isMobileDevice()) {
        // Redirect to notifications page on mobile
        window.location.href = '{{ route("notifications.index") }}';
    } else {
        // Open panel on desktop
        openNotificationsPanel();
    }
}

function openNotificationsPanel() {
    console.log('openNotificationsPanel called'); // Debug
    const overlay = document.getElementById('notifications-overlay');
    const panel = document.getElementById('notifications-panel');
    
    console.log('Overlay:', overlay); // Debug
    console.log('Panel:', panel); // Debug
    
    if (overlay && panel) {
        // Show overlay
        overlay.style.display = 'block';
        overlay.classList.remove('hidden');
        overlay.style.opacity = '0';
        
        // Show panel
        panel.style.display = 'flex';
        
        // Force reflow
        overlay.offsetHeight;
        panel.offsetHeight;
        
        // Animate in
        setTimeout(() => {
            console.log('Before remove translate-x-full:', panel.classList.contains('translate-x-full')); // Debug
            overlay.style.opacity = '1';
            panel.classList.remove('translate-x-full');
            console.log('After remove translate-x-full:', panel.classList.contains('translate-x-full')); // Debug
            console.log('Panel classes:', panel.className); // Debug
        }, 10);
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
        
        console.log('Panel opened'); // Debug
    } else {
        console.error('Elements not found!'); // Debug
    }
}

function closeNotificationsPanel() {
    console.log('closeNotificationsPanel called'); // Debug
    const overlay = document.getElementById('notifications-overlay');
    const panel = document.getElementById('notifications-panel');
    
    if (overlay && panel) {
        // Slide out panel
        panel.classList.add('translate-x-full');
        overlay.style.opacity = '0';
        
        // Hide overlay after animation
        setTimeout(() => {
            overlay.classList.add('hidden');
            overlay.style.display = 'none';
            panel.style.display = 'none';
        }, 300);
        
        // Restore body scroll
        document.body.style.overflow = '';
        
        console.log('Panel closed'); // Debug
    }
}

function markAsRead(notificationId) {
    // Send AJAX request to mark as read
    fetch(`/notifications/${notificationId}/mark-read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    }).then(response => {
        if (response.ok) {
            // Remove the notification from the panel
            const notificationItems = document.querySelectorAll('.notification-item');
            notificationItems.forEach(item => {
                if (item.querySelector('a').onclick && item.querySelector('a').onclick.toString().includes(notificationId)) {
                    item.style.opacity = '0';
                    setTimeout(() => item.remove(), 300);
                }
            });
            
            // Update the badge count
            updateNotificationCount();
        }
    }).catch(error => console.error('Error marking notification as read:', error));
}

function markAllAsRead() {
    // Send AJAX request to mark all as read
    fetch('/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    }).then(response => {
        if (response.ok) {
            // Reload page to update notification count
            window.location.reload();
        }
    }).catch(error => console.error('Error marking all notifications as read:', error));
}

// Close panel with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeNotificationsPanel();
    }
});

// Prevent clicks inside panel from closing it
document.getElementById('notifications-panel')?.addEventListener('click', function(event) {
    event.stopPropagation();
});

// Function to update notification count (only after marking as read)
function updateNotificationCount() {
    fetch('/api/notifications/count', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        const badge = document.querySelector('.notification-badge');
        const bellButton = document.querySelector('button[aria-label="Open notifications"]');
        
        if (data.count > 0) {
            if (badge) {
                badge.textContent = data.count;
                badge.style.display = 'flex';
            } else if (bellButton) {
                // Create badge if it doesn't exist
                const newBadge = document.createElement('span');
                newBadge.className = 'notification-badge absolute -top-1 -right-1 bg-red-600 text-white text-xs rounded-full min-w-[20px] h-5 px-1.5 flex items-center justify-center font-bold shadow-lg border-2 border-white animate-pulse pointer-events-none';
                newBadge.textContent = data.count;
                bellButton.appendChild(newBadge);
            }
        } else {
            if (badge) {
                badge.style.display = 'none';
            }
        }
    })
    .catch(error => console.error('Error updating notification count:', error));
}

// Poll for new notifications every 60 seconds (not on page load to avoid flash)
setInterval(() => {
    updateNotificationCount();
}, 60000);

// Add event listeners when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Add click event support for mobile (in case onclick doesn't work)
    const notificationButton = document.querySelector('button[aria-label="Open notifications"]');
    if (notificationButton) {
        // Add event listeners for better compatibility
        notificationButton.addEventListener('click', function(e) {
            e.stopPropagation();
            handleNotificationClick();
        });
        
        notificationButton.addEventListener('touchend', function(e) {
            e.preventDefault();
            e.stopPropagation();
            handleNotificationClick();
        }, { passive: false });
    }
});
</script>
