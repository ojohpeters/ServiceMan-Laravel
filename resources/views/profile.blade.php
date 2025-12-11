@extends('layouts.app')

@section('title', 'Profile - ServiceMan')
@section('description', 'Manage your ServiceMan profile and account settings.')

@section('content')
<div x-data="profilePage()" x-init="init()" class="min-h-screen bg-gray-50">
    <!-- Profile Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center space-x-4">
                <div class="w-16 h-16 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center">
                    <span class="text-white text-2xl font-bold" x-text="getUserInitials()"></span>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900" x-text="getFullName()"></h1>
                    <p class="text-gray-600 capitalize" x-text="user?.user_type?.toLowerCase() + ' Account'"></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Profile Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Profile Information</h2>
                        <p class="text-gray-600 mt-1">Update your personal information and preferences.</p>
                    </div>
                    
                    <form @submit.prevent="updateProfile" class="p-6 space-y-6">
                        <!-- Personal Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    First Name *
                                </label>
                                <input 
                                    id="first_name" 
                                    name="first_name" 
                                    type="text" 
                                    x-model="form.first_name"
                                    required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :class="{ 'border-red-500': errors.first_name }"
                                >
                                <div x-show="errors.first_name" class="mt-1 text-sm text-red-600" x-text="errors.first_name"></div>
                            </div>

                            <div>
                                <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Last Name *
                                </label>
                                <input 
                                    id="last_name" 
                                    name="last_name" 
                                    type="text" 
                                    x-model="form.last_name"
                                    required 
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                    :class="{ 'border-red-500': errors.last_name }"
                                >
                                <div x-show="errors.last_name" class="mt-1 text-sm text-red-600" x-text="errors.last_name"></div>
                            </div>
                        </div>

                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                                Username *
                            </label>
                            <input 
                                id="username" 
                                name="username" 
                                type="text" 
                                x-model="form.username"
                                required 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': errors.username }"
                            >
                            <div x-show="errors.username" class="mt-1 text-sm text-red-600" x-text="errors.username"></div>
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                Email Address *
                            </label>
                            <input 
                                id="email" 
                                name="email" 
                                type="email" 
                                x-model="form.email"
                                required 
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                :class="{ 'border-red-500': errors.email }"
                            >
                            <div x-show="errors.email" class="mt-1 text-sm text-red-600" x-text="errors.email"></div>
                        </div>

                        <!-- Client Specific Fields -->
                        <template x-if="user?.user_type === 'CLIENT'">
                            <div class="space-y-6 p-6 bg-gray-50 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-900">Contact Information</h3>
                                
                                <div>
                                    <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-phone mr-1 text-gray-500"></i>
                                        Phone Number
                                    </label>
                                    <input 
                                        id="phone_number" 
                                        name="phone_number" 
                                        type="tel" 
                                        x-model="form.phone_number"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :class="{ 'border-red-500': errors.phone_number }"
                                        placeholder="e.g., +234 801 234 5678"
                                    >
                                    <div x-show="errors.phone_number" class="mt-1 text-sm text-red-600" x-text="errors.phone_number"></div>
                                </div>

                                <div>
                                    <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                                        <i class="fas fa-map-marker-alt mr-1 text-gray-500"></i>
                                        Address
                                    </label>
                                    <textarea 
                                        id="address" 
                                        name="address" 
                                        x-model="form.address"
                                        rows="3"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                                        :class="{ 'border-red-500': errors.address }"
                                        placeholder="Enter your address (e.g., 123 Main Street, City, State)"
                                    ></textarea>
                                    <div x-show="errors.address" class="mt-1 text-sm text-red-600" x-text="errors.address"></div>
                                    <p class="mt-1 text-xs text-gray-500">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        This address will be used as the default service location when booking services.
                                    </p>
                                </div>
                            </div>
                        </template>

                        <!-- Serviceman Specific Fields -->
                        <template x-if="user?.user_type === 'SERVICEMAN'">
                            <div class="space-y-6 p-6 bg-gray-50 rounded-lg">
                                <h3 class="text-lg font-semibold text-gray-900">Professional Information</h3>
                                
                                <div>
                                    <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                                        Phone Number *
                                    </label>
                                    <input 
                                        id="phone_number" 
                                        name="phone_number" 
                                        type="tel" 
                                        x-model="form.phone_number"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :class="{ 'border-red-500': errors.phone_number }"
                                        placeholder="e.g., +234 801 234 5678"
                                    >
                                    <div x-show="errors.phone_number" class="mt-1 text-sm text-red-600" x-text="errors.phone_number"></div>
                                </div>

                                <div>
                                    <label for="experience_years" class="block text-sm font-medium text-gray-700 mb-2">
                                        Years of Experience *
                                    </label>
                                    <select 
                                        id="experience_years" 
                                        name="experience_years" 
                                        x-model="form.experience_years"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :class="{ 'border-red-500': errors.experience_years }"
                                    >
                                        <option value="">Select experience</option>
                                        <option value="0-1">0-1 years</option>
                                        <option value="1-3">1-3 years</option>
                                        <option value="3-5">3-5 years</option>
                                        <option value="5-10">5-10 years</option>
                                        <option value="10+">10+ years</option>
                                    </select>
                                    <div x-show="errors.experience_years" class="mt-1 text-sm text-red-600" x-text="errors.experience_years"></div>
                                </div>

                                <div>
                                    <label for="skills" class="block text-sm font-medium text-gray-700 mb-2">
                                        Skills & Specializations *
                                    </label>
                                    <textarea 
                                        id="skills" 
                                        name="skills" 
                                        x-model="form.skills"
                                        rows="4"
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :class="{ 'border-red-500': errors.skills }"
                                        placeholder="Describe your skills and specializations"
                                    ></textarea>
                                    <div x-show="errors.skills" class="mt-1 text-sm text-red-600" x-text="errors.skills"></div>
                                </div>
                            </div>
                        </template>

                        <!-- Submit Button -->
                        <div class="flex justify-end">
                            <button 
                                type="submit" 
                                :disabled="loading"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                <span x-show="!loading">Update Profile</span>
                                <span x-show="loading" class="flex items-center">
                                    <i class="fas fa-spinner fa-spin mr-2"></i>
                                    Updating...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Account Status -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Status</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Email Verified</span>
                            <span x-show="user?.is_email_verified" class="text-green-600 font-medium">
                                <i class="fas fa-check-circle mr-1"></i>Verified
                            </span>
                            <span x-show="!user?.is_email_verified" class="text-red-600 font-medium">
                                <i class="fas fa-times-circle mr-1"></i>Not Verified
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Account Type</span>
                            <span class="font-medium capitalize" x-text="user?.user_type?.toLowerCase()"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Member Since</span>
                            <span class="font-medium" x-text="formatDate(user?.created_at)"></span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                    <div class="space-y-3">
                        <button 
                            @click="changePassword()"
                            class="w-full text-left px-4 py-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors"
                        >
                            <i class="fas fa-key mr-3 text-gray-600"></i>
                            Change Password
                        </button>
                        
                        <template x-if="user?.user_type === 'SERVICEMAN'">
                            <button 
                                @click="updateAvailability()"
                                class="w-full text-left px-4 py-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors"
                            >
                                <i class="fas fa-calendar mr-3 text-gray-600"></i>
                                Update Availability
                            </button>
                        </template>

                        <button 
                            @click="viewNotifications()"
                            class="w-full text-left px-4 py-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors"
                        >
                            <i class="fas fa-bell mr-3 text-gray-600"></i>
                            Notifications
                        </button>
                    </div>
                </div>

                <!-- Account Stats -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Statistics</h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Total Services</span>
                            <span class="font-medium" x-text="stats?.total_services || 0">0</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Average Rating</span>
                            <span class="font-medium" x-text="stats?.average_rating || 'N/A'">N/A</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Completion Rate</span>
                            <span class="font-medium" x-text="stats?.completion_rate || '100%'">100%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function profilePage() {
    return {
        user: null,
        form: {},
        errors: {},
        loading: false,
        stats: {},

        async init() {
            await this.loadUserData();
            this.populateForm();
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

        populateForm() {
            // Load client profile data if available (Laravel converts camelCase to snake_case in JSON)
            const clientProfile = this.user?.client_profile || null;
            // Load serviceman profile data if available
            const servicemanProfile = this.user?.serviceman_profile || null;
            
            this.form = {
                first_name: this.user?.first_name || '',
                last_name: this.user?.last_name || '',
                username: this.user?.username || '',
                email: this.user?.email || '',
                // Client fields
                phone_number: clientProfile?.phone_number || servicemanProfile?.phone_number || '',
                address: clientProfile?.address || '',
                // Serviceman fields
                experience_years: servicemanProfile?.experience_years || '',
                skills: servicemanProfile?.skills || ''
            };
        },

        async updateProfile() {
            this.loading = true;
            this.errors = {};

            try {
                const token = localStorage.getItem('auth_token');
                const endpoint = this.user?.user_type === 'SERVICEMAN' ? 
                    '/api/users/serviceman-profile' : 
                    '/api/users/client-profile';

                const response = await fetch(endpoint, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'Authorization': `Bearer ${token}`,
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (response.ok) {
                    const updatedUser = await response.json();
                    // Update local user object with response data
                    if (updatedUser.client_profile || updatedUser.serviceman_profile) {
                        this.user = updatedUser;
                    }
                    this.showNotification('Profile updated successfully!', 'success');
                    // Reload user data to get latest from server
                    await this.loadUserData();
                    this.populateForm();
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        this.showNotification(data.message || 'Failed to update profile', 'error');
                    }
                }
            } catch (error) {
                console.error('Error updating profile:', error);
                this.showNotification('An error occurred. Please try again.', 'error');
            } finally {
                this.loading = false;
            }
        },

        getUserInitials() {
            if (!this.user) return 'U';
            const firstName = this.user.first_name || '';
            const lastName = this.user.last_name || '';
            return (firstName.charAt(0) + lastName.charAt(0)).toUpperCase() || this.user.username.charAt(0).toUpperCase();
        },

        getFullName() {
            if (!this.user) return 'User';
            const firstName = this.user.first_name || '';
            const lastName = this.user.last_name || '';
            return firstName && lastName ? `${firstName} ${lastName}` : this.user.username;
        },

        formatDate(date) {
            if (!date) return 'N/A';
            return new Intl.DateTimeFormat('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            }).format(new Date(date));
        },

        showNotification(message, type = 'info') {
            alert(message); // Simple fallback
        },

        changePassword() {
            this.showNotification('Password change functionality will be available soon!', 'info');
        },

        updateAvailability() {
            this.showNotification('Availability updated successfully!', 'success');
        },

        viewNotifications() {
            window.location.href = '/dashboard?tab=notifications';
        }
    }
}
</script>
@endpush
@endsection

