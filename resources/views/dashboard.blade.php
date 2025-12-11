@extends('layouts.app')

@section('title', 'Dashboard - ServiceMan')
@section('description', 'Manage your ServiceMan account, bookings, and services from your personalized dashboard.')

@section('content')
<div x-data="dashboard()" x-init="init()" class="min-h-screen bg-gray-50">
    <!-- Dashboard Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div class="flex-1 min-w-0">
                    <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 truncate">Welcome back, <span x-text="user?.first_name || user?.username || 'User'"></span>!</h1>
                    <p class="text-sm sm:text-base text-gray-600 mt-1" x-text="getWelcomeMessage()"></p>
                </div>
                <div class="flex items-center space-x-2 sm:space-x-4 flex-shrink-0">
                    <!-- Notification Bell -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="relative p-2 text-gray-600 hover:text-gray-900 transition-colors">
                            <i class="fas fa-bell text-xl"></i>
                            <span x-show="unreadNotifications > 0" 
                                  x-text="unreadNotifications" 
                                  class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center"></span>
                        </button>
                        
                        <!-- Notifications Dropdown -->
                        <div x-show="open" @click.away="open = false" x-transition 
                             class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                            <div class="p-4 border-b border-gray-200">
                                <h3 class="font-semibold text-gray-900">Notifications</h3>
                            </div>
                            <div class="max-h-64 overflow-y-auto">
                                <template x-for="notification in notifications.slice(0, 5)" :key="notification.id">
                                    <div class="p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer"
                                         @click="markAsRead(notification.id)">
                                        <div class="flex items-start">
                                            <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"
                                                 x-show="!notification.is_read"></div>
                                            <div class="flex-1">
                                                <p class="text-sm font-medium text-gray-900" x-text="notification.title"></p>
                                                <p class="text-sm text-gray-600 mt-1" x-text="notification.message"></p>
                                                <p class="text-xs text-gray-500 mt-2" x-text="formatDateTime(notification.created_at)"></p>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="notifications.length === 0" class="p-4 text-center text-gray-500">
                                    No notifications yet
                                </div>
                            </div>
                            <div class="p-4 border-t border-gray-200">
                                <a href="#" class="text-blue-600 text-sm font-medium">View all notifications</a>
                            </div>
                        </div>
                    </div>

                    <!-- User Avatar -->
                    <div class="flex items-center space-x-2 sm:space-x-3">
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-blue-600 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-white font-semibold text-xs sm:text-sm" x-text="getUserInitials()"></span>
                        </div>
                        <div class="hidden md:block">
                            <p class="text-sm font-medium text-gray-900" x-text="user?.first_name + ' ' + user?.last_name"></p>
                            <p class="text-xs text-gray-500 capitalize" x-text="user?.user_type?.toLowerCase()"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- Dashboard Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Loading State -->
        <div x-show="loading" class="text-center py-12">
            <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
            <p class="text-gray-600">Loading your dashboard...</p>
        </div>

        <!-- Dashboard Content -->
        <div x-show="!loading">
            <!-- Quick Actions -->
            <div class="mb-6 sm:mb-8">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-900 mb-3 sm:mb-4">Quick Actions</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4">
                    <template x-if="user?.user_type === 'CLIENT'">
                        <div>
                            <a href="#" @click="createServiceRequest()" 
                               class="block p-4 sm:p-6 bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-lg flex items-center justify-center mr-3 sm:mr-4 flex-shrink-0">
                                        <i class="fas fa-plus text-blue-600 text-lg sm:text-xl"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="font-semibold text-gray-900 text-sm sm:text-base">Book a Service</h3>
                                        <p class="text-xs sm:text-sm text-gray-600 truncate">Request professional services</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </template>

                    <template x-if="user?.user_type === 'SERVICEMAN'">
                        <div>
                            <a href="{{ route('availability.index') }}" 
                               class="block p-4 sm:p-6 bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-green-100 rounded-lg flex items-center justify-center mr-3 sm:mr-4 flex-shrink-0">
                                        <i class="fas fa-calendar-check text-green-600 text-lg sm:text-xl"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="font-semibold text-gray-900 text-sm sm:text-base">Availability Calendar</h3>
                                        <p class="text-xs sm:text-sm text-gray-600 truncate">Manage your schedule</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </template>

                    <template x-if="user?.user_type === 'ADMIN'">
                        <div>
                            <a href="#" @click="viewPendingAssignments()" 
                               class="block p-4 sm:p-6 bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-purple-100 rounded-lg flex items-center justify-center mr-3 sm:mr-4 flex-shrink-0">
                                        <i class="fas fa-tasks text-purple-600 text-lg sm:text-xl"></i>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="font-semibold text-gray-900 text-sm sm:text-base">Pending Assignments</h3>
                                        <p class="text-xs sm:text-sm text-gray-600 truncate">Manage service requests</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </template>

                    <div>
                        <a href="{{ url('/profile') }}" 
                           class="block p-4 sm:p-6 bg-white rounded-lg shadow hover:shadow-md transition-shadow">
                            <div class="flex items-center">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gray-100 rounded-lg flex items-center justify-center mr-3 sm:mr-4 flex-shrink-0">
                                    <i class="fas fa-user text-gray-600 text-lg sm:text-xl"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="font-semibold text-gray-900 text-sm sm:text-base">Update Profile</h3>
                                    <p class="text-xs sm:text-sm text-gray-600 truncate">Manage your account</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 lg:gap-6 mb-6 sm:mb-8">
                <template x-if="user?.user_type === 'CLIENT'">
                    <div>
                        <div class="bg-white rounded-lg shadow p-3 sm:p-4 lg:p-6">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2 sm:gap-3">
                                <div class="w-10 h-10 sm:w-12 sm:h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-calendar-alt text-blue-600 text-base sm:text-lg lg:text-xl"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs sm:text-sm font-medium text-gray-600 truncate">Active Requests</p>
                                    <p class="text-xl sm:text-2xl font-bold text-gray-900" x-text="stats?.active_requests || 0">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="user?.user_type === 'SERVICEMAN'">
                    <div>
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-briefcase text-green-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Active Jobs</p>
                                    <p class="text-2xl font-bold text-gray-900" x-text="stats?.active_jobs || 0">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="user?.user_type === 'ADMIN'">
                    <div>
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center">
                                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-clock text-purple-600 text-xl"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Pending Reviews</p>
                                    <p class="text-2xl font-bold text-gray-900" x-text="stats?.pending_reviews || 0">0</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-star text-yellow-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Average Rating</p>
                                <p class="text-2xl font-bold text-gray-900" x-text="stats?.average_rating || '4.8'">4.8</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-check-circle text-green-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Completed</p>
                                <p class="text-2xl font-bold text-gray-900" x-text="stats?.completed_services || 0">0</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-money-bill-wave text-blue-600 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Earnings</p>
                                <p class="text-2xl font-bold text-gray-900" x-text="formatCurrency(stats?.total_earnings || 0)">₦0</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Recent Service Requests -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Recent Activity</h3>
                    </div>
                    <div class="p-6">
                        <template x-if="recentActivity.length > 0">
                            <div class="space-y-4">
                                <template x-for="activity in recentActivity.slice(0, 5)" :key="activity.id">
                                    <div class="flex items-start space-x-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center"
                                             :class="getActivityIconClass(activity.type)">
                                            <i :class="getActivityIcon(activity.type)" class="text-sm"></i>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900" x-text="activity.title"></p>
                                            <p class="text-sm text-gray-600" x-text="activity.description"></p>
                                            <p class="text-xs text-gray-500 mt-1" x-text="formatDateTime(activity.created_at)"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <template x-if="recentActivity.length === 0">
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-history text-4xl mb-4"></i>
                                <p>No recent activity</p>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">This Month</h3>
                    </div>
                    <div class="p-6">
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Services Booked</span>
                                <span class="font-semibold" x-text="monthlyStats?.services_booked || 0">0</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Total Spent</span>
                                <span class="font-semibold" x-text="formatCurrency(monthlyStats?.total_spent || 0)">₦0</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Average Rating</span>
                                <span class="font-semibold" x-text="monthlyStats?.average_rating || '4.8'">4.8</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Response Time</span>
                                <span class="font-semibold" x-text="monthlyStats?.response_time || '< 2 hours'">< 2 hours</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function dashboard() {
    return {
        user: null,
        stats: {},
        monthlyStats: {},
        notifications: [],
        recentActivity: [],
        unreadNotifications: 0,
        loading: true,

        async init() {
            await this.loadUserData();
            await this.loadNotifications();
            await this.loadStats();
            await this.loadRecentActivity();
            this.loading = false;
        },

        async loadUserData() {
            try {
                const token = localStorage.getItem('auth_token');
                if (!token) {
                    window.location.href = '/login';
                    return;
                }

                const response = await fetch('/api/auth/me', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    this.user = await response.json();
                    // Don't redirect here - let the user stay on dashboard
                } else {
                    localStorage.removeItem('auth_token');
                    window.location.href = '/login';
                }
            } catch (error) {
                console.error('Error loading user data:', error);
                localStorage.removeItem('auth_token');
                window.location.href = '/login';
            }
        },

        async loadNotifications() {
            try {
                const token = localStorage.getItem('auth_token');
                const response = await fetch('/api/notifications', {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.notifications = data.data || [];
                    this.unreadNotifications = data.unread_count || 0;
                }
            } catch (error) {
                console.error('Error loading notifications:', error);
            }
        },

        async loadStats() {
            try {
                const token = localStorage.getItem('auth_token');
                const endpoint = this.user?.user_type === 'CLIENT' ? '/api/users/client-profile' : 
                                this.user?.user_type === 'SERVICEMAN' ? '/api/users/serviceman-profile' : 
                                '/api/admin/analytics/users';

                const response = await fetch(endpoint, {
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });

                if (response.ok) {
                    const data = await response.json();
                    this.stats = data.stats || {};
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        },

        async loadRecentActivity() {
            // For now, load demo data
            this.recentActivity = [
                {
                    id: 1,
                    type: 'service_request',
                    title: 'New service request created',
                    description: 'Electrical repair service requested',
                    created_at: new Date().toISOString()
                },
                {
                    id: 2,
                    type: 'payment',
                    title: 'Payment completed',
                    description: 'Payment of ₦5,000 received',
                    created_at: new Date(Date.now() - 3600000).toISOString()
                }
            ];
        },

        getWelcomeMessage() {
            if (!this.user) return '';
            
            switch (this.user.user_type) {
                case 'CLIENT':
                    return 'Ready to book your next service?';
                case 'SERVICEMAN':
                    return 'Check your assigned jobs and manage your schedule.';
                case 'ADMIN':
                    return 'Monitor platform activity and manage assignments.';
                default:
                    return 'Welcome to your dashboard!';
            }
        },

        getUserInitials() {
            if (!this.user) return 'U';
            const firstName = this.user.first_name || '';
            const lastName = this.user.last_name || '';
            return (firstName.charAt(0) + lastName.charAt(0)).toUpperCase() || this.user.username.charAt(0).toUpperCase();
        },

        getActivityIcon(type) {
            const iconMap = {
                'service_request': 'fas fa-plus',
                'payment': 'fas fa-credit-card',
                'completion': 'fas fa-check',
                'rating': 'fas fa-star',
                'message': 'fas fa-comment'
            };
            return iconMap[type] || 'fas fa-circle';
        },

        getActivityIconClass(type) {
            const classMap = {
                'service_request': 'bg-blue-100 text-blue-600',
                'payment': 'bg-green-100 text-green-600',
                'completion': 'bg-purple-100 text-purple-600',
                'rating': 'bg-yellow-100 text-yellow-600',
                'message': 'bg-gray-100 text-gray-600'
            };
            return classMap[type] || 'bg-gray-100 text-gray-600';
        },

        formatCurrency(amount) {
            return new Intl.NumberFormat('en-NG', {
                style: 'currency',
                currency: 'NGN'
            }).format(amount);
        },

        formatDateTime(date) {
            return new Intl.DateTimeFormat('en-US', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }).format(new Date(date));
        },

        async markAsRead(notificationId) {
            try {
                const token = localStorage.getItem('auth_token');
                await fetch(`/api/notifications/${notificationId}/read`, {
                    method: 'PATCH',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Accept': 'application/json'
                    }
                });
                
                // Update local state
                const notification = this.notifications.find(n => n.id === notificationId);
                if (notification) {
                    notification.is_read = true;
                }
                this.unreadNotifications = Math.max(0, this.unreadNotifications - 1);
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        },

        createServiceRequest() {
            window.location.href = '/services';
        },

        updateAvailability() {
            this.showNotification('Availability updated successfully!', 'success');
            // In a real app, this would make an API call to update availability
        },

        viewPendingAssignments() {
            // Redirect to service requests page to view pending assignments
            window.location.href = '/dashboard?tab=service-requests';
        },


        showNotification(message, type = 'info') {
            // Create a simple notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transform transition-all duration-300 translate-x-full`;
            
            // Set colors based on type
            const colors = {
                success: 'bg-green-500 text-white',
                error: 'bg-red-500 text-white',
                warning: 'bg-yellow-500 text-white',
                info: 'bg-blue-500 text-white'
            };
            
            notification.className += ` ${colors[type] || colors.info}`;
            notification.innerHTML = `
                <div class="flex items-center justify-between">
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Animate in
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => notification.remove(), 300);
            }, 5000);
        }
    }
}
</script>
@endpush
@endsection

